<?php
require_once __DIR__ . '/../config/database.php';

class AuditoriaAlteracao {
    
    /**
     * Registra uma alteração na auditoria (INSERT only - nunca UPDATE)
     * 
     * @param int $apontamento_id
     * @param int $usuario_alterador_id (Quem fez a alteração)
     * @param string $tipo_alteracao (entrada_criada, saida_criada, entrada_editada, etc)
     * @param mixed $valor_anterior (pode ser array/string)
     * @param mixed $valor_novo (pode ser array/string)
     * @param string $motivo_alteracao (obrigatório)
     * @return int|bool ID do registro de auditoria ou false se falhar
     */
    public static function registrarAlteracao(
        $apontamento_id,
        $usuario_alterador_id,
        $tipo_alteracao,
        $valor_anterior = null,
        $valor_novo = null,
        $motivo_alteracao = ''
    ) {
        $pdo = Database::getConnection();
        
        // Converte para JSON se necessário
        $anterior_json = is_array($valor_anterior) ? json_encode($valor_anterior) : $valor_anterior;
        $novo_json = is_array($valor_novo) ? json_encode($valor_novo) : $valor_novo;
        
        // Calcula hash SHA256 para integridade
        $conteudo_hash = json_encode([
            'apontamento_id' => $apontamento_id,
            'tipo' => $tipo_alteracao,
            'anterior' => $anterior_json,
            'novo' => $novo_json,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        $hash = hash('sha256', $conteudo_hash);
        
        try {
            $sql = "INSERT INTO historico_alteracoes_ponto 
                    (apontamento_id, usuario_alterador_id, tipo_alteracao, valor_anterior, valor_novo, motivo_alteracao, hash_sha256)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $apontamento_id,
                $usuario_alterador_id,
                $tipo_alteracao,
                $anterior_json,
                $novo_json,
                $motivo_alteracao,
                $hash
            ]);
            
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao registrar auditoria: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtém histórico completo de alterações de um apontamento
     */
    public static function obterHistoricoApontamento($apontamento_id) {
        $pdo = Database::getConnection();
        
        $sql = "SELECT h.*, u.nome as usuario_nome
                FROM historico_alteracoes_ponto h
                LEFT JOIN usuarios u ON h.usuario_alterador_id = u.id
                WHERE h.apontamento_id = ?
                ORDER BY h.criado_em DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$apontamento_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtém histórico de alterações de um usuário em um período
     */
    public static function obterHistoricoUsuario($usuario_id, $data_inicio, $data_fim) {
        $pdo = Database::getConnection();
        
        $sql = "SELECT h.*, u.nome as usuario_nome, ap.data as apontamento_data
                FROM historico_alteracoes_ponto h
                LEFT JOIN usuarios u ON h.usuario_alterador_id = u.id
                INNER JOIN apontamentos_ponto ap ON h.apontamento_id = ap.id
                WHERE ap.usuario_id = ? AND ap.data BETWEEN ? AND ?
                ORDER BY h.criado_em DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $data_inicio, $data_fim]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Valida integridade de um registro de auditoria usando seu hash
     */
    public static function validarIntegridade($auditoria_id) {
        $pdo = Database::getConnection();
        
        $sql = "SELECT * FROM historico_alteracoes_ponto WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$auditoria_id]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$registro) {
            return false;
        }
        
        // Reconstrói o hash
        $conteudo_hash = json_encode([
            'apontamento_id' => $registro['apontamento_id'],
            'tipo' => $registro['tipo_alteracao'],
            'anterior' => $registro['valor_anterior'],
            'novo' => $registro['valor_novo'],
            'timestamp' => $registro['criado_em']
        ], JSON_UNESCAPED_UNICODE);
        $hash_calculado = hash('sha256', $conteudo_hash);
        
        return $hash_calculado === $registro['hash_sha256'];
    }
}
?>
