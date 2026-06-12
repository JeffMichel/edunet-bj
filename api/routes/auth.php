<?php
// Routes d'authentification
global $input, $method, $uri;

$db = Database::getConnection();
$sub_uri = str_replace('/auth', '', $uri);

// Connexion
if ($method === 'POST' && $sub_uri === '/login') {
    $matricule = strtoupper(trim($input['matricule'] ?? ''));
    $password = trim($input['password'] ?? '');

    if (empty($matricule) || empty($password)) {
        json_error("Matricule et mot de passe requis.");
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE matricule = ?");
    $stmt->execute([$matricule]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        json_error("Matricule ou mot de passe incorrect.", 401);
    }

    if ($user['statut'] === 'suspendu') {
        json_error("Votre compte a été suspendu. Veuillez contacter l'administration.", 403);
    }

    $payload = [
        'id' => $user['id'],
        'matricule' => $user['matricule'],
        'role' => $user['role'],
        'premier_connexion' => intval($user['premier_connexion'])
    ];

    $jwt = jwt_generate($payload);
    $refresh_token = bin2hex(random_bytes(40));
    $expires_at = date('Y-m-d H:i:s', time() + REFRESH_TOKEN_EXPIRES_IN);

    $stmt = $db->prepare("INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $refresh_token, $expires_at]);

    json_success("Connexion réussie", [
        'token' => $jwt,
        'refresh_token' => $refresh_token,
        'user' => [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'matricule' => $user['matricule'],
            'role' => $user['role'],
            'classe' => $user['classe'],
            'matiere' => $user['matiere'],
            'avatar' => $user['avatar'],
            'premier_connexion' => intval($user['premier_connexion'])
        ]
    ]);
}

// Changement obligatoire de mot de passe à la première connexion
if ($method === 'POST' && $sub_uri === '/change-password') {
    $user = require_auth();
    $new_password = trim($input['new_password'] ?? '');
    $confirm_password = trim($input['confirm_password'] ?? '');

    if (empty($new_password) || empty($confirm_password)) {
        json_error("Le nouveau mot de passe et sa confirmation sont requis.");
    }

    if (strlen($new_password) < 6) {
        json_error("Le nouveau mot de passe doit contenir au moins 6 caractères.");
    }

    if ($new_password === '000000') {
        json_error("Le mot de passe ne peut pas être '000000'.");
    }

    if ($new_password !== $confirm_password) {
        json_error("La confirmation du mot de passe ne correspond pas.");
    }

    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("UPDATE users SET password = ?, premier_connexion = 0 WHERE id = ?");
    $stmt->execute([$hashed_password, $user['id']]);

    json_success("Votre mot de passe a été modifié avec succès.");
}

// Rafraîchir l'access token
if ($method === 'POST' && $sub_uri === '/refresh') {
    $refresh_token = trim($input['refresh_token'] ?? '');

    if (empty($refresh_token)) {
        json_error("Refresh token manquant.", 400);
    }

    $stmt = $db->prepare("SELECT r.*, u.nom, u.prenom, u.matricule, u.role, u.classe, u.matiere, u.statut, u.avatar, u.premier_connexion FROM refresh_tokens r JOIN users u ON r.user_id = u.id WHERE r.token = ? AND r.expires_at > NOW()");
    $stmt->execute([$refresh_token]);
    $session = $stmt->fetch();

    if (!$session) {
        json_error("Session expirée ou invalide. Veuillez vous reconnecter.", 401);
    }

    if ($session['statut'] !== 'actif') {
        json_error("Compte inactif ou suspendu.", 403);
    }

    $payload = [
        'id' => $session['user_id'],
        'matricule' => $session['matricule'],
        'role' => $session['role'],
        'premier_connexion' => intval($session['premier_connexion'])
    ];
    
    $new_jwt = jwt_generate($payload);
    $new_refresh_token = bin2hex(random_bytes(40));
    $new_expires_at = date('Y-m-d H:i:s', time() + REFRESH_TOKEN_EXPIRES_IN);

    $db->beginTransaction();
    $stmt = $db->prepare("DELETE FROM refresh_tokens WHERE id = ?");
    $stmt->execute([$session['id']]);

    $stmt = $db->prepare("INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$session['user_id'], $new_refresh_token, $new_expires_at]);
    $db->commit();

    json_success("Token rafraîchi avec succès", [
        'token' => $new_jwt,
        'refresh_token' => $new_refresh_token
    ]);
}

// Déconnexion
if ($method === 'POST' && $sub_uri === '/logout') {
    $user = require_auth();
    $refresh_token = trim($input['refresh_token'] ?? '');

    if (!empty($refresh_token)) {
        $stmt = $db->prepare("DELETE FROM refresh_tokens WHERE token = ? AND user_id = ?");
        $stmt->execute([$refresh_token, $user['id']]);
    }

    json_success("Déconnexion réussie");
}

json_error("Route d'authentification introuvable", 404);
