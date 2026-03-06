<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Produto.php'; 
require_once __DIR__ . '/../models/Estoque.php'; 

class EstoqueController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $estoque = Estoque::getEstoqueAgrupado();
        require __DIR__ . '/../views/estoque/saldo.php';
    }

    public function telaEntrada() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $listaProdutos = Produto::listarTodos();
        require __DIR__ . '/../views/estoque/entrada.php';
    }

    // --- ATUALIZADO PARA ACEITAR PERDA ---
    public function salvarEntrada() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $tipoFormulario = $_POST['tipo'];
        $obs = $_POST['observacao'];

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
            'produto'    => $_POST['produto'],
            'tamanho'    => $_POST['tamanho'],
            'cor'        => $_POST['cor'],
            'quantidade' => (int)$_POST['quantidade'],
            'observacao' => $obs,
            'usuario'    => $_SESSION['user_name'] ?? 'Admin'
        ];

        // Validação
        if (empty($dados['produto']) || empty($dados['quantidade'])) {
             echo "<script>alert('Erro: Preencha todos os campos!'); window.history.back();</script>";
             exit;
        }

        // Chama o Model
        if (Estoque::registrarMovimento($dados)) {
            header('Location: index.php?rota=estoque_historico');
        } else {
            echo "<script>alert('Erro ao salvar!'); window.history.back();</script>";
        }
    }

    public function historico() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $movimentacoes = Estoque::getHistoricoCompleto();
        require __DIR__ . '/../views/estoque/historico.php';
    }

    public function relatorioPerdas() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $perdas = Estoque::getRelatorioPerdas();
        require __DIR__ . '/../views/estoque/relatorio_perdas.php';
    }
}