<?php
require_once __DIR__ . '/../config/database.php';

class Atestado {
    
    /**
     * Solicita um novo atestado
     */
    public static function solicitarAtestado($usuario_id, $data_inicio, $data_fim, $tipo, $arquivo_path = null) {
        $pdo = Database::getConnection();
        
        try {
            $sql = "INSERT INTO atestados (usuario_id, data_inicio, data_fim, tipo, comprovante_url, status)
                    VALUES (?, ?, ?, ?, ?, 'pendente')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $usuario_id,
                $data_inicio,
                $data_fim,
                $tipo,
                $arquivo_path
            ]);
            
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao solicitar atestado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Aprova um atestado
     */
    public static function aprovarAtestado($atestado_id, $aprovador_id) {
        $pdo = Database::getConnection();
        
        try {
            $sql = "UPDATE atestados SET status = 'aprovado', aprovador_id = ?, aprovado_em = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$aprovador_id, $atestado_id]);
            
            // Obtém dados do atestado
            $atestado = self::obterAtestado($atestado_id);
            
            // Aplica ao ponto (marca dias como atestado)
            self::aplicarAtestadoAoPonto($atestado_id);
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao aprovar atestado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rejeita um atestado
     */
    public static function rejeitarAtestado($atestado_id, $motivo_rejeicao, $rejector_id) {
        $pdo = Database::getConnection();
        
        try {
            $sql = "UPDATE atestados SET status = 'rejeitado', motivo_rejeicao = ?, aprovador_id = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$motivo_rejeicao, $rejector_id, $atestado_id]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao rejeitar atestado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista atestados pendentes para RH
     */
    public static function listarPendentes() {
        $pdo = Database::getConnection();
        
        $sql = "SELECT a.*, u.nome as usuario_nome, u.departamento
                FROM atestados a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.status = 'pendente'
                ORDER BY a.criado_em DESC";
        
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtém atestados de um usuário
     */
    public static function listarPorUsuario($usuario_id) {
        $pdo = Database::getConnection();
        
        $sql = "SELECT * FROM atestados WHERE usuario_id = ? ORDER BY data_inicio DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtém um atestado pelo ID
     */
    public static function obterAtestado($atestado_id) {
        $pdo = Database::getConnection();
        
        $sql = "SELECT * FROM atestados WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$atestado_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Marca dias do atestado como 'atestado' nos apontamentos_ponto
     */
    public static function aplicarAtestadoAoPonto($atestado_id) {
        $pdo = Database::getConnection();
        $atestado = self::obterAtestado($atestado_id);
        
        if (!$atestado) {
            return false;
        }
        
        try {
            $data_inicio = strtotime($atestado['data_inicio']);
            $data_fim = strtotime($atestado['data_fim']);
            
            // Itera sobre cada dia do período
            for ($data = $data_inicio; $data <= $data_fim; $data += 86400) {
                $data_str = date('Y-m-d', $data);
                
                // Verifica se já tem apontamento para este dia
                $sql = "SELECT id FROM apontamentos_ponto WHERE usuario_id = ? AND data = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$atestado['usuario_id'], $data_str]);
                $apontamento = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($apontamento) {
                    // Atualiza status para atestado
                    $sql = "UPDATE apontamentos_ponto SET status = 'atestado' WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$apontamento['id']]);
                    
                    // Registra na auditoria
                    AuditoriaAlteracao::registrarAlteracao(
                        $apontamento['id'],
                        $atestado['aprovador_id'],
                        'atestado_anexado',
                        json_encode(['status' => 'presente']),
                        json_encode(['status' => 'atestado']),
                        'Atestado aprovado aplicado ao ponto'
                    );
                } else {
                    // Cria novo apontamento marcado como atestado
                    $sql = "INSERT INTO apontamentos_ponto (usuario_id, data, status) VALUES (?, ?, 'atestado')";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$atestado['usuario_id'], $data_str]);
                }
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao aplicar atestado ao ponto: " . $e->getMessage());
            return false;
        }
    }
}
?>
