<?php
// Routes d'administration
global $input, $method, $uri;

$db = Database::getConnection();
$user = require_role('admin');
$sub_uri = str_replace('/admin', '', $uri);

if ($method === 'GET' && $sub_uri === '/users') {
    $stmt = $db->query("SELECT id, nom, prenom, email, role, classe, matiere, avatar, statut, created_at FROM users ORDER BY role, nom, prenom ASC");
    $users = $stmt->fetchAll();
    json_success("Utilisateurs récupérés", $users);
}

if ($method === 'POST' && $sub_uri === '/users') {
    $nom = trim($input['nom'] ?? '');
    $prenom = trim($input['prenom'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');
    $matiere = trim($input['matiere'] ?? '');

    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($matiere)) {
        json_error("Veuillez remplir tous les champs (nom, prenom, email, password, matiere).");
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
    $stmt = $db->prepare("INSERT INTO users (nom, prenom, email, password, role, matiere, statut) VALUES (?, ?, ?, ?, 'enseignant', ?, 'actif')");
    $stmt->execute([$nom, $prenom, $email, $hashed_password, $matiere]);

    json_success("Compte enseignant créé avec succès.", null, 201);
}

if ($method === 'GET' && $sub_uri === '/stats') {
    $stmt = $db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $roles_count = $stmt->fetchAll();

    $stmt = $db->query("SELECT COUNT(*) FROM courses");
    $courses_count = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM assignments");
    $assignments_count = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM assignment_submissions");
    $submissions_count = $stmt->fetchColumn();

    $stmt = $db->query("SELECT statut, COUNT(*) as count FROM reports GROUP BY statut");
    $reports_count = $stmt->fetchAll();

    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE statut = 'en_attente'");
    $pending_count = $stmt->fetchColumn();

    json_success("Statistiques globales récupérées", [
        'roles' => $roles_count,
        'courses' => $courses_count,
        'assignments' => $assignments_count,
        'submissions' => $submissions_count,
        'reports' => $reports_count,
        'pending_users' => $pending_count
    ]);
}

if (preg_match('/^\/users\/(\d+)\/status$/', $sub_uri, $matches)) {
    $id = intval($matches[1]);

    if ($method === 'PUT') {
        $statut = trim($input['statut'] ?? '');
        $statuts_valides = ['en_attente', 'actif', 'suspendu'];
        
        if (!in_array($statut, $statuts_valides)) {
            json_error("Statut invalide. Doit être en_attente, actif ou suspendu.");
        }

        if ($id === $user['id']) {
            json_error("Vous ne pouvez pas modifier votre propre statut administrateur.");
        }

        $stmt = $db->prepare("UPDATE users SET statut = ? WHERE id = ?");
        $stmt->execute([$statut, $id]);

        json_success("Statut de l'utilisateur mis à jour avec succès.");
    }
}

if (preg_match('/^\/users\/(\d+)$/', $sub_uri, $matches)) {
    $id = intval($matches[1]);

    if ($method === 'DELETE') {
        if ($id === $user['id']) {
            json_error("Vous ne pouvez pas supprimer votre propre compte administrateur.");
        }

        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        json_success("Utilisateur supprimé avec succès.");
    }
}

json_error("Route administrative introuvable", 404);
