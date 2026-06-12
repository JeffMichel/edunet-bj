<?php
// Script to generate teachers and students
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
$pdo = Database::getConnection();

try {
    $pdo->beginTransaction();

    $mat_counter = 100;
    function getMat() {
        global $mat_counter;
        $mat_counter++;
        return 'BJ-2026-0' . $mat_counter;
    }

    $pass = password_hash('password123', PASSWORD_BCRYPT);

    $matieres = ['Mathématiques', 'Français', 'Anglais', 'Physique', 'SVT', 'Histoire-Géo', 'Philosophie', 'Informatique'];
    $classes = ['6ème A', '6ème B', '5ème A', '5ème B', '4ème A', '3ème A', '2nde C', '2nde D', '1ère C', '1ère D', 'Terminale C', 'Terminale D'];

    // Generate Teachers
    foreach ($matieres as $matiere) {
        // Create 2 teachers per subject
        for ($i=1; $i<=2; $i++) {
            $stmt = $pdo->prepare("INSERT INTO users (matricule, nom, prenom, password, role, matiere, statut, premier_connexion) VALUES (?, ?, ?, ?, ?, ?, 'actif', 0)");
            $stmt->execute([
                getMat(),
                "Prof" . $i,
                $matiere,
                $pass,
                'enseignant',
                $matiere
            ]);
        }
    }

    // Generate Students
    foreach ($classes as $classe) {
        // Create 10 students per class
        for ($i=1; $i<=10; $i++) {
            $stmt = $pdo->prepare("INSERT INTO users (matricule, nom, prenom, password, role, classe, statut, premier_connexion) VALUES (?, ?, ?, ?, ?, ?, 'actif', 0)");
            $stmt->execute([
                getMat(),
                "Eleve" . $i,
                str_replace(['è', ' '], ['e', ''], $classe),
                $pass,
                'eleve',
                $classe
            ]);
        }
    }

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Users generated successfully."]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
