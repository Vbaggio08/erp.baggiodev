<?php
require_once __DIR__ . '/../config/database.php';

class Produto {
    
    // Lista apenas produtos ATIVOS
    public static function listarTodos() {
        $pdo = Database::getConnection();
        // Ordena por nome para facilitar a busca
        $sql = "SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome ASC, tamanho ASC";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function listarPaginado(string $filtro = '', int $limite = 25, int $offset = 0): array {
        $pdo = Database::getConnection();

        $where = "WHERE ativo = 1";
        $params = [];

        if ($filtro !== '') {
            $where .= " AND (nome LIKE :filtro OR sku LIKE :filtro OR tamanho LIKE :filtro OR cor LIKE :filtro)";
            $params[':filtro'] = '%' . $filtro . '%';
        }

        $sql = "SELECT * FROM produtos {$where} ORDER BY nome ASC, tamanho ASC LIMIT :limite OFFSET :offset";
        $stmt = $pdo->prepare($sql);

        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function contarAtivos(string $filtro = ''): int {
        $pdo = Database::getConnection();

        $where = "WHERE ativo = 1";
        $params = [];

        if ($filtro !== '') {
            $where .= " AND (nome LIKE :filtro OR sku LIKE :filtro OR tamanho LIKE :filtro OR cor LIKE :filtro)";
            $params[':filtro'] = '%' . $filtro . '%';
        }

        $sql = "SELECT COUNT(*) FROM produtos {$where}";
        $stmt = $pdo->prepare($sql);

        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor, PDO::PARAM_STR);
        }

        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // Salva novo produto com SKU
    public static function salvar($dados) {
        $pdo = Database::getConnection();
        
        // Se não digitar SKU, gera um automático
        $sku = !empty($dados['sku']) ? $dados['sku'] : strtoupper(uniqid('PROD-'));

        $sql = "INSERT INTO produtos (nome, tamanho, cor, preco_custo, preco_venda, sku, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)";
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

    // Desativa o produto (não apaga para não quebrar histórico)
    public static function excluir($id) {
        $pdo = Database::getConnection();
        $pdo->prepare("UPDATE produtos SET ativo = 0 WHERE id = ?")->execute([$id]);
    }
}