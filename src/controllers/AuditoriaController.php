<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AuditoriaAlteracao.php';

class AuditoriaController {
    
    /**
     * Dashboard de Auditoria (Admin)
     */
    public function dashboard() {
        $this->verificarAdmin();
        
        $pdo = Database::getConnection();
        
        // Últimas alterações
        $sql = "SELECT h.*, u.nome as usuario_nome, ap.data as apontamento_data
                FROM historico_alteracoes_ponto h
                LEFT JOIN usuarios u ON h.usuario_alterador_id = u.id
                INNER JOIN apontamentos_ponto ap ON h.apontamento_id = ap.id
                ORDER BY h.criado_em DESC
                LIMIT 50";
        
        $alteracoes_recentes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/admin/auditoria_dashboard.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * Histórico de um apontamento
     */
    public function historicoApontamento() {
        $this->verificarRH();
        
        $apontamento_id = $_GET['id'];
        $historico = AuditoriaAlteracao::obterHistoricoApontamento($apontamento_id);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/admin/auditoria_apontamento.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * Histórico de um usuário em um período
     */
    public function historicoUsuario() {
        $this->verificarRH();
        
        $usuario_id = $_GET['usuario_id'];
        $data_inicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $data_fim = $_GET['data_fim'] ?? date('Y-m-d');
        
        $historico = AuditoriaAlteracao::obterHistoricoUsuario($usuario_id, $data_inicio, $data_fim);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/admin/auditoria_usuario.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * Relatório de Auditoria (Filtros)
     */
    public function relatorio() {
        $this->verificarAdmin();
        
        $pdo = Database::getConnection();
        
        $tipo_alteracao = $_GET['tipo'] ?? null;
        $usuario_alterador = $_GET['usuario_alterador'] ?? null;
        $data_inicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $data_fim = $_GET['data_fim'] ?? date('Y-m-d');
        
        $sql = "SELECT h.*, u.nome as usuario_nome, alterador.nome as alterador_nome
                FROM historico_alteracoes_ponto h
                LEFT JOIN usuarios u ON h.usuario_alterador_id = u.id
                LEFT JOIN usuarios alterador ON h.usuario_alterador_id = alterador.id
                INNER JOIN apontamentos_ponto ap ON h.apontamento_id = ap.id
                WHERE ap.criado_em BETWEEN ? AND ?";
        
        $params = [$data_inicio, $data_fim];
        
        if ($tipo_alteracao) {
            $sql .= " AND h.tipo_alteracao = ?";
            $params[] = $tipo_alteracao;
        }
        
        if ($usuario_alterador) {
            $sql .= " AND h.usuario_alterador_id = ?";
            $params[] = $usuario_alterador;
        }
        
        $sql .= " ORDER BY h.criado_em DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $alteracoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/admin/auditoria_relatorio.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * Validar integridade de um registro
     */
    public function validarIntegridade() {
        header('Content-Type: application/json');
        $this->verificarAdmin();
        
        $auditoria_id = $_GET['id'];
        $valido = AuditoriaAlteracao::validarIntegridade($auditoria_id);
        
        return json_encode([
            'id' => $auditoria_id,
            'valido' => $valido,
            'mensagem' => $valido ? 'Hash válido' : 'Hash inválido - registro foi alterado!'
        ]);
    }
    
    private function verificarAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== 'admin') {
            header('Location: index.php?rota=dashboard');
            exit;
        }
    }
    
    private function verificarRH() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_nivel']) || !in_array($_SESSION['user_nivel'], ['admin', 'rh'])) {
            header('Location: index.php?rota=dashboard');
            exit;
        }
    }
}
?>
