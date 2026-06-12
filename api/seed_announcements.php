<?php
// Script to generate detailed announcements
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
    $stmt = $pdo->query("SELECT id, nom, prenom, matiere FROM users WHERE role = 'enseignant'");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch unique classes
    $stmt = $pdo->query("SELECT DISTINCT classe FROM users WHERE role = 'eleve' AND classe IS NOT NULL");
    $classes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($teachers) || empty($classes)) {
        throw new Exception("Veuillez d'abord générer des enseignants et des élèves.");
    }

    $general_announcements = [
        [
            "titre" => "Cérémonie de remise des prix d'excellence",
            "contenu" => "La direction a le plaisir d'informer l'ensemble des élèves et du corps professoral que la cérémonie annuelle de remise des prix d'excellence se tiendra le vendredi 26 juin 2026, à 15h00 précises, dans le grand amphithéâtre. La présence de tous les délégués de classe est obligatoire. Les parents des lauréats recevront une invitation officielle par courrier."
        ],
        [
            "titre" => "Fermeture exceptionnelle du Bâtiment B",
            "contenu" => "En raison de travaux de rénovation de la tuyauterie, le Bâtiment B sera entièrement fermé du lundi 15 au mercredi 17 juin inclus. Tous les cours prévus dans les salles B101 à B205 sont déplacés dans le Bâtiment C. Un tableau d'affichage temporaire sera installé dans le hall principal pour indiquer les nouvelles salles d'affectation."
        ],
        [
            "titre" => "Rencontre Parents-Professeurs - 1er Trimestre",
            "contenu" => "La première réunion de rencontre parents-professeurs se déroulera le samedi 10 octobre de 8h00 à 12h30. Les enseignants vous recevront dans leurs salles habituelles. Les bulletins de notes de mi-trimestre seront remis en main propre. Les élèves sont tenus d'informer leurs parents de cette rencontre cruciale pour le suivi de leur scolarité."
        ],
        [
            "titre" => "Règles strictes sur l'usage des téléphones",
            "contenu" => "Suite au dernier conseil de discipline, il est rappelé avec insistance que l'usage des téléphones portables est strictement interdit dans l'enceinte de l'établissement (cours de récréation, couloirs et salles de classe). Tout appareil confisqué ne sera restitué qu'aux parents, à la fin de l'année scolaire. Aucune exception ne sera tolérée."
        ],
        [
            "titre" => "Nouvelles acquisitions à la bibliothèque",
            "contenu" => "La bibliothèque de l'établissement est heureuse d'annoncer l'arrivée de plus de 200 nouveaux ouvrages, incluant les manuels au programme de Philosophie et de Sciences Physiques, ainsi qu'une nouvelle collection de romans africains. La bibliothèque reste ouverte du lundi au vendredi, de 08h00 à 18h30 sans interruption."
        ]
    ];

    // Remove accents for DB compatibility if needed, or rely on utf8mb4. 
    // We will just use standard strings, assuming the DB is properly configured for utf8mb4.
    // If there's an issue, we can remove accents, but let's assume it's fine or we replace them just in case.
    function removeAccents($str) {
        $unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
        return strtr( $str, $unwanted_array );
    }

    // Insert general announcements
    foreach ($general_announcements as $ann) {
        $stmt = $pdo->prepare("INSERT INTO announcements (titre, contenu, auteur_id, classe_cible) VALUES (?, ?, ?, NULL)");
        $stmt->execute([
            removeAccents($ann['titre']),
            removeAccents($ann['contenu']),
            $admin_id
        ]);
    }

    // Insert specific announcements per class
    foreach ($classes as $classe) {
        // Create 3 specific announcements per class
        for ($i = 0; $i < 3; $i++) {
            $teacher = $teachers[array_rand($teachers)];
            $teacher_id = $teacher['id'];
            $matiere = $teacher['matiere'];
            
            $specific_announcements = [
                [
                    "titre" => "[$classe] - Deplacement du cours de $matiere",
                    "contenu" => "Chers eleves de $classe, le cours de $matiere initialement prevu ce jeudi a 10h est deplace a 15h en Salle 12. Merci de venir avec votre materiel de geometrie complet."
                ],
                [
                    "titre" => "[$classe] - Rappel urgent pour le devoir de $matiere",
                    "contenu" => "Le devoir sur table de $matiere se tiendra demain a la premiere heure. La calculatrice est strictement interdite. Veuillez reviser les chapitres 3 et 4 du manuel scolaire."
                ],
                [
                    "titre" => "[$classe] - Absence du professeur de $matiere",
                    "contenu" => "Suite a une urgence familiale, je serai absent ce mercredi. Les eleves de la $classe sont pries de se rendre en salle d'etude. Des exercices de $matiere ont ete laisses a la surveillance."
                ],
                [
                    "titre" => "[$classe] - Projet de groupe en $matiere",
                    "contenu" => "Pour votre evaluation de fin de mois en $matiere, vous devez former des groupes de 4 personnes. La liste des groupes doit m'etre remise par le delegue de la $classe avant vendredi midi."
                ],
                [
                    "titre" => "[$classe] - Rattrapage du controle de $matiere",
                    "contenu" => "Tous les eleves absents lors du dernier controle de $matiere sont convoques ce samedi a 08h00 en salle des professeurs pour la session de rattrapage."
                ]
            ];

            $ann = $specific_announcements[array_rand($specific_announcements)];

            $stmt = $pdo->prepare("INSERT INTO announcements (titre, contenu, auteur_id, classe_cible) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                removeAccents($ann['titre']),
                removeAccents($ann['contenu']),
                $teacher_id,
                $classe
            ]);
        }
    }

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Annonces detaillees generees avec succes !"]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
