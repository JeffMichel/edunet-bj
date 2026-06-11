<?php
// Routes des devoirs et soumissions
global $input, $method, $uri;

$db = Database::getConnection();
$user = require_auth();
$sub_uri = str_replace('/assignments', '', $uri);

if ($method === 'GET' && ($sub_uri === '' || $sub_uri === '/')) {
    if ($user['role'] === 'eleve') {
        $stmt = $db->prepare("
            SELECT a.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
                   s.id as submission_id, s.fichier_url as submission_fichier, s.note, s.commentaire, s.submitted_at
            FROM assignments a
            JOIN users u ON a.enseignant_id = u.id
            LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.eleve_id = ?
            WHERE a.classe = ?
            ORDER BY a.date_limite ASC
        ");
        $stmt->execute([$user['id'], $user['classe']]);
    } else {
        $stmt = $db->prepare("
            SELECT a.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
                   (SELECT COUNT(*) FROM assignment_submissions WHERE assignment_id = a.id) as submissions_count
            FROM assignments a
            JOIN users u ON a.enseignant_id = u.id
            WHERE u.id = ? OR ? = 'admin'
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$user['id'], $user['role']]);
    }
    $assignments = $stmt->fetchAll();
    json_success("Devoirs récupérés", $assignments);
}

if ($method === 'POST' && ($sub_uri === '' || $sub_uri === '/')) {
    require_role('enseignant');

    $titre = trim($input['titre'] ?? '');
    $description = trim($input['description'] ?? '');
    $classe = trim($input['classe'] ?? '');
    $matiere = trim($input['matiere'] ?? $user['matiere'] ?? '');
    $date_limite = trim($input['date_limite'] ?? '');

    if (empty($titre) || empty($description) || empty($classe) || empty($matiere) || empty($date_limite)) {
        json_error("Veuillez remplir tous les champs (titre, description, classe, matiere, date_limite).");
    }

    $fichier_url = null;
    if (isset($_FILES['fichier'])) {
        $upload_result = upload_file($_FILES['fichier'], 'assignments');
        if (!$upload_result['success']) {
            json_error($upload_result['message']);
        }
        $fichier_url = $upload_result['url'];
    }

    $stmt = $db->prepare("INSERT INTO assignments (titre, description, fichier_url, enseignant_id, classe, matiere, date_limite) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $description, $fichier_url, $user['id'], $classe, $matiere, $date_limite]);

    json_success("Devoir créé avec succès.", null, 201);
}

if (preg_match('/^\/(\d+)\/submit$/', $sub_uri, $matches)) {
    $id = intval($matches[1]);
    require_role('eleve');

    $stmt = $db->prepare("SELECT * FROM assignments WHERE id = ?");
    $stmt->execute([$id]);
    $assignment = $stmt->fetch();

    if (!$assignment) {
        json_error("Devoir non trouvé.", 404);
    }

    if (strtotime($assignment['date_limite']) < time()) {
        json_error("La date limite de rendu pour ce devoir est dépassée.");
    }

    $stmt = $db->prepare("SELECT id FROM assignment_submissions WHERE assignment_id = ? AND eleve_id = ?");
    $stmt->execute([$id, $user['id']]);
    if ($stmt->fetch()) {
        json_error("Vous avez déjà soumis un travail pour ce devoir.");
    }

    if (!isset($_FILES['fichier'])) {
        json_error("Le fichier du devoir est requis pour la soumission.");
    }

    $upload_result = upload_file($_FILES['fichier'], 'assignments');
    if (!$upload_result['success']) {
        json_error($upload_result['message']);
    }

    $fichier_url = $upload_result['url'];

    $stmt = $db->prepare("INSERT INTO assignment_submissions (assignment_id, eleve_id, fichier_url) VALUES (?, ?, ?)");
    $stmt->execute([$id, $user['id'], $fichier_url]);

    json_success("Votre travail a été soumis avec succès.", null, 201);
}

if (preg_match('/^\/(\d+)\/submissions$/', $sub_uri, $matches)) {
    $id = intval($matches[1]);
    require_role('enseignant');

    $stmt = $db->prepare("SELECT * FROM assignments WHERE id = ?");
    $stmt->execute([$id]);
    $assignment = $stmt->fetch();

    if (!$assignment) {
        json_error("Devoir non trouvé.", 404);
    }

    if ($assignment['enseignant_id'] !== $user['id'] && $user['role'] !== 'admin') {
        json_error("Accès non autorisé aux soumissions de ce devoir.", 403);
    }

    $stmt = $db->prepare("
        SELECT s.*, u.nom, u.prenom, u.email
        FROM assignment_submissions s
        JOIN users u ON s.eleve_id = u.id
        WHERE s.assignment_id = ?
        ORDER BY s.submitted_at DESC
    ");
    $stmt->execute([$id]);
    $submissions = $stmt->fetchAll();

    json_success("Soumissions récupérées", [
        'assignment' => $assignment,
        'submissions' => $submissions
    ]);
}

if (preg_match('/^\/(\d+)\/submissions\/(\d+)$/', $sub_uri, $matches)) {
    $id = intval($matches[1]);
    $sid = intval($matches[2]);
    require_role('enseignant');

    $stmt = $db->prepare("SELECT * FROM assignments WHERE id = ?");
    $stmt->execute([$id]);
    $assignment = $stmt->fetch();

    if (!$assignment) {
        json_error("Devoir non trouvé.", 404);
    }

    if ($assignment['enseignant_id'] !== $user['id'] && $user['role'] !== 'admin') {
        json_error("Accès non autorisé.", 403);
    }

    $stmt = $db->prepare("SELECT * FROM assignment_submissions WHERE id = ? AND assignment_id = ?");
    $stmt->execute([$sid, $id]);
    $submission = $stmt->fetch();

    if (!$submission) {
        json_error("Soumission non trouvée.", 404);
    }

    $note = isset($input['note']) ? floatval($input['note']) : null;
    $commentaire = isset($input['commentaire']) ? trim($input['commentaire']) : null;

    if ($note === null || $note < 0 || $note > 20) {
        json_error("Note invalide (doit être comprise entre 0 et 20).");
    }

    $db->beginTransaction();
    $stmt = $db->prepare("UPDATE assignment_submissions SET note = ?, commentaire = ? WHERE id = ?");
    $stmt->execute([$note, $commentaire, $sid]);

    $annee = '2025-2026';
    $trimestre = '1';
    $appreciation = "Devoir: " . $assignment['titre'] . (!empty($commentaire) ? " - " . $commentaire : "");

    $stmt = $db->prepare("INSERT INTO grades (eleve_id, enseignant_id, matiere, note, appreciation, trimestre, annee_scolaire) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$submission['eleve_id'], $user['id'], $assignment['matiere'], $note, $appreciation, $trimestre, $annee]);
    $db->commit();

    json_success("Soumission notée avec succès.");
}

json_error("Route de devoir introuvable", 404);
