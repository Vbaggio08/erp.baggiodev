<?php
require_once __DIR__ . '/../config/database.php';

class ClienteController {

    // 1. LISTAR TODOS (Modo padrão: formulário vazio em cima, tabela embaixo)
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        
        $clientes = $pdo->query("SELECT * FROM clientes ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/cadastros/clientes.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // 2. MODO EDIÇÃO (Formulário preenchido em cima, tabela embaixo)
    public function editar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        
        // Busca TODOS os clientes para a tabela não sumir
        $clientes = $pdo->query("SELECT * FROM clientes ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Busca o cliente ESPECÍFICO que você clicou para editar
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
            $stmt->execute([$id]);
            $clienteEdit = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/cadastros/clientes.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // 3. SALVAR (Cadastra um novo ou atualiza um existente)
    public function salvar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();

        // Se veio o ID no formulário escondido (hidden), é uma atualização
        $id = $_POST['id'] ?? null;
        
        $dados = [
            $_POST['nome'],
            $_POST['cpf_cnpj'] ?? '',
            $_POST['telefone'] ?? '',
            $_POST['email'] ?? '',
            $_POST['cep'] ?? '',
            $_POST['endereco'] ?? '',
            $_POST['cidade'] ?? ''
        ];

        if (!empty($id)) {
            // ATUALIZAR
            $sql = "UPDATE clientes SET nome=?, cpf_cnpj=?, telefone=?, email=?, cep=?, endereco=?, cidade=? WHERE id=?";
            $dados[] = $id;
            $pdo->prepare($sql)->execute($dados);
        } else {
            // CRIAR NOVO
            $sql = "INSERT INTO clientes (nome, cpf_cnpj, telefone, email, cep, endereco, cidade) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute($dados);
        }

        // Volta para a tela padrão de clientes
        header('Location: index.php?rota=cad_clientes');
    }

    // 4. EXCLUIR CLIENTE
    public function excluir() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Bloqueio de segurança: apenas admin exclui
        if (($_SESSION['user_nivel'] ?? '') !== 'admin') {
            header('Location: index.php?rota=cad_clientes');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if ($id) {
            $pdo = Database::getConnection();
            $pdo->prepare("DELETE FROM clientes WHERE id = ?")->execute([$id]);
        }
        
        header('Location: index.php?rota=cad_clientes');
    }
}