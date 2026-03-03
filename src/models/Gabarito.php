<?php
require_once __DIR__ . '/../config/database.php';

class Gabarito {

    // 1. CONTA PEÇAS DE HOJE (Para o Dashboard)
    public static function contarProducaoHoje() {
        $pdo = Database::getConnection();
        $hoje = date('Y-m-d');
        
        $sql = "SELECT SUM(quantidade) as total FROM gabaritos WHERE data_pedido = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$hoje]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ?? 0;
    }

    // 2. LISTA TODAS AS FICHAS (Para a tela de Listagem)
    public static function listarTodos() {
        $pdo = Database::getConnection();
        // Busca as últimas 50 para não pesar a tela
        return $pdo->query("SELECT * FROM gabaritos ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. EXCLUIR (NOVA FUNÇÃO QUE ESTAVA FALTANDO)
    public static function excluir($id) {
        $pdo = Database::getConnection();
        try {
            // Deleta a linha do banco baseada no ID
            $stmt = $pdo->prepare("DELETE FROM gabaritos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            // Se der erro (ex: banco desligado), retorna falso
            return false;
        }
    }
    public static function atualizarStatus($id, $novoStatus) {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("UPDATE gabaritos SET status = ? WHERE id = ?");
            return $stmt->execute([$novoStatus, $id]);
        } catch (Exception $e) {
            return false;
        }
    }
}

    

