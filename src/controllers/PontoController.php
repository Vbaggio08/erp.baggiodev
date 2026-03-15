<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Ponto.php';
require_once __DIR__ . '/../models/AuditoriaAlteracao.php';
require_once __DIR__ . '/../models/DispositivoAuto.php';
require_once __DIR__ . '/../models/SyncOffline.php';

class PontoController {
    
    /**
     * Tela principal: Bater Ponto
     */
    public function baterPonto() {
        $this->verificarLogin();
        $usuario_id = $_SESSION['user_id'];
        
        // Obtém apontamento de hoje
        $apontamento = Ponto::obterApontamentoDia($usuario_id);
        $config = Ponto::obterConfiguracaoPonto();
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/ponto_bater.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * AJAX: Bater ponto (entrada ou saída)
     * Retorna JSON
     */
    public function baterPontoAjax() {
        $this->verificarLogin();
        header('Content-Type: application/json');
        
        $usuario_id = $_SESSION['user_id'];
        $tipo = $_POST['tipo'] ?? 'entrada'; // entrada ou saida
        $numero_batida = (int)($_POST['numero_batida'] ?? 1);
        $foto_base64 = $_POST['foto'] ?? null;
        $geo_lat = $_POST['geo_lat'] ?? null;
        $geo_lng = $_POST['geo_lng'] ?? null;
        $geo_precisao = $_POST['geo_precisao'] ?? null;
        $device_id = $_POST['device_id'] ?? null;
        
        try {
            // Valida proximidade de batida (< 5 minutos)
            $ultima_batida = Ponto::obterUltimaBatidaDia($usuario_id);
            if ($ultima_batida) {
                $agora = time();
                $minutos_decorridos = ($agora - $ultima_batida) / 60;
                $config = Ponto::obterConfiguracaoPonto();
                $limiar = $config['limiar_proximidade_minutos'] ?? 5;
                
                if ($minutos_decorridos < $limiar) {
                    http_response_code(202); // ACCEPTED - requer confirmação
                    return json_encode([
                        'status' => 'validacao_requerida',
                        'ultima_batida' => date('H:i:s', $ultima_batida),
                        'minutos_decorridos' => round($minutos_decorridos, 2),
                        'mensagem' => 'Batida muito próxima. Confirme a ação.'
                    ]);
                }
            }
            
            // Processa foto se enviada
            $foto_path = null;
            if ($foto_base64) {
                $foto_path = $this->salvarFoto($usuario_id, $foto_base64, $tipo, $numero_batida);
            }
            
            // Registra a batida
            if ($tipo === 'entrada') {
                $resultado = Ponto::registrarEntrada(
                    $usuario_id,
                    $numero_batida,
                    null, // hora = agora
                    $foto_path,
                    $geo_lat,
                    $geo_lng,
                    $geo_precisao,
                    $_SERVER['REMOTE_ADDR'],
                    $device_id,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                );
            } else {
                $resultado = Ponto::registrarSaida(
                    $usuario_id,
                    $numero_batida,
                    null,
                    $foto_path,
                    $geo_lat,
                    $geo_lng,
                    $geo_precisao,
                    $_SERVER['REMOTE_ADDR'],
                    $device_id,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                );
            }
            
            if ($resultado) {
                http_response_code(200);
                $proxima_batida = Ponto::incrementarBatida($usuario_id);
                
                return json_encode([
                    'status' => 'sucesso',
                    'mensagem' => ucfirst($tipo) . ' registrada com sucesso!',
                    'tipo' => $tipo,
                    'numero_batida' => $numero_batida,
                    'proxima_batida' => $proxima_batida,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                http_response_code(500);
                return json_encode([
                    'status' => 'erro',
                    'mensagem' => 'Erro ao registrar batida'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode([
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Confirma ou cancela validação de batida próxima
     */
    public function confirmarAlteracao() {
        $this->verificarLogin();
        header('Content-Type: application/json');
        
        $usuario_id = $_SESSION['user_id'];
        $tipo_acao = $_POST['tipo_acao'] ?? null; // confirmar_saida, alterar_entrada, alterar_saida, cancelada
        
        try {
            $apontamento = Ponto::obterApontamentoDia($usuario_id);
            
            if (!$apontamento) {
                return json_encode(['status' => 'erro', 'mensagem' => 'Sem apontamento para hoje']);
            }
            
            switch ($tipo_acao) {
                case 'confirmar_saida':
                    // Registra saída
                    Ponto::registrarSaida(
                        $usuario_id,
                        1,
                        null,
                        $_POST['foto'] ?? null,
                        $_POST['geo_lat'] ?? null,
                        $_POST['geo_lng'] ?? null,
                        $_POST['geo_precisao'] ?? null,
                        $_SERVER['REMOTE_ADDR'],
                        $_POST['device_id'] ?? null,
                        $_SERVER['HTTP_USER_AGENT'] ?? null
                    );
                    
                    AuditoriaAlteracao::registrarAlteracao(
                        $apontamento['id'],
                        $usuario_id,
                        'validacao_proximidade_confirmada_saida',
                        null,
                        json_encode(['acao' => 'confirmada_saida']),
                        'Usuário confirmou saída após aviso de proximidade'
                    );
                    
                    return json_encode(['status' => 'saida_registrada', 'mensagem' => 'Saída registrada!']);
                    
                case 'cancelada':
                    AuditoriaAlteracao::registrarAlteracao(
                        $apontamento['id'],
                        $usuario_id,
                        'validacao_proximidade_cancelada',
                        null,
                        json_encode(['acao' => 'cancelada']),
                        'Usuário cancelou batida'
                    );
                    
                    return json_encode(['status' => 'cancelada', 'mensagem' => 'Batida cancelada']);
                    
                default:
                    return json_encode(['status' => 'erro', 'mensagem' => 'Ação desconhecida']);
            }
        } catch (Exception $e) {
            return json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }
    
    /**
     * Meu Ponto: últimos 30 dias
     */
    public function meuPonto() {
        $this->verificarLogin();
        $usuario_id = $_SESSION['user_id'];
        
        // Últimos 30 dias
        $data_fim = date('Y-m-d');
        $data_inicio = date('Y-m-d', strtotime('-30 days'));
        
        $apontamentos = Ponto::listarJornadaUsuario($usuario_id, $data_inicio, $data_fim);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/meu_ponto.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * Saldo de Horas
     */
    public function saldoHoras() {
        $this->verificarLogin();
        $usuario_id = $_SESSION['user_id'];
        
        $mes_atual = date('m');
        $ano_atual = date('Y');
        
        $saldo = Ponto::calcularSaldoHoras($usuario_id, $mes_atual, $ano_atual);
        $usuario = Ponto::obterUsuario($usuario_id);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/saldo_horas.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * Dashboard RH: Todos os pontos
     */
    public function listarPontosTodos() {
        $this->verificarRH();
        
        $pdo = Database::getConnection();
        $departamento = $_GET['departamento'] ?? null;
        $periodo = $_GET['periodo'] ?? '30'; // dias
        
        $sql = "SELECT ap.*, u.nome, u.departamento, u.cargo 
                FROM apontamentos_ponto ap
                INNER JOIN usuarios u ON ap.usuario_id = u.id
                WHERE ap.data >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
        
        $params = [$periodo];
        
        if ($departamento) {
            $sql .= " AND u.departamento = ?";
            $params[] = $departamento;
        }
        
        $sql .= " ORDER BY ap.data DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $apontamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/admin/ponto_todos.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * Editar Ponto (RH/Admin)
     */
    public function editarPonto() {
        $this->verificarRH();
        
        $apontamento_id = $_GET['id'] ?? null;
        $pdo = Database::getConnection();
        
        $sql = "SELECT ap.*, u.nome FROM apontamentos_ponto ap INNER JOIN usuarios u ON ap.usuario_id = u.id WHERE ap.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$apontamento_id]);
        $apontamento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/admin/editar_ponto.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * Salvar edição de ponto
     */
    public function salvarEdicaoPonto() {
        $this->verificarRH();
        
        $apontamento_id = $_POST['apontamento_id'];
        $hora_entrada_1 = $_POST['hora_entrada_1'] ?? null;
        $hora_saida_1 = $_POST['hora_saida_1'] ?? null;
        $motivo = $_POST['motivo_alteracao'];
        
        $pdo = Database::getConnection();
        
        // Obtém valores antigos
        $sql = "SELECT * FROM apontamentos_ponto WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$apontamento_id]);
        $anterior = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Atualiza
        $sql = "UPDATE apontamentos_ponto SET hora_entrada_1 = ?, hora_saida_1 = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$hora_entrada_1, $hora_saida_1, $apontamento_id]);
        
        // Registra na auditoria
        AuditoriaAlteracao::registrarAlteracao(
            $apontamento_id,
            $_SESSION['user_id'],
            'entrada_saida_editada',
            json_encode(['entrada' => $anterior['hora_entrada_1'], 'saida' => $anterior['hora_saida_1']]),
            json_encode(['entrada' => $hora_entrada_1, 'saida' => $hora_saida_1]),
            $motivo
        );
        
        // Envia notificação ao usuário (simples)
        // TODO: Implementar sistema de notificações
        
        header('Location: index.php?rota=ponto_todos');
    }
    
    /**
     * Solicitar alteração de ponto (Funcionário)
     */
    public function solicitarAlteracao() {
        $this->verificarLogin();
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/solicitar_alteracao_ponto.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * AJAX: Sincronizar pontos offline
     */
    public function sincronizarOffline() {
        $this->verificarLogin();
        header('Content-Type: application/json');
        
        try {
            $usuario_id = $_SESSION['user_id'];
            $lote_pontos = $_POST['pontos'] ?? [];
            
            $resultado = SyncOffline::sincronizarComServidor($usuario_id, $lote_pontos);
            
            return json_encode($resultado);
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }
    
    /**
     * AJAX: Status de sincronização
     */
    public function statusSincronizacao() {
        $this->verificarLogin();
        header('Content-Type: application/json');
        
        $usuario_id = $_SESSION['user_id'];
        $pendentes = SyncOffline::contarPendentesSinc($usuario_id);
        
        return json_encode(['pendentes' => $pendentes, 'status' => $pendentes > 0 ? 'offline' : 'online']);
    }
    
    /**
     * Relatório de Ponto do Mês (Imprimível)
     */
    public function relatorioPonto() {
        $this->verificarLogin();
        $usuario_id = $_SESSION['user_id'];
        
        $mes = date('m');
        $ano = date('Y');
        
        $apontamentos = Ponto::listarJornadaUsuario(
            $usuario_id,
            "$ano-$mes-01",
            date('Y-m-t', strtotime("$ano-$mes-01"))
        );
        
        $usuario = Ponto::obterUsuario($usuario_id);
        $saldo = Ponto::calcularSaldoHoras($usuario_id, $mes, $ano);
        
        require __DIR__ . '/../views/producao/relatorio_ponto_mes.php';
    }
    
    /**
     * FASE 3: Calcular Saldo Mensal Avançado
     * GET /ponto/saldo-mensal?mes=2026-03&usuario_id=123
     * 
     * Retorna saldo completo com:
     * - Horas trabalhadas vs esperadas
     * - Faltas e atestados
     * - Horas extras aprovadas
     * - Cálculo de DSR
     */
    public function calcularSaldoMensal() {
        $this->verificarLogin();
        header('Content-Type: application/json');
        
        try {
            $usuario_id = intval($_GET['usuario_id'] ?? $_SESSION['user_id']);
            $mes_ano = $_GET['mes'] ?? date('Y-m');
            
            // Apenas RH pode ver de outros usuários
            if ($usuario_id !== $_SESSION['user_id'] && !$this->ehRH()) {
                http_response_code(403);
                return json_encode(['erro' => 'Acesso negado']);
            }
            
            // Integração com FASE 3
            require_once __DIR__ . '/../models/PontoCalculador.php';
            require_once __DIR__ . '/../models/HorasExtras.php';
            require_once __DIR__ . '/../models/Feriados.php';
            
            $calculador = new \Src\Models\PontoCalculador();
            $saldo = $calculador->calcularSaldoMensalUsuario($usuario_id, $mes_ano);
            
            http_response_code(200);
            return json_encode([
                'sucesso' => true,
                'saldo' => $saldo,
                'mes' => $mes_ano,
                'calculado_em' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode([
                'erro' => 'Erro ao calcular saldo',
                'detalhes' => $e->getMessage()
            ]);
        }
    }

    /**
     * FASE 3: Relatório Mensal Avançado com DSR e Horas Extras
     * GET /ponto/relatorio-avancado?mes=2026-03&usuario_id=123
     * 
     * Retorna relatório consolidado:
     * - Saldo mensal
     * - Horas extras registradas e detectadas
     * - DSR por semana
     * - Sugestões de ações
     */
    public function relatorioMensalAvancado() {
        $this->verificarLogin();
        header('Content-Type: application/json');
        
        try {
            $usuario_id = intval($_GET['usuario_id'] ?? $_SESSION['user_id']);
            $mes_ano = $_GET['mes'] ?? date('Y-m');
            
            if ($usuario_id !== $_SESSION['user_id'] && !$this->ehRH()) {
                http_response_code(403);
                return json_encode(['erro' => 'Acesso negado']);
            }
            
            require_once __DIR__ . '/../models/PontoCalculador.php';
            
            $calculador = new \Src\Models\PontoCalculador();
            $relatorio = $calculador->gerarRelatorioMensal($usuario_id, $mes_ano);
            
            http_response_code(200);
            return json_encode([
                'sucesso' => true,
                'relatorio' => $relatorio,
                'formato' => 'avancado_fase3'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * FASE 3: Detectar Horas Extras Automaticamente
     * GET /ponto/detectar-extras?mes=2026-03&usuario_id=123
     * 
     * Analisa apontamentos do mês e sugere horas extras não registradas
     * baseado em limite diário configurado
     */
    public function detectarHorasExtrasAutomaticamente() {
        $this->verificarLogin();
        header('Content-Type: application/json');
        
        try {
            $usuario_id = intval($_GET['usuario_id'] ?? $_SESSION['user_id']);
            $mes_ano = $_GET['mes'] ?? date('Y-m');
            
            if ($usuario_id !== $_SESSION['user_id'] && !$this->ehRH()) {
                http_response_code(403);
                return json_encode(['erro' => 'Acesso negado']);
            }
            
            require_once __DIR__ . '/../models/PontoCalculador.php';
            
            $calculador = new \Src\Models\PontoCalculador();
            $potenciais = $calculador->detectarHorasExtras($usuario_id, $mes_ano);
            
            $total_horas = array_sum(array_map(function($p) { return $p['horas_poten']; }, $potenciais));
            
            http_response_code(200);
            return json_encode([
                'sucesso' => true,
                'total_detectados' => count($potenciais),
                'total_horas' => round($total_horas, 2),
                'potenciais' => $potenciais,
                'recomendacao' => count($potenciais) > 0 
                    ? "Foram detectadas $total_horas horas extras. Revise e registre aquelas que procedem."
                    : "Nenhuma hora extra potencial detectada neste período."
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * FASE 3: Obter Configuração de Ponto do Sistema
     * GET /ponto/configuracao?empresa_id=1
     * 
     * Retorna configurações da FASE 3:
     * - Limites de horas extras
     * - Cálculo de DSR
     * - Tolerâncias
     * - Percentuais
     */
    public function obterConfiguracaoPonto() {
        header('Content-Type: application/json');
        
        try {
            $empresa_id = intval($_GET['empresa_id'] ?? $_SESSION['empresa_id'] ?? 1);
            
            require_once __DIR__ . '/../models/ConfiguracaoPontos.php';
            
            $configuracao = \Src\Models\ConfiguracaoPontos::obterConfiguracao($empresa_id);
            
            http_response_code(200);
            return json_encode([
                'sucesso' => true,
                'configuracao' => $configuracao,
                'empresa_id' => $empresa_id
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * FASE 3: Visualizar DSR da Semana
     * GET /ponto/dsr-semana?data=2026-03-15&usuario_id=123
     * 
     * Retorna cálculo de DSR para a semana (Seg-Dom) contendo a data
     */
    public function visualizarDSRSemana() {
        $this->verificarLogin();
        header('Content-Type: application/json');
        
        try {
            $usuario_id = intval($_GET['usuario_id'] ?? $_SESSION['user_id']);
            $data = $_GET['data'] ?? date('Y-m-d');
            
            if ($usuario_id !== $_SESSION['user_id'] && !$this->ehRH()) {
                http_response_code(403);
                return json_encode(['erro' => 'Acesso negado']);
            }
            
            require_once __DIR__ . '/../models/PontoCalculador.php';
            
            // Converter data para DateTime da semana
            $data_obj = new \DateTime($data);
            // Voltar para segunda-feira
            $dia_semana = (int)$data_obj->format('w');
            if ($dia_semana === 0) {
                $data_obj->modify('-1 day'); // Se domingo, volta para sábado noturno, então para segunda anterior
            }
            
            $calculador = new \Src\Models\PontoCalculador();
            $dsr = $calculador->calcularDSRSemana($usuario_id, $data_obj);
            
            http_response_code(200);
            return json_encode([
                'sucesso' => true,
                'dsr' => $dsr,
                'usuario_id' => $usuario_id
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * Verifica se usuário atual é RH/Manager
     */
    private function ehRH(): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        return isset($_SESSION['user_nivel']) && 
               in_array($_SESSION['user_nivel'], ['admin', 'rh', 'gerente', 'manager']);
    }

    /**
     * Salva foto de ponto
     * Retorna o caminho relativo do arquivo
     */
    private function salvarFoto($usuario_id, $foto_base64, $tipo, $numero_batida) {
        $data = date('Y-m-d');
        $tipo_abrev = substr($tipo, 0, 3); // ent ou sai
        $numero = (int)$numero_batida;
        
        // Decodifica base64
        $img_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $foto_base64));
        
        if (!$img_data) {
            return null;
        }
        
        // Cria diretório se não existir
        $dir = __DIR__ . '/../../assets/uploads/fotos_ponto/' . $data;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        // Nome do arquivo
        $filename = "user_{$usuario_id}_{$tipo}_{$numero}.jpg";
        $filepath = $dir . '/' . $filename;
        
        // Salva arquivo
        file_put_contents($filepath, $img_data);
        
        // Retorna caminho relativo para banco de dados
        return "$data/$filename";
    }

    /**
     * Gerenciar ponto pessoal - NOVO - Menu centralizado
     * GET /index.php?rota=gerenciar_ponto_pessoal
     * Mostra apenas o ponto do usuário logado
     */
    public function gerenciarMeuPonto() {
        $this->verificarLogin();
        $usuario_id = $_SESSION['user_id'];

        require __DIR__ . '/../views/geral/header.php';
        
        // Obter apontamentos do último mês
        $data_inicio = date('Y-m-01');
        $data_fim = date('Y-m-t');
        
        try {
            $apontamentos = Ponto::listarJornadaUsuario($usuario_id, $data_inicio, $data_fim);
            $total_dias = count($apontamentos);
            $total_horas = array_sum(array_column($apontamentos, 'total_horas'));
        } catch (\Exception $e) {
            $apontamentos = [];
            $total_dias = 0;
            $total_horas = 0;
        }
        
        // View com estilo melhorado
        echo '<div class="container-fluid p-4">';
        
        // Header
        echo '<div class="row mb-4">';
        echo '<div class="col-md-8">';
        echo '<h2><i class="fas fa-clock"></i> Gerenciar Meu Ponto</h2>';
        echo '<p class="text-muted">Visualize e edite suas batidas de ponto (mês atual)</p>';
        echo '</div>';
        echo '<div class="col-md-4 text-end">';
        echo '<a href="' . BASE_URL . 'index.php?rota=dashboard_ponto" class="btn btn-outline-primary">';
        echo '<i class="fas fa-chart-line"></i> Voltar ao Dashboard';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        
        // KPI Cards
        echo '<div class="row mb-4">';
        echo '<div class="col-md-4">';
        echo '<div class="card border-left-primary shadow h-100 py-2">';
        echo '<div class="card-body">';
        echo '<div class="text-primary text-uppercase mb-1 small">dias Registrados</div>';
        echo '<div class="h3 mb-0">' . $total_dias . '</div>';
        echo '<small class="text-muted">este mês</small>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="col-md-4">';
        echo '<div class="card border-left-success shadow h-100 py-2">';
        echo '<div class="card-body">';
        echo '<div class="text-success text-uppercase mb-1 small">Total de Horas</div>';
        echo '<div class="h3 mb-0">' . number_format($total_horas, 1, ',', '.') . ' <small>h</small></div>';
        echo '<small class="text-muted">acumuladas</small>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="col-md-4">';
        echo '<div class="card border-left-info shadow h-100 py-2">';
        echo '<div class="card-body">';
        echo '<div class="text-info text-uppercase mb-1 small">Período</div>';
        echo '<div class="h3 mb-0" style="font-size: 1.1rem;">' . date('d/m', strtotime($data_inicio)) . ' a ' . date('d/m') . '</div>';
        echo '<small class="text-muted">mês vigente</small>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        // Tabela
        echo '<div class="card shadow mb-4">';
        echo '<div class="card-header bg-light">';
        echo '<h5 class="mb-0"><i class="fas fa-table"></i> Histórico de Apontamentos</h5>';
        echo '</div>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped table-hover mb-0">';
        echo '<thead class="table-light">';
        echo '<tr class="text-center small">';
        echo '<th>Data</th>';
        echo '<th>Entrada 1</th>';
        echo '<th>Saída 1</th>';
        echo '<th>Entrada 2</th>';
        echo '<th>Saída 2</th>';
        echo '<th>Total</th>';
        echo '<th>Editar</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (empty($apontamentos)) {
            echo '<tr><td colspan="7" class="text-center text-muted py-5">';
            echo '<i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 10px;"></i><br>';
            echo 'Nenhum apontamento neste período';
            echo '</td></tr>';
        } else {
            foreach ($apontamentos as $apt) {
                $data_fmt = date('d/m/Y', strtotime($apt['data'] ?? $apt['data_apontamento'] ?? ''));
                $total = number_format($apt['total_horas'] ?? 0, 2, ',', '.');
                echo '<tr>';
                echo '<td><strong>' . $data_fmt . '</strong></td>';
                echo '<td class="text-center">' . ($apt['hora_entrada_1'] ?? '-') . '</td>';
                echo '<td class="text-center">' . ($apt['hora_saida_1'] ?? '-') . '</td>';
                echo '<td class="text-center">' . ($apt['hora_entrada_2'] ?? '-') . '</td>';
                echo '<td class="text-center">' . ($apt['hora_saida_2'] ?? '-') . '</td>';
                echo '<td class="text-center">';
                echo '<span class="badge bg-primary">' . $total . 'h</span>';
                echo '</td>';
                echo '<td class="text-center">';
                echo '<a href="' . BASE_URL . 'index.php?rota=editar_ponto&id=' . ($apt['id'] ?? 0) . '" class="btn btn-sm btn-outline-warning">';
                echo '<i class="fas fa-edit"></i>';
                echo '</a>';
                echo '</td>';
                echo '</tr>';
            }
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        
        // Botões de ação
        echo '<div class="mb-4">';
        echo '<a href="' . BASE_URL . 'index.php?rota=meu_ponto" class="btn btn-primary">';
        echo '<i class="fas fa-arrow-left"></i> Voltar';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        
        require __DIR__ . '/../views/geral/footer.php';
    }

    /**
     * Gerenciar todos os pontos - ADMIN ONLY - Menu centralizado
     * GET /index.php?rota=gerenciar_ponto_todos
     * Mostra pontos de TODOS os usuários (apenas admin)
     */
    public function gerenciarPontosTodos() {
        $this->verificarLogin();
        
        // Verificação extra de admin
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_admin']) || $_SESSION['usuario_admin'] != 1) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Acesso negado';
            exit;
        }

        $mes = $_GET['mes'] ?? date('m');
        $ano = $_GET['ano'] ?? date('Y');

        require __DIR__ . '/../views/geral/header.php';
        
        echo '<div class="container-fluid mt-4">';
        echo '<div class="row mb-4">';
        echo '<div class="col-md-8">';
        echo '<h2>Gerenciar Pontos de Todos</h2>';
        echo '<p class="text-muted">Visualize e edite pontos de qualquer funcionário</p>';
        echo '</div>';
        echo '<div class="col-md-4">';
        echo '<form method="GET" class="row">';
        echo '<input type="hidden" name="rota" value="gerenciar_ponto_todos">';
        echo '<div class="col-6">';
        echo '<select name="mes" class="form-select form-select-sm">';
        for ($m = 1; $m <= 12; $m++) {
            $selected = ($m == $mes) ? 'selected' : '';
            echo '<option value="' . $m . '" ' . $selected . '>' . str_pad($m, 2, '0', STR_PAD_LEFT) . '</option>';
        }
        echo '</select></div>';
        echo '<div class="col-6">';
        echo '<input type="number" name="ano" class="form-control form-control-sm" value="' . $ano . '" min="2020" max="2030">';
        echo '</div>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        
        // Tabela de usuários e seus pontos
        $sql = "
            SELECT DISTINCT 
                u.id, 
                u.nome, 
                u.email,
                COUNT(a.id) as total_apontamentos,
                ROUND(COALESCE(SUM(
                    CASE 
                        WHEN a.hora_saida_1 IS NOT NULL AND a.hora_entrada_1 IS NOT NULL
                        THEN (TIME_TO_SEC(a.hora_saida_1) - TIME_TO_SEC(a.hora_entrada_1)) / 3600.0
                        ELSE 0
                    END +
                    CASE 
                        WHEN a.hora_saida_2 IS NOT NULL AND a.hora_entrada_2 IS NOT NULL
                        THEN (TIME_TO_SEC(a.hora_saida_2) - TIME_TO_SEC(a.hora_entrada_2)) / 3600.0
                        ELSE 0
                    END +
                    CASE 
                        WHEN a.hora_saida_3 IS NOT NULL AND a.hora_entrada_3 IS NOT NULL
                        THEN (TIME_TO_SEC(a.hora_saida_3) - TIME_TO_SEC(a.hora_entrada_3)) / 3600.0
                        ELSE 0
                    END
                ), 0), 2) as total_horas
            FROM usuarios u
            LEFT JOIN apontamentos_ponto a ON u.id = a.usuario_id 
                AND MONTH(a.data) = ? 
                AND YEAR(a.data) = ?
            GROUP BY u.id, u.nome, u.email
            ORDER BY u.nome
        ";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$mes, $ano]);
        $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo '<div class="table-responsive">';
        echo '<table class="table table-striped table-hover">';
        echo '<thead class="table-dark"><tr>';
        echo '<th>Usuário</th>';
        echo '<th class="text-center">Email</th>';
        echo '<th class="text-center">Apontamentos</th>';
        echo '<th class="text-center">Total Horas</th>';
        echo '<th class="text-center">Ações</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        if (empty($usuarios)) {
            echo '<tr><td colspan="5" class="text-center text-muted">Nenhum usuário encontrado</td></tr>';
        } else {
            foreach ($usuarios as $user) {
                echo '<tr>';
                echo '<td><strong>' . htmlspecialchars($user['nome']) . '</strong></td>';
                echo '<td class="text-center"><small>' . htmlspecialchars($user['email']) . '</small></td>';
                echo '<td class="text-center"><span class="badge bg-info">' . ($user['total_apontamentos'] ?? 0) . '</span></td>';
                echo '<td class="text-center"><strong>' . number_format($user['total_horas'] ?? 0, 2, ',', '.') . 'h</strong></td>';
                echo '<td class="text-center">';
                echo '<a href="' . BASE_URL . 'index.php?rota=ponto_todos&usuario_id=' . $user['id'] . '&mes=' . $mes . '&ano=' . $ano . '" class="btn btn-sm btn-primary">Ver</a> ';
                echo '<a href="' . BASE_URL . 'index.php?rota=relatorio_ponto_mes&usuario_id=' . $user['id'] . '&mes=' . $mes . '&ano=' . $ano . '" class="btn btn-sm btn-secondary">Relatório</a>';
                echo '</td>';
                echo '</tr>';
            }
        }
        
        echo '</tbody></table></div>';
        echo '</div>';
        
        require __DIR__ . '/../views/geral/footer.php';
    }

    /**
     * Exporta relatório de ponto em PDF ou Excel - FASE 5
     * GET /index.php?rota=exportar_ponto&mes_ano=YYYY-MM&formato=pdf|excel
     */
    public function exportarRelatorioPonto($mes_ano = null, $formato = 'html') {
        $this->verificarLogin();
        
        $mes_ano = $mes_ano ?? ($_GET['mes_ano'] ?? date('Y-m'));
        $formato = $formato ?? ($_GET['formato'] ?? 'html');
        $usuario_id = $_SESSION['user_id'];
        
        try {
            require_once __DIR__ . '/../models/GeradorRelatorioPDF.php';
            
            // Mapear dos formatos antigos para novos
            if ($formato === 'pdf') $formato = 'html';
            
            $gerador = new \Src\Models\GeradorRelatorioPDF($formato);
            
            // Obter dados do usuário
            $sql = "SELECT nome, email FROM usuarios WHERE id = ?";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->execute([$usuario_id]);
            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                throw new \Exception('Usuário não encontrado');
            }
            
            // Obter apontamentos do mês
            $sql = "SELECT * FROM apontamentos_ponto WHERE usuario_id = ? AND YEAR(data) = ? AND MONTH(data) = ? ORDER BY data ASC";
            $parts = explode('-', $mes_ano);
            if (count($parts) !== 2) {
                throw new \Exception('Formato de mês inválido. Use YYYY-MM');
            }
            
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->execute([$usuario_id, $parts[0], $parts[1]]);
            $apontamentos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Calcular total_horas para cada apontamento
            foreach ($apontamentos as &$apt) {
                $horas = 0;
                // Batida 1
                if (!empty($apt['hora_entrada_1']) && !empty($apt['hora_saida_1'])) {
                    $entrada = strtotime($apt['hora_entrada_1']);
                    $saida = strtotime($apt['hora_saida_1']);
                    $horas += ($saida - $entrada) / 3600;
                }
                // Batida 2
                if (!empty($apt['hora_entrada_2']) && !empty($apt['hora_saida_2'])) {
                    $entrada = strtotime($apt['hora_entrada_2']);
                    $saida = strtotime($apt['hora_saida_2']);
                    $horas += ($saida - $entrada) / 3600;
                }
                // Batida 3
                if (!empty($apt['hora_entrada_3']) && !empty($apt['hora_saida_3'])) {
                    $entrada = strtotime($apt['hora_entrada_3']);
                    $saida = strtotime($apt['hora_saida_3']);
                    $horas += ($saida - $entrada) / 3600;
                }
                $apt['total_horas'] = round($horas, 2);
            }
            
            // Calcular totais
            $total_horas = 0;
            foreach ($apontamentos as $apt) {
                $total_horas += ($apt['total_horas'] ?? 0);
            }
            
            // Gerar relatório
            $caminho = $gerador->gerarRelatorioPonto(
                usuario_nome: $usuario['nome'] ?? 'N/A',
                mes_ano: $mes_ano,
                dados_ponto: [
                    'dias_trabalhados' => count($apontamentos),
                    'dias_uteis' => 22,
                    'faltas' => 0,
                    'atestados' => 0,
                    'horas_trabalhadas' => $total_horas,
                    'horas_esperadas' => 176,
                    'horas_extras_aprovadas' => 0,
                    'saldo_final' => 0
                ],
                apontamentos: $apontamentos
            );
            
            // Enviar arquivo
            $extensao = pathinfo($caminho, PATHINFO_EXTENSION);
            
            if ($extensao === 'html') {
                // Exibir HTML no navegador
                header('Content-Type: text/html; charset=utf-8');
                readfile($caminho);
            } elseif ($extensao === 'csv') {
                // Download CSV
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=relatório_ponto_' . $mes_ano . '.csv');
                readfile($caminho);
            }
            
            exit;
            
            
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['erro' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Exporta recibo de ponto em PDF - FASE 5
     * GET /index.php?rota=exportar_recibo&batida_id=ID
     */
    public function exportarReciboPonto($batida_id = null) {
        $this->verificarLogin();
        
        $batida_id = $batida_id ?? ($_GET['batida_id'] ?? 0);
        $usuario_id = $_SESSION['user_id'];
        
        try {
            require_once __DIR__ . '/../models/GeradorRelatorioPDF.php';
            $gerador = new \Src\Models\GeradorRelatorioPDF();
            
            // Obter dados da batida
            $sql = "SELECT a.*, u.nome FROM apontamentos_ponto a JOIN usuarios u ON a.usuario_id = u.id WHERE a.id = ? AND a.usuario_id = ?";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->execute([$batida_id, $usuario_id]);
            $batida = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$batida) {
                throw new \Exception('Batida não encontrada');
            }
            
            // Gerar recibo
            $caminho = $gerador->gerarReciboPonto(
                usuario_nome: $batida['nome'],
                data_ponto: $batida['data'],
                dados_batida: [$batida]
            );
            
            // Download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename=' . basename($caminho));
            readfile($caminho);
            exit;
            
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['erro' => $e->getMessage()]);
            exit;
        }
    }
    
    private function verificarLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?rota=login');
            exit;
        }
    }

    private function verificarRH() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_nivel']) || !in_array($_SESSION['user_nivel'], ['admin', 'rh'])) {
            $url = "index.php?rota=dashboard";
            echo "<meta http-equiv='refresh' content='3;url=$url'>";
            exit;
        }
    }
}
?>
