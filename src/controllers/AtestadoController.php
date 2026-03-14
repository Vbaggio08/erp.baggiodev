<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Atestado.php';
require_once __DIR__ . '/../models/AuditoriaAlteracao.php';

class AtestadoController {
    
    /**
     * Solicitar Atestado (Funcionário)
     */
    public function solicitarAtestado() {
        $this->verificarLogin();
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/solicitar_atestado.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * Salvar nova solicitação de atestado
     */
    public function salvarSolicitacao() {
        $this->verificarLogin();
        
        $usuario_id = $_SESSION['user_id'];
        $data_inicio = $_POST['data_inicio'];
        $data_fim = $_POST['data_fim'];
        $tipo = $_POST['tipo'];
        
        // Processa arquivo de comprovante se enviado
        $arquivo_path = null;
        if (!empty($_FILES['comprovante']['name'])) {
            $arquivo_path = $this->salvarComprovante($usuario_id);
        }
        
        $atestado_id = Atestado::solicitarAtestado(
            $usuario_id,
            $data_inicio,
            $data_fim,
            $tipo,
            $arquivo_path
        );
        
        if ($atestado_id) {
            header('Location: index.php?rota=meus_atestados&msg=atestado_solicitado');
        } else {
            header('Location: index.php?rota=solicitar_atestado&erro=erro_ao_salvar');
        }
    }
    
    /**
     * Meus Atestados (Histórico do Funcionário)
     */
    public function meusAtestados() {
        $this->verificarLogin();
        
        $usuario_id = $_SESSION['user_id'];
        $atestados = Atestado::listarPorUsuario($usuario_id);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/meus_atestados.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * Atestados Pendentes (RH)
     */
    public function atestadosPendentes() {
        $this->verificarRH();
        
        $atestados = Atestado::listarPendentes();
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/admin/atestados_pendentes.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * Aprovar Atestado (RH)
     */
    public function aprovarAtestado() {
        $this->verificarRH();
        
        $atestado_id = $_POST['atestado_id'];
        $aprovador_id = $_SESSION['user_id'];
        
        $resultado = Atestado::aprovarAtestado($atestado_id, $aprovador_id);
        
        if ($resultado) {
            header('Location: index.php?rota=atestados_pendentes&msg=atestado_aprovado');
        } else {
            header('Location: index.php?rota=atestados_pendentes&erro=erro');
        }
    }
    
    /**
     * Rejeitar Atestado (RH)
     */
    public function rejeitarAtestado() {
        $this->verificarRH();
        
        $atestado_id = $_POST['atestado_id'];
        $motivo = $_POST['motivo_rejeicao'];
        $rejector_id = $_SESSION['user_id'];
        
        $resultado = Atestado::rejeitarAtestado($atestado_id, $motivo, $rejector_id);
        
        if ($resultado) {
            header('Location: index.php?rota=atestados_pendentes&msg=atestado_rejeitado');
        } else {
            header('Location: index.php?rota=atestados_pendentes&erro=erro');
        }
    }
    
    /**
     * Relatório de Atestados (RH)
     */
    public function relatorioAtestados() {
        $this->verificarRH();
        
        $pdo = Database::getConnection();
        
        $mes = $_GET['mes'] ?? date('m');
        $ano = $_GET['ano'] ?? date('Y');
        $status = $_GET['status'] ?? null;
        
        $sql = "SELECT a.*, u.nome, u.departamento
                FROM atestados a
                INNER JOIN usuarios u ON a.usuario_id = u.id
                WHERE MONTH(a.data_inicio) = ? AND YEAR(a.data_inicio) = ?";
        
        $params = [$mes, $ano];
        
        if ($status) {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $atestados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/admin/relatorio_atestados.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
    
    /**
     * Salva arquivo de comprovante
     */
    private function salvarComprovante($usuario_id) {
        $arquivo = $_FILES['comprovante'] ?? null;
        
        if (!$arquivo || $arquivo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        // Validação básica
        $extensoes_permitidas = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $extensoes_permitidas)) {
            return null;
        }
        
        // Cria diretório
        $data = date('Y-m-d');
        $dir = __DIR__ . '/../../assets/uploads/atestados/' . $data;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        // Gera nome hash
        $hash = md5(uniqid());
        $filename = "user_{$usuario_id}_" . $hash . ".$ext";
        $filepath = $dir . '/' . $filename;
        
        // Move arquivo
        move_uploaded_file($arquivo['tmp_name'], $filepath);
        
        // Retorna caminho relativo
        return "$data/$filename";
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
