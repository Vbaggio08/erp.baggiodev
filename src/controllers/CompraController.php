<?php
require_once __DIR__ . '/../config/database.php';

class CompraController {
    
    // 1. LISTAR COMPRAS
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        // Busca as últimas 50 compras
        $compras = $pdo->query("SELECT * FROM compras ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/compras/lista.php'; // ATENÇÃO AO CAMINHO
        require __DIR__ . '/../views/geral/footer.php';
    }

    // 2. NOVA COMPRA (Tela)
    public function nova() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Inicia lista vazia se não existir
        if (!isset($_SESSION['lista_compra'])) $_SESSION['lista_compra'] = [];

        $pdo = Database::getConnection();
        // Busca fornecedores
        $fornecedores = $pdo->query("SELECT * FROM fornecedores ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/compras/nova.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // 3. ADICIONAR ITEM NA SESSÃO
    public function adicionarItem() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $qtd = (float) $_POST['qtd'];
        $valor = (float) str_replace(',', '.', $_POST['valor']);
        
        $item = [
            'produto'   => $_POST['produto'],
            'qtd'       => $qtd,
            'valor'     => $valor,
            'total'     => $qtd * $valor
        ];
        
        $_SESSION['lista_compra'][] = $item;
        header('Location: index.php?rota=nova_compra');
    }

    // 4. REMOVER ITEM
    public function removerItem() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $index = $_POST['index'] ?? null;
        if ($index !== null && isset($_SESSION['lista_compra'][$index])) {
            unset($_SESSION['lista_compra'][$index]);
            $_SESSION['lista_compra'] = array_values($_SESSION['lista_compra']);
        }
        header('Location: index.php?rota=nova_compra');
    }

    // 5. LIMPAR LISTA (Reset)
    public function limparLista() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['lista_compra'] = [];
        header('Location: index.php?rota=nova_compra');
    }

    // 6. SALVAR NO BANCO
    // 6. SALVAR NO BANCO (Versão Corrigida)
    public function salvar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        
        $fornecedor = $_POST['fornecedor'] ?? 'Não informado';
        $dataCompra = $_POST['data_compra'] ?? date('Y-m-d');
        $obs        = $_POST['obs'] ?? '';

        $itens = $_SESSION['lista_compra'] ?? [];
        $valorTotal = 0;
        $resumo = "";

        foreach($itens as $i) {
            $totalItem = $i['total'] ?? 0;
            $valorTotal += $totalItem;
            $resumo .= "{$i['qtd']}x {$i['produto']} | ";
        }
        
        $itensJson = json_encode($itens);

        // SQL alinhado com as colunas: fornecedor, produto, status, data_compra, valor_total, itens_json, observacoes
        $sql = "INSERT INTO compras (fornecedor, produto, status, data_compra, valor_total, itens_json, observacoes) 
                VALUES (?, ?, 'Pendente', ?, ?, ?, ?)";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $fornecedor, 
                substr($resumo, 0, 100), // Resumo do pedido
                $dataCompra, 
                $valorTotal, 
                $itensJson, 
                $obs
            ]);
            
            $_SESSION['lista_compra'] = []; // Limpa a lista após salvar
            header('Location: index.php?rota=compras');
        } catch (PDOException $e) {
            echo "Erro ao salvar no banco: " . $e->getMessage();
            exit;
        }
    }

    // 7. MUDAR STATUS
    public function mudarStatus() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $id = $_GET['id'];
        $status = $_GET['status'];
        
        $pdo = Database::getConnection();
        
        // Se mudar para "Recebido", poderíamos atualizar estoque aqui futuramente
        $stmt = $pdo->prepare("UPDATE compras SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        header('Location: index.php?rota=compras');
    }

    // 8. EXCLUIR
    public function excluir() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (($_SESSION['user_nivel'] ?? '') !== 'admin') {
            header('Location: index.php?rota=compras');
            exit;
        }
        $id = $_GET['id'];
        $pdo = Database::getConnection();
        $pdo->prepare("DELETE FROM compras WHERE id = ?")->execute([$id]);
        header('Location: index.php?rota=compras');
    }
}