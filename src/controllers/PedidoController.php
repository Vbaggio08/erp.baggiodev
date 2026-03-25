<?php
require_once __DIR__ . '/../config/database.php';

class PedidoController {
    private function colunaExiste(string $tabela, string $coluna): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SHOW COLUMNS FROM {$tabela} LIKE '" . addslashes($coluna) . "'");
        return (bool) ($stmt && $stmt->fetch(PDO::FETCH_ASSOC));
    }

    private function garantirEstruturaPedidosDtf(): void {
        $pdo = Database::getConnection();

        if (!$this->colunaExiste('pedidos_dtf', 'vendedor_id')) {
            $pdo->exec("ALTER TABLE pedidos_dtf ADD COLUMN vendedor_id INT NULL AFTER caminho_comprovante");
        }

        if (!$this->colunaExiste('pedidos_dtf', 'meio_pagamento')) {
            $pdo->exec("ALTER TABLE pedidos_dtf ADD COLUMN meio_pagamento VARCHAR(50) NULL AFTER observacoes");
        }

        if (!$this->colunaExiste('pedidos_dtf', 'status')) {
            $pdo->exec("ALTER TABLE pedidos_dtf ADD COLUMN status VARCHAR(50) DEFAULT 'Mockup' AFTER data_criacao");
        }
    }
    
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

        $this->garantirEstruturaPedidosDtf();

        $pdo = Database::getConnection();
        $usuarios = $pdo->query("SELECT id, nome FROM usuarios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

        $ficha = [];
        if (!empty($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM pedidos_dtf WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $ficha = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        if (!isset($_GET['numero_pedido']) && empty($ficha['numero_pedido'])) {
            $stmt = $pdo->query("SELECT MAX(CAST(numero_pedido AS UNSIGNED)) FROM pedidos_dtf");
            $ultimo = $stmt->fetchColumn();
            $proximo = $ultimo ? (int) $ultimo + 1 : 1;
            $num = str_pad($proximo, 2, '0', STR_PAD_LEFT);
        } else {
            $num = $_GET['numero_pedido'] ?? ($ficha['numero_pedido'] ?? '01');
        }

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

        $this->garantirEstruturaPedidosDtf();

        $pdo = Database::getConnection();
        $id = $_POST['id'] ?? null;

        // 1. Tratar Upload de Arquivos
        $nomeArquivoImpressao = $_POST['arquivo_impressao_atual'] ?? null;
        if (isset($_FILES['arquivo_impressao']) && $_FILES['arquivo_impressao']['error'] == 0) {
            $uploadDir = __DIR__ . '/../../assets/uploads/';
            $ext = pathinfo($_FILES['arquivo_impressao']['name'], PATHINFO_EXTENSION);
            $nomeArquivoImpressao = uniqid() . '.' . $ext;
            
            if (!move_uploaded_file($_FILES['arquivo_impressao']['tmp_name'], $uploadDir . $nomeArquivoImpressao)) {
                die("Erro ao fazer upload do arquivo de impressão.");
            }
        }

        $nomeArquivoComprovante = $_POST['comprovante_atual'] ?? null;
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
        $meioPagamento = $_POST['meio_pagamento'] ?? null;
        $vendedorId = !empty($_POST['vendedor_id']) ? (int) $_POST['vendedor_id'] : null;

        // 3. Inserir no Banco de Dados
        try {
            $dados = [
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
                $meioPagamento,
                $nomeArquivoImpressao,
                $nomeArquivoComprovante,
                $vendedorId
            ];

            if ($id) {
                $sql = "UPDATE pedidos_dtf
                           SET cliente = ?, contato = ?, plataforma = ?, numero_pedido = ?, data_pedido = ?,
                               data_entrega = ?, metros = ?, valor_metro = ?, valor_final = ?, observacoes = ?,
                               meio_pagamento = ?, arquivo_impressao = ?, caminho_comprovante = ?, vendedor_id = ?
                         WHERE id = ?";
                $dados[] = $id;
            } else {
                $sql = "INSERT INTO pedidos_dtf
                            (cliente, contato, plataforma, numero_pedido, data_pedido, data_entrega, metros, valor_metro, valor_final, observacoes, meio_pagamento, arquivo_impressao, caminho_comprovante, vendedor_id)
                        VALUES
                            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($dados);
            
            header('Location: index.php?rota=ver_producao_dtf');
            exit;

        } catch (Exception $e) {
            echo "Erro ao salvar pedido DTF: " . $e->getMessage();
            echo "<br><a href='index.php?rota=novo_dtf'>Voltar</a>";
        }
    }

    // Lista os pedidos DTF
    public function listar_dtf() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?rota=login');
            exit;
        }

        $this->garantirEstruturaPedidosDtf();

        $pdo = Database::getConnection();
        try {
            $pedidosDtf = $pdo->query(
                "SELECT d.*, u.nome AS vendedor_nome
                   FROM pedidos_dtf d
              LEFT JOIN usuarios u ON d.vendedor_id = u.id
               ORDER BY d.data_pedido DESC, d.id DESC"
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $pedidosDtf = [];
        }

        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/lista_dtf.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    public function excluir_dtf() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?rota=login');
            exit;
        }

        if (($_SESSION['user_nivel'] ?? '') !== 'admin') {
            header('Location: index.php?rota=ver_producao_dtf');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!empty($id)) {
            $stmt = Database::getConnection()->prepare("DELETE FROM pedidos_dtf WHERE id = ?");
            $stmt->execute([$id]);
        }

        header('Location: index.php?rota=ver_producao_dtf');
        exit;
    }

    public function mudar_status_dtf() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?rota=login');
            exit;
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $status = $_GET['status'] ?? null;

        if ($id && $status) {
            $this->garantirEstruturaPedidosDtf();
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE pedidos_dtf SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        }

        header('Location: index.php?rota=ver_producao_dtf');
        exit;
    }
}