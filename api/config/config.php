<?php
// Charger les variables d'environnement
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        // Retirer les guillemets éventuels autour de la valeur
        $value = trim($value, '"\'');
        
        putenv(sprintf('%s=%s', $name, $value));
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

// Charger .env depuis la racine (2 niveaux au-dessus de api/config/config.php)
loadEnv(__DIR__ . '/../../.env');

// Fonction robuste pour récupérer les variables d'environnement
function get_env_var($key, $default = null) {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }
    return $default;
}

// Définir des constantes pour une utilisation facile
define('DB_HOST', get_env_var('DB_HOST', 'localhost'));
define('DB_PORT', get_env_var('DB_PORT', '3306'));
define('DB_NAME', get_env_var('DB_NAME', 'edunet_bj'));
define('DB_USER', get_env_var('DB_USER', 'root'));
define('DB_PASSWORD', get_env_var('DB_PASSWORD') !== null ? get_env_var('DB_PASSWORD') : '');

define('JWT_SECRET', get_env_var('JWT_SECRET', 'edunet_bj_secret_key_change_this_in_production'));
define('JWT_EXPIRES_IN', intval(get_env_var('JWT_EXPIRES_IN', 900)));
define('REFRESH_TOKEN_EXPIRES_IN', intval(get_env_var('REFRESH_TOKEN_EXPIRES_IN', 604800)));

define('UPLOAD_DIR', __DIR__ . '/../' . get_env_var('UPLOAD_DIR', 'uploads/'));
define('MAX_FILE_SIZE', intval(get_env_var('MAX_FILE_SIZE', 5242880)));
define('APP_URL', get_env_var('APP_URL', 'http://localhost'));

// Diagnostic pour Render : si on est sur Render mais que DB_HOST vaut localhost
if (get_env_var('RENDER') || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false)) {
    if (DB_HOST === 'localhost' || DB_HOST === '127.0.0.1') {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => "Erreur critique : Les variables d'environnement de la base de données ne sont pas chargées sur Render. Veuillez vérifier l'onglet 'Environment' de votre service sur Render.",
            'data' => null
        ]);
        exit();
    }
}
