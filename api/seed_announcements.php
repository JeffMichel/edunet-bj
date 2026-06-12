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
            "titre" => "Bienvenue pour cette nouvelle rentree !",
            "contenu" => "Toute l equipe d EduNet BJ vous souhaite une excellente annee scolaire. N oubliez pas de consulter regulierement votre emploi du temps et vos devoirs."
        ],
        [
            "titre" => "Fermeture exceptionnelle ce vendredi",
            "contenu" => "En raison de travaux de maintenance sur le reseau electrique de l etablissement, les cours de l apres-midi seront suspendus ce vendredi."
        ],
        [
            "titre" => "Reunion des parents d eleves",
            "contenu" => "La premiere reunion de rencontre parents-professeurs se tiendra le samedi de la semaine prochaine a partir de 8h00 dans la grande salle de conference."
        ],
        [
            "titre" => "Nouvelles mesures de securite",
            "contenu" => "Il est rappele a tous les eleves de respecter le reglement interieur concernant la tenue vestimentaire et la ponctualite."
        ],
        [
            "titre" => "Ouverture de la bibliotheque",
            "contenu" => "La bibliotheque de l etablissement a recu de nouveaux ouvrages. Elle sera desormais ouverte jusqu a 18h tous les jours ouvrables."
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
        "Excursion pedagogique",
        "Materiel requis"
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
