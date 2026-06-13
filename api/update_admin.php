<?php
require_once __DIR__ . '/config/database.php';

$new_matricule = 'ADMIN-EDN-2026';
$new_password  = 'EduN3t@BJ#R00t!2026$S3cur3&Adm';

$pdo  = Database::getConnection();
$hash = password_hash($new_password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("UPDATE users SET matricule = ?, password = ?, premier_connexion = 0 WHERE role = 'admin' LIMIT 1");
$stmt->execute([$new_matricule, $hash]);

echo json_encode([
    "status"     => "success",
    "matricule"  => $new_matricule,
    "password"   => $new_password,
    "message"    => "Identifiants admin mis a jour avec succes."
]);
