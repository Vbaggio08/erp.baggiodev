<?php
require_once __DIR__ . '/../config/database.php';

class FornecedorController {

    public function listar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        
        $fornecedores = $pdo->query("SELECT * FROM fornecedores ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/fornecedores/lista.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    public function novo() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/fornecedores/novo.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    public function editar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM fornecedores WHERE id = ?");
            $stmt->execute([$id]);
            $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/fornecedores/novo.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    public function salvar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();

        $id = $_POST['id'] ?? null;
        
        $dados = [
            $_POST['nome'],
            $_POST['cnpj'] ?? '',
            $_POST['email'] ?? '',
            $_POST['contato'] ?? '',
            $_POST['categoria'] ?? '',
            $_POST['cep'] ?? '',
            $_POST['endereco'] ?? '',
            $_POST['cidade'] ?? ''
        ];

        if ($id) {
            $sql = "UPDATE fornecedores SET nome=?, cnpj=?, email=?, contato=?, categoria=?, cep=?, endereco=?, cidade=? WHERE id=?";
            $dados[] = $id;
            $pdo->prepare($sql)->execute($dados);
        } else {
            $sql = "INSERT INTO fornecedores (nome, cnpj, email, contato, categoria, cep, endereco, cidade) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute($dados);
        }

        header('Location: index.php?rota=fornecedores');
    }

    public function excluir() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (($_SESSION['user_nivel'] ?? '') !== 'admin') {
            header('Location: index.php?rota=fornecedores');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if ($id) {
            $pdo = Database::getConnection();
            $pdo->prepare("DELETE FROM fornecedores WHERE id = ?")->execute([$id]);
        }
        header('Location: index.php?rota=fornecedores');
    }
}