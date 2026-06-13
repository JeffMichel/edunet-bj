<?php
// Standalone admin updater - connexion directe PDO sans framework
// Detecte l'environnement automatiquement

$root = __DIR__ . '/../../';
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

$env = file_exists($env_local) ? parseEnv($env_local) : parseEnv($env_prod);

$host = $env['DB_HOST']     ?? 'localhost';
$port = $env['DB_PORT']     ?? '3306';
$name = $env['DB_NAME']     ?? 'edunet_bj';
$user = $env['DB_USER']     ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

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
