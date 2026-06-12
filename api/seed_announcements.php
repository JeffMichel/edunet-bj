<?php
// Script to generate announcements
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    // Clear existing
    $pdo->exec("DELETE FROM announcements");

    // Fetch Admin
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $admin_id = $admin ? $admin['id'] : 1;

    // Fetch teachers
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'enseignant'");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch unique classes
    $stmt = $pdo->query("SELECT DISTINCT classe FROM users WHERE role = 'eleve' AND classe IS NOT NULL");
    $classes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($teachers) || empty($classes)) {
        throw new Exception("Veuillez d'abord générer des enseignants et des élèves.");
    }

    $general_announcements = [
        [
            "titre" => "Bienvenue pour cette nouvelle rentrée !",
            "contenu" => "Toute l'équipe d'EduNet BJ vous souhaite une excellente année scolaire. N'oubliez pas de consulter régulièrement votre emploi du temps et vos devoirs."
        ],
        [
            "titre" => "Fermeture exceptionnelle ce vendredi",
            "contenu" => "En raison de travaux de maintenance sur le réseau électrique de l'établissement, les cours de l'après-midi seront suspendus ce vendredi."
        ],
        [
            "titre" => "Réunion des parents d'élèves",
            "contenu" => "La première réunion de rencontre parents-professeurs se tiendra le samedi de la semaine prochaine à partir de 8h00 dans la grande salle de conférence."
        ],
        [
            "titre" => "Nouvelles mesures de sécurité",
            "contenu" => "Il est rappelé à tous les élèves de respecter le règlement intérieur concernant la tenue vestimentaire et la ponctualité."
        ],
        [
            "titre" => "Ouverture de la bibliothèque",
            "contenu" => "La bibliothèque de l'établissement a reçu de nouveaux ouvrages. Elle sera désormais ouverte jusqu'à 18h tous les jours ouvrables."
        ]
    ];

    // Insert general announcements
    foreach ($general_announcements as $ann) {
        $stmt = $pdo->prepare("INSERT INTO announcements (titre, contenu, auteur_id, classe_cible) VALUES (?, ?, ?, NULL)");
        $stmt->execute([
            $ann['titre'],
            $ann['contenu'],
            $admin_id
        ]);
    }

    $specific_topics = [
        "Changement de salle",
        "Rappel pour le devoir",
        "Absence exceptionnelle",
        "Travail de groupe",
        "Excursion pédagogique",
        "Matériel requis"
    ];

    // Insert specific announcements per class
    foreach ($classes as $classe) {
        // Create 3 specific announcements per class
        for ($i = 0; $i < 3; $i++) {
            $teacher_id = $teachers[array_rand($teachers)]['id'];
            $sujet = $specific_topics[array_rand($specific_topics)];
            
            $titre = "[$classe] - $sujet";
            $contenu = "Ceci est une annonce importante destinée uniquement à la classe de $classe concernant : " . strtolower($sujet) . ". Merci d'en prendre note.";

            $stmt = $pdo->prepare("INSERT INTO announcements (titre, contenu, auteur_id, classe_cible) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $titre,
                $contenu,
                $teacher_id,
                $classe
            ]);
        }
    }

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Annonces générées avec succès !"]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
