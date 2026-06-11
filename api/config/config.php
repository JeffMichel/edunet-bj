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

// Définir des constantes pour une utilisation facile
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'edunet_bj');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');

define('JWT_SECRET', getenv('JWT_SECRET') ?: 'edunet_bj_secret_key_change_this_in_production');
define('JWT_EXPIRES_IN', intval(getenv('JWT_EXPIRES_IN') ?: 900));
define('REFRESH_TOKEN_EXPIRES_IN', intval(getenv('REFRESH_TOKEN_EXPIRES_IN') ?: 604800));

define('UPLOAD_DIR', __DIR__ . '/../' . (getenv('UPLOAD_DIR') ?: 'uploads/'));
define('MAX_FILE_SIZE', intval(getenv('MAX_FILE_SIZE') ?: 5242880));
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
