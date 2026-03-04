<?php
class Database {
    private static $pdo;

    public static function getConnection() {
        if (!self::$pdo) {
            try {
                // --- COLOQUE AQUI AS CREDENCIAIS DO BANCO DE DADOS DA HOSTGATOR ---
                $host = 'localhost'; // ou o host que a HostGator fornecer
                $dbname = 'vin31871_ripfire';
                $user = 'vin31871_ripfire';
                $password = 'Vb357753@';
                // --------------------------------------------------------------------

                self::$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // Desativa o modo ONLY_FULL_GROUP_BY para compatibilidade com o servidor da HostGator
                self::$pdo->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");
            } catch (PDOException $e) {
                die("Erro de conexão: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}