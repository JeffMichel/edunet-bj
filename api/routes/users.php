<?php
// Routes utilisateurs
global $input, $method, $uri;

$db = Database::getConnection();
$user = require_auth();
$sub_uri = str_replace('/users', '', $uri);

if ($method === 'GET' && $sub_uri === '/me') {
    json_success("Profil récupéré", [
        'user' => [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'matricule' => $user['matricule'],
            'role' => $user['role'],
            'classe' => $user['classe'],
            'matiere' => $user['matiere'],
            'avatar' => $user['avatar'],
            'statut' => $user['statut']
        ]
    ]);
}

if ($method === 'GET' && ($sub_uri === '' || $sub_uri === '/')) {
    // Liste des enseignants et censeurs actifs (pour formulaires)
    $stmt = $db->query("SELECT id, nom, prenom, role, matiere FROM users WHERE role IN ('enseignant', 'censeur', 'admin') AND statut = 'actif'");
    $staff = $stmt->fetchAll();
    json_success("Personnel récupéré", $staff);
}

if ($method === 'PUT' && $sub_uri === '/me') {
    $nom = trim($input['nom'] ?? $user['nom']);
    $prenom = trim($input['prenom'] ?? $user['prenom']);

    if (empty($nom) || empty($prenom)) {
        json_error("Le nom et le prénom ne peuvent pas être vides.");
    }

    $avatar_url = $user['avatar'];
    if (isset($_FILES['avatar'])) {
        $upload_result = upload_file($_FILES['avatar'], 'avatars');
        if (!$upload_result['success']) {
            json_error($upload_result['message']);
        }
        $avatar_url = $upload_result['url'];
    }

    $stmt = $db->prepare("UPDATE users SET nom = ?, prenom = ?, avatar = ? WHERE id = ?");
    $stmt->execute([$nom, $prenom, $avatar_url, $user['id']]);

    json_success("Profil mis à jour avec succès", [
        'user' => [
            'id' => $user['id'],
            'nom' => $nom,
            'prenom' => $prenom,
            'matricule' => $user['matricule'],
            'role' => $user['role'],
            'classe' => $user['classe'],
            'matiere' => $user['matiere'],
            'avatar' => $avatar_url,
            'statut' => $user['statut']
        ]
    ]);
}

if ($method === 'PUT' && $sub_uri === '/me/password') {
    $current_password = trim($input['current_password'] ?? '');
    $new_password = trim($input['new_password'] ?? '');

    if (empty($current_password) || empty($new_password)) {
        json_error("Le mot de passe actuel et le nouveau mot de passe sont requis.");
    }

    if (strlen($new_password) < 6) {
        json_error("Le nouveau mot de passe doit contenir au moins 6 caractères.");
    }

    if ($new_password === '000000') {
        json_error("Le mot de passe ne peut pas être '000000'.");
    }

    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $db_pass = $stmt->fetchColumn();

    if (!password_verify($current_password, $db_pass)) {
        json_error("Le mot de passe actuel est incorrect.");
    }

    $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$new_hashed_password, $user['id']]);

    json_success("Mot de passe modifié avec succès.");
}

json_error("Route utilisateur introuvable", 404);
