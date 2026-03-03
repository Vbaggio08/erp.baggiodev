<?php
require_once __DIR__ . '/../config/database.php';

class Produto {
    
    // Lista todos os produtos ativos (ordenados por Nome e Tamanho)
    public static function listarTodos() {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome ASC, tamanho ASC";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Salvar novo produto (Com SKU)
    public static function salvar($dados) {
        $pdo = Database::getConnection();
        
        // Se o SKU não for enviado, gera um aleatório
        $sku = !empty($dados['sku']) ? $dados['sku'] : strtoupper(uniqid('PROD-'));

        $sql = "INSERT INTO produtos (nome, tamanho, cor, preco_custo, preco_venda, sku) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $dados['nome'], 
            $dados['tamanho'], 
            $dados['cor'], 
            $dados['preco_custo'], 
            $dados['preco_venda'],
            $sku
        ]);
    }

    // Excluir (Desativar)
    public static function excluir($id) {
        $pdo = Database::getConnection();
        $pdo->prepare("UPDATE produtos SET ativo = 0 WHERE id = ?")->execute([$id]);
    }
}