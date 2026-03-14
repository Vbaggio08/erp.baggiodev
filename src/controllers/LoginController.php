<?php
require_once __DIR__ . '/../config/database.php';

class LoginController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?rota=dashboard');
            exit;
        }
        require __DIR__ . '/../views/geral/login.php';
    }

    public function autenticar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && (password_verify($senha, $user['senha']) || md5($senha) === $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_nivel'] = $user['nivel'];
            header('Location: index.php?rota=dashboard');
            exit; 
        } else {
            echo "<script>alert('Dados inválidos!'); window.location='index.php?rota=login';</script>";
        }
    }

    public function sair() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header('Location: index.php?rota=login');
        exit;
    }

    // --- USUÁRIOS ---

    public function listarUsuarios() {
        $this->verificarAdmin();
        $pdo = Database::getConnection();
        $usuarios = $pdo->query("SELECT * FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
        // Variável vazia para o formulário de cadastro
        $usuarioEdit = null; 
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/admin/usuarios.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // NOVA FUNÇÃO: Carrega dados para edição
    public function editarUsuario() {
        $this->verificarAdmin();
        $pdo = Database::getConnection();
        $id = $_GET['id'];
        
        // Busca o usuário específico
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuarioEdit = $stmt->fetch(PDO::FETCH_ASSOC);

        // Busca a lista para mostrar na tabela abaixo
        $usuarios = $pdo->query("SELECT * FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/admin/usuarios.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    public function salvarUsuario() {
        $this->verificarAdmin();
        $pdo = Database::getConnection();

        $id = $_POST['id'] ?? null;
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $nivel = $_POST['nivel'];
        $senha = $_POST['senha'];
        $departamento = $_POST['departamento'] ?? 'Geral';
        $cargo = $_POST['cargo'] ?? 'Operacional';
        $carga_horaria_diaria = $_POST['carga_horaria_diaria'] ?? 8.0;
        $data_admissao = $_POST['data_admissao'] ?? date('Y-m-d');
        $tipo_contrato = $_POST['tipo_contrato'] ?? 'CLT';

        if ($id) {
            // --- É UMA EDIÇÃO (UPDATE) ---
            if (!empty($senha)) {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, email=?, senha=?, nivel=?, departamento=?, cargo=?, carga_horaria_diaria=?, data_admissao=?, tipo_contrato=? WHERE id=?");
                $stmt->execute([$nome, $email, $senhaHash, $nivel, $departamento, $cargo, $carga_horaria_diaria, $data_admissao, $tipo_contrato, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, email=?, nivel=?, departamento=?, cargo=?, carga_horaria_diaria=?, data_admissao=?, tipo_contrato=? WHERE id=?");
                $stmt->execute([$nome, $email, $nivel, $departamento, $cargo, $carga_horaria_diaria, $data_admissao, $tipo_contrato, $id]);
            }
        } else {
            // --- É UM NOVO (INSERT) ---
            if (!empty($senha)) {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel, departamento, cargo, carga_horaria_diaria, data_admissao, tipo_contrato) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $email, $senhaHash, $nivel, $departamento, $cargo, $carga_horaria_diaria, $data_admissao, $tipo_contrato]);
            }
        }

        header('Location: index.php?rota=gerenciar_usuarios');
    }

    public function excluirUsuario() {
        $this->verificarAdmin();
        $pdo = Database::getConnection();
        $id = $_GET['id'];
        if ($id != $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: index.php?rota=gerenciar_usuarios');
    }

    public function definirCargoUnidade() {
        $this->verificarAdmin();
        $pdo = Database::getConnection();
        
        $usuario_id = $_POST['usuario_id'];
        $departamento = $_POST['departamento'];
        $cargo = $_POST['cargo'];
        $carga_horaria_diaria = $_POST['carga_horaria_diaria'];
        
        $stmt = $pdo->prepare("UPDATE usuarios SET departamento=?, cargo=?, carga_horaria_diaria=? WHERE id=?");
        $stmt->execute([$departamento, $cargo, $carga_horaria_diaria, $usuario_id]);
        
        header('Location: index.php?rota=gerenciar_usuarios');
    }

    private function verificarAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== 'admin') {
            
            $url = "index.php?rota=dashboard"; 
            $tempo = 3; 

            echo "<div style='font-family: Arial, sans-serif; text-align: center; margin-top: 50px;'>";
            echo "<h1 style='color: #e74c3c;'>🚫 Acesso Negado</h1>";
            echo "<p style='font-size: 18px;'>Você não tem permissão para acessar esta área.</p>";
            echo "<p>Redirecionando em <strong>$tempo</strong> segundos...</p>";
            
            echo "<meta http-equiv='refresh' content='$tempo;url=$url'>";
            echo "</div>";

            
            exit; 
        }
        
        return true; 
    }

    protected function verificarRH() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_nivel']) || !in_array($_SESSION['user_nivel'], ['admin', 'rh'])) {
            
            $url = "index.php?rota=dashboard"; 
            $tempo = 3; 

            echo "<div style='font-family: Arial, sans-serif; text-align: center; margin-top: 50px;'>";
            echo "<h1 style='color: #e74c3c;'>🚫 Acesso Negado</h1>";
            echo "<p style='font-size: 18px;'>Apenas RH e Admin podem acessar.</p>";
            echo "<p>Redirecionando em <strong>$tempo</strong> segundos...</p>";
            
            echo "<meta http-equiv='refresh' content='$tempo;url=$url'>";
            echo "</div>";

            exit; 
        }
        
        return true; 
    }
}