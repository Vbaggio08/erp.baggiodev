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
    public static function calcularHorasTrabalhadas($apontamento) {
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
    public static function excedeuToleranciAtraso($hora_entrada, $usuario_id = null) {
        $config = self::obterConfiguracaoPonto();
        $tolerancia = $config['tolerancia_atraso_minutos'] ?? 5;
        
        // Usar horário individual do funcionário se disponível
        $horario_inicio = $config['horario_inicio_expediente'] ?? '08:00:00';
        if ($usuario_id) {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT horario_entrada_1 FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($usuario && $usuario['horario_entrada_1']) {
                $horario_inicio = $usuario['horario_entrada_1'];
            }
        }
        
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
        
        $apontamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular total_horas para cada apontamento
        foreach ($apontamentos as &$apt) {
            $horas = 0;
            // Batida 1
            if (!empty($apt['hora_entrada_1']) && !empty($apt['hora_saida_1'])) {
                $entrada = strtotime($apt['hora_entrada_1']);
                $saida = strtotime($apt['hora_saida_1']);
                if ($entrada !== false && $saida !== false) {
                    $horas += ($saida - $entrada) / 3600;
                }
            }
            // Batida 2
            if (!empty($apt['hora_entrada_2']) && !empty($apt['hora_saida_2'])) {
                $entrada = strtotime($apt['hora_entrada_2']);
                $saida = strtotime($apt['hora_saida_2']);
                if ($entrada !== false && $saida !== false) {
                    $horas += ($saida - $entrada) / 3600;
                }
            }
            // Batida 3
            if (!empty($apt['hora_entrada_3']) && !empty($apt['hora_saida_3'])) {
                $entrada = strtotime($apt['hora_entrada_3']);
                $saida = strtotime($apt['hora_saida_3']);
                if ($entrada !== false && $saida !== false) {
                    $horas += ($saida - $entrada) / 3600;
                }
            }
            $apt['total_horas'] = round($horas, 2);
        }
        
        return $apontamentos;
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
     * Lê configurações adicionais de escala e batidas (tabela chave/valor).
     */
    public static function obterConfiguracaoEscalasBatidas(): array {
        $pdo = Database::getConnection();

        $defaults = [
            'regra_incompleto_fim_dia' => true,
            'batidas_padrao_dia' => 4,
            'dias_ativos' => [
                'seg' => true,
                'ter' => true,
                'qua' => true,
                'qui' => true,
                'sex' => true,
                'sab' => false,
                'dom' => false,
            ],
            'batidas_por_dia' => [
                'seg' => 4,
                'ter' => 4,
                'qua' => 4,
                'qui' => 4,
                'sex' => 4,
                'sab' => 2,
                'dom' => 0,
            ],
        ];

        $sql = "SELECT chave, valor FROM configuracoes_ponto WHERE chave IN (
                    'ponto_regra_incompleto_fim_dia',
                    'ponto_batidas_padrao_dia',
                    'ponto_dias_ativos',
                    'ponto_batidas_por_dia'
                )";
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            switch ($row['chave']) {
                case 'ponto_regra_incompleto_fim_dia':
                    $defaults['regra_incompleto_fim_dia'] = in_array((string)$row['valor'], ['1', 'true', 'on'], true);
                    break;
                case 'ponto_batidas_padrao_dia':
                    $defaults['batidas_padrao_dia'] = max(0, (int)$row['valor']);
                    break;
                case 'ponto_dias_ativos':
                    $parsed = json_decode((string)$row['valor'], true);
                    if (is_array($parsed)) {
                        $defaults['dias_ativos'] = array_merge($defaults['dias_ativos'], $parsed);
                    }
                    break;
                case 'ponto_batidas_por_dia':
                    $parsed = json_decode((string)$row['valor'], true);
                    if (is_array($parsed)) {
                        $defaults['batidas_por_dia'] = array_merge($defaults['batidas_por_dia'], $parsed);
                    }
                    break;
            }
        }

        return $defaults;
    }

    /**
     * Salva configurações adicionais de escala e batidas.
     */
    public static function salvarConfiguracaoEscalasBatidas(array $dados): bool {
        $pdo = Database::getConnection();

        $regra = !empty($dados['regra_incompleto_fim_dia']) ? '1' : '0';
        $padrao = max(0, (int)($dados['batidas_padrao_dia'] ?? 4));

        $dias_ativos = $dados['dias_ativos'] ?? [];
        if (!is_array($dias_ativos)) {
            $dias_ativos = [];
        }

        $batidas_por_dia = $dados['batidas_por_dia'] ?? [];
        if (!is_array($batidas_por_dia)) {
            $batidas_por_dia = [];
        }

        $itens = [
            'ponto_regra_incompleto_fim_dia' => [$regra, 'Marcar apontamento como incompleto ao final do dia', 'bool'],
            'ponto_batidas_padrao_dia' => [(string)$padrao, 'Quantidade padrao de batidas por dia', 'int'],
            'ponto_dias_ativos' => [json_encode($dias_ativos), 'Dias ativos para escala de ponto (JSON)', 'json'],
            'ponto_batidas_por_dia' => [json_encode($batidas_por_dia), 'Quantidade de batidas por dia da semana (JSON)', 'json'],
        ];

        $sql = "INSERT INTO configuracoes_ponto (chave, valor, descricao, tipo)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor), descricao = VALUES(descricao), tipo = VALUES(tipo)";
        $stmt = $pdo->prepare($sql);

        foreach ($itens as $chave => [$valor, $descricao, $tipo]) {
            if (!$stmt->execute([$chave, $valor, $descricao, $tipo])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Restaura os padrões de escala/batidas.
     */
    public static function resetarConfiguracaoEscalasBatidas(): bool {
        return self::salvarConfiguracaoEscalasBatidas([
            'regra_incompleto_fim_dia' => true,
            'batidas_padrao_dia' => 4,
            'dias_ativos' => [
                'seg' => true,
                'ter' => true,
                'qua' => true,
                'qui' => true,
                'sex' => true,
                'sab' => false,
                'dom' => false,
            ],
            'batidas_por_dia' => [
                'seg' => 4,
                'ter' => 4,
                'qua' => 4,
                'qui' => 4,
                'sex' => 4,
                'sab' => 2,
                'dom' => 0,
            ],
        ]);
    }

    /**
     * Obtém a máquina global autorizada para bater ponto por CPF.
     */
    public static function obterMaquinaGlobalAutorizada(): array {
        $pdo = Database::getConnection();
        $chave = 'ponto_maquina_global_autorizada';
        $sql = 'SELECT valor, atualizado_em FROM configuracoes_ponto WHERE chave = ? LIMIT 1';
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([$chave]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [
                'device_id' => '',
                'nome_maquina' => '',
                'autorizado_por' => null,
                'ip_origem' => null,
                'user_agent' => null,
                'atualizado_em' => null,
            ];
        }

        if (!$row || empty($row['valor'])) {
            return [
                'device_id' => '',
                'nome_maquina' => '',
                'autorizado_por' => null,
                'ip_origem' => null,
                'user_agent' => null,
                'atualizado_em' => null,
            ];
        }

        $payload = json_decode((string)$row['valor'], true);
        if (!is_array($payload)) {
            $payload = [];
        }

        return [
            'device_id' => (string)($payload['device_id'] ?? ''),
            'nome_maquina' => (string)($payload['nome_maquina'] ?? ''),
            'autorizado_por' => $payload['autorizado_por'] ?? null,
            'ip_origem' => $payload['ip_origem'] ?? null,
            'user_agent' => $payload['user_agent'] ?? null,
            'atualizado_em' => $row['atualizado_em'] ?? null,
        ];
    }

    /**
     * Define a máquina global autorizada para bater ponto por CPF.
     */
    public static function salvarMaquinaGlobalAutorizada(string $device_id, int $autorizado_por, ?string $ip_origem = null, ?string $user_agent = null, ?string $nome_maquina = null): bool {
        $pdo = Database::getConnection();
        $chave = 'ponto_maquina_global_autorizada';
        $payload = json_encode([
            'device_id' => trim($device_id),
            'nome_maquina' => trim((string)($nome_maquina ?? '')),
            'autorizado_por' => $autorizado_por,
            'ip_origem' => $ip_origem,
            'user_agent' => $user_agent,
            'autorizado_em' => date('Y-m-d H:i:s'),
        ]);

        $sql = "INSERT INTO configuracoes_ponto (chave, valor, descricao, tipo)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor), descricao = VALUES(descricao), tipo = VALUES(tipo)";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            $chave,
            $payload,
            'Device global autorizado para batida por CPF no login',
            'json',
        ]);
    }

    /**
     * Revoga a máquina global autorizada para batida por CPF.
     */
    public static function revogarMaquinaGlobalAutorizada(): bool {
        $pdo = Database::getConnection();
        $chave = 'ponto_maquina_global_autorizada';
        $sql = 'DELETE FROM configuracoes_ponto WHERE chave = ? LIMIT 1';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$chave]);
    }

    /**
     * Garante a existência da tabela de configuração individual de ponto.
     */
    private static function garantirTabelaConfiguracaoUsuario(): void {
        $pdo = Database::getConnection();
        $sql = "CREATE TABLE IF NOT EXISTS configuracao_ponto_usuario (
                    id INT NOT NULL AUTO_INCREMENT,
                    usuario_id INT NOT NULL,
                    permite_horas_extras TINYINT(1) NOT NULL DEFAULT 1,
                    batidas_padrao_dia INT NOT NULL DEFAULT 4,
                    dias_ativos_json TEXT NULL,
                    batidas_por_dia_json TEXT NULL,
                    horario_entrada_1 TIME NULL,
                    horario_saida_1 TIME NULL,
                    horario_entrada_2 TIME NULL,
                    horario_saida_2 TIME NULL,
                    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY uq_config_ponto_usuario (usuario_id),
                    KEY idx_config_ponto_usuario_user (usuario_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);
    }

    /**
     * Retorna configurações de ponto individuais para um usuário.
     */
    public static function obterConfiguracaoUsuarioPonto(int $usuario_id): array {
        self::garantirTabelaConfiguracaoUsuario();

        $usuario_id = max(0, (int)$usuario_id);
        $escala_global = self::obterConfiguracaoEscalasBatidas();
        $config = [
            'usuario_id' => $usuario_id,
            'permite_horas_extras' => true,
            'batidas_padrao_dia' => (int)($escala_global['batidas_padrao_dia'] ?? 4),
            'dias_ativos' => $escala_global['dias_ativos'] ?? [],
            'batidas_por_dia' => $escala_global['batidas_por_dia'] ?? [],
            'horario_entrada_1' => '08:00',
            'horario_saida_1' => '12:00',
            'horario_entrada_2' => '13:00',
            'horario_saida_2' => '18:00',
        ];

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM configuracao_ponto_usuario WHERE usuario_id = ? LIMIT 1');
        $stmt->execute([$usuario_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $config['permite_horas_extras'] = !empty($row['permite_horas_extras']);
            $config['batidas_padrao_dia'] = max(0, (int)($row['batidas_padrao_dia'] ?? $config['batidas_padrao_dia']));

            $dias = json_decode((string)($row['dias_ativos_json'] ?? ''), true);
            if (is_array($dias)) {
                $config['dias_ativos'] = array_merge($config['dias_ativos'], $dias);
            }

            $batidas = json_decode((string)($row['batidas_por_dia_json'] ?? ''), true);
            if (is_array($batidas)) {
                $config['batidas_por_dia'] = array_merge($config['batidas_por_dia'], $batidas);
            }

            if (!empty($row['horario_entrada_1'])) {
                $config['horario_entrada_1'] = substr((string)$row['horario_entrada_1'], 0, 5);
            }
            if (!empty($row['horario_saida_1'])) {
                $config['horario_saida_1'] = substr((string)$row['horario_saida_1'], 0, 5);
            }
            if (!empty($row['horario_entrada_2'])) {
                $config['horario_entrada_2'] = substr((string)$row['horario_entrada_2'], 0, 5);
            }
            if (!empty($row['horario_saida_2'])) {
                $config['horario_saida_2'] = substr((string)$row['horario_saida_2'], 0, 5);
            }
        }

        // Complementa com os horários da tabela de usuários quando disponíveis.
        $stmtUser = $pdo->prepare('SELECT horario_entrada_1, horario_saida_1, horario_entrada_2, horario_saida_2 FROM usuarios WHERE id = ? LIMIT 1');
        try {
            $stmtUser->execute([$usuario_id]);
            $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC) ?: [];
            foreach (['horario_entrada_1', 'horario_saida_1', 'horario_entrada_2', 'horario_saida_2'] as $campo) {
                if (!empty($usuario[$campo])) {
                    $config[$campo] = substr((string)$usuario[$campo], 0, 5);
                }
            }
        } catch (\Throwable $e) {
            // Ambientes legados podem não ter essas colunas na tabela de usuários.
        }

        return $config;
    }

    /**
     * Salva configurações de ponto individuais de um usuário.
     */
    public static function salvarConfiguracaoUsuarioPonto(int $usuario_id, array $dados): bool {
        self::garantirTabelaConfiguracaoUsuario();

        $usuario_id = max(0, (int)$usuario_id);
        if ($usuario_id <= 0) {
            return false;
        }

        $dias_ativos = is_array($dados['dias_ativos'] ?? null) ? $dados['dias_ativos'] : [];
        $batidas_por_dia = is_array($dados['batidas_por_dia'] ?? null) ? $dados['batidas_por_dia'] : [];

        $horario_entrada_1 = trim((string)($dados['horario_entrada_1'] ?? '08:00'));
        $horario_saida_1 = trim((string)($dados['horario_saida_1'] ?? '12:00'));
        $horario_entrada_2 = trim((string)($dados['horario_entrada_2'] ?? '13:00'));
        $horario_saida_2 = trim((string)($dados['horario_saida_2'] ?? '18:00'));

        $pdo = Database::getConnection();
        $sql = "INSERT INTO configuracao_ponto_usuario
                    (usuario_id, permite_horas_extras, batidas_padrao_dia, dias_ativos_json, batidas_por_dia_json,
                     horario_entrada_1, horario_saida_1, horario_entrada_2, horario_saida_2)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    permite_horas_extras = VALUES(permite_horas_extras),
                    batidas_padrao_dia = VALUES(batidas_padrao_dia),
                    dias_ativos_json = VALUES(dias_ativos_json),
                    batidas_por_dia_json = VALUES(batidas_por_dia_json),
                    horario_entrada_1 = VALUES(horario_entrada_1),
                    horario_saida_1 = VALUES(horario_saida_1),
                    horario_entrada_2 = VALUES(horario_entrada_2),
                    horario_saida_2 = VALUES(horario_saida_2)";
        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([
            $usuario_id,
            !empty($dados['permite_horas_extras']) ? 1 : 0,
            max(0, (int)($dados['batidas_padrao_dia'] ?? 4)),
            json_encode($dias_ativos),
            json_encode($batidas_por_dia),
            $horario_entrada_1,
            $horario_saida_1,
            $horario_entrada_2,
            $horario_saida_2,
        ]);

        if (!$ok) {
            return false;
        }

        // Atualiza horários na tabela de usuários para refletir na tela de bater ponto.
        try {
            $stmtUsuarios = $pdo->prepare('UPDATE usuarios
                                           SET horario_entrada_1 = ?, horario_saida_1 = ?, horario_entrada_2 = ?, horario_saida_2 = ?
                                           WHERE id = ?');
            $stmtUsuarios->execute([
                $horario_entrada_1,
                $horario_saida_1,
                $horario_entrada_2,
                $horario_saida_2,
                $usuario_id,
            ]);
        } catch (\Throwable $e) {
            // Ambientes legados podem não ter colunas de horário em usuários.
        }

        return true;
    }

    /**
     * Aplica marcação automática de apontamentos incompletos para dias anteriores.
     */
    public static function aplicarRegraIncompletoFimDia(?string $data_limite = null): int {
        $pdo = Database::getConnection();
        $cfg = self::obterConfiguracaoEscalasBatidas();

        if (empty($cfg['regra_incompleto_fim_dia'])) {
            return 0;
        }

        $limite = $data_limite ?: date('Y-m-d', strtotime('-1 day'));
        $sql = "SELECT id, data, status,
                       hora_entrada_1, hora_saida_1,
                       hora_entrada_2, hora_saida_2,
                       hora_entrada_3, hora_saida_3
                FROM apontamentos_ponto
                WHERE data <= ?
                  AND status <> 'incompleta'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limite]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dias_ativos = $cfg['dias_ativos'] ?? [];
        $batidas_por_dia = $cfg['batidas_por_dia'] ?? [];
        $batidas_padrao = max(0, (int)($cfg['batidas_padrao_dia'] ?? 4));

        $mapeamento = [1 => 'seg', 2 => 'ter', 3 => 'qua', 4 => 'qui', 5 => 'sex', 6 => 'sab', 7 => 'dom'];
        $ids_incompletos = [];

        foreach ($rows as $row) {
            $dia_semana = (int)date('N', strtotime($row['data']));
            $dia_chave = $mapeamento[$dia_semana] ?? 'seg';
            $ativo = $dias_ativos[$dia_chave] ?? true;

            if (!$ativo) {
                continue;
            }

            $esperadas = isset($batidas_por_dia[$dia_chave])
                ? max(0, (int)$batidas_por_dia[$dia_chave])
                : $batidas_padrao;

            if ($esperadas <= 0) {
                continue;
            }

            $realizadas = 0;
            $campos = [
                'hora_entrada_1', 'hora_saida_1',
                'hora_entrada_2', 'hora_saida_2',
                'hora_entrada_3', 'hora_saida_3',
            ];
            foreach ($campos as $campo) {
                if (!empty($row[$campo])) {
                    $realizadas++;
                }
            }

            if ($realizadas > 0 && $realizadas < $esperadas) {
                $ids_incompletos[] = (int)$row['id'];
            }
        }

        if (empty($ids_incompletos)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids_incompletos), '?'));
        $sql = "UPDATE apontamentos_ponto
                SET status = 'incompleta', atualizado_em = NOW()
                WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids_incompletos);

        return $stmt->rowCount();
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
