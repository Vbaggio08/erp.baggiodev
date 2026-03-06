<?php
require_once __DIR__ . '/../config/database.php';

class ServicoController {
    
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        $servicos = $pdo->query("SELECT * FROM servicos_os ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/servicos/lista.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    public function nova() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['lista_os'])) $_SESSION['lista_os'] = [];

        $pdo = Database::getConnection();
        $empresas = $pdo->query("SELECT * FROM empresas WHERE ativo = 1 ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        $prestadores = $pdo->query("SELECT * FROM fornecedores ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/servicos/nova.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // AQUI É IMPORTANTE: O CÁLCULO DO VALOR
    public function adicionarItem() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $qtd = (float) $_POST['qtd'];
        // Troca vírgula por ponto para o cálculo funcionar
        $valor = (float) str_replace(',', '.', $_POST['valor']);
        
        $item = [
            'descricao' => $_POST['descricao'],
            'qtd'       => $qtd,
            'valor'     => $valor,          // Salva o valor unitário
            'total'     => $qtd * $valor    // Salva o total da linha
        ];
        
        $_SESSION['lista_os'][] = $item;
        header('Location: index.php?rota=nova_os');
    }

    public function removerItem() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $index = $_POST['index'] ?? null;
        if ($index !== null && isset($_SESSION['lista_os'][$index])) {
            unset($_SESSION['lista_os'][$index]);
            $_SESSION['lista_os'] = array_values($_SESSION['lista_os']);
        }
        header('Location: index.php?rota=nova_os');
    }

    // IMPORTANTE: ROTA PARA LIMPAR LISTA (Corrige seus erros atuais)
    public function limparLista() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['lista_os'] = []; // Zera a lista
        header('Location: index.php?rota=nova_os');
    }

    public function salvar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        
        $empresa = $_POST['empresa'];
        $prestador = $_POST['prestador'];
        $dataEntrega = $_POST['data_entrega'];
        $obs = $_POST['obs'];

        $itens = $_SESSION['lista_os'] ?? [];
        $valorTotal = 0;
        $descricaoResumo = "";

        foreach($itens as $i) {
            // Garante que existe total, senão usa 0
            $totalItem = $i['total'] ?? 0;
            $valorTotal += $totalItem;
            $descricaoResumo .= "{$i['qtd']}x {$i['descricao']} | ";
        }
        
        $itensJson = json_encode($itens);

        $sql = "INSERT INTO servicos_os (empresa, prestador, descricao, status, data_abertura, data_conclusao, valor_total, itens_json) 
                VALUES (?, ?, ?, 'Pendente', NOW(), ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$empresa, $prestador, $obs . " [" . substr($descricaoResumo, 0, 50) . "...]", $dataEntrega, $valorTotal, $itensJson]);
        
        $_SESSION['lista_os'] = [];
        header('Location: index.php?rota=servicos');
    }
    // ... funções anteriores ...

    // NOVA FUNÇÃO: MUDAR STATUS DA O.S.
    public function mudarStatus() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $id = $_GET['id'] ?? null;
        $status = $_GET['status'] ?? null;
        
        if ($id && $status) {
            $pdo = Database::getConnection();
            
            // Se for concluído, atualiza também a data de conclusão para HOJE
            if ($status == 'Concluido') {
                $sql = "UPDATE servicos_os SET status = ?, data_conclusao = NOW() WHERE id = ?";
            } else {
                $sql = "UPDATE servicos_os SET status = ? WHERE id = ?";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$status, $id]);
        }
        
        header('Location: index.php?rota=servicos');
    }

    // NOVA FUNÇÃO: EXCLUIR O.S. (Caso queira apagar)
    public function excluir() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Só admin pode excluir
        if (($_SESSION['user_nivel'] ?? '') !== 'admin') {
            header('Location: index.php?rota=servicos');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if ($id) {
            $pdo = Database::getConnection();
            $pdo->prepare("DELETE FROM servicos_os WHERE id = ?")->execute([$id]);
        }
        header('Location: index.php?rota=servicos');
    }

} // FIM DA CLASSE
