<?php
require_once __DIR__ . '/../config/database.php';

class Ponto {
    
    /**
     * Registra uma entrada de ponto
     * 
     * @param int $usuario_id
     * @param int $numero_batida (1, 2 ou 3)
     * @param string|null $hora_entrada (HH:MM ou HH:MM:SS, null = usa NOW())
     * @param string|null $foto_path (path do arquivo armazenado)
     * @param string|null $geo_lat (formato: -23.5505)
     * @param string|null $geo_lng (formato: -46.6333)
     * @param int|null $geo_precisao (em metros)
     * @param string|null $ip_origem
     * @param string|null $device_id (fingerprint)
     * @param string|null $user_agent
     * @return bool
     */
    public static function registrarEntrada(
        $usuario_id,
        $numero_batida = 1,
        $hora_entrada = null,
        $foto_path = null,
        $geo_lat = null,
        $geo_lng = null,
        $geo_precisao = null,
        $ip_origem = null,
        $device_id = null,
        $user_agent = null
    ) {
        $pdo = Database::getConnection();
        $hora = $hora_entrada ?? date('H:i:s');
        $data = date('Y-m-d');
        
        // Colunas dinamicamente baseadas no numero_batida
        $col_hora_entrada = "hora_entrada_$numero_batida";
        $col_foto_entrada = "foto_entrada_$numero_batida";
        $col_geo_entrada = "geo_entrada_$numero_batida";
        $col_geo_precisao_entrada = "geo_precisao_entrada_$numero_batida";
        $col_ip_entrada = "ip_origem_entrada_$numero_batida";
        $col_device_entrada = "device_id_entrada_$numero_batida";
        $col_user_agent_entrada = "user_agent_entrada_$numero_batida";
        
        try {
            // Verifica se já existe apontamento do dia
            $sql = "SELECT id FROM apontamentos_ponto WHERE usuario_id = ? AND data = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id, $data]);
            $apontamento = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($apontamento) {
                // Atualiza apontamento existente
                $apontamento_id = $apontamento['id'];
                $geo_str = $geo_lat && $geo_lng ? "$geo_lat,$geo_lng" : null;
                
                $sql = "UPDATE apontamentos_ponto SET 
                        $col_hora_entrada = ?,
                        $col_foto_entrada = ?,
                        $col_geo_entrada = ?,
                        $col_geo_precisao_entrada = ?,
                        $col_ip_entrada = ?,
                        $col_device_entrada = ?,
                        $col_user_agent_entrada = ?
                        WHERE id = ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $hora,
                    $foto_path,
                    $geo_str,
                    $geo_precisao,
                    $ip_origem,
                    $device_id,
                    $user_agent,
                    $apontamento_id
                ]);
                
                // Registra na auditoria
                AuditoriaAlteracao::registrarAlteracao(
                    $apontamento_id,
                    $usuario_id,
                    'entrada_criada',
                    null,
                    json_encode(['hora' => $hora, 'batida' => $numero_batida]),
                    'Entrada registrada'
                );
            } else {
                // Cria novo apontamento
                $geo_str = $geo_lat && $geo_lng ? "$geo_lat,$geo_lng" : null;
                
                $sql = "INSERT INTO apontamentos_ponto (
                        usuario_id, data, $col_hora_entrada, $col_foto_entrada,
                        $col_geo_entrada, $col_geo_precisao_entrada, $col_ip_entrada,
                        $col_device_entrada, $col_user_agent_entrada, status
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'presente')";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $usuario_id,
                    $data,
                    $hora,
                    $foto_path,
                    $geo_str,
                    $geo_precisao,
                    $ip_origem,
                    $device_id,
                    $user_agent
                ]);
                
                $apontamento_id = $pdo->lastInsertId();
                
                // Registra na auditoria
                AuditoriaAlteracao::registrarAlteracao(
                    $apontamento_id,
                    $usuario_id,
                    'entrada_criada',
                    null,
                    json_encode(['hora' => $hora, 'batida' => $numero_batida]),
                    'Primeira entrada do dia'
                );
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao registrar entrada: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registra uma saída de ponto
     */
    public static function registrarSaida(
        $usuario_id,
        $numero_batida = 1,
        $hora_saida = null,
        $foto_path = null,
        $geo_lat = null,
        $geo_lng = null,
        $geo_precisao = null,
        $ip_origem = null,
        $device_id = null,
        $user_agent = null
    ) {
        $pdo = Database::getConnection();
        $hora = $hora_saida ?? date('H:i:s');
        $data = date('Y-m-d');
        
        $col_hora_saida = "hora_saida_$numero_batida";
        $col_foto_saida = "foto_saida_$numero_batida";
        $col_geo_saida = "geo_saida_$numero_batida";
        $col_geo_precisao_saida = "geo_precisao_saida_$numero_batida";
        $col_ip_saida = "ip_origem_saida_$numero_batida";
        $col_device_saida = "device_id_saida_$numero_batida";
        $col_user_agent_saida = "user_agent_saida_$numero_batida";
        
        try {
            $sql = "SELECT id FROM apontamentos_ponto WHERE usuario_id = ? AND data = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id, $data]);
            $apontamento = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$apontamento) {
                error_log("Tentativa de registrar saída sem entrada: usuario_id=$usuario_id");
                return false;
            }
            
            $apontamento_id = $apontamento['id'];
            $geo_str = $geo_lat && $geo_lng ? "$geo_lat,$geo_lng" : null;
            
            $sql = "UPDATE apontamentos_ponto SET 
                    $col_hora_saida = ?,
                    $col_foto_saida = ?,
                    $col_geo_saida = ?,
                    $col_geo_precisao_saida = ?,
                    $col_ip_saida = ?,
                    $col_device_saida = ?,
                    $col_user_agent_saida = ?
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $hora,
                $foto_path,
                $geo_str,
                $geo_precisao,
                $ip_origem,
                $device_id,
                $user_agent,
                $apontamento_id
            ]);
            
            // Registra na auditoria
            AuditoriaAlteracao::registrarAlteracao(
                $apontamento_id,
                $usuario_id,
                'saida_criada',
                null,
                json_encode(['hora' => $hora, 'batida' => $numero_batida]),
                'Saída registrada'
            );
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao registrar saída: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtém o último apontamento do dia de um usuário
     */
    public static function obterApontamentoDia($usuario_id, $data = null) {
        $pdo = Database::getConnection();
        $data = $data ?? date('Y-m-d');
        
        $sql = "SELECT * FROM apontamentos_ponto WHERE usuario_id = ? AND data = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $data]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtém última batida do dia (entrada OU saída mais recente)
     */
    public static function obterUltimaBatidaDia($usuario_id, $data = null) {
        $apontamento = self::obterApontamentoDia($usuario_id, $data);
        
        if (!$apontamento) {
            return null;
        }
        
        // Verifica qual batida é a mais recente
        $ultimos_tempos = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $entrada = $apontamento["hora_entrada_$i"];
            $saida = $apontamento["hora_saida_$i"];
            
            if ($entrada) {
                $ultimos_tempos[] = strtotime($entrada);
            }
            if ($saida) {
                $ultimos_tempos[] = strtotime($saida);
            }
        }
        
        return empty($ultimos_tempos) ? null : max($ultimos_tempos);
    }
    
    /**
     * Valida se geolocalização está dentro do raio permitido
     */
    public static function validarGeolocalizacao($geo_lat, $geo_lng, $empresa_id = 1) {
        $pdo = Database::getConnection();
        
        $sql = "SELECT latitude, longitude, raio_metros FROM geolocation_empresa 
                WHERE empresa_id = ? AND ativo = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$empresa_id]);
        $local = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$local) {
            // Se não tem geolocalização cadastrada, permite
            return true;
        }
        
        $distancia = self::calcularDistancia(
            $geo_lat,
            $geo_lng,
            $local['latitude'],
            $local['longitude']
        );
        
        return $distancia <= $local['raio_metros'];
    }
    
    /**
     * Calcula distância entre dois pontos em metros (Haversine formula)
     */
    public static function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
        $R = 6371000; // Raio da Terra em metros
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $R * $c;
    }
    
    /**
     * Valida se dispositivo está autorizado para bater ponto
     */
    public static function validarDispositivo($usuario_id, $device_id) {
        $pdo = Database::getConnection();
        
        // Verifica config se permite múltiplas máquinas
        $config = self::obterConfiguracaoPonto();
        
        if ($config['modo_multiplas_maquinas']) {
            // Permite qualquer dispositivo
            return true;
        }
        
        // Modo restrito: apenas 1 dispositivo/usuário
        $sql = "SELECT COUNT(*) FROM dispositivos_autorizados 
                WHERE usuario_id = ? AND device_id = ? AND ativo = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $device_id]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Incrementa contador de batidas do dia
     */
    public static function incrementarBatida($usuario_id) {
        // Lógica: verifica quantas batidas já tem no dia
        // Retorna o número da próxima batida (1, 2 ou 3)
        $apontamento = self::obterApontamentoDia($usuario_id);
        
        if (!$apontamento) {
            return 1; // Primeira batida
        }
        
        // Conta quantas batidas (entrada ou saída) já foram registradas
        $batida_count = 0;
        for ($i = 1; $i <= 3; $i++) {
            if ($apontamento["hora_entrada_$i"] || $apontamento["hora_saida_$i"]) {
                $batida_count++;
            }
        }
        
        // Retorna próxima batida (máximo 3)
        return min($batida_count + 1, 3);
    }
    
    /**
     * Calcula horas trabalhadas em um apontamento
     */
    public static function calcularHorasTrabalhas($apontamento) {
        $total_horas = 0;
        
        for ($i = 1; $i <= 3; $i++) {
            $entrada = $apontamento["hora_entrada_$i"];
            $saida = $apontamento["hora_saida_$i"];
            
            if ($entrada && $saida) {
                $time_entrada = strtotime($entrada);
                $time_saida = strtotime($saida);
                $diferenca_segundos = $time_saida - $time_entrada;
                $horas = $diferenca_segundos / 3600;
                $total_horas += $horas;
            }
        }
        
        return round($total_horas, 2);
    }
    
    /**
     * Calcula saldo de horas do mês para um usuário
     */
    public static function calcularSaldoHoras($usuario_id, $mes = null, $ano = null) {
        $pdo = Database::getConnection();
        $mes = $mes ?? date('m');
        $ano = $ano ?? date('Y');
        
        $usuario = self::obterUsuario($usuario_id);
        $carga_horaria = $usuario['carga_horaria_diaria'] ?? 8;
        
        // Total de horas na folha
        $sql = "SELECT SUM(
                    TIME_TO_SEC(hora_saida_1) - TIME_TO_SEC(hora_entrada_1) +
                    COALESCE(TIME_TO_SEC(hora_saida_2) - TIME_TO_SEC(hora_entrada_2), 0) +
                    COALESCE(TIME_TO_SEC(hora_saida_3) - TIME_TO_SEC(hora_entrada_3), 0)
                ) as total_segundos
                FROM apontamentos_ponto
                WHERE usuario_id = ? AND MONTH(data) = ? AND YEAR(data) = ? AND status = 'presente'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $mes, $ano]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total_segundos = $result['total_segundos'] ?? 0;
        $total_horas = $total_segundos / 3600;
        
        // Dias úteis do mês
        $dias_uteis = self::contarDiasUteisMes($mes, $ano);
        $horas_previstas = $dias_uteis * $carga_horaria;
        
        $saldo = $total_horas - $horas_previstas;
        
        return round($saldo, 2);
    }
    
    /**
     * Conta dias úteis (seg-sex, excluindo feriados)
     */
    public static function contarDiasUteisMes($mes, $ano) {
        $config = self::obterConfiguracaoPonto();
        $feriados = $config['lista_feriados'] ? json_decode($config['lista_feriados'], true) : [];
        
        $dias_uteis = 0;
        $ultimo_dia = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
        
        for ($dia = 1; $dia <= $ultimo_dia; $dia++) {
            $data = sprintf("%04d-%02d-%02d", $ano, $mes, $dia);
            $dia_semana = date('N', strtotime($data)); // 1=seg ... 7=dom
            
            // Se é seg-sex (1-5) e não é feriado
            if ($dia_semana < 6 && !in_array($data, $feriados)) {
                $dias_uteis++;
            }
        }
        
        return $dias_uteis;
    }
    
    /**
     * Verifica se ultrapassou tolerância de atraso
     */
    public static function excedeuToleranciAtraso($hora_entrada) {
        $config = self::obterConfiguracaoPonto();
        $tolerancia = $config['tolerancia_atraso_minutos'] ?? 5;
        $horario_inicio = $config['horario_inicio_expediente'] ?? '08:00:00';
        
        $time_entrada = strtotime($hora_entrada);
        $time_inicio = strtotime($horario_inicio);
        
        $diferenca_minutos = ($time_entrada - $time_inicio) / 60;
        
        return $diferenca_minutos > $tolerancia;
    }
    
    /**
     * Lista jornada de um usuário entre datas
     */
    public static function listarJornadaUsuario($usuario_id, $data_inicio, $data_fim) {
        $pdo = Database::getConnection();
        
        $sql = "SELECT * FROM apontamentos_ponto 
                WHERE usuario_id = ? AND data BETWEEN ? AND ?
                ORDER BY data ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $data_inicio, $data_fim]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtém configuração global de ponto
     */
    public static function obterConfiguracaoPonto() {
        $pdo = Database::getConnection();
        
        $sql = "SELECT * FROM configuracao_ponto WHERE id = 1";
        $result = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: [
            'tolerancia_atraso_minutos' => 5,
            'horario_inicio_expediente' => '08:00:00',
            'horario_fim_expediente' => '17:00:00',
            'usar_geolocalizacao' => 0,
            'raio_permitido_metros' => 500,
            'quantidade_batidas' => 2,
            'modo_multiplas_maquinas' => 0
        ];
    }
    
    /**
     * Obtém dados do usuário
     */
    public static function obterUsuario($usuario_id) {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
