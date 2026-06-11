<?php
// Middleware de vérification JWT et autorisation

if (!function_exists('apache_request_headers')) {
    function apache_request_headers() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $header_name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$header_name] = $value;
            }
        }
        return $headers;
    }
}

function get_auth_token() {
    $headers = apache_request_headers();
    
    // Rechercher l'en-tête de manière insensible à la casse
    $authHeader = null;
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'authorization') {
            $authHeader = $value;
            break;
        }
    }
    
    if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }

    if ($authHeader && preg_match('/Bearer\s(\S+)/i', $authHeader, $matches)) {
        return $matches[1];
    }
    return null;
}

function require_auth() {
    $token = get_auth_token();
    if (!$token) {
        json_error("Accès refusé. Token manquant.", 401);
    }

    $payload = jwt_verify($token);
    if (!$payload) {
        json_error("Token invalide ou expiré.", 401);
    }

    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT id, nom, prenom, email, role, classe, matiere, statut, avatar FROM users WHERE id = ?");
    $stmt->execute([$payload['id']]);
    $user = $stmt->fetch();

    if (!$user) {
        json_error("Utilisateur non trouvé.", 401);
    }

    if ($user['statut'] !== 'actif') {
        json_error("Compte inactif ou suspendu. Veuillez contacter l'administration.", 403);
    }

    return $user;
}

function require_role($roles) {
    $user = require_auth();
    if (is_string($roles)) {
        $roles = [$roles];
    }
    if (!in_array($user['role'], $roles)) {
        json_error("Accès interdit. Autorisation insuffisante.", 403);
    }
    return $user;
}
