<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Produto.php';

class ProdutoController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Chama o método estático do Model
        $produtos = Produto::listarTodos();
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/produtos/listar.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    public function criar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Busca os produtos para exibir na tabela lateral
        $produtos = Produto::listarTodos(); 
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/produtos/criar.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    public function salvar() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    $cores = $_POST['cores'] ?? [];
    $tamanhos = $_POST['tamanhos'] ?? [];
    $sku_base = strtoupper(trim($_POST['sku'] ?? ''));

    // 1. Dicionário Oficial de Siglas da Ripfire
    $siglas_cores = [
        'Preta'         => 'PR',
        'Branca'        => 'BR',
        'Off-White'     => 'OW',
        'Azul-Marinho'  => 'AM',
        'Bordô'         => 'BO',
        'Verde'         => 'VD',
        'Marrom'        => 'MA',
        'Vermelha'      => 'VM',
        'Doce de Leite' => 'DL'
    ];

    // Validação de segurança
    if (empty($cores) || empty($tamanhos)) {
        echo "<script>alert('Erro: Selecione ao menos uma COR e um TAMANHO!'); window.history.back();</script>";
        exit;
    }

    $nome = $_POST['nome'];
    $preco_custo = str_replace(['R$', '.', ','], ['', '', '.'], $_POST['custo']);
    $preco_venda = str_replace(['R$', '.', ','], ['', '', '.'], $_POST['preco']);

    // --- LOOP ANINHADO: Para cada COR, gera todos os TAMANHOS ---
    foreach ($cores as $cor) {
        
        // Busca a sigla exata. Se a cor não estiver no dicionário, usa as 2 primeiras letras como "quebra-galho"
        $cor_slug = $siglas_cores[$cor] ?? strtoupper(substr($cor, 0, 2));

        foreach ($tamanhos as $tamanho) {
            
            // Gerador de SKU Inteligente: BASE-COR-TAMANHO (Ex: RF01-PR-G)
            $sku_final = '';
            if (!empty($sku_base)) {
                $sku_final = "{$sku_base}-{$cor_slug}-{$tamanho}";
            }

            $dados = [
                'nome'        => $nome,
                'tamanho'     => $tamanho,
                'cor'         => $cor,
                'sku'         => $sku_final,
                'preco_custo' => $preco_custo,
                'preco_venda' => $preco_venda
            ];

            // 2. Bloco Try-Catch para evitar o "Fatal Error" de SKU duplicado no banco
            try {
                Produto::salvar($dados);
            } catch (Exception $e) {
                // Se der erro (ex: código duplicado), o sistema apenas ignora este item e pula pro próximo
                continue; 
            }
        }
    }
    
    header('Location: index.php?rota=produtos');
    exit;
}
    public function excluir() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Segurança: Só admin deleta
        if (($_SESSION['user_nivel'] ?? '') !== 'admin') {
            header('Location: index.php?rota=produtos');
            exit;
        }

        $id = $_GET['id'] ?? null;
        
        if ($id) {
            Produto::excluir($id); // Chama o Soft Delete
        }

        header('Location: index.php?rota=produtos');
    }
}