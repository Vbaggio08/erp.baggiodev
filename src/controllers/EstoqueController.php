<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Produto.php'; 
require_once __DIR__ . '/../models/Estoque.php'; 

class EstoqueController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $busca = $_GET['busca'] ?? '';
        $estoque = Estoque::getEstoqueAgrupado($busca);
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/estoque/saldo.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    public function telaEntrada() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_estoque_mov'])) {
            $_SESSION['csrf_estoque_mov'] = bin2hex(random_bytes(32));
        }
        $listaProdutos = Produto::listarTodos();
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/estoque/entrada.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // --- ATUALIZADO PARA ACEITAR PERDA ---
    public function salvarEntrada() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $csrfForm = (string)($_POST['csrf_estoque_mov'] ?? '');
        $csrfSessao = (string)($_SESSION['csrf_estoque_mov'] ?? '');
        if ($csrfForm === '' || $csrfSessao === '' || !hash_equals($csrfSessao, $csrfForm)) {
            echo "<script>alert('Erro de seguranca (CSRF). Recarregue a tela e tente novamente.'); window.history.back();</script>";
            exit;
        }

        $tipoFormulario = trim((string)($_POST['tipo'] ?? ''));
        $obs = trim((string)($_POST['observacao'] ?? ''));

        if (!in_array($tipoFormulario, ['entrada', 'saida', 'perda'], true)) {
            echo "<script>alert('Erro: Tipo de movimentacao invalido!'); window.history.back();</script>";
            exit;
        }

        // LÓGICA INTELIGENTE:
        // Se o usuário escolheu "perda", a gente converte para "saida" no banco
        // E adiciona "[PERDA]" na observação automaticamente.
        if ($tipoFormulario === 'perda') {
            $tipoBanco = 'saida'; 
            $obs = "[PERDA] " . $obs; // Adiciona a etiqueta
        } else {
            $tipoBanco = $tipoFormulario;
        }

        $dados = [
            'tipo'       => $tipoBanco, // Grava como saida ou entrada
            'produto_id' => (int)($_POST['produto_id'] ?? 0),
            'produto'    => trim((string)($_POST['produto'] ?? '')),
            'tamanho'    => trim((string)($_POST['tamanho'] ?? '')),
            'cor'        => trim((string)($_POST['cor'] ?? '')),
            'quantidade' => (int)$_POST['quantidade'],
            'observacao' => $obs,
            'usuario'    => $_SESSION['user_name'] ?? 'Admin',
            'usuario_id' => (int)($_SESSION['user_id'] ?? 0)
        ];

        // Validação
        if (empty($dados['produto']) || empty($dados['tamanho']) || empty($dados['cor']) || empty($dados['quantidade'])) {
             echo "<script>alert('Erro: Preencha todos os campos!'); window.history.back();</script>";
             exit;
        }

        // Chama o Model
        if (Estoque::registrarMovimento($dados)) {
            header('Location: index.php?rota=estoque_historico');
        } else {
            $erro = Estoque::getUltimoErro();
            $mensagem = $erro !== '' ? $erro : 'Erro ao salvar!';
            echo "<script>alert('" . addslashes($mensagem) . "'); window.history.back();</script>";
        }
    }

    public function historico() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $busca = $_GET['busca'] ?? '';
        $movimentacoes = Estoque::getHistoricoCompleto($busca);
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/estoque/historico.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    public function relatorioPerdas() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $busca = $_GET['busca'] ?? '';
        $perdas = Estoque::getRelatorioPerdas($busca);
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/estoque/relatorio_perdas.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
}