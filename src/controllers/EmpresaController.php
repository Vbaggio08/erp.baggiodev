<?php
require_once __DIR__ . '/../config/database.php';

class EmpresaController {
    
    // Lista as empresas e prepara a EDIÇÃO se tiver ID na URL
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        
        // 1. Lógica de EDIÇÃO: Se tiver ?id=1 na URL, busca os dados para preencher o form
        $empresaEdit = null;
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM empresas WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $empresaEdit = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // 2. Busca lista de todas as empresas
        try {
            $empresas = $pdo->query("SELECT * FROM empresas")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $empresas = [];
        }
        
        // Carrega a view (passando a variável $empresaEdit)
        require __DIR__ . '/../views/cadastros/empresas.php';
    }

    public function salvar() {
        $pdo = Database::getConnection();
        
        // Recebe todos os campos do seu formulário antigo
        $id = $_POST['id'] ?? '';
        $nome = $_POST['nome'];
        $cnpj = $_POST['cnpj'] ?? '';
        $email = $_POST['email'] ?? '';
        $cep = $_POST['cep'] ?? '';
        $endereco = $_POST['endereco'] ?? '';
        $cidade = $_POST['cidade'] ?? '';

        if (!empty($id)) {
            // ATUALIZAR
            $sql = "UPDATE empresas SET nome=?, cnpj=?, email=?, cep=?, endereco=?, cidade=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $cnpj, $email, $cep, $endereco, $cidade, $id]);
        } else {
            // CRIAR NOVA
            $sql = "INSERT INTO empresas (nome, cnpj, email, cep, endereco, cidade, responsavel) VALUES (?, ?, ?, ?, ?, ?, 'Admin')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $cnpj, $email, $cep, $endereco, $cidade]);
        }
        
        // Volta para a tela de empresas limpa (sem ID na URL)
        header('Location: index.php?rota=empresas');
    }

    public function excluir() {
        $id = $_GET['id'];
        if (!empty($id)) {
            // Verifica se não é a Ripfire Principal (opcional, segurança)
            $pdo = Database::getConnection();
            $pdo->prepare("DELETE FROM empresas WHERE id = ?")->execute([$id]);
        }
        header('Location: index.php?rota=empresas');
    }
}