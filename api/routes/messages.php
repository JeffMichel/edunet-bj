<?php
// Routes de messagerie de classe
global $input, $method, $uri;

$db = Database::getConnection();
$user = require_auth();
$sub_uri = str_replace('/messages', '', $uri);

// GET /messages — liste selon le rôle
if ($method === 'GET' && ($sub_uri === '' || $sub_uri === '/')) {
    if (in_array($user['role'], ['admin', 'censeur'])) {
        $stmt = $db->query("SELECT m.*, u.nom, u.prenom, u.role, u.avatar FROM messages m JOIN users u ON m.expediteur_id = u.id ORDER BY m.created_at DESC LIMIT 200");
    } else {
        $classe = $user['classe'] ?? '';
        if (empty($classe)) json_error("Aucune classe associée à votre compte.", 400);
        $stmt = $db->prepare("SELECT m.*, u.nom, u.prenom, u.role, u.avatar FROM messages m JOIN users u ON m.expediteur_id = u.id WHERE m.classe = ? ORDER BY m.created_at ASC LIMIT 100");
        $stmt->execute([$classe]);
    }
    json_success("Messages récupérés", $stmt->fetchAll());
}

// GET /messages/{classe}
if ($method === 'GET' && preg_match('/^\/([^\/]+)$/', $sub_uri, $matches)) {
    $classe = urldecode($matches[1]);

    if ($user['role'] === 'eleve' && $user['classe'] !== $classe) {
        json_error("Accès interdit aux messages de cette classe.", 403);
    }

    $stmt = $db->prepare("
        SELECT m.*, u.nom, u.prenom, u.role, u.avatar
        FROM messages m
        JOIN users u ON m.expediteur_id = u.id
        WHERE m.classe = ?
        ORDER BY m.created_at ASC
        LIMIT 100
    ");
    $stmt->execute([$classe]);
    $messages = $stmt->fetchAll();

    json_success("Messages récupérés", $messages);
}

if ($method === 'POST' && ($sub_uri === '' || $sub_uri === '/')) {
    $classe = trim($input['classe'] ?? '');
    $contenu = trim($input['contenu'] ?? '');

    if (empty($classe) || empty($contenu)) {
        json_error("La classe et le contenu du message sont requis.");
    }

    if ($user['role'] === 'eleve' && $user['classe'] !== $classe) {
        json_error("Vous ne pouvez envoyer de messages que dans votre propre classe.", 403);
    }

    $stmt = $db->prepare("INSERT INTO messages (expediteur_id, classe, contenu) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $classe, $contenu]);

    json_success("Message envoyé avec succès.");
}

json_error("Route de messagerie introuvable", 404);
