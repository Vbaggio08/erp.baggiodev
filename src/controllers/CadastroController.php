<?php
require_once __DIR__ . '/../config/database.php';

class CadastroController {

    private function verificarSessao() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) { header('Location: index.php?rota=login'); exit; }
    }

    // =============================================================
    // 🏢 EMPRESAS (Suas Filiais)
    // =============================================================
    public function listarEmpresas() {
        $this->verificarSessao();
        $pdo = Database::getConnection();
        
        $empresaEdit = null;
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM empresas WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $empresaEdit = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $empresas = $pdo->query("SELECT * FROM empresas ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!is_dir(__DIR__ . '/../views/cadastros')) mkdir(__DIR__ . '/../views/cadastros', 0777, true);
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/cadastros/empresas.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    public function salvarEmpresa() {
        $this->verificarSessao();
        $pdo = Database::getConnection();
        
        $id = $_POST['id'] ?? '';
        $dados = [
            $_POST['nome'], $_POST['cnpj'], $_POST['email'], 
            $_POST['cep'], $_POST['endereco'], $_POST['cidade']
        ];

        if (!empty($id)) {
            $sql = "UPDATE empresas SET nome=?, cnpj=?, email=?, cep=?, endereco=?, cidade=? WHERE id=?";
            $dados[] = $id;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($dados);
        } else {
            $sql = "INSERT INTO empresas (nome, cnpj, email, cep, endereco, cidade) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($dados);
        }
        header('Location: index.php?rota=cad_empresas');
    }

    public function excluirEmpresa() {
        $this->verificarSessao();
        $id = $_GET['id'];
        if($id > 1) { 
            Database::getConnection()->prepare("DELETE FROM empresas WHERE id = ?")->execute([$id]);
        }
        header('Location: index.php?rota=cad_empresas');
    }

    // =============================================================
    // 👥 CLIENTES
    // =============================================================
    public function listarClientes() {
        $this->verificarSessao();
        $pdo = Database::getConnection();
        
        $clienteEdit = null;
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $clienteEdit = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $clientes = $pdo->query("SELECT * FROM clientes ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/cadastros/clientes.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    public function salvarCliente() {
        $this->verificarSessao();
        $pdo = Database::getConnection();
        
        $id = $_POST['id'] ?? '';
        $dados = [
            $_POST['nome'], $_POST['cpf_cnpj'], $_POST['email'], $_POST['telefone'],
            $_POST['cep'], $_POST['endereco'], $_POST['cidade']
        ];

        if (!empty($id)) {
            $sql = "UPDATE clientes SET nome=?, cpf_cnpj=?, email=?, telefone=?, cep=?, endereco=?, cidade=? WHERE id=?";
            $dados[] = $id;
            $pdo->prepare($sql)->execute($dados);
        } else {
            $sql = "INSERT INTO clientes (nome, cpf_cnpj, email, telefone, cep, endereco, cidade) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute($dados);
        }
        header('Location: index.php?rota=cad_clientes');
    }

    public function excluirCliente() {
        $this->verificarSessao();
        Database::getConnection()->prepare("DELETE FROM clientes WHERE id = ?")->execute([$_GET['id']]);
        header('Location: index.php?rota=cad_clientes');
    }

    // =============================================================
    // 🚛 FORNECEDORES (ATUALIZADO)
    // =============================================================
    public function listarFornecedores() {
        $this->verificarSessao();
        $pdo = Database::getConnection();
        
        // Lógica de Edição adicionada
        $fornecedorEdit = null;
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM fornecedores WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $fornecedorEdit = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $fornecedores = $pdo->query("SELECT * FROM fornecedores ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/cadastros/fornecedores.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    public function salvarFornecedor() {
        $this->verificarSessao();
        $pdo = Database::getConnection();
        
        $id = $_POST['id'] ?? '';
        // Array com os novos campos
        $dados = [
            $_POST['nome'], $_POST['cnpj'], $_POST['email'], 
            $_POST['contato'], $_POST['categoria'],
            $_POST['cep'], $_POST['endereco'], $_POST['cidade']
        ];

        if (!empty($id)) {
            // Update
            $sql = "UPDATE fornecedores SET nome=?, cnpj=?, email=?, contato=?, categoria=?, cep=?, endereco=?, cidade=? WHERE id=?";
            $dados[] = $id;
            $pdo->prepare($sql)->execute($dados);
        } else {
            // Insert
            $sql = "INSERT INTO fornecedores (nome, cnpj, email, contato, categoria, cep, endereco, cidade) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute($dados);
        }
        
        header('Location: index.php?rota=cad_fornecedores');
    }
    
    public function excluirFornecedor() {
        $this->verificarSessao();
        Database::getConnection()->prepare("DELETE FROM fornecedores WHERE id = ?")->execute([$_GET['id']]);
        header('Location: index.php?rota=cad_fornecedores');
    }
}