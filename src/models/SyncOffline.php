<?php
require_once __DIR__ . '/../config/database.php';

class SyncOffline {
    
    /**
     * Registra um ponto que foi batido offline (armazenado localmente)
     * Este método registra no servidor que houve um ponto offline
     */
    public static function apenasRegistroLocal($usuario_id, $data_ponto, $dados) {
        // Este é mais um método conceitual - o registro real é feito no IndexedDB do navegador
        // Aqui apenas salvamos metadata no servidor quando ocorre sincronização
        
        return [
            'id_local' => uniqid('offline_'),
            'usuario_id' => $usuario_id,
            'data_ponto' => $data_ponto,
            'timestamp_local' => $dados['timestamp'] ?? time(),
            'status' => 'RASCUNHO'
        ];
    }
    
    /**
     * Sincroniza um lote de pontos offline com o servidor
     * 
     * @param int $usuario_id
     * @param array $lote_pontos Dados dos pontos vindos do IndexedDB
     * @return array Resultado da sincronização
     */
    public static function sincronizarComServidor($usuario_id, $lote_pontos) {
        $pdo = Database::getConnection();
        $sucesso = 0;
        $conflitos = 0;
        $erros = [];
        
        try {
            // Tempo de volta online
            $timestamp_volta = date('Y-m-d H:i:s');
            $data_online = date('Y-m-d');
            $data_offline = null;
            
            foreach ($lote_pontos as $ponto) {
                try {
                    // Verifica se já existe apontamento para este dia
                    $data_ponto = $ponto['data'] ?? date('Y-m-d');
                    
                    if (!$data_offline) {
                        $data_offline = $data_ponto;
                    }
                    
                    $sql = "SELECT id FROM apontamentos_ponto WHERE usuario_id = ? AND data = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$usuario_id, $data_ponto]);
                    $apontamento_existente = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($apontamento_existente) {
                        // Verifica se houve mudança (conflito)
                        $conflito = self::resolverConflito(
                            $ponto,
                            $apontamento_existente,
                            $usuario_id,
                            $data_ponto
                        );
                        
                        if ($conflito) {
                            $conflitos++;
                        }
                    } else {
                        // Cria novo apontamento
                        $numero_batida = $ponto['numero_batida'] ?? 1;
                        
                        if ($ponto['tipo'] === 'entrada') {
                            Ponto::registrarEntrada(
                                $usuario_id,
                                $numero_batida,
                                $ponto['hora'] ?? null,
                                $ponto['foto'] ?? null,
                                $ponto['geo_lat'] ?? null,
                                $ponto['geo_lng'] ?? null,
                                $ponto['geo_precisao'] ?? null,
                                $ponto['ip'] ?? $_SERVER['REMOTE_ADDR'],
                                $ponto['device_id'] ?? null,
                                $ponto['user_agent'] ?? null
                            );
                        } else {
                            Ponto::registrarSaida(
                                $usuario_id,
                                $numero_batida,
                                $ponto['hora'] ?? null,
                                $ponto['foto'] ?? null,
                                $ponto['geo_lat'] ?? null,
                                $ponto['geo_lng'] ?? null,
                                $ponto['geo_precisao'] ?? null,
                                $ponto['ip'] ?? $_SERVER['REMOTE_ADDR'],
                                $ponto['device_id'] ?? null,
                                $ponto['user_agent'] ?? null
                            );
                        }
                        
                        $sucesso++;
                    }
                } catch (Exception $e) {
                    $erros[] = [
                        'data' => $ponto['data'] ?? 'desconhecida',
                        'mensagem' => $e->getMessage()
                    ];
                }
            }
            
            // Registra a sincronização
            if ($data_offline) {
                $sql = "INSERT INTO sincronizacoes_offline 
                        (usuario_id, data_offline, data_online, timestamp_volta, pontos_synced, conflitos)
                        VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usuario_id, $data_offline, $data_online, $timestamp_volta, $sucesso, $conflitos]);
            }
            
            return [
                'status' => 'sucesso',
                'sucesso' => $sucesso,
                'conflitos' => $conflitos,
                'erros' => $erros,
                'timestamp_volta' => $timestamp_volta
            ];
        } catch (PDOException $e) {
            error_log("Erro ao sincronizar offline: " . $e->getMessage());
            return [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Resolve conflito quando um ponto foi batido offline E online
     * Estratégia: merge inteligente
     * 
     * @return bool true se houve conflito e foi resolvido
     */
    public static function resolverConflito($ponto_offline, $apontamento_servidor, $usuario_id, $data_ponto) {
        $pdo = Database::getConnection();
        
        // Se a diferença entre offline e online é menor que 20 minutos, considera resolvido
        $hora_offline = strtotime($ponto_offline['hora'] ?? 'now');
        $tipo_offline = $ponto_offline['tipo'];
        $numero_batida = $ponto_offline['numero_batida'] ?? 1;
        
        $col_hora_offline = "hora_{$tipo_offline}_{$numero_batida}";
        $hora_servidor = $apontamento_servidor[$col_hora_offline] ?? null;
        
        if (!$hora_servidor) {
            // Se servidor não tem, usa o offline
            return false;
        }
        
        $hora_servidor_time = strtotime($hora_servidor);
        $diferenca_segundos = abs($hora_offline - $hora_servidor_time);
        $diferenca_minutos = $diferenca_segundos / 60;
        
        if ($diferenca_minutos < 20) {
            // Mantém o registro do servidor, considera resolvido
            AuditoriaAlteracao::registrarAlteracao(
                $apontamento_servidor['id'],
                $usuario_id,
                'sincronizacao_offline_resolvida_merge',
                json_encode(['hora_offline' => date('H:i:s', $hora_offline)]),
                json_encode(['hora_servidor' => $hora_servidor]),
                "Conflito de sincronização resolvido: offline ({$diferenca_minutos}min de diferença)"
            );
            
            return true;
        }
        
        // Se diferença > 20 minutos, atualiza com o offline
        $col = "hora_{$tipo_offline}_$numero_batida";
        $sql = "UPDATE apontamentos_ponto SET $col = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([date('H:i:s', $hora_offline), $apontamento_servidor['id']]);
        
        AuditoriaAlteracao::registrarAlteracao(
            $apontamento_servidor['id'],
            $usuario_id,
            'sincronizacao_offline_resolvida_sobrescrita',
            json_encode(['hora_servidor' => $hora_servidor]),
            json_encode(['hora_offline' => date('H:i:s', $hora_offline)]),
            "Conflito de sincronização: offline sobrescreveu servidor ({$diferenca_minutos}min de diferença)"
        );
        
        return true;
    }
    
    /**
     * Marca um apontamento como sincronizado
     */
    public static function marcarComoSincronizado($apontamento_id) {
        $pdo = Database::getConnection();
        
        try {
            // Adiciona tag de sincronizado na observação
            $sql = "SELECT observacao FROM apontamentos_ponto WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$apontamento_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $obs_nova = ($result['observacao'] ?? '') . ' [SINCRONIZADO]';
            
            $sql = "UPDATE apontamentos_ponto SET observacao = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([trim($obs_nova), $apontamento_id]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao marcar como sincronizado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtém histórico de sincronizações de um usuário
     */
    public static function obterHistoricoSincronizacoes($usuario_id) {
        $pdo = Database::getConnection();
        
        $sql = "SELECT * FROM sincronizacoes_offline WHERE usuario_id = ? ORDER BY sincronizado_em DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Conta quantos pontos estão pendentes de sincronização (offline)
     */
    public static function contarPendentesSinc($usuario_id) {
        $pdo = Database::getConnection();
        
        $sql = "SELECT COUNT(*) FROM apontamentos_ponto 
                WHERE usuario_id = ? AND observacao LIKE '%OFFLINE%'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        
        return $stmt->fetchColumn();
    }
}
?>
