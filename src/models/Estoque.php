<?php
require_once __DIR__ . '/../config/database.php';

class Estoque {

    // 1. REGISTRA MOVIMENTAÇÃO (Entrada ou Saída)
    public static function registrarMovimento($dados) {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $usuario = $dados['usuario'] ?? 'Sistema';

            // Usando estoque_movimentacoes (Plural)
            $sql = "INSERT INTO estoque_movimentacoes 
                    (tipo, produto, tamanho, cor, quantidade, observacao, usuario, data_movimento) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $dados['tipo'],         // entrada ou saida
                $dados['produto'],      // Nome (ex: Camiseta Oversized)
                $dados['tamanho'],      // M
                $dados['cor'],          // Preta
                $dados['quantidade'],   // Número (sempre positivo aqui)
                $dados['observacao'],
                $usuario
            ]);

            $pdo->commit();
            return true;

        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    // 2. CALCULA O SALDO (Agrupado com LEFT JOIN para mostrar os zerados)
    public static function getEstoqueAgrupado() {
        $pdo = Database::getConnection();
        
        // Faz um LEFT JOIN partindo de PRODUTOS para garantir que os zerados apareçam
        // Note que ajustei para estoque_movimentacoes (Plural) para bater com a função de cima
        $sql = "SELECT 
                    p.sku, 
                    p.nome as produto, 
                    p.cor, 
                    p.tamanho, 
                    IFNULL(
                        SUM(
                            CASE 
                                WHEN m.tipo = 'entrada' THEN m.quantidade 
                                WHEN m.tipo = 'saida' THEN -m.quantidade 
                                ELSE 0 
                            END
                        ), 0
                    ) as saldo_total
                FROM produtos p
                LEFT JOIN estoque_movimentacoes m ON p.nome = m.produto AND p.cor = m.cor AND p.tamanho = m.tamanho
                WHERE p.ativo = 1
                GROUP BY p.id, p.nome, p.cor, p.tamanho
                ORDER BY p.nome ASC, p.tamanho ASC";
                
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. HISTÓRICO COMPLETO
    public static function getHistoricoCompleto() {
        $pdo = Database::getConnection();
        return $pdo->query("SELECT * FROM estoque_movimentacoes ORDER BY data_movimento DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. RELATÓRIO DE PERDAS
    public static function getRelatorioPerdas() {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM estoque_movimentacoes 
                WHERE observacao LIKE '%perda%' OR observacao LIKE '%quebra%' 
                ORDER BY data_movimento DESC";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}