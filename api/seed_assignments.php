<?php
// Script to generate assignments and submissions quickly
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    // Clear existing
    $pdo->exec("DELETE FROM assignment_submissions");
    $pdo->exec("DELETE FROM assignments");

    // Fetch teachers
    $stmt = $pdo->query("SELECT id, matiere FROM users WHERE role = 'enseignant'");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch students grouped by class
    $stmt = $pdo->query("SELECT id, classe FROM users WHERE role = 'eleve'");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $studentsByClass = [];
    foreach ($students as $student) {
        $studentsByClass[$student['classe']][] = $student['id'];
    }

    $classes = array_keys($studentsByClass);

    if (empty($teachers) || empty($students)) {
        throw new Exception("Veuillez d'abord générer des enseignants et des élèves.");
    }

    $appreciations = [
        ['min' => 16, 'text' => 'Excellent travail, très bien rédigé.'],
        ['min' => 14, 'text' => 'Bon devoir, quelques petites erreurs.'],
        ['min' => 12, 'text' => 'Assez bien, mais manque de profondeur.'],
        ['min' => 10, 'text' => 'Passable, revoyez le cours.'],
        ['min' => 0, 'text' => 'Devoir incomplet ou hors sujet.']
    ];

    // Generate Assignments (Only 1 per class to be fast)
    foreach ($classes as $classe) {
        $teacher = $teachers[array_rand($teachers)];
        $matiere = $teacher['matiere'] ?? 'Matière générique';
        
        $titre = "Devoir Final de $matiere";
        $description = "Ceci est le devoir final pour la classe de $classe. Veuillez soumettre votre PDF.";
        $date_limite = date('Y-m-d H:i:s', strtotime("+7 days"));

        $stmt = $pdo->prepare("INSERT INTO assignments (titre, description, enseignant_id, classe, matiere, date_limite) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $titre,
            $description,
            $teacher['id'],
            $classe,
            $matiere,
            $date_limite
        ]);
        $assignment_id = $pdo->lastInsertId();

        // Generate Submissions for max 3 students in this class
        $studentsInClass = $studentsByClass[$classe];
        shuffle($studentsInClass);
        $selectedStudents = array_slice($studentsInClass, 0, 3);

        foreach ($selectedStudents as $eleve_id) {
            $note = rand(50, 200) / 10; // 5.0 to 20.0
            
            $commentaire = '';
            foreach ($appreciations as $a) {
                if ($note >= $a['min']) {
                    $commentaire = $a['text'];
                    break;
                }
            }

            $fichier_url = "uploads/submissions/devoir_eleve_" . $eleve_id . ".pdf";

            $stmt = $pdo->prepare("INSERT INTO assignment_submissions (assignment_id, eleve_id, fichier_url, note, commentaire) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $assignment_id,
                $eleve_id,
                $fichier_url,
                $note,
                $commentaire
            ]);
        }
    }

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Devoirs et rendus générés avec succès !"]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
