<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Ponto.php';
require_once __DIR__ . '/../models/AuditoriaAlteracao.php';
require_once __DIR__ . '/../models/DispositivoAuto.php';
require_once __DIR__ . '/../models/SyncOffline.php';
require_once __DIR__ . '/../models/Usuario.php';

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
        
        // Horário individual do funcionário
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT horario_entrada_1, horario_saida_1, horario_entrada_2, horario_saida_2 FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $horario_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
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
        $device_id = trim((string)($_POST['device_id'] ?? ''));
        
        try {
            $erroMaquina = $this->validarMaquinaGlobalAutorizada($device_id);
            if ($erroMaquina !== null) {
                http_response_code(403);
                return json_encode([
                    'status' => 'erro',
                    'mensagem' => $erroMaquina,
                ]);
            }

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
        $device_id = trim((string)($_POST['device_id'] ?? ''));
        
        try {
            $erroMaquina = $this->validarMaquinaGlobalAutorizada($device_id);
            if ($erroMaquina !== null) {
                http_response_code(403);
                return json_encode(['status' => 'erro', 'mensagem' => $erroMaquina]);
            }

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
                        $device_id,
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
     * Salva foto base64 capturada na câmera e retorna caminho relativo.
     * A foto é salva em assets/uploads/comprovantes/fotos_ponto/YYYY-MM-DD/
     */
    private function salvarFotoBase64($fotoBase64, $usuario_id) {
        if (empty($fotoBase64)) {
            return null;
        }

        try {
            // Remove o prefixo "data:image/jpeg;base64," se existir
            if (strpos($fotoBase64, 'data:') === 0) {
                $fotoBase64 = preg_replace('~^data:image/[^;]+;base64,~', '', $fotoBase64);
            }

            $decodificada = base64_decode($fotoBase64, true);
            if ($decodificada === false) {
                return null;
            }

            // Criar diretório de fotos de ponto se não existir
            $data = date('Y-m-d');
            $dirFotos = __DIR__ . '/../../assets/uploads/comprovantes/fotos_ponto/' . $data;
            if (!is_dir($dirFotos)) {
                mkdir($dirFotos, 0755, true);
            }

            // Gerar nome único da foto: user_ID_timestamp.jpg
            $nomeArquivo = 'user_' . $usuario_id . '_' . time() . '.jpg';
            $caminhoCompleto = $dirFotos . '/' . $nomeArquivo;
            $caminhoRelativo = 'fotos_ponto/' . $data . '/' . $nomeArquivo;

            // Salvar arquivo
            file_put_contents($caminhoCompleto, $decodificada);

            return $caminhoRelativo;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Bate ponto por CPF diretamente na tela de login.
     * Fluxo sem sessão/autenticação tradicional.
     */
    public function baterPontoCpfLogin() {
        header('Content-Type: application/json');

        try {
            $dados = $this->obterDadosRequisicao();
            $cpf = trim((string)($dados['cpf'] ?? ''));
            $device_id = trim((string)($dados['device_id'] ?? ''));
            $fotoBase64 = $dados['foto'] ?? null;

            $cpfNormalizado = Usuario::normalizarCpf($cpf);
            if (strlen($cpfNormalizado) !== 11) {
                http_response_code(400);
                return json_encode(['sucesso' => false, 'erro' => 'CPF inválido']);
            }

            if ($device_id === '') {
                http_response_code(400);
                return json_encode(['sucesso' => false, 'erro' => 'Dispositivo não identificado']);
            }

            $maquina = Ponto::obterMaquinaGlobalAutorizada();
            if (empty($maquina['device_id'])) {
                http_response_code(403);
                return json_encode(['sucesso' => false, 'erro' => 'Nenhuma máquina autorizada para bater ponto por CPF']);
            }

            if ((string)$maquina['device_id'] !== $device_id) {
                http_response_code(403);
                return json_encode(['sucesso' => false, 'erro' => 'Máquina não autorizada para bater ponto de todos']);
            }

            $usuario = Usuario::buscarPorCPF($cpfNormalizado);
            if (!$usuario || empty($usuario['id'])) {
                http_response_code(404);
                return json_encode(['sucesso' => false, 'erro' => 'Usuário não encontrado para o CPF informado']);
            }

            $usuario_id = (int)$usuario['id'];
            $proxima = $this->resolverProximaBatidaUsuario($usuario_id);
            if (!empty($proxima['completo'])) {
                http_response_code(400);
                return json_encode(['sucesso' => false, 'erro' => 'Todas as batidas do dia já foram realizadas']);
            }

            $tipo = $proxima['tipo'];
            $numero = (int)$proxima['numero_batida'];

            // Salvar foto se fornecida
            $caminhoFoto = $this->salvarFotoBase64($fotoBase64, $usuario_id);

            if ($tipo === 'entrada') {
                $ok = Ponto::registrarEntrada(
                    $usuario_id,
                    $numero,
                    null,
                    $caminhoFoto,
                    null,
                    null,
                    null,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $device_id,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                );
            } else {
                $ok = Ponto::registrarSaida(
                    $usuario_id,
                    $numero,
                    null,
                    $caminhoFoto,
                    null,
                    null,
                    null,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $device_id,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                );
            }

            if (!$ok) {
                http_response_code(500);
                return json_encode(['sucesso' => false, 'erro' => 'Não foi possível registrar a batida']);
            }

            return json_encode([
                'sucesso' => true,
                'tipo_batida' => $tipo,
                'mensagem' => 'Ponto batido com sucesso',
                'usuario' => $usuario['nome'] ?? 'Usuário',
                'timestamp' => date('Y-m-d H:i:s'),
                'foto_salva' => !empty($caminhoFoto)
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }

    /**
     * Admin/RH define máquina global autorizada para batida por CPF.
     */
    public function autorizarMaquinaGlobalPonto() {
        $this->verificarRH();
        header('Content-Type: application/json');

        try {
            $dados = $this->obterDadosRequisicao();
            $device_id = trim((string)($dados['device_id'] ?? ''));
            $nome_maquina = trim((string)($dados['nome_maquina'] ?? ''));

            if ($device_id === '') {
                http_response_code(400);
                return json_encode(['sucesso' => false, 'erro' => 'Device ID obrigatório']);
            }

            $ok = Ponto::salvarMaquinaGlobalAutorizada(
                $device_id,
                (int)($_SESSION['user_id'] ?? 0),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $nome_maquina
            );

            return json_encode([
                'sucesso' => $ok,
                'mensagem' => $ok ? 'Máquina global autorizada com sucesso' : 'Falha ao autorizar máquina global'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }

    /**
     * Retorna status da máquina global autorizada para batida por CPF.
     */
    public function statusMaquinaGlobalPonto() {
        $this->verificarRH();
        header('Content-Type: application/json');

        try {
            $dados = Ponto::obterMaquinaGlobalAutorizada();
            return json_encode(['sucesso' => true, 'dados' => $dados]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }

    /**
     * Revoga a máquina global autorizada para batida por CPF.
     */
    public function revogarMaquinaGlobalPonto() {
        $this->verificarRH();
        header('Content-Type: application/json');

        try {
            $ok = Ponto::revogarMaquinaGlobalAutorizada();
            return json_encode([
                'sucesso' => $ok,
                'mensagem' => $ok ? 'Máquina global revogada com sucesso' : 'Falha ao revogar máquina global'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }
    
    /**
     * Meu Ponto: últimos 30 dias
     */
    public function meuPonto() {
        $this->verificarLogin();
        $this->atualizarApontamentosIncompletosSeNecessario();
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
        $this->atualizarApontamentosIncompletosSeNecessario();
        
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
     * Espelho de ponto por funcionario (RH/Admin)
     */
    public function espelhoPontoFuncionario() {
        $this->verificarRH();
        $this->atualizarApontamentosIncompletosSeNecessario();

        $pdo = Database::getConnection();
        $funcionarioId = (int)($_GET['usuario_id'] ?? 0);
        $mesAno = trim((string)($_GET['mes_ano'] ?? date('Y-m')));

        if (!preg_match('/^\d{4}-\d{2}$/', $mesAno)) {
            $mesAno = date('Y-m');
        }

        $funcionariosStmt = $pdo->query("SELECT id, nome, departamento FROM usuarios ORDER BY nome ASC");
        $funcionarios = $funcionariosStmt->fetchAll(PDO::FETCH_ASSOC);

        $apontamentos = [];
        $funcionarioSelecionado = null;

        if ($funcionarioId > 0) {
            $funcionarioStmt = $pdo->prepare("SELECT id, nome, departamento FROM usuarios WHERE id = ? LIMIT 1");
            $funcionarioStmt->execute([$funcionarioId]);
            $funcionarioSelecionado = $funcionarioStmt->fetch(PDO::FETCH_ASSOC);

            if ($funcionarioSelecionado) {
                $sql = "SELECT ap.*,
                               u.nome,
                               u.departamento
                        FROM apontamentos_ponto ap
                        INNER JOIN usuarios u ON u.id = ap.usuario_id
                        WHERE ap.usuario_id = ?
                          AND DATE_FORMAT(ap.data, '%Y-%m') = ?
                        ORDER BY ap.data DESC";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$funcionarioId, $mesAno]);
                $apontamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/admin/espelho_ponto_funcionario.php';
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
        $hora_entrada_2 = $_POST['hora_entrada_2'] ?? null;
        $hora_saida_2 = $_POST['hora_saida_2'] ?? null;
        $motivo = $_POST['motivo_alteracao'];
        
        $pdo = Database::getConnection();
        
        // Obtém valores antigos
        $sql = "SELECT * FROM apontamentos_ponto WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$apontamento_id]);
        $anterior = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$anterior) {
            header('Location: index.php?rota=ponto_todos&msg=apontamento_nao_encontrado');
            exit;
        }

        // Regra de governança: quem edita o próprio ponto precisa aprovação de outro admin/RH.
        if ((int)($_SESSION['user_id'] ?? 0) === (int)$anterior['usuario_id']) {
            $sql = "INSERT INTO solicitacoes_alteracao_ponto
                    (usuario_id, data_apontamento, tipo_alteracao, entrada_1_corrigida, saida_1_corrigida, entrada_2_corrigida, saida_2_corrigida, motivo, status, criado_em)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendente', NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $anterior['usuario_id'],
                $anterior['data'],
                'ambas_incorretas',
                $hora_entrada_1,
                $hora_saida_1,
                $hora_entrada_2,
                $hora_saida_2,
                $motivo
            ]);

            AuditoriaAlteracao::registrarAlteracao(
                $apontamento_id,
                $_SESSION['user_id'],
                'edicao_pendente_aprovacao',
                json_encode(['entrada_1' => $anterior['hora_entrada_1'], 'saida_1' => $anterior['hora_saida_1'], 'entrada_2' => $anterior['hora_entrada_2'], 'saida_2' => $anterior['hora_saida_2']]),
                json_encode(['entrada_1' => $hora_entrada_1, 'saida_1' => $hora_saida_1, 'entrada_2' => $hora_entrada_2, 'saida_2' => $hora_saida_2]),
                'Autorização pendente: edição no próprio ponto'
            );

            header('Location: index.php?rota=ponto_todos&msg=autorizacao_pendente');
            exit;
        }
        
        // Atualiza
        $sql = "UPDATE apontamentos_ponto SET hora_entrada_1 = ?, hora_saida_1 = ?, hora_entrada_2 = ?, hora_saida_2 = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$hora_entrada_1, $hora_saida_1, $hora_entrada_2, $hora_saida_2, $apontamento_id]);
        
        // Registra na auditoria
        AuditoriaAlteracao::registrarAlteracao(
            $apontamento_id,
            $_SESSION['user_id'],
            'entrada_saida_editada',
            json_encode(['entrada_1' => $anterior['hora_entrada_1'], 'saida_1' => $anterior['hora_saida_1'], 'entrada_2' => $anterior['hora_entrada_2'], 'saida_2' => $anterior['hora_saida_2']]),
            json_encode(['entrada_1' => $hora_entrada_1, 'saida_1' => $hora_saida_1, 'entrada_2' => $hora_entrada_2, 'saida_2' => $hora_saida_2]),
            $motivo
        );
        
        header('Location: index.php?rota=ponto_todos');
    }

    /**
     * Lista solicitações pendentes de alteração de ponto (RH/Admin)
     */
    public function listarSolicitacoesAlteracao() {
        $this->verificarRH();

        $pdo = Database::getConnection();
        $sql = "SELECT s.*, u.nome as usuario_nome
                FROM solicitacoes_alteracao_ponto s
                INNER JOIN usuarios u ON u.id = s.usuario_id
                WHERE s.status = 'pendente'
                ORDER BY s.criado_em DESC";
        $solicitacoes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/admin/solicitacoes_alteracao_ponto.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    /**
     * Aprova uma solicitação pendente e aplica alteração no apontamento.
     */
    public function aprovarSolicitacaoAlteracao() {
        $this->verificarRH();

        $solicitacao_id = (int)($_POST['solicitacao_id'] ?? 0);
        $observacao = trim($_POST['observacao'] ?? '');
        if ($solicitacao_id <= 0) {
            header('Location: index.php?rota=solicitacoes_alteracao_ponto&msg=id_invalido');
            exit;
        }

        $pdo = Database::getConnection();
        $sql = "SELECT * FROM solicitacoes_alteracao_ponto WHERE id = ? AND status = 'pendente'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$solicitacao_id]);
        $sol = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sol) {
            header('Location: index.php?rota=solicitacoes_alteracao_ponto&msg=solicitacao_invalida');
            exit;
        }

        // Impede autoaprovação.
        if ((int)$sol['usuario_id'] === (int)($_SESSION['user_id'] ?? 0)) {
            header('Location: index.php?rota=solicitacoes_alteracao_ponto&msg=autoaprovacao_bloqueada');
            exit;
        }

        $sql = "SELECT * FROM apontamentos_ponto WHERE usuario_id = ? AND data = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sol['usuario_id'], $sol['data_apontamento']]);
        $apontamento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$apontamento) {
            header('Location: index.php?rota=solicitacoes_alteracao_ponto&msg=apontamento_nao_encontrado');
            exit;
        }

        $sql = "UPDATE apontamentos_ponto
                SET hora_entrada_1 = ?, hora_saida_1 = ?, hora_entrada_2 = ?, hora_saida_2 = ?
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $sol['entrada_1_corrigida'] ?: $apontamento['hora_entrada_1'],
            $sol['saida_1_corrigida'] ?: $apontamento['hora_saida_1'],
            $sol['entrada_2_corrigida'] ?: $apontamento['hora_entrada_2'],
            $sol['saida_2_corrigida'] ?: $apontamento['hora_saida_2'],
            $apontamento['id']
        ]);

        $sql = "UPDATE solicitacoes_alteracao_ponto
                SET status = 'aprovado', aprovador_id = ?, observacao_aprovador = ?, atualizado_em = NOW()
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $observacao, $solicitacao_id]);

        AuditoriaAlteracao::registrarAlteracao(
            $apontamento['id'],
            $_SESSION['user_id'],
            'edicao_aprovada',
            json_encode(['entrada_1' => $apontamento['hora_entrada_1'], 'saida_1' => $apontamento['hora_saida_1'], 'entrada_2' => $apontamento['hora_entrada_2'], 'saida_2' => $apontamento['hora_saida_2']]),
            json_encode(['entrada_1' => $sol['entrada_1_corrigida'], 'saida_1' => $sol['saida_1_corrigida'], 'entrada_2' => $sol['entrada_2_corrigida'], 'saida_2' => $sol['saida_2_corrigida']]),
            'Aprovação de solicitação pendente'
        );

        header('Location: index.php?rota=solicitacoes_alteracao_ponto&msg=aprovada');
        exit;
    }

    /**
     * Rejeita uma solicitação pendente de alteração.
     */
    public function rejeitarSolicitacaoAlteracao() {
        $this->verificarRH();

        $solicitacao_id = (int)($_POST['solicitacao_id'] ?? 0);
        $observacao = trim($_POST['observacao'] ?? '');
        if ($solicitacao_id <= 0) {
            header('Location: index.php?rota=solicitacoes_alteracao_ponto&msg=id_invalido');
            exit;
        }

        $pdo = Database::getConnection();
        $sql = "UPDATE solicitacoes_alteracao_ponto
                SET status = 'rejeitado', aprovador_id = ?, observacao_aprovador = ?, atualizado_em = NOW()
                WHERE id = ? AND status = 'pendente'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $observacao, $solicitacao_id]);

        header('Location: index.php?rota=solicitacoes_alteracao_ponto&msg=rejeitada');
        exit;
    }
    
    /**
     * Solicitar alteração de ponto (Funcionário)
     */
    public function solicitarAlteracao() {
        $this->verificarLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario_id = $_SESSION['user_id'];
            $data_apontamento = $_POST['data_apontamento'] ?? null;
            $tipo_alteracao = $_POST['tipo_alteracao'] ?? null;
            $motivo = $_POST['motivo'] ?? '';
            $entrada_1 = $_POST['entrada_1_corrigida'] ?? null;
            $saida_1 = $_POST['saida_1_corrigida'] ?? null;
            $entrada_2 = $_POST['entrada_2_corrigida'] ?? null;
            $saida_2 = $_POST['saida_2_corrigida'] ?? null;
            
            $pdo = Database::getConnection();
            
            $sql = "INSERT INTO solicitacoes_alteracao_ponto 
                    (usuario_id, data_apontamento, tipo_alteracao, entrada_1_corrigida, saida_1_corrigida, entrada_2_corrigida, saida_2_corrigida, motivo, status, criado_em)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendente', NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id, $data_apontamento, $tipo_alteracao, $entrada_1, $saida_1, $entrada_2, $saida_2, $motivo]);
            
            header('Location: index.php?rota=meu_ponto&msg=solicitacao_enviada');
            exit;
        }
        
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

            if (!is_array($lote_pontos) || empty($lote_pontos)) {
                http_response_code(400);
                return json_encode(['status' => 'erro', 'mensagem' => 'Lote de pontos inválido']);
            }

            foreach ($lote_pontos as $ponto) {
                $device_id = trim((string)($ponto['device_id'] ?? ''));
                $erroMaquina = $this->validarMaquinaGlobalAutorizada($device_id);
                if ($erroMaquina !== null) {
                    http_response_code(403);
                    return json_encode(['status' => 'erro', 'mensagem' => $erroMaquina]);
                }
            }
            
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
        $this->atualizarApontamentosIncompletosSeNecessario();
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
            
            $calculador = new PontoCalculador();
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
            
            $calculador = new PontoCalculador();
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
            
            $calculador = new PontoCalculador();
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
        $this->verificarRH();
        header('Content-Type: application/json');
        
        try {
            $empresa_id = $this->resolverEmpresaConfiguracao($_GET['empresa_id'] ?? null);
            
            require_once __DIR__ . '/../models/ConfiguracaoPontos.php';
            
            $configuracao = ConfiguracaoPontos::obterConfiguracao($empresa_id);
            $config_escala = Ponto::obterConfiguracaoEscalasBatidas();
            $configuracao = array_merge($configuracao, $config_escala);
            
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
     * Salva configuração do sistema de ponto (FASE 3 + escala/batidas).
     */
    public function salvarConfiguracaoPonto() {
        $this->verificarRH();
        header('Content-Type: application/json');

        try {
            require_once __DIR__ . '/../models/ConfiguracaoPontos.php';

            $dados = $this->obterDadosRequisicao();
            $empresa_id = $this->resolverEmpresaConfiguracao();

            $dados_avancados = [
                'permite_horas_extras' => !empty($dados['permite_horas_extras']) ? 1 : 0,
                'limite_horas_extras_diarias' => (float)($dados['limite_horas_extras_diarias'] ?? 2.0),
                'limite_horas_extras_mensais' => (float)($dados['limite_horas_extras_mensais'] ?? 20.0),
                'percentual_hora_extra_50' => (float)($dados['percentual_hora_extra_50'] ?? 50.0),
                'percentual_hora_extra_100' => (float)($dados['percentual_hora_extra_100'] ?? 100.0),
                'calcula_dsr' => !empty($dados['calcula_dsr']) ? 1 : 0,
                'dsr_dias_compensacao' => (int)($dados['dsr_dias_compensacao'] ?? 1),
                'desconta_feriado_nao_trabalhado' => !empty($dados['desconta_feriado_nao_trabalhado']) ? 1 : 0,
                'aplicar_dsr_compensado_feriado' => !empty($dados['aplicar_dsr_compensado_feriado']) ? 1 : 0,
                'tolerancia_entrada_minutos' => (int)($dados['tolerancia_entrada_minutos'] ?? 5),
                'tolerancia_saida_minutos' => (int)($dados['tolerancia_saida_minutos'] ?? 5),
                'considerar_lunch_automatico' => !empty($dados['considerar_lunch_automatico']) ? 1 : 0,
                'duracao_lunch_minutos' => (int)($dados['duracao_lunch_minutos'] ?? 60),
            ];

            $ok_avancado = ConfiguracaoPontos::atualizar($dados_avancados, $empresa_id);

            $ok_escala = Ponto::salvarConfiguracaoEscalasBatidas([
                'regra_incompleto_fim_dia' => !empty($dados['regra_incompleto_fim_dia']),
                'batidas_padrao_dia' => (int)($dados['batidas_padrao_dia'] ?? 4),
                'dias_ativos' => is_array($dados['dias_ativos'] ?? null) ? $dados['dias_ativos'] : [],
                'batidas_por_dia' => is_array($dados['batidas_por_dia'] ?? null) ? $dados['batidas_por_dia'] : [],
            ]);

            // Compatibilidade com configuração base existente do ponto.
            $pdo = Database::getConnection();
            $sql = "INSERT INTO configuracao_ponto (id, quantidade_batidas, tolerancia_atraso_minutos, usar_dsr)
                    VALUES (1, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        quantidade_batidas = VALUES(quantidade_batidas),
                        tolerancia_atraso_minutos = VALUES(tolerancia_atraso_minutos),
                        usar_dsr = VALUES(usar_dsr)";
            $stmt = $pdo->prepare($sql);
            $ok_base = $stmt->execute([
                max(0, (int)($dados['batidas_padrao_dia'] ?? 4)),
                max(0, (int)($dados['tolerancia_entrada_minutos'] ?? 5)),
                !empty($dados['calcula_dsr']) ? 1 : 0,
            ]);

            if ($ok_avancado || $ok_escala || $ok_base) {
                return json_encode([
                    'sucesso' => true,
                    'mensagem' => 'Configurações salvas com sucesso'
                ]);
            }

            http_response_code(400);
            return json_encode(['sucesso' => false, 'erro' => 'Nenhuma configuração válida foi salva']);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }

    /**
     * Retorna configuração individual de ponto para um usuário.
     */
    public function obterConfiguracaoPontoUsuario() {
        $this->verificarRH();
        header('Content-Type: application/json');

        try {
            $usuario_id = (int)($_GET['usuario_id'] ?? 0);
            if ($usuario_id <= 0) {
                http_response_code(400);
                return json_encode(['sucesso' => false, 'erro' => 'Usuário inválido']);
            }

            $configuracao = Ponto::obterConfiguracaoUsuarioPonto($usuario_id);

            return json_encode([
                'sucesso' => true,
                'usuario_id' => $usuario_id,
                'configuracao' => $configuracao,
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }

    /**
     * Salva configuração individual de ponto para um usuário.
     */
    public function salvarConfiguracaoPontoUsuario() {
        $this->verificarRH();
        header('Content-Type: application/json');

        try {
            $dados = $this->obterDadosRequisicao();
            $usuario_id = (int)($dados['usuario_id'] ?? 0);
            if ($usuario_id <= 0) {
                http_response_code(400);
                return json_encode(['sucesso' => false, 'erro' => 'Usuário inválido']);
            }

            $ok = Ponto::salvarConfiguracaoUsuarioPonto($usuario_id, [
                'permite_horas_extras' => !empty($dados['permite_horas_extras']),
                'batidas_padrao_dia' => (int)($dados['batidas_padrao_dia'] ?? 4),
                'dias_ativos' => is_array($dados['dias_ativos'] ?? null) ? $dados['dias_ativos'] : [],
                'batidas_por_dia' => is_array($dados['batidas_por_dia'] ?? null) ? $dados['batidas_por_dia'] : [],
                'horario_entrada_1' => trim((string)($dados['horario_entrada_1'] ?? '08:00')),
                'horario_saida_1' => trim((string)($dados['horario_saida_1'] ?? '12:00')),
                'horario_entrada_2' => trim((string)($dados['horario_entrada_2'] ?? '13:00')),
                'horario_saida_2' => trim((string)($dados['horario_saida_2'] ?? '18:00')),
            ]);

            if (!$ok) {
                http_response_code(400);
                return json_encode(['sucesso' => false, 'erro' => 'Não foi possível salvar configuração do usuário']);
            }

            return json_encode([
                'sucesso' => true,
                'mensagem' => 'Configuração do usuário salva com sucesso',
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }

    /**
     * Restaura configurações padrão do ponto.
     */
    public function resetarConfiguracaoPonto() {
        $this->verificarRH();
        header('Content-Type: application/json');

        try {
            require_once __DIR__ . '/../models/ConfiguracaoPontos.php';

            $empresa_id = $this->resolverEmpresaConfiguracao();
            $padrao = [
                'permite_horas_extras' => 1,
                'limite_horas_extras_diarias' => 2.0,
                'limite_horas_extras_mensais' => 20.0,
                'percentual_hora_extra_50' => 50.0,
                'percentual_hora_extra_100' => 100.0,
                'calcula_dsr' => 1,
                'dsr_dias_compensacao' => 1,
                'desconta_feriado_nao_trabalhado' => 0,
                'aplicar_dsr_compensado_feriado' => 1,
                'tolerancia_entrada_minutos' => 5,
                'tolerancia_saida_minutos' => 5,
                'considerar_lunch_automatico' => 0,
                'duracao_lunch_minutos' => 60,
            ];

            ConfiguracaoPontos::atualizar($padrao, $empresa_id);
            Ponto::resetarConfiguracaoEscalasBatidas();

            $pdo = Database::getConnection();
            $sql = "INSERT INTO configuracao_ponto (id, quantidade_batidas, tolerancia_atraso_minutos, usar_dsr)
                    VALUES (1, 4, 5, 1)
                    ON DUPLICATE KEY UPDATE
                        quantidade_batidas = 4,
                        tolerancia_atraso_minutos = 5,
                        usar_dsr = 1";
            $pdo->exec($sql);

            return json_encode([
                'sucesso' => true,
                'mensagem' => 'Configurações restauradas para o padrão'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }

    /**
     * Lista feriados para tela de configuração.
     */
    public function listarFeriadosConfiguracao() {
        $this->verificarRH();
        header('Content-Type: application/json');

        try {
            require_once __DIR__ . '/../models/Feriados.php';

            $data_inicio = $_GET['data_inicio'] ?? date('Y-01-01', strtotime('-1 year'));
            $data_fim = $_GET['data_fim'] ?? date('Y-12-31', strtotime('+2 years'));

            $dados = Feriados::listarPeriodo($data_inicio, $data_fim);
            return json_encode(['sucesso' => true, 'dados' => $dados]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }

    /**
     * Adiciona feriado via tela de configuração.
     */
    public function adicionarFeriadoConfiguracao() {
        $this->verificarRH();
        header('Content-Type: application/json');

        try {
            require_once __DIR__ . '/../models/Feriados.php';

            $dados = $this->obterDadosRequisicao();
            $data = trim($dados['data'] ?? '');
            $descricao = trim($dados['descricao'] ?? 'Feriado');
            $tipo = trim($dados['tipo'] ?? 'personalizado');

            if ($data === '') {
                http_response_code(400);
                return json_encode(['sucesso' => false, 'erro' => 'Data do feriado é obrigatória']);
            }

            $id = Feriados::adicionar($data, $descricao, $tipo, null);
            return json_encode(['sucesso' => true, 'id' => $id]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }

    /**
     * Remove feriado via tela de configuração.
     */
    public function removerFeriadoConfiguracao() {
        $this->verificarRH();
        header('Content-Type: application/json');

        try {
            require_once __DIR__ . '/../models/Feriados.php';

            $dados = $this->obterDadosRequisicao();
            $id = (int)($dados['id'] ?? ($_GET['id'] ?? 0));
            if ($id <= 0) {
                http_response_code(400);
                return json_encode(['sucesso' => false, 'erro' => 'ID inválido']);
            }

            $ok = Feriados::remover($id);
            return json_encode(['sucesso' => $ok]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        }
    }

    /**
     * Lista usuários para teste de cálculos na tela de configuração.
     */
    public function listarUsuariosTeste() {
        $this->verificarRH();
        header('Content-Type: application/json');

        try {
            $pdo = Database::getConnection();
            $sql = "SELECT id, nome FROM usuarios ORDER BY nome ASC";
            $dados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            return json_encode(['sucesso' => true, 'data' => $dados]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
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
            
            $calculador = new PontoCalculador();
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
        $this->atualizarApontamentosIncompletosSeNecessario();
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
            
            $gerador = new GeradorRelatorioPDF($formato);
            
            // Obter dados do usuário
            $sql = "SELECT nome, email FROM usuarios WHERE id = ?";
            $stmt = Database::getConnection()->prepare($sql);
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
            
            $stmt = Database::getConnection()->prepare($sql);
            $stmt->execute([$usuario_id, $parts[0], $parts[1]]);
            $apontamentos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Consolidar linhas duplicadas da mesma data para evitar repetição no espelho/exportação.
            $apontamentos = $this->consolidarApontamentosPorData($apontamentos);
            
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
     * Consolida apontamentos com a mesma data em um único registro.
     * Preserva dados não vazios de batidas e mantém o registro de maior ID como base final.
     */
    private function consolidarApontamentosPorData(array $apontamentos): array {
        if (count($apontamentos) <= 1) {
            return $apontamentos;
        }

        $porData = [];

        foreach ($apontamentos as $registro) {
            $data = (string)($registro['data'] ?? '');
            if ($data === '') {
                continue;
            }

            if (!isset($porData[$data])) {
                $porData[$data] = $registro;
                continue;
            }

            $base = $porData[$data];

            // Completa campos vazios com dados não vazios encontrados em outro registro do mesmo dia.
            foreach ($registro as $campo => $valor) {
                if ((empty($base[$campo]) || $base[$campo] === '00:00:00') && !empty($valor)) {
                    $base[$campo] = $valor;
                }
            }

            // Se o registro atual for mais novo, ele vira a base e recebe os complementos do anterior.
            $idBase = (int)($base['id'] ?? 0);
            $idRegistro = (int)($registro['id'] ?? 0);
            if ($idRegistro > $idBase) {
                foreach ($base as $campo => $valor) {
                    if ((empty($registro[$campo]) || $registro[$campo] === '00:00:00') && !empty($valor)) {
                        $registro[$campo] = $valor;
                    }
                }
                $base = $registro;
            }

            $porData[$data] = $base;
        }

        ksort($porData);
        return array_values($porData);
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
            $gerador = new GeradorRelatorioPDF();
            
            // Obter dados da batida
            $sql = "SELECT a.*, u.nome FROM apontamentos_ponto a JOIN usuarios u ON a.usuario_id = u.id WHERE a.id = ? AND a.usuario_id = ?";
            $stmt = Database::getConnection()->prepare($sql);
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

    /**
     * Obtém dados de entrada aceitando JSON e form-data.
     */
    private function obterDadosRequisicao(): array {
        if (!empty($_POST)) {
            return $_POST;
        }

        $raw = file_get_contents('php://input');
        if (!$raw) {
            return [];
        }

        $json = json_decode($raw, true);
        return is_array($json) ? $json : [];
    }

    /**
     * Atualiza status de apontamentos antigos para incompleta quando aplicável.
     */
    private function atualizarApontamentosIncompletosSeNecessario(): void {
        try {
            Ponto::aplicarRegraIncompletoFimDia();
        } catch (\Exception $e) {
            error_log('Falha ao atualizar apontamentos incompletos: ' . $e->getMessage());
        }
    }

    /**
     * Resolve o identificador aceito pelo schema atual de configuracao_pontos_avancado.
     * Se o empresa_id da sessão não existir em usuarios.id, usa configuração global (NULL).
     */
    private function resolverEmpresaConfiguracao($empresaIdInformado = null): ?int {
        $empresaId = $empresaIdInformado;

        if ($empresaId === null || $empresaId === '') {
            $empresaId = $_SESSION['empresa_id'] ?? null;
        }

        if ($empresaId === null || $empresaId === '') {
            return null;
        }

        $empresaId = (int)$empresaId;
        if ($empresaId <= 0) {
            return null;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$empresaId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ? $empresaId : null;
    }

    /**
     * Descobre qual é a próxima batida esperada para o dia (entrada/saída).
     */
    private function resolverProximaBatidaUsuario(int $usuario_id): array {
        $apontamento = Ponto::obterApontamentoDia($usuario_id);

        if (!$apontamento) {
            return ['tipo' => 'entrada', 'numero_batida' => 1];
        }

        for ($i = 1; $i <= 3; $i++) {
            if (empty($apontamento["hora_entrada_$i"])) {
                return ['tipo' => 'entrada', 'numero_batida' => $i];
            }
            if (empty($apontamento["hora_saida_$i"])) {
                return ['tipo' => 'saida', 'numero_batida' => $i];
            }
        }

        return ['completo' => true, 'tipo' => null, 'numero_batida' => 0];
    }

    /**
     * Regra global: apenas uma maquina autorizada pode bater ponto.
     * Retorna null quando valido, ou mensagem de erro quando invalido.
     */
    private function validarMaquinaGlobalAutorizada(string $device_id): ?string {
        if ($device_id === '') {
            return 'Dispositivo não identificado';
        }

        $maquina = Ponto::obterMaquinaGlobalAutorizada();
        $deviceAutorizado = trim((string)($maquina['device_id'] ?? ''));

        if ($deviceAutorizado === '') {
            return 'Nenhuma máquina global autorizada para bater ponto';
        }

        if ($deviceAutorizado !== $device_id) {
            return 'Máquina não autorizada para bater ponto';
        }

        return null;
    }
}
?>
