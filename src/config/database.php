<?php
class Database {
    private static $pdo;

    public static function getConnection() {
        if (!self::$pdo) {
            try {
                // Ajuste aqui se seu banco tiver senha
                self::$pdo = new PDO('mysql:host=localhost;dbname=ripfire_db;charset=utf8', 'root', '');
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Erro de conexão: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}