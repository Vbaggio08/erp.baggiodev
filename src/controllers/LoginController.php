<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

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
        $identificador = trim((string)($_POST['login'] ?? ($_POST['email'] ?? '')));
        $senha = $_POST['senha'] ?? '';

        $user = $this->buscarUsuarioPorIdentificador($identificador);

        if ($user && (password_verify($senha, $user['senha']) || md5($senha) === $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_nivel'] = $user['nivel'];
            // Flag para compatibilidade com resto do código
            $_SESSION['usuario_admin'] = ($user['nivel'] === 'admin') ? 1 : 0;
            header('Location: index.php?rota=dashboard');
            exit; 
        } else {
            echo "<script>alert('Usuário/Email/CPF ou senha inválidos!'); window.location='index.php?rota=login';</script>";
        }
    }

    /**
     * Busca usuário por username, email, nome ou CPF.
     */
    private function buscarUsuarioPorIdentificador(string $identificador): ?array {
        if ($identificador === '') {
            return null;
        }

        $pdo = Database::getConnection();
        $cpfNormalizado = Usuario::normalizarCpf($identificador);

        $condicoes = [];
        $params = [];

        if ($this->colunaExiste('usuarios', 'username')) {
            $condicoes[] = 'LOWER(username) = LOWER(?)';
            $params[] = $identificador;
        }

        $condicoes[] = 'LOWER(email) = LOWER(?)';
        $params[] = $identificador;

        $condicoes[] = 'LOWER(nome) = LOWER(?)';
        $params[] = $identificador;

        if ($this->colunaExiste('usuarios', 'cpf')) {
            $condicoes[] = "REPLACE(REPLACE(REPLACE(COALESCE(cpf, ''), '.', ''), '-', ''), '/', '') = ?";
            $params[] = $cpfNormalizado;
        }

        $sql = 'SELECT * FROM usuarios WHERE ' . implode(' OR ', $condicoes) . ' LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    private function colunaExiste(string $tabela, string $coluna): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SHOW COLUMNS FROM {$tabela} LIKE '" . addslashes($coluna) . "'");
        return (bool)($stmt && $stmt->fetch(PDO::FETCH_ASSOC));
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
        $this->garantirEstruturaUsuariosAuth();
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
        $this->garantirEstruturaUsuariosAuth();
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
        $this->garantirEstruturaUsuariosAuth();
        $pdo = Database::getConnection();

        $id = $_POST['id'] ?? null;
        $nome = $_POST['nome'];
        $username = strtolower(trim((string)($_POST['username'] ?? '')));
        $email = $_POST['email'];
        $cpf = Usuario::normalizarCpf((string)($_POST['cpf'] ?? ''));
        $nivel = $_POST['nivel'];
        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';
        $departamento = $_POST['departamento'] ?? 'Geral';
        $cargo = $_POST['cargo'] ?? 'Operacional';
        $carga_horaria_diaria = $_POST['carga_horaria_diaria'] ?? 8.0;
        $data_admissao = $_POST['data_admissao'] ?? date('Y-m-d');
        $tipo_contrato = $_POST['tipo_contrato'] ?? 'CLT';
        $horario_entrada_1 = $_POST['horario_entrada_1'] ?? '08:00';
        $horario_saida_1 = $_POST['horario_saida_1'] ?? '12:00';
        $horario_entrada_2 = $_POST['horario_entrada_2'] ?? '13:00';
        $horario_saida_2 = $_POST['horario_saida_2'] ?? '18:00';

        if (!$id && $username === '') {
            echo "<script>alert('Informe o usuário de login.'); window.location='index.php?rota=gerenciar_usuarios';</script>";
            return;
        }

        if (strlen($cpf) !== 11) {
            echo "<script>alert('CPF inválido. Informe 11 dígitos.'); window.location='index.php?rota=gerenciar_usuarios';</script>";
            return;
        }

        if ($username !== '') {
            $sqlUser = 'SELECT id FROM usuarios WHERE username = ?' . ($id ? ' AND id <> ?' : '');
            $stmtUser = $pdo->prepare($sqlUser);
            $stmtUser->execute($id ? [$username, $id] : [$username]);
            if ($stmtUser->fetch(PDO::FETCH_ASSOC)) {
                echo "<script>alert('Usuário de login já cadastrado.'); window.location='index.php?rota=gerenciar_usuarios';</script>";
                return;
            }
        }

        $sqlCpf = 'SELECT id FROM usuarios WHERE cpf = ?' . ($id ? ' AND id <> ?' : '');
        $stmtCpf = $pdo->prepare($sqlCpf);
        $stmtCpf->execute($id ? [$cpf, $id] : [$cpf]);
        if ($stmtCpf->fetch(PDO::FETCH_ASSOC)) {
            echo "<script>alert('CPF já cadastrado para outro usuário.'); window.location='index.php?rota=gerenciar_usuarios';</script>";
            return;
        }

        if ($id) {
            // --- É UMA EDIÇÃO (UPDATE) ---
            if (!empty($senha)) {
                if ($senha !== $confirmar_senha) {
                    echo "<script>alert('Senha e confirmação de senha não conferem.'); window.location='index.php?rota=gerenciar_usuarios';</script>";
                    return;
                }
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, username=?, email=?, cpf=?, senha=?, nivel=?, departamento=?, cargo=?, carga_horaria_diaria=?, data_admissao=?, tipo_contrato=?, horario_entrada_1=?, horario_saida_1=?, horario_entrada_2=?, horario_saida_2=? WHERE id=?");
                $stmt->execute([$nome, ($username !== '' ? $username : null), $email, $cpf, $senhaHash, $nivel, $departamento, $cargo, $carga_horaria_diaria, $data_admissao, $tipo_contrato, $horario_entrada_1, $horario_saida_1, $horario_entrada_2, $horario_saida_2, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, username=?, email=?, cpf=?, nivel=?, departamento=?, cargo=?, carga_horaria_diaria=?, data_admissao=?, tipo_contrato=?, horario_entrada_1=?, horario_saida_1=?, horario_entrada_2=?, horario_saida_2=? WHERE id=?");
                $stmt->execute([$nome, ($username !== '' ? $username : null), $email, $cpf, $nivel, $departamento, $cargo, $carga_horaria_diaria, $data_admissao, $tipo_contrato, $horario_entrada_1, $horario_saida_1, $horario_entrada_2, $horario_saida_2, $id]);
            }
        } else {
            // --- É UM NOVO (INSERT) ---
            if (empty($senha)) {
                echo "<script>alert('Informe a senha para novo usuário.'); window.location='index.php?rota=gerenciar_usuarios';</script>";
                return;
            }

            if ($senha !== $confirmar_senha) {
                echo "<script>alert('Senha e confirmação de senha não conferem.'); window.location='index.php?rota=gerenciar_usuarios';</script>";
                return;
            }

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, username, email, cpf, senha, nivel, departamento, cargo, carga_horaria_diaria, data_admissao, tipo_contrato, horario_entrada_1, horario_saida_1, horario_entrada_2, horario_saida_2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $username, $email, $cpf, $senhaHash, $nivel, $departamento, $cargo, $carga_horaria_diaria, $data_admissao, $tipo_contrato, $horario_entrada_1, $horario_saida_1, $horario_entrada_2, $horario_saida_2]);
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

    private function garantirEstruturaUsuariosAuth(): void {
        $pdo = Database::getConnection();
        try {
            $pdo->exec('ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS cpf VARCHAR(14) NULL AFTER email');
        } catch (\Throwable $e) {
            // Sem bloqueio: em schema já atualizado não deve falhar.
        }

        try {
            $pdo->exec('ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS username VARCHAR(50) NULL AFTER nome');
        } catch (\Throwable $e) {
            // Sem bloqueio: em schema já atualizado não deve falhar.
        }

        try {
            $pdo->exec('ALTER TABLE usuarios ADD UNIQUE KEY uk_usuarios_cpf (cpf)');
        } catch (\Throwable $e) {
            // Índice pode já existir.
        }

        try {
            $pdo->exec('ALTER TABLE usuarios ADD UNIQUE KEY uk_usuarios_username (username)');
        } catch (\Throwable $e) {
            // Índice pode já existir.
        }
    }
}