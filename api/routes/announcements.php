<?php
// Routes d'annonces
global $input, $method, $uri;

$db = Database::getConnection();
$user = require_auth();
$sub_uri = str_replace('/announcements', '', $uri);

if ($method === 'GET' && ($sub_uri === '' || $sub_uri === '/')) {
    if ($user['role'] === 'eleve') {
        $stmt = $db->prepare("SELECT a.*, u.nom as auteur_nom, u.prenom as auteur_prenom, u.avatar as auteur_avatar FROM announcements a JOIN users u ON a.auteur_id = u.id WHERE a.classe_cible = ? OR a.classe_cible IS NULL ORDER BY a.created_at DESC");
        $stmt->execute([$user['classe']]);
    } else {
        $stmt = $db->query("SELECT a.*, u.nom as auteur_nom, u.prenom as auteur_prenom, u.avatar as auteur_avatar FROM announcements a JOIN users u ON a.auteur_id = u.id ORDER BY a.created_at DESC");
    }
    $announcements = $stmt->fetchAll();
    json_success("Annonces récupérées", $announcements);
}

if ($method === 'POST' && ($sub_uri === '' || $sub_uri === '/')) {
    require_role(['enseignant', 'admin']);
    
    $titre = trim($input['titre'] ?? '');
    $contenu = trim($input['contenu'] ?? '');
    $classe_cible = isset($input['classe_cible']) && trim($input['classe_cible']) !== '' ? trim($input['classe_cible']) : null;

    if (empty($titre) || empty($contenu)) {
        json_error("Le titre et le contenu sont requis.");
    }

    $stmt = $db->prepare("INSERT INTO announcements (titre, contenu, auteur_id, classe_cible) VALUES (?, ?, ?, ?)");
    $stmt->execute([$titre, $contenu, $user['id'], $classe_cible]);

    json_success("Annonce créée avec succès.", null, 201);
}

if (preg_match('/^\/(\d+)$/', $sub_uri, $matches)) {
    $id = intval($matches[1]);

    $stmt = $db->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $announcement = $stmt->fetch();

    if (!$announcement) {
        json_error("Annonce non trouvée.", 404);
    }

    if ($method === 'PUT') {
        if ($user['role'] !== 'admin' && $announcement['auteur_id'] !== $user['id']) {
            json_error("Vous n'êtes pas autorisé à modifier cette annonce.", 403);
        }

        $titre = trim($input['titre'] ?? $announcement['titre']);
        $contenu = trim($input['contenu'] ?? $announcement['contenu']);
        $classe_cible = isset($input['classe_cible']) && trim($input['classe_cible']) !== '' ? trim($input['classe_cible']) : null;

        if (empty($titre) || empty($contenu)) {
            json_error("Le titre et le contenu ne peuvent pas être vides.");
        }

        $stmt = $db->prepare("UPDATE announcements SET titre = ?, contenu = ?, classe_cible = ? WHERE id = ?");
        $stmt->execute([$titre, $contenu, $classe_cible, $id]);

        json_success("Annonce mise à jour avec succès.");
    }

    if ($method === 'DELETE') {
        if ($user['role'] !== 'admin' && $announcement['auteur_id'] !== $user['id']) {
            json_error("Vous n'êtes pas autorisé à supprimer cette annonce.", 403);
        }

        $stmt = $db->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$id]);

        json_success("Annonce supprimée avec succès.");
    }
}

json_error("Route d'annonce introuvable", 404);
