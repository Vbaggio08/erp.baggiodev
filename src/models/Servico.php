<?php
require_once __DIR__ . '/../config/database.php';

class Servico {

    public static function criar($cliente, $descricao, $valor, $empresa) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO servicos_os (cliente, descricao, valor, empresa, status, data_abertura) VALUES (?, ?, ?, ?, 'pendente', NOW())");
        $stmt->execute([$cliente, $descricao, $valor, $empresa]);
    }

    public static function listar() {
        $pdo = Database::getConnection();
        // Ordena pelos mais recentes
        $stmt = $pdo->query("SELECT * FROM servicos_os ORDER BY status DESC, data_abertura DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function concluir($id) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE servicos_os SET status = 'concluido', data_conclusao = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    public static function excluir($id) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM servicos_os WHERE id = ?");
        $stmt->execute([$id]);
    }
}