<?php
require_once __DIR__ . '/../config/database.php';
// Importa os Models
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Estoque.php';
require_once __DIR__ . '/../models/Gabarito.php';

class DashboardController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. PEDIDOS PENDENTES
        $pendentesLista = [];
        if (class_exists('Pedido') && method_exists('Pedido', 'listarPendentes')) {
            $pendentesLista = Pedido::listarPendentes();
        }
        $totalPendentes = count($pendentesLista);

        // 2. ESTOQUE BAIXO (Nova Lógica)
        $estoqueBaixo = [];
        if (class_exists('Estoque') && method_exists('Estoque', 'listarEstoqueBaixo')) {
            $estoqueBaixo = Estoque::listarEstoqueBaixo();
        }
        
        // 3. TOTAL DE PEÇAS (Nova Lógica)
        $totalPecas = 0;
        if (class_exists('Estoque') && method_exists('Estoque', 'getTotalPecas')) {
            $totalPecas = Estoque::getTotalPecas();
        }

        // 4. PERDAS
        $totalPerdas = 0;
        if (class_exists('Estoque') && method_exists('Estoque', 'getRelatorioPerdas')) {
            $perdasLista = Estoque::getRelatorioPerdas();
            $totalPerdas = count($perdasLista);
        }

        // 5. PRODUZIDOS HOJE
        $produzidosHoje = 0;
        if (class_exists('Gabarito') && method_exists('Gabarito', 'contarProducaoHoje')) {
            $produzidosHoje = Gabarito::contarProducaoHoje();
        }

        // Carrega a view do dashboard, passando as variáveis
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/geral/dashboard.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // ===== FASE 5 - Métodos JSON para Dashboard de Ponto =====

    /**
     * GET /index.php?rota=dashboard_ponto_json
     * Retorna dados do dashboard pessoal de ponto
     */
    public function getDadosUsuario() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            return ['status' => 'erro', 'mensagem' => 'Não autenticado'];
        }

        $usuario_id = $_SESSION['user_id'];

        try {
            $db = requireConnection();

            // Dados básicos do usuário
            $sql = "SELECT id, nome, email FROM usuarios WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$usuario_id]);
            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Horas de hoje
            $sql = "SELECT COALESCE(SUM(total_horas), 0) as horas FROM apontamentos WHERE usuario_id = ? AND DATE(data_apontamento) = CURDATE()";
            $stmt = $db->prepare($sql);
            $stmt->execute([$usuario_id]);
            $hoje = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Saldo do mês
            $sql = "SELECT COALESCE(SUM(saldo_horas), 0) as saldo FROM saldos_mensais WHERE usuario_id = ? AND mes = MONTH(CURDATE()) AND ano = YEAR(CURDATE())";
            $stmt = $db->prepare($sql);
            $stmt->execute([$usuario_id]);
            $saldo = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Extras pendentes
            $sql = "SELECT COUNT(*) as total FROM horas_extras WHERE usuario_id = ? AND status = 'pendente'";
            $stmt = $db->prepare($sql);
            $stmt->execute([$usuario_id]);
            $extras_pend = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Extras a pagar
            $sql = "SELECT COUNT(*) as total FROM horas_extras WHERE usuario_id = ? AND status = 'aprovado' AND pago = 0";
            $stmt = $db->prepare($sql);
            $stmt->execute([$usuario_id]);
            $extras_pagar_row = $stmt->fetch(\PDO::FETCH_ASSOC);

            return [
                'status' => 'sucesso',
                'usuario' => $usuario,
                'metricas' => [
                    'horas_hoje' => (float)($hoje['horas'] ?? 0),
                    'saldo_mes' => (float)($saldo['saldo'] ?? 0),
                    'extras_pendentes' => (int)($extras_pend['total'] ?? 0),
                    'extras_pagar' => (int)($extras_pagar_row['total'] ?? 0)
                ],
                'proximosEventos' => [
                    'dsr' => null,
                    'feriado' => null
                ],
                'anomalias' => []
            ];

        } catch (\Exception $e) {
            return ['status' => 'erro', 'mensagem' => $e->getMessage()];
        }
    }

    /**
     * GET /index.php?rota=dashboard_graficos_json
     * Retorna dados para gráfico de horas (últimos 30 dias)
     */
    public function getGraficosHoras() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            return ['status' => 'erro', 'mensagem' => 'Não autenticado'];
        }

        $usuario_id = $_SESSION['user_id'];

        try {
            $db = requireConnection();

            $sql = "
                SELECT 
                    DATE(data_apontamento) as data,
                    SUM(total_horas) as horas
                FROM apontamentos
                WHERE usuario_id = ? AND data_apontamento >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DATE(data_apontamento)
                ORDER BY data ASC
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([$usuario_id]);
            $dados = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $labels = [];
            $horas = [];
            
            foreach ($dados as $linha) {
                $labels[] = date('d/m', strtotime($linha['data']));
                $horas[] = (float)$linha['horas'];
            }

            return [
                'status' => 'sucesso',
                'dados' => [
                    'grafico_horas_30dias' => [
                        'labels' => $labels,
                        'datasets' => [
                            [
                                'label' => 'Horas Trabalhadas',
                                'data' => $horas,
                                'borderColor' => '#667cea',
                                'backgroundColor' => 'rgba(102, 126, 234, 0.1)',
                                'borderWidth' => 2
                            ]
                        ]
                    ]
                ]
            ];

        } catch (\Exception $e) {
            return ['status' => 'erro', 'mensagem' => $e->getMessage()];
        }
    }

    /**
     * GET /index.php?rota=dashboard_saldo_json
     * Retorna dados para gráfico de saldo (últimos 6 meses)
     */
    public function getGraficoSaldoMensal() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            return ['status' => 'erro', 'mensagem' => 'Não autenticado'];
        }

        $usuario_id = $_SESSION['user_id'];

        try {
            $db = requireConnection();
            $meses = 6;
            $labels = [];
            $dados = [];

            for ($i = $meses - 1; $i >= 0; $i--) {
                $data = date('Y-m-01', strtotime("-$i months"));
                $mes = (int)date('n', strtotime($data));
                $ano = (int)date('Y', strtotime($data));
                $label = date('M/y', strtotime($data));

                $sql = "SELECT SUM(saldo_horas) as saldo FROM saldos_mensais WHERE usuario_id = ? AND mes = ? AND ano = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$usuario_id, $mes, $ano]);
                $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);

                $labels[] = $label;
                $dados[] = (float)($resultado['saldo'] ?? 0);
            }

            return [
                'status' => 'sucesso',
                'dados' => [
                    'grafico_saldo_6meses' => [
                        'labels' => $labels,
                        'datasets' => [
                            [
                                'label' => 'Saldo de Horas',
                                'data' => $dados,
                                'backgroundColor' => '#667cea'
                            ]
                        ]
                    ]
                ]
            ];

        } catch (\Exception $e) {
            return ['status' => 'erro', 'mensagem' => $e->getMessage()];
        }
    }

    /**
     * GET /index.php?rota=dashboard_rh_json
     * Retorna dados consolidados de RH
     */
    public function getDadosRH() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Verificar permissão admin
        if (!isset($_SESSION['usuario_admin']) || $_SESSION['usuario_admin'] != 1) {
            return ['status' => 'erro', 'mensagem' => 'Acesso negado'];
        }

        $mes = (int)($_GET['mes'] ?? date('m'));
        $ano = (int)($_GET['ano'] ?? date('Y'));

        try {
            $db = requireConnection();

            // Totais gerais
            $sql = "
                SELECT 
                    COUNT(DISTINCT usuario_id) as usuarios,
                    SUM(total_horas) as horas_total
                FROM apontamentos
                WHERE MONTH(data_apontamento) = ? AND YEAR(data_apontamento) = ?
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([$mes, $ano]);
            $totais = $stmt->fetch(\PDO::FETCH_ASSOC);

            return [
                'status' => 'sucesso',
                'periodo' => "$mes/$ano",
                'dados' => [
                    'totais' => [
                        'usuarios_com_apontamento' => (int)($totais['usuarios'] ?? 0),
                        'horas_trabalhadas_total' => (float)($totais['horas_total'] ?? 0),
                        'extras_pendentes' => 0,
                        'extras_pagar' => 0
                    ],
                    'top_horas_extras' => [],
                    'usuarios_com_faltas' => [],
                    'usuarios_com_offline' => []
                ]
            ];

        } catch (\Exception $e) {
            return ['status' => 'erro', 'mensagem' => $e->getMessage()];
        }
    }
}