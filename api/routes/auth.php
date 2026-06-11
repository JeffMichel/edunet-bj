<?php
// Routes d'authentification
global $input, $method, $uri;

$db = Database::getConnection();
$sub_uri = str_replace('/auth', '', $uri);

if ($method === 'POST' && $sub_uri === '/register') {
    $nom = trim($input['nom'] ?? '');
    $prenom = trim($input['prenom'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');
    $classe = trim($input['classe'] ?? '');

    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($classe)) {
        json_error("Veuillez remplir tous les champs obligatoires (nom, prenom, email, password, classe).");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_error("Format d'email invalide.");
    }

    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_error("Cet email est déjà utilisé.");
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (nom, prenom, email, password, role, classe, statut) VALUES (?, ?, ?, ?, 'eleve', ?, 'en_attente')");
    $stmt->execute([$nom, $prenom, $email, $hashed_password, $classe]);

    json_success("Inscription réussie. Votre compte est en attente d'approbation par l'administration.", null, 201);
}

if ($method === 'POST' && $sub_uri === '/login') {
    $email = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');

    if (empty($email) || empty($password)) {
        json_error("Email et mot de passe requis.");
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        json_error("Email ou mot de passe incorrect.", 401);
    }

    if ($user['statut'] === 'en_attente') {
        json_error("Votre compte est en attente de validation par l'administration.", 403);
    } elseif ($user['statut'] === 'suspendu') {
        json_error("Votre compte a été suspendu. Veuillez contacter l'administration.", 403);
    }

    $payload = [
        'id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role']
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
            'email' => $user['email'],
            'role' => $user['role'],
            'classe' => $user['classe'],
            'matiere' => $user['matiere'],
            'avatar' => $user['avatar']
        ]
    ]);
}

if ($method === 'POST' && $sub_uri === '/refresh') {
    $refresh_token = trim($input['refresh_token'] ?? '');

    if (empty($refresh_token)) {
        json_error("Refresh token manquant.", 400);
    }

    $stmt = $db->prepare("SELECT r.*, u.nom, u.prenom, u.email, u.role, u.classe, u.matiere, u.statut, u.avatar FROM refresh_tokens r JOIN users u ON r.user_id = u.id WHERE r.token = ? AND r.expires_at > NOW()");
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
        'email' => $session['email'],
        'role' => $session['role']
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
