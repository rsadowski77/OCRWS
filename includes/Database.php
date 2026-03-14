<?php
require_once __DIR__ . '/config.php';
class Database {
    private static ?PDO $pdo = null;
    public static function connection(): PDO {
        if (self::$pdo !== null) return self::$pdo;
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$pdo = $pdo;
            return self::$pdo;
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Database connection failed.');
        }
    }
}
