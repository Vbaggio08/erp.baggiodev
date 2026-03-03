<?php
require_once __DIR__ . '/../config/database.php';

class Compra {
    
    // Método atualizado para receber empresa
    public static function salvarPedidoCompleto($fornecedor, $dataEntrega, $usuario, $lista, $condicoesPagamento, $empresa) {
        $pdo = Database::getConnection();
        
        try {
            $pdo->beginTransaction();

            // INSERT com a coluna empresa
            $stmt = $pdo->prepare("INSERT INTO pedidos_compra (fornecedor, data_entrega, usuario_emissor, condicoes_pagamento, empresa) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$fornecedor, $dataEntrega, $usuario, $condicoesPagamento, $empresa]);
            
            $pedidoId = $pdo->lastInsertId();

            // Insere os Itens
            $stmtItem = $pdo->prepare("INSERT INTO itens_compra (pedido_id, produto, cor, tamanho, qtd, obs, preco) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($lista as $item) {
                // Formata preço (troca vírgula por ponto)
                $preco = str_replace(',', '.', $item['preco']);
                
                $stmtItem->execute([
                    $pedidoId, 
                    $item['produto'], 
                    $item['cor'], 
                    $item['tam'], 
                    $item['qtd'], 
                    $item['obs'],
                    $preco
                ]);
            }

            $pdo->commit();
            return $pedidoId;

        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public static function listarHistorico() {
        $pdo = Database::getConnection();
        $sql = "SELECT p.*, COUNT(i.id) as itens_qtd 
                FROM pedidos_compra p 
                LEFT JOIN itens_compra i ON p.id = i.pedido_id 
                GROUP BY p.id ORDER BY p.id DESC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function buscarPedido($id) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM pedidos_compra WHERE id = ?");
        $stmt->execute([$id]);
        $pedido = $stmt->fetch();

        if ($pedido) {
            $stmtItens = $pdo->prepare("SELECT * FROM itens_compra WHERE pedido_id = ?");
            $stmtItens->execute([$id]);
            $pedido['lista'] = $stmtItens->fetchAll();
            return $pedido;
        }
        return null;
    }
}