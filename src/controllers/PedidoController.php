<?php
require_once __DIR__ . '/../config/database.php';

class PedidoController {
    
    // Lista a Fila de Produção
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) header('Location: index.php?rota=login');

        $pdo = Database::getConnection();
        // Tenta buscar. Se der erro (tabela não existe), retorna vazio para não quebrar a tela.
        try {
            $pedidos = $pdo->query("SELECT * FROM pedidos_producao ORDER BY prioridade DESC, data_entrada ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $pedidos = [];
        }
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/fila.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // Abre o formulário de novo pedido
    public function novo() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) header('Location: index.php?rota=login');
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/nova_venda.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // Salva o pedido no banco
    public function salvar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $pdo = Database::getConnection();
        
        // Pega os dados do formulário
        $cliente = $_POST['cliente_nome'] ?? 'Consumidor Final';
        $produto = $_POST['produto_nome'] ?? 'Personalizado';
        $quantidade = $_POST['quantidade'] ?? 1;
        $tamanho = $_POST['tamanho'] ?? 'U';
        $cor = $_POST['cor'] ?? 'Padrão';
        $obs = $_POST['observacao'] ?? '';
        $prioridade = $_POST['prioridade'] ?? 'Normal'; // Alta, Normal, Baixa
        $status = 'Pendente'; // Status inicial

        try {
            $sql = "INSERT INTO pedidos_producao 
                    (cliente, produto, quantidade, tamanho, cor, observacao, prioridade, status, data_entrada) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$cliente, $produto, $quantidade, $tamanho, $cor, $obs, $prioridade, $status]);
            
            header('Location: index.php?rota=pedidos');

        } catch (Exception $e) {
            // Se der erro (ex: tabela não tem coluna prioridade), avisa
            echo "Erro ao salvar: " . $e->getMessage();
            echo "<br><a href='index.php?rota=novo_pedido'>Voltar</a>";
        }
    }

    public function excluir() {
        $id = $_GET['id'];
        if (!empty($id)) {
            Database::getConnection()->prepare("DELETE FROM pedidos_producao WHERE id = ?")->execute([$id]);
        }
        header('Location: index.php?rota=pedidos');
    }

    // Abre o formulário para um novo pedido DTF
    public function novo_dtf() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) header('Location: index.php?rota=login');
        
        // A view não precisa de dados extras por enquanto
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/nova_dtf.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // Salva o pedido DTF no banco de dados
    public function salvar_dtf() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?rota=login');
            exit;
        }

        $pdo = Database::getConnection();

        // 1. Tratar Upload de Arquivos
        $nomeArquivoImpressao = null;
        if (isset($_FILES['arquivo_impressao']) && $_FILES['arquivo_impressao']['error'] == 0) {
            $uploadDir = __DIR__ . '/../../assets/uploads/';
            $ext = pathinfo($_FILES['arquivo_impressao']['name'], PATHINFO_EXTENSION);
            $nomeArquivoImpressao = uniqid() . '.' . $ext;
            
            if (!move_uploaded_file($_FILES['arquivo_impressao']['tmp_name'], $uploadDir . $nomeArquivoImpressao)) {
                die("Erro ao fazer upload do arquivo de impressão.");
            }
        }

        $nomeArquivoComprovante = null;
        if (isset($_FILES['arquivo_comprovante']) && $_FILES['arquivo_comprovante']['error'] == 0) {
            $uploadDirComprovante = __DIR__ . '/../../assets/uploads/comprovantes/';
            if (!is_dir($uploadDirComprovante)) {
                mkdir($uploadDirComprovante, 0777, true);
            }
            $ext = pathinfo($_FILES['arquivo_comprovante']['name'], PATHINFO_EXTENSION);
            $nomeArquivoComprovante = uniqid() . '_comp.' . $ext;
            
            if (!move_uploaded_file($_FILES['arquivo_comprovante']['tmp_name'], $uploadDirComprovante . $nomeArquivoComprovante)) {
                die("Erro ao fazer upload do comprovante.");
            }
        }

        // 2. Coletar e Preparar Dados do Formulário
        $cliente = $_POST['cliente'] ?? 'Consumidor Final';
        $contato = $_POST['contato'] ?? null;
        $plataforma = $_POST['plataforma'] ?? null;
        $numero_pedido = $_POST['numero_pedido'] ?? null;
        $data_pedido = !empty($_POST['data_pedido']) ? $_POST['data_pedido'] : date('Y-m-d');
        $data_entrega = !empty($_POST['data_entrega']) ? $_POST['data_entrega'] : null;
        
        $metros = isset($_POST['metros']) ? floatval(str_replace(',', '.', $_POST['metros'])) : 0;
        $valor_metro = isset($_POST['valor_metro']) ? floatval(str_replace(',', '.', $_POST['valor_metro'])) : 0;
        $valor_final = isset($_POST['valor_final']) ? floatval(str_replace(',', '.', $_POST['valor_final'])) : 0;
        
        $observacoes = $_POST['obs'] ?? null;

        // 3. Inserir no Banco de Dados
        try {
            $sql = "INSERT INTO pedidos_dtf 
                        (cliente, contato, plataforma, numero_pedido, data_pedido, data_entrega, metros, valor_metro, valor_final, observacoes, arquivo_impressao, caminho_comprovante) 
                    VALUES 
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $cliente, 
                $contato, 
                $plataforma, 
                $numero_pedido, 
                $data_pedido, 
                $data_entrega, 
                $metros, 
                $valor_metro, 
                $valor_final, 
                $observacoes, 
                $nomeArquivoImpressao,
                $nomeArquivoComprovante
            ]);
            
            header('Location: index.php?rota=novo_dtf&status=success');

        } catch (Exception $e) {
            echo "Erro ao salvar pedido DTF: " . $e->getMessage();
            echo "<br><a href='index.php?rota=novo_dtf'>Voltar</a>";
        }
    }
}