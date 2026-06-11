<?php
// Routes des cours
global $input, $method, $uri;

$db = Database::getConnection();
$user = require_auth();
$sub_uri = str_replace('/courses', '', $uri);

if ($method === 'GET' && ($sub_uri === '' || $sub_uri === '/')) {
    if ($user['role'] === 'eleve') {
        $stmt = $db->prepare("SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom FROM courses c JOIN users u ON c.enseignant_id = u.id WHERE c.classe = ? ORDER BY c.created_at DESC");
        $stmt->execute([$user['classe']]);
    } else {
        $stmt = $db->query("SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom FROM courses c JOIN users u ON c.enseignant_id = u.id ORDER BY c.created_at DESC");
    }
    $courses = $stmt->fetchAll();
    json_success("Cours récupérés", $courses);
}

if ($method === 'POST' && ($sub_uri === '' || $sub_uri === '/')) {
    require_role('enseignant');

    $titre = trim($input['titre'] ?? '');
    $description = trim($input['description'] ?? '');
    $classe = trim($input['classe'] ?? '');
    $matiere = trim($input['matiere'] ?? $user['matiere'] ?? '');

    if (empty($titre) || empty($classe) || empty($matiere)) {
        json_error("Le titre, la classe et la matière sont requis.");
    }

    if (!isset($_FILES['fichier'])) {
        json_error("Veuillez joindre un fichier PDF pour le cours.");
    }

    $upload_result = upload_file($_FILES['fichier'], 'courses');
    if (!$upload_result['success']) {
        json_error($upload_result['message']);
    }

    $fichier_url = $upload_result['url'];

    $stmt = $db->prepare("INSERT INTO courses (titre, description, fichier_url, enseignant_id, classe, matiere) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $description, $fichier_url, $user['id'], $classe, $matiere]);

    json_success("Cours publié avec succès.", null, 201);
}

if (preg_match('/^\/(\d+)$/', $sub_uri, $matches)) {
    $id = intval($matches[1]);

    $stmt = $db->prepare("SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom FROM courses c JOIN users u ON c.enseignant_id = u.id WHERE c.id = ?");
    $stmt->execute([$id]);
    $course = $stmt->fetch();

    if (!$course) {
        json_error("Cours non trouvé.", 404);
    }

    if ($method === 'GET') {
        if ($user['role'] === 'eleve' && $course['classe'] !== $user['classe']) {
            json_error("Accès interdit à ce cours.", 403);
        }
        json_success("Détail du cours", $course);
    }

    if ($method === 'DELETE') {
        if ($user['role'] !== 'admin' && $course['enseignant_id'] !== $user['id']) {
            json_error("Vous n'êtes pas autorisé à supprimer ce cours.", 403);
        }

        $filepath = __DIR__ . '/../' . $course['fichier_url'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);

        json_success("Cours supprimé avec succès.");
    }
}

json_error("Route de cours introuvable", 404);
