<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/ImageOptimizer.php';
require_once __DIR__ . '/../models/Gabarito.php';

class GabaritoController {

    private function colunaExiste(string $tabela, string $coluna): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SHOW COLUMNS FROM {$tabela} LIKE '" . addslashes($coluna) . "'");
        return (bool) ($stmt && $stmt->fetch(PDO::FETCH_ASSOC));
    }

    private function garantirEstruturaGabaritos(): void {
        $pdo = Database::getConnection();
        if (!$this->colunaExiste('gabaritos', 'pedido_site')) {
            $pdo->exec("ALTER TABLE gabaritos ADD COLUMN pedido_site VARCHAR(100) NULL AFTER meio_pagamento");
        }
    }

    private function prepararModelosETamanhos(array $produtos): array {
        $modelos_unicos = [];
        $tamanhos_por_modelo = [];
        $chavesModelo = [];

        foreach ($produtos as $p) {
            $modeloRaw = trim((string)($p['nome'] ?? ''));
            if ($modeloRaw === '') {
                continue;
            }

            // Remove espacos repetidos e usa chave normalizada para evitar duplicados visuais.
            $modelo = preg_replace('/\s+/', ' ', $modeloRaw);
            $chaveModelo = mb_strtolower($modelo, 'UTF-8');

            if (!isset($chavesModelo[$chaveModelo])) {
                $chavesModelo[$chaveModelo] = $modelo;
                $modelos_unicos[] = $modelo;
                $tamanhos_por_modelo[$modelo] = [];
            }

            $modeloCanonico = $chavesModelo[$chaveModelo];
            $tamanho = trim((string)($p['tamanho'] ?? ''));

            if ($tamanho !== '' && !in_array($tamanho, $tamanhos_por_modelo[$modeloCanonico], true)) {
                $tamanhos_por_modelo[$modeloCanonico][] = $tamanho;
            }
        }

        return [$modelos_unicos, $tamanhos_por_modelo];
    }

    // 1. LISTAR GABARITOS (Agrupados)
    public function listar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->garantirEstruturaGabaritos();
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
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/lista_gabaritos.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // 2. NOVO GABARITO (Com Numeração Automática 01, 02...)
    public function novo() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->garantirEstruturaGabaritos();
        $pdo = Database::getConnection();

        $ficha = [];
        $itens_pedido = [];
        
        if (!isset($_GET['numero_pedido'])) {
            // Busca o maior número e soma +1
            $stmt = $pdo->query("SELECT MAX(CAST(numero_pedido AS UNSIGNED)) FROM gabaritos");
            $ultimo = $stmt->fetchColumn();
            $proximo = $ultimo ? (int)$ultimo + 1 : 1;
            $num = str_pad($proximo, 2, '0', STR_PAD_LEFT);
        } else {
            $num = $_GET['numero_pedido'];

            // Ao continuar adicionando itens no mesmo pedido, mostra a barra lateral com os itens ja salvos.
            $stmt = $pdo->prepare("SELECT id, modelo, cor, quantidade FROM gabaritos WHERE numero_pedido = ? ORDER BY id ASC");
            $stmt->execute([$num]);
            $itens_pedido = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $produtos = $pdo->query("SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        $usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        [$modelos_unicos, $tamanhos_por_modelo] = $this->prepararModelosETamanhos($produtos);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/novo_gabarito.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // 3. EDITAR GABARITO (Com Barra Lateral de Itens)
    public function editar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->garantirEstruturaGabaritos();
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
        
        [$modelos_unicos, $tamanhos_por_modelo] = $this->prepararModelosETamanhos($produtos);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/novo_gabarito.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // 4. SALVAR GABARITO
    public function salvar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->garantirEstruturaGabaritos();
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
        $valorUnit = $this->normalizarDecimal($_POST['valor_unit'] ?? null);
        $valorTotal = $this->normalizarDecimal($_POST['valor_total'] ?? null);
        $vendedorId = $this->normalizarInteiroNulo($_POST['vendedor_id'] ?? null);
        $dataPedido = $this->normalizarDataNula($_POST['data_pedido'] ?? null);
        $dataEntrega = $this->normalizarDataNula($_POST['data_entrega'] ?? null);
        $numeroPedido = trim((string)($_POST['numero_pedido'] ?? ''));
        $plataforma = trim((string)($_POST['plataforma'] ?? ''));
        $meioPagamento = trim((string)($_POST['meio_pagamento'] ?? ''));
        $pedidoSite = trim((string)($_POST['pedido_site'] ?? ''));
        $temPedidoSite = $this->colunaExiste('gabaritos', 'pedido_site');

        $dadosBase = [
            $_POST['cliente'],
            $numeroPedido,
            $plataforma,
            $_POST['contato'],
            $dataPedido,
            $_POST['modelo'],
            $_POST['cor'],
            trim($resumoTexto),
            $totalQtd,
            $valorUnit,
            $valorTotal,
            $dataEntrega,
            $imagemNome,
            $_POST['obs'] ?? '',
            $jsonGrade,
            $meioPagamento,
            $comprovanteNome,
            $vendedorId
        ];

        try {
            if ($id) {
                if ($temPedidoSite) {
                    $sql = "UPDATE gabaritos SET cliente=?, numero_pedido=?, plataforma=?, contato=?, data_pedido=?, modelo=?, cor=?, tamanho=?, quantidade=?, valor_unit=?, valor_total=?, data_entrega=?, imagem_mockup=?, observacoes=?, itens_json=?, meio_pagamento=?, pedido_site=?, caminho_comprovante=?, vendedor_id=? WHERE id=?";
                    $dados = $dadosBase;
                    array_splice($dados, 16, 0, [$pedidoSite]);
                } else {
                    $sql = "UPDATE gabaritos SET cliente=?, numero_pedido=?, plataforma=?, contato=?, data_pedido=?, modelo=?, cor=?, tamanho=?, quantidade=?, valor_unit=?, valor_total=?, data_entrega=?, imagem_mockup=?, observacoes=?, itens_json=?, meio_pagamento=?, caminho_comprovante=?, vendedor_id=? WHERE id=?";
                    $dados = $dadosBase;
                }

                $dados[] = $id;
                $pdo->prepare($sql)->execute($dados);
                $lastId = $id;
            } else {
                if ($temPedidoSite) {
                    $sql = "INSERT INTO gabaritos (cliente, numero_pedido, plataforma, contato, data_pedido, modelo, cor, tamanho, quantidade, valor_unit, valor_total, data_entrega, imagem_mockup, observacoes, itens_json, meio_pagamento, pedido_site, caminho_comprovante, vendedor_id, data_criacao, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Mockup')";
                    $dados = $dadosBase;
                    array_splice($dados, 16, 0, [$pedidoSite]);
                } else {
                    $sql = "INSERT INTO gabaritos (cliente, numero_pedido, plataforma, contato, data_pedido, modelo, cor, tamanho, quantidade, valor_unit, valor_total, data_entrega, imagem_mockup, observacoes, itens_json, meio_pagamento, caminho_comprovante, vendedor_id, data_criacao, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Mockup')";
                    $dados = $dadosBase;
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($dados);
                $lastId = $pdo->lastInsertId();
            }

            // Mantem o canal de venda igual em todas as folhas do mesmo pedido.
            if ($numeroPedido !== '' && $plataforma !== '') {
                $stmt = $pdo->prepare("UPDATE gabaritos SET plataforma = ? WHERE numero_pedido = ?");
                $stmt->execute([$plataforma, $numeroPedido]);
            }

            // Mantem o meio de pagamento igual em todas as folhas do mesmo pedido.
            if ($numeroPedido !== '') {
                if ($temPedidoSite) {
                    $stmt = $pdo->prepare("UPDATE gabaritos SET meio_pagamento = ?, pedido_site = ? WHERE numero_pedido = ?");
                    $stmt->execute([$meioPagamento, $pedidoSite, $numeroPedido]);
                } else {
                    $stmt = $pdo->prepare("UPDATE gabaritos SET meio_pagamento = ? WHERE numero_pedido = ?");
                    $stmt->execute([$meioPagamento, $numeroPedido]);
                }
            }
        } catch (Exception $e) {
            $_SESSION['erro_salvar_gabarito'] = $e->getMessage();
            $paramsErro = http_build_query([
                'cliente' => $_POST['cliente'],
                'contato' => $_POST['contato'],
                'numero_pedido' => $numeroPedido,
                'plataforma' => $plataforma,
                'meio_pagamento' => $meioPagamento,
                'pedido_site' => $pedidoSite,
                'data_pedido' => $_POST['data_pedido'] ?? '',
                'data_entrega' => $_POST['data_entrega'] ?? '',
                'msg' => 'erro_salvar'
            ]);
            header("Location: index.php?rota=novo_gabarito&$paramsErro");
            exit;
        }

        if ($acao === 'continuar') {
            $params = http_build_query([
                'cliente' => $_POST['cliente'],
                'contato' => $_POST['contato'],
                'numero_pedido' => $numeroPedido,
                'plataforma' => $plataforma,
                'meio_pagamento' => $meioPagamento,
                'pedido_site' => $pedidoSite,
                'data_pedido' => $_POST['data_pedido'],
                'data_entrega' => $_POST['data_entrega'],
                'msg' => 'item_adicionado'
            ]);
            header("Location: index.php?rota=novo_gabarito&$params");
            exit;
        } else {
            header("Location: index.php?rota=imprimir_gabarito&id=$lastId");
            exit;
        }
    }

    private function normalizarDecimal($valor): string {
        $valor = trim((string)($valor ?? ''));

        if ($valor === '') {
            return '0.00';
        }

        $valor = str_replace(' ', '', $valor);

        if (strpos($valor, ',') !== false && strpos($valor, '.') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } else {
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? number_format((float)$valor, 2, '.', '') : '0.00';
    }

    private function normalizarInteiroNulo($valor): ?int {
        $valor = trim((string)($valor ?? ''));
        return ($valor === '' || !is_numeric($valor)) ? null : (int)$valor;
    }

    private function normalizarDataNula($valor): ?string {
        $valor = trim((string)($valor ?? ''));
        return $valor === '' ? null : $valor;
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
        $this->garantirEstruturaGabaritos();
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

    // 6. EXCLUIR APENAS O ITEM SELECIONADO
    public function excluir() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (($_SESSION['user_nivel'] ?? '') !== 'admin') {
            header('Location: index.php?rota=listar_gabaritos');
            exit;
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $pdo = Database::getConnection();
            $pdo->prepare("DELETE FROM gabaritos WHERE id = ?")->execute([$id]);
        }

        header('Location: index.php?rota=listar_gabaritos');
        exit;
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