<?php
// Standalone admin updater - no framework includes needed
$host = 'localhost';
$db   = 'edunet_bj';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $new_matricule = 'ADMIN-EDN-2026';
    $new_password  = 'EduN3t@BJ#R00t!2026Adm';
    $hash = password_hash($new_password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("UPDATE users SET matricule = ?, password = ?, premier_connexion = 0 WHERE role = 'admin' LIMIT 1");
    $stmt->execute([$new_matricule, $hash]);

    echo json_encode([
        "status"    => "success",
        "matricule" => $new_matricule,
        "password"  => $new_password
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
