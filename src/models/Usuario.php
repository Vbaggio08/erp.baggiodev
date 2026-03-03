<?php
// Arquivo: src/models/Usuario.php
require_once __DIR__ . '/../config/database.php';

class Usuario {
    public static function login($email, $senha) {
        $pdo = Database::getConnection();
        
        // Busca o usuário pelo email
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se achou o usuário E a senha bate com a criptografia
        if ($user && password_verify($senha, $user['senha'])) {
            return $user;
        }
        
        return false;
    }

    public static function listar() {
        $pdo = Database::getConnection();
        return $pdo->query("SELECT id, nome, email, nivel FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
    }
}