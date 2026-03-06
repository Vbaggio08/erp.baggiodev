<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Gabarito.php';

class GabaritoController {

    // 1. LISTAR GABARITOS (Agrupados)
    public function listar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        
        $sql = "SELECT 
                    g.*, 
                    u.nome as vendedor_nome,
                    GROUP_CONCAT(g.modelo SEPARATOR ' + ') as modelos_agrupados, 
                    GROUP_CONCAT(g.cor SEPARATOR ' / ') as cores_agrupadas,
                    SUM(g.quantidade) as total_pecas_pedido 
                FROM gabaritos g
                LEFT JOIN usuarios u ON g.vendedor_id = u.id
                GROUP BY g.numero_pedido 
                ORDER BY g.id DESC LIMIT 50";
        
        $fichas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../views/producao/lista_gabaritos.php';
    }

    // 2. NOVO GABARITO (Com Numeração Automática 01, 02...)
    public function novo() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        
        if (!isset($_GET['numero_pedido'])) {
            // Busca o maior número e soma +1
            $stmt = $pdo->query("SELECT MAX(CAST(numero_pedido AS UNSIGNED)) FROM gabaritos");
            $ultimo = $stmt->fetchColumn();
            $proximo = $ultimo ? (int)$ultimo + 1 : 1;
            $num = str_pad($proximo, 2, '0', STR_PAD_LEFT);
        } else {
            $num = $_GET['numero_pedido'];
        }

        $produtos = $pdo->query("SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        $usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../views/producao/novo_gabarito.php';
    }

    // 3. EDITAR GABARITO (Com Barra Lateral de Itens)
    public function editar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        $id = $_GET['id'];
        
        $stmt = $pdo->prepare("SELECT * FROM gabaritos WHERE id = ?");
        $stmt->execute([$id]);
        $ficha = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $itens_pedido = [];
        if (!empty($ficha['numero_pedido'])) {
            $num = $ficha['numero_pedido'];
            $stmt = $pdo->prepare("SELECT id, modelo, cor, quantidade FROM gabaritos WHERE numero_pedido = ? ORDER BY id ASC");
            $stmt->execute([$num]);
            $itens_pedido = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $produtos = $pdo->query("SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        $usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../views/producao/novo_gabarito.php';
    }

    // 4. SALVAR GABARITO
    public function salvar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();

        $id = $_POST['id'] ?? null;
        $acao = $_POST['acao'] ?? 'finalizar'; 
        $imagemNome = $_POST['imagem_atual'] ?? null; 
        $comprovanteNome = $_POST['comprovante_atual'] ?? null;

        // Upload do Mockup
        if (isset($_FILES['mockup']) && $_FILES['mockup']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['mockup']['name'], PATHINFO_EXTENSION));
            $nomeImg = uniqid() . '.' . $ext;
            $destination = __DIR__ . '/../../assets/uploads/' . $nomeImg;

            if ($this->otimizarImagem($_FILES['mockup']['tmp_name'], $destination, $ext)) {
                $imagemNome = $nomeImg;
            } else {
                if (move_uploaded_file($_FILES['mockup']['tmp_name'], $destination)) {
                    $imagemNome = $nomeImg;
                }
            }
        }

        // Upload do Comprovante de Pagamento
        if (isset($_FILES['caminho_comprovante']) && $_FILES['caminho_comprovante']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['caminho_comprovante']['name'], PATHINFO_EXTENSION));
            $nomeComp = uniqid() . '_comp.' . $ext;
            $destination = __DIR__ . '/../../assets/uploads/comprovantes/' . $nomeComp;
            
            if ($this->otimizarImagem($_FILES['caminho_comprovante']['tmp_name'], $destination, $ext)) {
                $comprovanteNome = $nomeComp;
            } else {
                if (move_uploaded_file($_FILES['caminho_comprovante']['tmp_name'], $destination)) {
                    $comprovanteNome = $nomeComp;
                }
            }
        }

        $grade = $_POST['grade'] ?? [];
        $gradeFinal = [];
        $totalQtd = 0;
        $resumoTexto = ""; 

        foreach($grade as $tam => $qtd) {
            if($qtd > 0) {
                $gradeFinal[$tam] = $qtd;
                $totalQtd += $qtd;
                $resumoTexto .= "$tam:$qtd ";
            }
        }
        if($totalQtd == 0) {
            $totalQtd = $_POST['quantidade'] ?? 0;
            $resumoTexto = "UNICA";
        }
        $jsonGrade = json_encode($gradeFinal);

        $dados = [
            $_POST['cliente'],
            $_POST['numero_pedido'],
            $_POST['plataforma'],
            $_POST['contato'],
            $_POST['data_pedido'],
            $_POST['modelo'],
            $_POST['cor'],
            trim($resumoTexto),
            $totalQtd,
            str_replace(',', '.', $_POST['valor_unit']),
            str_replace(',', '.', $_POST['valor_total']),
            $_POST['data_entrega'],
            $imagemNome,
            $_POST['obs'] ?? '',
            $jsonGrade,
            $_POST['meio_pagamento'] ?? '',
            $comprovanteNome,
            $_POST['vendedor_id'] ?? null
        ];

        if ($id) {
            $sql = "UPDATE gabaritos SET cliente=?, numero_pedido=?, plataforma=?, contato=?, data_pedido=?, modelo=?, cor=?, tamanho=?, quantidade=?, valor_unit=?, valor_total=?, data_entrega=?, imagem_mockup=?, observacoes=?, itens_json=?, meio_pagamento=?, caminho_comprovante=?, vendedor_id=? WHERE id=?";
            $dados[] = $id;
            $pdo->prepare($sql)->execute($dados);
            $lastId = $id;
        } else {
            $sql = "INSERT INTO gabaritos (cliente, numero_pedido, plataforma, contato, data_pedido, modelo, cor, tamanho, quantidade, valor_unit, valor_total, data_entrega, imagem_mockup, observacoes, itens_json, meio_pagamento, caminho_comprovante, vendedor_id, data_criacao, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Mockup')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($dados);
            $lastId = $pdo->lastInsertId();
        }

        if ($acao === 'continuar') {
            $params = http_build_query([
                'cliente' => $_POST['cliente'],
                'contato' => $_POST['contato'],
                'numero_pedido' => $_POST['numero_pedido'],
                'plataforma' => $_POST['plataforma'],
                'data_pedido' => $_POST['data_pedido'],
                'data_entrega' => $_POST['data_entrega'],
                'msg' => 'item_adicionado'
            ]);
            header("Location: index.php?rota=novo_gabarito&$params");
        } else {
            header("Location: index.php?rota=imprimir_gabarito&id=$lastId");
        }
    }

    /**
     * Otimiza uma imagem (JPG, PNG, GIF) redimensionando e comprimindo.
     * @param string $source O caminho do arquivo de origem.
     * @param string $destination O caminho para salvar o arquivo otimizado.
     * @param string $ext A extensão do arquivo.
     * @param int $maxWidth A largura máxima permitida.
     * @param int $maxHeight A altura máxima permitida.
     * @param int $quality A qualidade da compressão (0-100 para JPG).
     * @return bool Retorna true em sucesso, false em falha.
     */
    private function otimizarImagem($source, $destination, $ext, $maxWidth = 1600, $maxHeight = 1600, $quality = 85) {
        if (!function_exists('imagecreatefromjpeg')) {
            return false; // Extensão GD não está disponível
        }

        list($width, $height) = getimagesize($source);
        if ($width === null) return false;

        $image = null;
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'png':
                $image = imagecreatefrompng($source);
                break;
            case 'gif':
                $image = imagecreatefromgif($source);
                break;
            default:
                return false; // Tipo não suportado
        }

        if ($width <= $maxWidth && $height <= $maxHeight) {
            $newWidth = $width;
            $newHeight = $height;
        } else {
            $ratio = $width / $height;
            if ($maxWidth / $maxHeight > $ratio) {
                $newWidth = $maxHeight * $ratio;
                $newHeight = $maxHeight;
            } else {
                $newHeight = $maxWidth / $ratio;
                $newWidth = $maxWidth;
            }
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        if ($ext === 'png') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $success = false;
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $success = imagejpeg($newImage, $destination, $quality);
                break;
            case 'png':
                $pngQuality = floor(9 * $quality / 100);
                $success = imagepng($newImage, $destination, $pngQuality);
                break;
            case 'gif':
                $success = imagegif($newImage, $destination);
                break;
        }

        imagedestroy($image);
        imagedestroy($newImage);

        return $success;
    }


    // 5. IMPRIMIR (A que estava faltando!)
    public function imprimir() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Database::getConnection();
        $id = $_GET['id'];
        
        $stmt = $pdo->prepare("SELECT numero_pedido FROM gabaritos WHERE id = ?");
        $stmt->execute([$id]);
        $ped = $stmt->fetchColumn();

        $query = "
            SELECT g.*, u.nome as vendedor_nome 
            FROM gabaritos g
            LEFT JOIN usuarios u ON g.vendedor_id = u.id
        ";

        if (!empty($ped)) {
            $stmt = $pdo->prepare("$query WHERE g.numero_pedido = ? ORDER BY g.id ASC");
            $stmt->execute([$ped]);
            $lista_fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare("$query WHERE g.id = ?");
            $stmt->execute([$id]);
            $lista_fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        require __DIR__ . '/../views/producao/imprimir_gabarito.php';
    }

    // 6. EXCLUIR PEDIDO COMPLETO
    public function excluir() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (($_SESSION['user_nivel'] ?? '') !== 'admin') {
            header('Location: index.php?rota=listar_gabaritos');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if ($id) {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT numero_pedido FROM gabaritos WHERE id = ?");
            $stmt->execute([$id]);
            $numeroPedido = $stmt->fetchColumn();

            if ($numeroPedido) {
                $pdo->prepare("DELETE FROM gabaritos WHERE numero_pedido = ?")->execute([$numeroPedido]);
            } else {
                $pdo->prepare("DELETE FROM gabaritos WHERE id = ?")->execute([$id]);
            }
        }
        header('Location: index.php?rota=listar_gabaritos');
    }

    // 7. MUDAR STATUS
    public function mudarStatus() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id = $_GET['id'];
        $status = $_GET['status'];
        Gabarito::atualizarStatus($id, $status);
        header('Location: index.php?rota=listar_gabaritos');
    }
}