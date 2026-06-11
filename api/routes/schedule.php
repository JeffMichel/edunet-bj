<?php
// Routes d'emploi du temps
global $input, $method, $uri;

$db = Database::getConnection();
$user = require_auth();
$sub_uri = str_replace('/schedule', '', $uri);

// GET /schedule — liste selon le rôle
if ($method === 'GET' && ($sub_uri === '' || $sub_uri === '/')) {
    if (in_array($user['role'], ['admin', 'censeur'])) {
        $stmt = $db->query("SELECT s.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom FROM schedule s JOIN users u ON s.enseignant_id = u.id ORDER BY s.classe, FIELD(s.jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'), s.heure_debut ASC");
    } else {
        $classe = $user['classe'] ?? '';
        if (empty($classe)) {
            // Pour les enseignants, on retourne tout leur emploi du temps
            $stmt = $db->prepare("SELECT s.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom FROM schedule s JOIN users u ON s.enseignant_id = u.id WHERE s.enseignant_id = ? ORDER BY FIELD(s.jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'), s.heure_debut ASC");
            $stmt->execute([$user['id']]);
        } else {
            $stmt = $db->prepare("SELECT s.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom FROM schedule s JOIN users u ON s.enseignant_id = u.id WHERE s.classe = ? ORDER BY FIELD(s.jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'), s.heure_debut ASC");
            $stmt->execute([$classe]);
        }
    }
    json_success("Emploi du temps récupéré", $stmt->fetchAll());
}

// GET /schedule/{classe}
if ($method === 'GET' && preg_match('/^\/([^\/]+)$/', $sub_uri, $matches)) {
    $classe = urldecode($matches[1]);

    $stmt = $db->prepare("
        SELECT s.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom
        FROM schedule s
        JOIN users u ON s.enseignant_id = u.id
        WHERE s.classe = ?
        ORDER BY FIELD(s.jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'), s.heure_debut ASC
    ");
    $stmt->execute([$classe]);
    $schedule = $stmt->fetchAll();

    json_success("Emploi du temps récupéré", $schedule);
}

if ($method === 'POST' && ($sub_uri === '' || $sub_uri === '/')) {
    require_role(['admin', 'enseignant']);

    $classe = trim($input['classe'] ?? '');
    $matiere = trim($input['matiere'] ?? '');
    $enseignant_id = isset($input['enseignant_id']) ? intval($input['enseignant_id']) : $user['id'];
    $jour = trim($input['jour'] ?? '');
    $heure_debut = trim($input['heure_debut'] ?? '');
    $heure_fin = trim($input['heure_fin'] ?? '');
    $salle = trim($input['salle'] ?? '');

    if (empty($classe) || empty($matiere) || empty($jour) || empty($heure_debut) || empty($heure_fin)) {
        json_error("Veuillez remplir les champs obligatoires (classe, matiere, jour, heure_debut, heure_fin).");
    }

    $jours_valides = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    if (!in_array(strtolower($jour), $jours_valides)) {
        json_error("Jour invalide. Doit être lundi, mardi, mercredi, jeudi, vendredi ou samedi.");
    }

    $stmt = $db->prepare("INSERT INTO schedule (classe, matiere, enseignant_id, jour, heure_debut, heure_fin, salle) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$classe, $matiere, $enseignant_id, strtolower($jour), $heure_debut, $heure_fin, $salle]);

    json_success("Créneau ajouté avec succès.", null, 201);
}

if (preg_match('/^\/(\d+)$/', $sub_uri, $matches)) {
    $id = intval($matches[1]);
    
    $stmt = $db->prepare("SELECT * FROM schedule WHERE id = ?");
    $stmt->execute([$id]);
    $slot = $stmt->fetch();

    if (!$slot) {
        json_error("Créneau d'emploi du temps non trouvé.", 404);
    }

    if ($method === 'PUT') {
        require_role('admin');

        $classe = trim($input['classe'] ?? $slot['classe']);
        $matiere = trim($input['matiere'] ?? $slot['matiere']);
        $enseignant_id = isset($input['enseignant_id']) ? intval($input['enseignant_id']) : $slot['enseignant_id'];
        $jour = trim($input['jour'] ?? $slot['jour']);
        $heure_debut = trim($input['heure_debut'] ?? $slot['heure_debut']);
        $heure_fin = trim($input['heure_fin'] ?? $slot['heure_fin']);
        $salle = trim($input['salle'] ?? $slot['salle']);

        $jours_valides = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
        if (!in_array(strtolower($jour), $jours_valides)) {
            json_error("Jour invalide.");
        }

        $stmt = $db->prepare("UPDATE schedule SET classe = ?, matiere = ?, enseignant_id = ?, jour = ?, heure_debut = ?, heure_fin = ?, salle = ? WHERE id = ?");
        $stmt->execute([$classe, $matiere, $enseignant_id, strtolower($jour), $heure_debut, $heure_fin, $salle, $id]);

        json_success("Créneau mis à jour avec succès.");
    }

    if ($method === 'DELETE') {
        require_role('admin');

        $stmt = $db->prepare("DELETE FROM schedule WHERE id = ?");
        $stmt->execute([$id]);

        json_success("Créneau supprimé avec succès.");
    }
}

json_error("Route d'emploi du temps introuvable", 404);
