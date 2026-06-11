<?php
// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Charger config
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/jwt.php';
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/upload.php';

// Parser l'URI
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!$uri) $uri = '/';

// Si /api est présent dans le chemin (ex: /EDUNET/api/auth/login),
// on extrait la partie après /api pour le routage
$apiPos = strpos($uri, '/api');
if ($apiPos !== false) {
    $uri = substr($uri, $apiPos + 4);
}

// S'assurer que le chemin commence par un slash
$uri = '/' . ltrim($uri, '/');
if (empty($uri)) {
    $uri = '/';
}

// Méthode HTTP
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Parser le corps JSON de la requête
$input = [];
$raw_input = file_get_contents('php://input');
if (!empty($raw_input)) {
    $decoded = json_decode($raw_input, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $input = $decoded;
    }
}
// Fusionner avec $_POST pour supporter le multipart/form-data (uploads)
$input = array_merge($input, $_POST);

// ================================================================
// Router principal — routes françaises ET anglaises supportées
// ================================================================

// AUTH
if (preg_match('/^\/auth(\/|$)/', $uri)) {
    require_once __DIR__ . '/routes/auth.php';

// USERS
} elseif (preg_match('/^\/users(\/|$)/', $uri)) {
    require_once __DIR__ . '/routes/users.php';

// COURS / COURSES
} elseif (preg_match('/^\/cours(\/|$)/', $uri)) {
    $uri = preg_replace('/^\/cours/', '/courses', $uri);
    require_once __DIR__ . '/routes/courses.php';
} elseif (preg_match('/^\/courses(\/|$)/', $uri)) {
    require_once __DIR__ . '/routes/courses.php';

// DEVOIRS / ASSIGNMENTS
} elseif (preg_match('/^\/devoirs(\/|$)/', $uri)) {
    $uri = preg_replace('/^\/devoirs/', '/assignments', $uri);
    require_once __DIR__ . '/routes/assignments.php';
} elseif (preg_match('/^\/assignments(\/|$)/', $uri)) {
    require_once __DIR__ . '/routes/assignments.php';

// ANNONCES / ANNOUNCEMENTS
} elseif (preg_match('/^\/annonces(\/|$)/', $uri)) {
    $uri = preg_replace('/^\/annonces/', '/announcements', $uri);
    require_once __DIR__ . '/routes/announcements.php';
} elseif (preg_match('/^\/announcements(\/|$)/', $uri)) {
    require_once __DIR__ . '/routes/announcements.php';

// MESSAGES
} elseif (preg_match('/^\/messages(\/|$)/', $uri)) {
    require_once __DIR__ . '/routes/messages.php';

// EMPLOI DU TEMPS / SCHEDULE
} elseif (preg_match('/^\/emploi-du-temps(\/|$)/', $uri)) {
    $uri = preg_replace('/^\/emploi-du-temps/', '/schedule', $uri);
    require_once __DIR__ . '/routes/schedule.php';
} elseif (preg_match('/^\/schedule(\/|$)/', $uri)) {
    require_once __DIR__ . '/routes/schedule.php';

// NOTES / GRADES
} elseif (preg_match('/^\/notes(\/|$)/', $uri)) {
    $uri = preg_replace('/^\/notes/', '/grades', $uri);
    require_once __DIR__ . '/routes/grades.php';
} elseif (preg_match('/^\/grades(\/|$)/', $uri)) {
    require_once __DIR__ . '/routes/grades.php';

// SIGNALEMENTS / REPORTS
} elseif (preg_match('/^\/signalements(\/|$)/', $uri)) {
    $uri = preg_replace('/^\/signalements/', '/reports', $uri);
    require_once __DIR__ . '/routes/reports.php';
} elseif (preg_match('/^\/reports(\/|$)/', $uri)) {
    require_once __DIR__ . '/routes/reports.php';

// ADMIN
} elseif (preg_match('/^\/admin(\/|$)/', $uri)) {
    require_once __DIR__ . '/routes/admin.php';

} else {
    json_error('Route non trouvée', 404);
}
