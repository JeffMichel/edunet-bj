<?php
// Script to generate schedule and grades
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
$pdo = Database::getConnection();

try {
    $pdo->beginTransaction();

    // Clear existing
    $pdo->exec("DELETE FROM schedule");
    $pdo->exec("DELETE FROM grades");

    // Fetch teachers
    $stmt = $pdo->query("SELECT id, nom, prenom, matiere FROM users WHERE role = 'enseignant'");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch students
    $stmt = $pdo->query("SELECT id, nom, prenom, classe FROM users WHERE role = 'eleve'");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get distinct classes from students
    $classes = array_unique(array_column($students, 'classe'));
    if (empty($classes)) {
        $classes = ['6ème A', '5ème A', '4ème A', '3ème A', '2nde C', '1ère C', 'Terminale C'];
    }

    $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
    $creneaux = [
        ['08:00:00', '10:00:00'],
        ['10:00:00', '12:00:00'],
        ['15:00:00', '17:00:00'],
        ['17:00:00', '19:00:00']
    ];

    $teacherAvailability = [];

    // Assign schedule
    foreach ($classes as $classe) {
        foreach ($jours as $jour) {
            foreach ($creneaux as $creneau) {
                $heure_debut = $creneau[0];
                $heure_fin = $creneau[1];

                // Shuffle teachers so classes get different subjects
                shuffle($teachers);
                $assigned = false;

                foreach ($teachers as $teacher) {
                    $teacher_id = $teacher['id'];
                    $time_key = "{$jour}_{$heure_debut}";

                    if (!isset($teacherAvailability[$teacher_id][$time_key])) {
                        // Assign teacher
                        $stmt = $pdo->prepare("INSERT INTO schedule (classe, matiere, enseignant_id, jour, heure_debut, heure_fin, salle) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $salle = "Salle " . rand(1, 20);
                        $stmt->execute([
                            $classe,
                            $teacher['matiere'] ?? 'Matière',
                            $teacher_id,
                            $jour,
                            $heure_debut,
                            $heure_fin,
                            $salle
                        ]);

                        $teacherAvailability[$teacher_id][$time_key] = true;
                        $assigned = true;
                        break; // Move to next timeslot
                    }
                }
            }
        }
    }

    // Assign grades
    $trimestres = ['1', '2', '3'];
    $annee = '2025-2026';
    $appreciations = [
        ['min' => 16, 'text' => 'Excellent travail !'],
        ['min' => 14, 'text' => 'Très bien, continuez ainsi.'],
        ['min' => 12, 'text' => 'Bon travail dans l\'ensemble.'],
        ['min' => 10, 'text' => 'Moyen, peut mieux faire.'],
        ['min' => 0, 'text' => 'Insuffisant, redoublez d\'efforts.']
    ];

    foreach ($students as $student) {
        // Assign 3-5 random grades per student per trimester
        foreach ($trimestres as $trim) {
            $num_grades = rand(3, 5);
            for ($i = 0; $i < $num_grades; $i++) {
                $teacher = $teachers[array_rand($teachers)];
                $note = rand(50, 200) / 10; // Random note between 5.0 and 20.0
                
                $app = '';
                foreach ($appreciations as $a) {
                    if ($note >= $a['min']) {
                        $app = $a['text'];
                        break;
                    }
                }

                $stmt = $pdo->prepare("INSERT INTO grades (eleve_id, enseignant_id, matiere, note, appreciation, trimestre, annee_scolaire) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $student['id'],
                    $teacher['id'],
                    $teacher['matiere'] ?? 'Matière',
                    $note,
                    $app,
                    $trim,
                    $annee
                ]);
            }
        }
    }

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Schedule and grades generated successfully."]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
