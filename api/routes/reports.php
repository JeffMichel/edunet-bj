<?php
// Routes des signalements (discipline)
global $input, $method, $uri;

$db = Database::getConnection();
$user = require_auth();
$sub_uri = str_replace('/reports', '', $uri);

if ($method === 'GET' && ($sub_uri === '' || $sub_uri === '/')) {
    require_role(['censeur', 'admin']);

    $stmt = $db->query("
        SELECT r.*,
               u1.nom as signaleur_nom, u1.prenom as signaleur_prenom, u1.role as signaleur_role,
               u2.nom as cible_nom, u2.prenom as cible_prenom, u2.role as cible_role, u2.classe as cible_classe
        FROM reports r
        JOIN users u1 ON r.signaleur_id = u1.id
        JOIN users u2 ON r.cible_id = u2.id
        ORDER BY r.created_at DESC
    ");
    $reports = $stmt->fetchAll();

    json_success("Signalements récupérés", $reports);
}

if ($method === 'POST' && ($sub_uri === '' || $sub_uri === '/')) {
    $cible_id = isset($input['cible_id']) ? intval($input['cible_id']) : null;
    $motif = trim($input['motif'] ?? '');

    if (empty($cible_id) || empty($motif)) {
        json_error("L'utilisateur cible et le motif du signalement sont requis.");
    }

    $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$cible_id]);
    if (!$stmt->fetch()) {
        json_error("L'utilisateur cible n'existe pas.");
    }

    $stmt = $db->prepare("INSERT INTO reports (signaleur_id, cible_id, motif, statut) VALUES (?, ?, ?, 'ouvert')");
    $stmt->execute([$user['id'], $cible_id, $motif]);

    json_success("Signalement envoyé avec succès.", null, 201);
}

if (preg_match('/^\/(\d+)$/', $sub_uri, $matches)) {
    $id = intval($matches[1]);

    if ($method === 'PUT') {
        require_role(['censeur', 'admin']);

        $stmt = $db->prepare("SELECT * FROM reports WHERE id = ?");
        $stmt->execute([$id]);
        $report = $stmt->fetch();

        if (!$report) {
            json_error("Signalement non trouvé.", 404);
        }

        $statut = trim($input['statut'] ?? '');
        $statuts_valides = ['ouvert', 'traite', 'ferme'];
        if (!in_array($statut, $statuts_valides)) {
            json_error("Statut invalide. Doit être ouvert, traite, ou ferme.");
        }

        $stmt = $db->prepare("UPDATE reports SET statut = ? WHERE id = ?");
        $stmt->execute([$statut, $id]);

        json_success("Statut du signalement mis à jour avec succès.");
    }
}

json_error("Route de signalement introuvable", 404);
