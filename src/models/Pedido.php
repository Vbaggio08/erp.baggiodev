<?php
require_once __DIR__ . '/../config/database.php';

class Pedido {

    public static function listarPendentes(): array {
        $pdo = Database::getConnection();

        try {
            $sql = "SELECT id, cliente, produto, quantidade, prioridade, status, data_entrada
                    FROM pedidos_producao
                    WHERE status IS NULL OR status NOT IN ('Concluido', 'Concluído', 'Entregue', 'Cancelado')
                    ORDER BY prioridade DESC, data_entrada ASC
                    LIMIT 50";

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }
}
