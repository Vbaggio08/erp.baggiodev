<?php

namespace Src\Controllers;

use Src\Models\HorasExtras;
use Src\Models\PontoCalculador;
use Src\Models\ConfiguracaoPontos;
use Src\Models\Usuario;
use Src\Models\NotificadorEmail;

/**
 * HorasExtrasController
 * Gerencia registro, aprovação e compensação de horas extras
 * Implementa workflows de RH para validação de overtime
 * 
 * Endpoints:
 * - POST /horas-extras/registrar: Registra nova hora extra (apenas usuários autenticados)
 * - POST /horas-extras/aprovar: Aprova hora extra (apenas RH/Manager)
 * - POST /horas-extras/rejeitar: Rejeita hora extra com motivo (apenas RH/Manager)
 * - GET /horas-extras/pendentes: Lista horas extras aguardando aprovação
 * - GET /horas-extras/historico: Histórico de horas extras do usuário
 * - GET /horas-extras/relatorio: Relatório mensal de horas extras (RH)
 */
class HorasExtrasController
{
    private $model;
    private $calculador;
    private $configuracao;
    private $usuario_id;
    private $empresa_id;

    public function __construct()
    {
        // Validar autenticação (deve vir de SessionController ou middleware)
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['erro' => 'Não autenticado']);
            exit;
        }

        $this->usuario_id = $_SESSION['usuario_id'];
        $this->empresa_id = $_SESSION['empresa_id'] ?? 1;
        $this->model = new HorasExtras();
        $this->calculador = new PontoCalculador();
        $this->configuracao = new ConfiguracaoPontos();
    }

    /**
     * Registra nova hora extra
     * POST /horas-extras/registrar
     * 
     * Payload:
     * {
     *   "apontamento_id": 123,
     *   "horas_extras": 1.5,
     *   "tipo": "50" ou "100"
     *   "motivo": "Projeto urgente X"
     * }
     * 
     * @return JSON {id, status, mensagem}
     */
    public function registrar()
    {
        try {
            // Validar entrada
            $dados = json_decode(file_get_contents('php://input'), true);

            if (!isset($dados['horas_extras']) || !isset($dados['tipo'])) {
                http_response_code(400);
                return json_encode([
                    'erro' => 'Campos obrigatórios: horas_extras, tipo',
                    'codigo' => 'CAMPO_OBRIGATORIO'
                ]);
            }

            $horas_extras = floatval($dados['horas_extras']);
            $tipo = $dados['tipo']; // '50' ou '100'
            $apontamento_id = isset($dados['apontamento_id']) ? intval($dados['apontamento_id']) : null;

            // Validar tipo
            if (!in_array($tipo, ['50', '100'])) {
                http_response_code(400);
                return json_encode(['erro' => 'Tipo inválido. Deve ser 50 ou 100']);
            }

            // Validar se há limite de horas extras
            $config = $this->configuracao->obterConfiguracao($this->empresa_id);
            if (!$config['permite_horas_extras']) {
                http_response_code(403);
                return json_encode(['erro' => 'Horas extras não permitidas nesta empresa']);
            }

            // Validar limite mensal
            $mes_ano = date('Y-m');
            $total_mes = HorasExtras::calcularTotalAprovado($this->usuario_id, $mes_ano);

            if ($total_mes + $horas_extras > $config['limite_horas_extras_mensais']) {
                http_response_code(400);
                return json_encode([
                    'erro' => 'Limite de horas extras mensais atingido',
                    'limite' => $config['limite_horas_extras_mensais'],
                    'atual' => $total_mes,
                    'solicitado' => $horas_extras,
                    'codigo' => 'LIMITE_EXCEDIDO'
                ]);
            }

            // Registrar hora extra
            $id = $this->model->registrarHoraExtra(
                usuario_id: $this->usuario_id,
                apontamento_id: $apontamento_id,
                horas_extras: $horas_extras,
                tipo: $tipo,
                motivo: $dados['motivo'] ?? 'Registrado manualmente'
            );

            http_response_code(201);
            return json_encode([
                'sucesso' => true,
                'id' => $id,
                'status' => 'pendente',
                'mensagem' => 'Hora extra registrada e aguardando aprovação'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode([
                'erro' => 'Erro ao registrar hora extra',
                'detalhes' => $e->getMessage()
            ]);
        }
    }

    /**
     * Lista horas extras pendentes de aprovação (apenas RH/Manager)
     * GET /horas-extras/pendentes?usuario_id=123&mes=2026-03
     * 
     * @return JSON array de horas extras com dados de usuário
     */
    public function listarPendentes()
    {
        try {
            // Verificar permissão (deve ser RH/Manager)
            if (!$this->ehRH()) {
                http_response_code(403);
                return json_encode(['erro' => 'Acesso negado. Requer permissão RH']);
            }

            $usuario_id = $_GET['usuario_id'] ?? null;
            $mes = $_GET['mes'] ?? null;

            $pendentes = $this->model->listarPendentes(
                usuario_id: $usuario_id ? intval($usuario_id) : null,
                mes: $mes
            );

            // Enriquecer com dados do usuário
            $usuario_model = new Usuario();
            foreach ($pendentes as &$pend) {
                $usuario = $usuario_model->buscarPorId($pend['usuario_id']);
                $pend['nome_usuario'] = $usuario['nome'] ?? 'Desconhecido';
                $pend['email'] = $usuario['email'] ?? '';
            }
            unset($pend);

            return json_encode([
                'sucesso' => true,
                'total' => count($pendentes),
                'dados' => $pendentes
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * Aprova hora extra como RH
     * POST /horas-extras/aprovar
     * 
     * Payload:
     * {
     *   "id": 123,
     *   "observacao": "Aprovado conforme solicitação"
     * }
     * 
     * @return JSON {sucesso, mensagem}
     */
    public function aprovar()
    {
        try {
            if (!$this->ehRH()) {
                http_response_code(403);
                return json_encode(['erro' => 'Acesso negado']);
            }

            $dados = json_decode(file_get_contents('php://input'), true);

            if (!isset($dados['id'])) {
                http_response_code(400);
                return json_encode(['erro' => 'ID da hora extra é obrigatório']);
            }

            $id = intval($dados['id']);

            // Aprovar
            $resultado = $this->model->aprovar($id, $this->usuario_id);

            if (!$resultado) {
                http_response_code(400);
                return json_encode(['erro' => 'Não foi possível aprovar. Verifique status.']);
            }

            // ✅ ENVIAR EMAIL DE APROVAÇÃO - FASE 5
            try {
                $hora_extra = HorasExtras::buscarPorId($id);
                $usuario = Usuario::buscarPorId($hora_extra['usuario_id']);
                
                if ($usuario && $usuario['email']) {
                    NotificadorEmail::notificarHoraExtraAprovada(
                        email: $usuario['email'],
                        nome: $usuario['nome'],
                        horas: $hora_extra['horas_extras'],
                        observacao: $dados['observacao'] ?? 'Aprovado conforme solicitação'
                    );
                }
            } catch (\Exception $e) {
                // Log silencioso - não falha a operação
                error_log('Erro ao enviar email de aprovação: ' . $e->getMessage());
            }

            return json_encode([
                'sucesso' => true,
                'mensagem' => 'Hora extra aprovada com sucesso'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * Rejeita hora extra com motivo
     * POST /horas-extras/rejeitar
     * 
     * Payload:
     * {
     *   "id": 123,
     *   "motivo": "Horas não conferem com apontamento"
     * }
     * 
     * @return JSON {sucesso, mensagem}
     */
    public function rejeitar()
    {
        try {
            if (!$this->ehRH()) {
                http_response_code(403);
                return json_encode(['erro' => 'Acesso negado']);
            }

            $dados = json_decode(file_get_contents('php://input'), true);

            if (!isset($dados['id']) || !isset($dados['motivo'])) {
                http_response_code(400);
                return json_encode(['erro' => 'ID e motivo são obrigatórios']);
            }

            $id = intval($dados['id']);
            $motivo = trim($dados['motivo']);

            if (strlen($motivo) < 5) {
                http_response_code(400);
                return json_encode(['erro' => 'Motivo deve ter pelo menos 5 caracteres']);
            }

            // Rejeitar
            $resultado = $this->model->rejeitar($id, $this->usuario_id, $motivo);

            if (!$resultado) {
                http_response_code(400);
                return json_encode(['erro' => 'Não foi possível rejeitar.']);
            }

            // ✅ ENVIAR EMAIL DE REJEIÇÃO - FASE 5
            try {
                $hora_extra = HorasExtras::buscarPorId($id);
                $usuario = Usuario::buscarPorId($hora_extra['usuario_id']);
                
                if ($usuario && $usuario['email']) {
                    NotificadorEmail::notificarHoraExtraRejeitada(
                        email: $usuario['email'],
                        nome: $usuario['nome'],
                        horas: $hora_extra['horas_extras'],
                        motivo: $motivo
                    );
                }
            } catch (\Exception $e) {
                // Log silencioso - não falha a operação
                error_log('Erro ao enviar email de rejeição: ' . $e->getMessage());
            }

            return json_encode([
                'sucesso' => true,
                'mensagem' => 'Hora extra rejeitada com motivo registrado'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * Lista histórico de horas extras do usuário
     * GET /horas-extras/historico?mes=2026-03
     * 
     * @return JSON array com detalhes e sumário
     */
    public function historico()
    {
        try {
            $mes = $_GET['mes'] ?? date('Y-m');

            $historico = $this->model->listarPorUsuario($this->usuario_id, $mes);

            // Calcular sumário por status
            $sumario = [
                'pendente' => 0,
                'aprovado' => 0,
                'rejeitado' => 0,
                'pago' => 0,
                'compensado' => 0,
                'total_horas' => 0,
                'total_aprovado' => 0
            ];

            foreach ($historico as $registro) {
                $status = $registro['status'];
                if (isset($sumario[$status])) {
                    $sumario[$status]++;
                }
                $sumario['total_horas'] += floatval($registro['horas_extras']);
                if (in_array($status, ['aprovado', 'pago', 'compensado'])) {
                    $sumario['total_aprovado'] += floatval($registro['horas_extras']);
                }
            }

            return json_encode([
                'sucesso' => true,
                'mes' => $mes,
                'sumario' => $sumario,
                'registros' => $historico
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * Gera relatório mensal de horas extras (apenas RH)
     * GET /horas-extras/relatorio?mes=2026-03&empresa_id=1
     * 
     * @return JSON Array com consolidação por usuário e totais
     */
    public function relatorio()
    {
        try {
            if (!$this->ehRH()) {
                http_response_code(403);
                return json_encode(['erro' => 'Acesso negado']);
            }

            $mes = $_GET['mes'] ?? date('Y-m');
            $empresa_id = intval($_GET['empresa_id'] ?? $this->empresa_id);

            // Buscar todas as horas extras do mês
            $db = \Src\Config\Database::getConnection();
            $sql = "
                SELECT he.*, u.nome, u.email
                FROM horas_extras he
                JOIN usuarios u ON he.usuario_id = u.id
                WHERE DATE_FORMAT(he.data_referencia, '%Y-%m') = :mes
                AND u.empresa_id = :empresa_id
                ORDER BY u.nome, he.data_referencia
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute(['mes' => $mes, 'empresa_id' => $empresa_id]);
            $registros = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Consolidar por usuário
            $consolidado = [];
            foreach ($registros as $reg) {
                $uid = $reg['usuario_id'];
                if (!isset($consolidado[$uid])) {
                    $consolidado[$uid] = [
                        'usuario_id' => $uid,
                        'nome' => $reg['nome'],
                        'email' => $reg['email'],
                        'pendente' => 0,
                        'aprovado' => 0,
                        'rejeitado' => 0,
                        'pago' => 0,
                        'compensado' => 0,
                        'total_horas' => 0,
                        'total_aprovado' => 0
                    ];
                }

                $status = $reg['status'];
                $horas = floatval($reg['horas_extras']);

                $consolidado[$uid][$status] += $horas;
                $consolidado[$uid]['total_horas'] += $horas;

                if (in_array($status, ['aprovado', 'pago', 'compensado'])) {
                    $consolidado[$uid]['total_aprovado'] += $horas;
                }
            }

            // Calcular totais globais
            $totais_globais = [
                'total_usuarios' => count($consolidado),
                'total_horas' => 0,
                'total_aprovado' => 0,
                'custo_estimado' => 0 // Se houver configuração de valor/hora
            ];

            foreach ($consolidado as $cons) {
                $totais_globais['total_horas'] += $cons['total_horas'];
                $totais_globais['total_aprovado'] += $cons['total_aprovado'];
            }

            return json_encode([
                'sucesso' => true,
                'mes' => $mes,
                'empresa_id' => $empresa_id,
                'totais' => $totais_globais,
                'por_usuario' => array_values($consolidado),
                'total_registros' => count($registros)
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * Detecta e lista horas extras potenciais não registradas
     * GET /horas-extras/detectar?usuario_id=123&mes=2026-03
     * 
     * @return JSON Array com horas extras sugeridas para registro
     */
    public function detectarPotenciais()
    {
        try {
            $usuario_id = intval($_GET['usuario_id'] ?? $this->usuario_id);
            $mes = $_GET['mes'] ?? date('Y-m');

            // Apenas RH pode ver de outros usuários
            if ($usuario_id !== $this->usuario_id && !$this->ehRH()) {
                http_response_code(403);
                return json_encode(['erro' => 'Acesso negado']);
            }

            // Detectar horas extras potenciais
            $potenciais = $this->calculador->detectarHorasExtras($usuario_id, $mes);

            return json_encode([
                'sucesso' => true,
                'total_detectados' => count($potenciais),
                'mes' => $mes,
                'potenciais' => $potenciais,
                'mensagem' => 'Horas extras detectadas automaticamente. Revise e registre manualmente se necessário.'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * Marca hora extra como pago
     * POST /horas-extras/marcar-pago
     * 
     * Payload:
     * {
     *   "id": 123,
     *   "data_pagamento": "2026-03-31",
     *   "valor_pago": 500.00
     * }
     * 
     * @return JSON {sucesso, mensagem}
     */
    public function marcarComoPago()
    {
        try {
            if (!$this->ehRH()) {
                http_response_code(403);
                return json_encode(['erro' => 'Acesso negado']);
            }

            $dados = json_decode(file_get_contents('php://input'), true);

            if (!isset($dados['id'])) {
                http_response_code(400);
                return json_encode(['erro' => 'ID da hora extra é obrigatório']);
            }

            $id = intval($dados['id']);

            // Marcar como pago
            $resultado = $this->model->marcarComoPago($id);

            if (!$resultado) {
                http_response_code(400);
                return json_encode(['erro' => 'Não foi possível marcar como pago.']);
            }

            return json_encode([
                'sucesso' => true,
                'mensagem' => 'Hora extra marcada como paga'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            return json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * Verifica se usuário atual é RH/Manager
     * Simples validação baseada em role (implementar conforme sua lógica de permissões)
     * 
     * @return bool
     */
    private function ehRH(): bool
    {
        // Implementar conforme sua lógica de roles
        // Exemplo: verificar $_SESSION['role'] === 'RH' ou similar
        return isset($_SESSION['role']) && in_array($_SESSION['role'], ['RH', 'gerente', 'admin']);
    }
}
