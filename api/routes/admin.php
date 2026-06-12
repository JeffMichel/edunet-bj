<?php
// Routes d'administration
global $input, $method, $uri;

$db = Database::getConnection();
$user = require_role('admin');
$sub_uri = str_replace('/admin', '', $uri);

// Récupérer la liste des utilisateurs
if ($method === 'GET' && $sub_uri === '/users') {
    $stmt = $db->query("SELECT id, nom, prenom, matricule, role, classe, matiere, avatar, statut, created_at FROM users ORDER BY role, nom, prenom ASC");
    $users = $stmt->fetchAll();
    json_success("Utilisateurs récupérés", $users);
}

// Créer un nouvel utilisateur (tous rôles confondus) avec génération matricule et mot de passe par défaut
if ($method === 'POST' && $sub_uri === '/users') {
    $nom = trim($input['nom'] ?? '');
    $prenom = trim($input['prenom'] ?? '');
    $role = trim($input['role'] ?? '');
    $classe = trim($input['classe'] ?? null);
    $matiere = trim($input['matiere'] ?? null);

    if (empty($nom) || empty($prenom) || empty($role)) {
        json_error("Veuillez remplir les champs obligatoires (nom, prenom, rôle).");
    }

    $roles_valides = ['eleve', 'enseignant', 'censeur'];
    if (!in_array($role, $roles_valides)) {
        json_error("Rôle invalide.");
    }

    if ($role === 'eleve' && empty($classe)) {
        json_error("La classe est obligatoire pour un élève.");
    }

    if ($role === 'enseignant' && empty($matiere)) {
        json_error("La matière est obligatoire pour un enseignant.");
    }

    try {
        $annee = intval(date('Y'));
        
        $db->beginTransaction();
        
        // 1. Incrémenter le compteur de matricules pour l'année en cours
        $stmt = $db->prepare("INSERT INTO matricule_counter (annee, dernier_numero) VALUES (?, 1) ON DUPLICATE KEY UPDATE dernier_numero = dernier_numero + 1");
        $stmt->execute([$annee]);
        
        // 2. Récupérer le numéro incrémenté
        $stmt = $db->prepare("SELECT dernier_numero FROM matricule_counter WHERE annee = ?");
        $stmt->execute([$annee]);
        $numero = $stmt->fetchColumn();
        
        // 3. Composer le matricule
        $matricule = sprintf('BJ-%d-%04d', $annee, $numero);
        
        // 4. Insérer le nouvel utilisateur (mot de passe 000000 par défaut, premier_connexion = 1)
        $hashed_password = password_hash('000000', PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO users (matricule, nom, prenom, password, role, classe, matiere, statut, premier_connexion) VALUES (?, ?, ?, ?, ?, ?, ?, 'actif', 1)");
        $stmt->execute([$matricule, $nom, $prenom, $hashed_password, $role, $role === 'eleve' ? $classe : null, $role === 'enseignant' ? $matiere : null]);
        
        $db->commit();
        
        json_success("Compte créé avec succès.", [
            'matricule' => $matricule,
            'mot_de_passe_defaut' => '000000'
        ], 201);
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        json_error("Erreur lors de la création du compte : " . $e->getMessage());
    }
}

// Réinitialiser le mot de passe d'un utilisateur à 000000
if (preg_match('/^\/users\/(\d+)\/reset-password$/', $sub_uri, $matches)) {
    $id = intval($matches[1]);

    if ($method === 'POST' || $method === 'PUT') {
        if ($id === $user['id']) {
            json_error("Vous ne pouvez pas réinitialiser votre propre mot de passe administrateur par cette voie.");
        }

        $hashed_password = password_hash('000000', PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET password = ?, premier_connexion = 1 WHERE id = ?");
        $stmt->execute([$hashed_password, $id]);

        json_success("Le mot de passe de l'utilisateur a été réinitialisé à '000000' avec obligation de changement à sa connexion.");
    }
}

// Activer / suspendre un compte
if (preg_match('/^\/users\/(\d+)\/status$/', $sub_uri, $matches)) {
    $id = intval($matches[1]);

    if ($method === 'PUT') {
        $statut = trim($input['statut'] ?? '');
        $statuts_valides = ['actif', 'suspendu'];
        
        if (!in_array($statut, $statuts_valides)) {
            json_error("Statut invalide. Doit être actif ou suspendu.");
        }

        if ($id === $user['id']) {
            json_error("Vous ne pouvez pas modifier votre propre statut administrateur.");
        }

        $stmt = $db->prepare("UPDATE users SET statut = ? WHERE id = ?");
        $stmt->execute([$statut, $id]);

        json_success("Statut de l'utilisateur mis à jour avec succès.");
    }
}

// Supprimer définitivement un utilisateur (le matricule n'est plus attribué)
if (preg_match('/^\/users\/(\d+)$/', $sub_uri, $matches)) {
    $id = intval($matches[1]);

    if ($method === 'DELETE') {
        if ($id === $user['id']) {
            json_error("Vous ne pouvez pas supprimer votre propre compte administrateur.");
        }

        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        json_success("Utilisateur supprimé avec succès.");
    }
}

// Statistiques globales
if ($method === 'GET' && $sub_uri === '/stats') {
    $stmt = $db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $roles_count = $stmt->fetchAll();

    $stmt = $db->query("SELECT COUNT(*) FROM courses");
    $courses_count = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM assignments");
    $assignments_count = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM assignment_submissions");
    $submissions_count = $stmt->fetchColumn();

    $stmt = $db->query("SELECT statut, COUNT(*) as count FROM reports GROUP BY statut");
    $reports_count = $stmt->fetchAll();

    json_success("Statistiques globales récupérées", [
        'roles' => $roles_count,
        'courses' => $courses_count,
        'assignments' => $assignments_count,
        'submissions' => $submissions_count,
        'reports' => $reports_count,
        'pending_users' => 0 // Plus de comptes en attente avec inscription fermée
    ]);
}

json_error("Route administrative introuvable", 404);
