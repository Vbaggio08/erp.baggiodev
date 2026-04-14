<?php
// Arquivo: src/models/Usuario.php
require_once __DIR__ . '/../config/database.php';

class Usuario {
    private static $colunaCpfCache = null;

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
        return $pdo->query("SELECT id, nome, username, email, nivel, cpf FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function buscarPorId(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, nome, username, email, nivel, departamento, cargo, cpf FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function normalizarCpf(string $cpf): string {
        return preg_replace('/\D+/', '', $cpf) ?? '';
    }

    public static function buscarPorCPF(string $cpf): ?array {
        $cpfNormalizado = self::normalizarCpf($cpf);
        if (strlen($cpfNormalizado) !== 11) {
            return null;
        }

        $colunaCpf = self::obterColunaCpfDisponivel();
        if ($colunaCpf === null) {
            return null;
        }

        $pdo = Database::getConnection();

        if ($colunaCpf === 'cpf') {
            $sql = "SELECT * FROM usuarios WHERE REPLACE(REPLACE(REPLACE(COALESCE(cpf, ''), '.', ''), '-', ''), '/', '') = ? LIMIT 1";
        } elseif ($colunaCpf === 'cpf_cnpj') {
            $sql = "SELECT * FROM usuarios WHERE REPLACE(REPLACE(REPLACE(COALESCE(cpf_cnpj, ''), '.', ''), '-', ''), '/', '') = ? LIMIT 1";
        } else {
            return null;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cpfNormalizado]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    private static function obterColunaCpfDisponivel(): ?string {
        if (self::$colunaCpfCache !== null) {
            return self::$colunaCpfCache;
        }

        $pdo = Database::getConnection();

        $sql = 'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?';
        $stmt = $pdo->prepare($sql);

        foreach (['cpf', 'cpf_cnpj'] as $coluna) {
            $stmt->execute(['usuarios', $coluna]);
            if ((int)$stmt->fetchColumn() > 0) {
                self::$colunaCpfCache = $coluna;
                return self::$colunaCpfCache;
            }
        }

        return null;
    }
}