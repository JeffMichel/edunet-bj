<?php
// Routes des notes scolaires
global $input, $method, $uri;

$db = Database::getConnection();
$user = require_auth();
$sub_uri = str_replace('/grades', '', $uri);

// GET /grades — liste selon le rôle
if ($method === 'GET' && ($sub_uri === '' || $sub_uri === '/')) {
    if (in_array($user['role'], ['admin', 'censeur'])) {
        $stmt = $db->query("SELECT g.*, u.nom as eleve_nom, u.prenom as eleve_prenom, e.nom as enseignant_nom, e.prenom as enseignant_prenom FROM grades g JOIN users u ON g.eleve_id = u.id JOIN users e ON g.enseignant_id = e.id ORDER BY g.created_at DESC");
        json_success("Notes récupérées", $stmt->fetchAll());
    } elseif ($user['role'] === 'eleve') {
        $stmt = $db->prepare("SELECT g.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom FROM grades g JOIN users u ON g.enseignant_id = u.id WHERE g.eleve_id = ? ORDER BY g.created_at DESC");
        $stmt->execute([$user['id']]);
        json_success("Vos notes", $stmt->fetchAll());
    } elseif ($user['role'] === 'enseignant') {
        $stmt = $db->prepare("SELECT g.*, u.nom as eleve_nom, u.prenom as eleve_prenom FROM grades g JOIN users u ON g.eleve_id = u.id WHERE g.enseignant_id = ? ORDER BY g.created_at DESC");
        $stmt->execute([$user['id']]);
        json_success("Notes saisies", $stmt->fetchAll());
    } else {
        json_error("Accès non autorisé.", 403);
    }
}

// GET /grades/me — notes de l'élève connecté
if ($method === 'GET' && $sub_uri === '/me') {
    require_role('eleve');

    $stmt = $db->prepare("
        SELECT g.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom
        FROM grades g
        JOIN users u ON g.enseignant_id = u.id
        WHERE g.eleve_id = ?
        ORDER BY g.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $grades = $stmt->fetchAll();

    json_success("Notes récupérées", $grades);
}

if ($method === 'POST' && ($sub_uri === '' || $sub_uri === '/')) {
    require_role('enseignant');

    $eleve_id = isset($input['eleve_id']) ? intval($input['eleve_id']) : null;
    $matiere = trim($input['matiere'] ?? $user['matiere'] ?? '');
    $note = isset($input['note']) ? floatval($input['note']) : null;
    $appreciation = trim($input['appreciation'] ?? '');
    $trimestre = trim($input['trimestre'] ?? '1');
    $annee_scolaire = trim($input['annee_scolaire'] ?? '2025-2026');

    if (empty($eleve_id) || empty($matiere) || $note === null || empty($trimestre) || empty($annee_scolaire)) {
        json_error("Veuillez remplir tous les champs obligatoires (eleve_id, matiere, note, trimestre, annee_scolaire).");
    }

    if ($note < 0 || $note > 20) {
        json_error("La note doit être comprise entre 0 et 20.");
    }

    if (!in_array($trimestre, ['1', '2', '3'])) {
        json_error("Trimestre invalide (doit être 1, 2 ou 3).");
    }

    $stmt = $db->prepare("SELECT id, role FROM users WHERE id = ?");
    $stmt->execute([$eleve_id]);
    $eleve = $stmt->fetch();

    if (!$eleve || $eleve['role'] !== 'eleve') {
        json_error("L'élève spécifié n'existe pas.");
    }

    $stmt = $db->prepare("
        INSERT INTO grades (eleve_id, enseignant_id, matiere, note, appreciation, trimestre, annee_scolaire)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$eleve_id, $user['id'], $matiere, $note, $appreciation, $trimestre, $annee_scolaire]);

    json_success("Note ajoutée avec succès.", null, 201);
}

if (preg_match('/^\/(\d+)$/', $sub_uri, $matches)) {
    $id_val = intval($matches[1]);

    if ($method === 'GET') {
        require_role(['enseignant', 'censeur', 'admin']);
        $stmt = $db->prepare("
            SELECT g.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom
            FROM grades g
            JOIN users u ON g.enseignant_id = u.id
            WHERE g.eleve_id = ?
            ORDER BY g.created_at DESC
        ");
        $stmt->execute([$id_val]);
        $grades = $stmt->fetchAll();
        json_success("Notes de l'élève récupérées", $grades);
    }

    if ($method === 'PUT') {
        require_role('enseignant');
        
        $stmt = $db->prepare("SELECT * FROM grades WHERE id = ?");
        $stmt->execute([$id_val]);
        $grade = $stmt->fetch();

        if (!$grade) {
            json_error("Note non trouvée.", 404);
        }

        if ($grade['enseignant_id'] !== $user['id'] && $user['role'] !== 'admin') {
            json_error("Vous n'êtes pas autorisé à modifier cette note.", 403);
        }

        $note = isset($input['note']) ? floatval($input['note']) : $grade['note'];
        $appreciation = isset($input['appreciation']) ? trim($input['appreciation']) : $grade['appreciation'];
        $trimestre = isset($input['trimestre']) ? trim($input['trimestre']) : $grade['trimestre'];
        $annee_scolaire = isset($input['annee_scolaire']) ? trim($input['annee_scolaire']) : $grade['annee_scolaire'];

        if ($note < 0 || $note > 20) {
            json_error("La note doit être comprise entre 0 et 20.");
        }

        $stmt = $db->prepare("
            UPDATE grades SET note = ?, appreciation = ?, trimestre = ?, annee_scolaire = ?
            WHERE id = ?
        ");
        $stmt->execute([$note, $appreciation, $trimestre, $annee_scolaire, $id_val]);

        json_success("Note mise à jour avec succès.");
    }
}

json_error("Route de note introuvable", 404);
