<?php
// Connexion PDO MySQL

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                // Aiven requiert SSL — activé automatiquement si ce n'est pas localhost
                if (DB_HOST !== 'localhost' && DB_HOST !== '127.0.0.1') {
                    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                    $options[PDO::MYSQL_ATTR_SSL_CA] = '';
                }
                self::$pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
            } catch (PDOException $e) {
                header('Content-Type: application/json; charset=UTF-8');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur de connexion à la base de données : ' . $e->getMessage(),
                    'data' => null
                ]);
                exit();
            }
        }
        return self::$pdo;
    }
}
