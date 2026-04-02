<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Produto.php';

class ProdutoController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $filtro = trim((string)($_GET['filtro'] ?? ''));
        $paginaAtual = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
        $paginaAtual = ($paginaAtual && $paginaAtual > 0) ? $paginaAtual : 1;
        $itensPorPagina = 25;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $totalItens = Produto::contarAtivos($filtro);
        $totalPaginas = max(1, (int) ceil($totalItens / $itensPorPagina));

        if ($paginaAtual > $totalPaginas) {
            $paginaAtual = $totalPaginas;
            $offset = ($paginaAtual - 1) * $itensPorPagina;
        }

        $produtos = Produto::listarPaginado($filtro, $itensPorPagina, $offset);

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

    $temGrade = is_array($cores) && count($cores) > 0 && is_array($tamanhos) && count($tamanhos) > 0;

    // Suporta o formulario simples (tamanho/cor unitarios) da tela antiga de cadastros.
    if (!$temGrade) {
        $nome = trim((string)($_POST['nome'] ?? ''));
        $tamanho = trim((string)($_POST['tamanho'] ?? ''));
        $cor = trim((string)($_POST['cor'] ?? ''));
        $preco_custo_raw = $_POST['preco_custo'] ?? ($_POST['custo'] ?? '0');
        $preco_venda_raw = $_POST['preco_venda'] ?? ($_POST['preco'] ?? '0');

        if ($nome === '' || $tamanho === '') {
            echo "<script>alert('Erro: Preencha nome e tamanho do produto.'); window.history.back();</script>";
            exit;
        }

        $preco_custo = str_replace(['R$', '.', ','], ['', '', '.'], (string)$preco_custo_raw);
        $preco_venda = str_replace(['R$', '.', ','], ['', '', '.'], (string)$preco_venda_raw);

        $dados = [
            'nome'        => $nome,
            'tamanho'     => $tamanho,
            'cor'         => $cor,
            'sku'         => $sku_base,
            'preco_custo' => $preco_custo,
            'preco_venda' => $preco_venda
        ];

        try {
            Produto::salvar($dados);
            header('Location: index.php?rota=produtos');
            exit;
        } catch (Exception $e) {
            echo "<script>alert('Erro ao salvar produto: verifique SKU duplicado ou dados invalidos.'); window.history.back();</script>";
            exit;
        }
    }

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
    $itensSalvos = 0;
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
                $itensSalvos++;
            } catch (Exception $e) {
                // Se der erro (ex: código duplicado), o sistema apenas ignora este item e pula pro próximo
                continue; 
            }
        }
    }

    if ($itensSalvos === 0) {
        echo "<script>alert('Nenhum item foi salvo. Verifique se os SKUs gerados ja existem.'); window.history.back();</script>";
        exit;
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

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if ($id) {
            Produto::excluir($id); // Chama o Soft Delete
        }

        $redirect = $_GET['redirect'] ?? '';
        if ($redirect === 'criar_produto') {
            header('Location: index.php?rota=criar_produto');
        } else {
            header('Location: index.php?rota=produtos');
        }
        exit;
    }
}