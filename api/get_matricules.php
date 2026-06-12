<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getConnection();
    
    // Enseignants
    $stmt = $pdo->query("SELECT matricule, nom, prenom, role, matiere FROM users WHERE role = 'enseignant'");
    $enseignants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "### Liste des Enseignants\n\n";
    echo "| Matricule | Nom & Prénom | Rôle | Matière |\n";
    echo "|---|---|---|---|\n";
    foreach ($enseignants as $e) {
        echo "| `{$e['matricule']}` | {$e['nom']} {$e['prenom']} | Enseignant | {$e['matiere']} |\n";
    }
    
    // Eleves
    $stmt = $pdo->query("SELECT matricule, nom, prenom, role, classe FROM users WHERE role = 'eleve'");
    $eleves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n### Liste des Élèves\n\n";
    echo "| Matricule | Nom & Prénom | Rôle | Classe |\n";
    echo "|---|---|---|---|\n";
    foreach ($eleves as $e) {
        echo "| `{$e['matricule']}` | {$e['nom']} {$e['prenom']} | Élève | {$e['classe']} |\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
