<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    // 1. Delete all users except admin (cascades to all other tables)
    $pdo->exec("DELETE FROM users WHERE role != 'admin'");

    // Reset matricule counter (Optional but cleaner)
    $pdo->exec("UPDATE matricule_counter SET dernier_numero = 1 WHERE annee = 2026");
    $mat_counter = 1;
    function getMat() {
        global $mat_counter;
        $mat_counter++;
        return 'BJ-2026-' . str_pad($mat_counter, 4, '0', STR_PAD_LEFT);
    }

    $pass = password_hash('000000', PASSWORD_BCRYPT);

    $noms = ['DOSSA', 'ADJANOHOUN', 'KIKI', 'HOUENOU', 'ZINSOU', 'AGBO', 'MENSAH', 'DANSOU', 'HOUNGBEDJI', 'SOGLO', 'YOMI', 'D ALMEIDA', 'BIO', 'CHABI', 'TCHABI', 'KPADONOU', 'VODONOU', 'LOKO'];
    $prenoms_m = ['Koffi', 'Kossi', 'Kwame', 'Setondji', 'Mahougnon', 'Gbedonougbo', 'Jean', 'Paul', 'Marc', 'Luc', 'Eric', 'Alain'];
    $prenoms_f = ['Fifame', 'Sessi', 'Senami', 'Marie', 'Gisele', 'Fatima', 'Aline', 'Elodie', 'Juliette', 'Pelagie'];

    function getName() {
        global $noms, $prenoms_m, $prenoms_f;
        $nom = $noms[array_rand($noms)];
        $is_f = rand(0, 1);
        $prenom = $is_f ? $prenoms_f[array_rand($prenoms_f)] : $prenoms_m[array_rand($prenoms_m)];
        return [$nom, $prenom];
    }

    $matieres = ['Mathematiques', 'Francais', 'Anglais', 'Physique', 'SVT', 'Histoire-Geo', 'Philosophie', 'Informatique'];
    $classes = ['6eme A', '6eme B', '5eme A', '5eme B', '4eme A', '3eme A', '2nde C', '2nde D', '1ere C', '1ere D', 'Terminale C', 'Terminale D'];

    $teachers_data = [];
    $students_data = [];

    // Generate Teachers (2 per subject)
    foreach ($matieres as $matiere) {
        for ($i=1; $i<=2; $i++) {
            list($nom, $prenom) = getName();
            $mat = getMat();
            $stmt = $pdo->prepare("INSERT INTO users (matricule, nom, prenom, password, role, matiere, statut, premier_connexion) VALUES (?, ?, ?, ?, 'enseignant', ?, 'actif', 0)");
            $stmt->execute([$mat, $nom, $prenom, $pass, $matiere]);
            $teacher_id = $pdo->lastInsertId();
            $teachers_data[] = ['id' => $teacher_id, 'matricule' => $mat, 'nom' => $nom, 'prenom' => $prenom, 'matiere' => $matiere];
        }
    }

    // Generate Students (5 per class for brevity and cleaner lists)
    foreach ($classes as $classe) {
        for ($i=1; $i<=5; $i++) {
            list($nom, $prenom) = getName();
            $mat = getMat();
            $stmt = $pdo->prepare("INSERT INTO users (matricule, nom, prenom, password, role, classe, statut, premier_connexion) VALUES (?, ?, ?, ?, 'eleve', ?, 'actif', 0)");
            $stmt->execute([$mat, $nom, $prenom, $pass, $classe]);
            $student_id = $pdo->lastInsertId();
            $students_data[] = ['id' => $student_id, 'matricule' => $mat, 'nom' => $nom, 'prenom' => $prenom, 'classe' => $classe];
        }
    }

    // Update actual counter
    $stmt = $pdo->prepare("UPDATE matricule_counter SET dernier_numero = ? WHERE annee = 2026");
    $stmt->execute([$mat_counter]);

    $pdo->commit();

    // Print markdown output
    echo "### 👨‍🏫 Liste Structurée des Enseignants\n\n";
    echo "| Matricule | Nom & Prénom | Rôle | Matière |\n";
    echo "|---|---|---|---|\n";
    foreach ($teachers_data as $e) {
        echo "| `{$e['matricule']}` | **{$e['nom']}** {$e['prenom']} | Enseignant | {$e['matiere']} |\n";
    }

    echo "\n### 👨‍🎓 Liste Structurée des Élèves (Apprenants)\n\n";
    echo "| Matricule | Nom & Prénom | Rôle | Classe |\n";
    echo "|---|---|---|---|\n";
    $current_class = '';
    foreach ($students_data as $e) {
        if ($e['classe'] !== $current_class) {
            echo "| **---** | **CLASSE DE " . strtoupper($e['classe']) . "** | **---** | **---** |\n";
            $current_class = $e['classe'];
        }
        echo "| `{$e['matricule']}` | **{$e['nom']}** {$e['prenom']} | Élève | {$e['classe']} |\n";
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
