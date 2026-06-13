<?php
// Standalone admin updater - connexion directe PDO sans framework
// Detecte l'environnement automatiquement

$root = __DIR__ . '/../';
$env_local = $root . '.env.local';
$env_prod  = $root . '.env';

function parseEnv($path) {
    $vars = [];
    if (!file_exists($path)) return $vars;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        list($k, $v) = explode('=', $line, 2);
        $vars[trim($k)] = trim(trim($v), '"\'');
    }
    return $vars;
}

function getVar($key, $env, $default = '') {
    // Priorité : .env.local ou .env > variables système (Render dashboard)
    if (!empty($env[$key])) return $env[$key];
    $sys = getenv($key);
    if ($sys !== false && $sys !== '') return $sys;
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    return $default;
}

if (file_exists($env_local)) {
    $env = parseEnv($env_local);
    $env_name = "LOCAL (WAMP)";
} elseif (file_exists($env_prod)) {
    $env = parseEnv($env_prod);
    $env_name = "PRODUCTION (fichier .env)";
} else {
    $env = [];
    $env_name = "PRODUCTION (Render - variables systeme)";
}

$host = getVar('DB_HOST', $env, 'localhost');
$port = getVar('DB_PORT', $env, '3306');
$name = getVar('DB_NAME', $env, 'edunet_bj');
$user = getVar('DB_USER', $env, 'root');
$pass = getVar('DB_PASSWORD', $env, '');

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($host !== 'localhost' && $host !== '127.0.0.1') {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        $options[PDO::MYSQL_ATTR_SSL_CA] = '';
    }
    $pdo = new PDO($dsn, $user, $pass, $options);

    $new_matricule = 'ADMIN-EDN-2026';
    $new_password  = 'EduN3t@BJ#R00t!2026Adm';
    $hash = password_hash($new_password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("UPDATE users SET matricule = ?, password = ?, premier_connexion = 0 WHERE role = 'admin' LIMIT 1");
    $stmt->execute([$new_matricule, $hash]);

    $admin = $pdo->query("SELECT id, matricule, nom, prenom, role FROM users WHERE role = 'admin' LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status"    => "success",
        "env"       => file_exists($env_local) ? "LOCAL (WAMP)" : "PRODUCTION (Aiven)",
        "matricule" => $new_matricule,
        "password"  => $new_password,
        "admin"     => $admin
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
