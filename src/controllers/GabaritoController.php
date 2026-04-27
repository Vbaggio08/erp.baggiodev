<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/ImageOptimizer.php';
require_once __DIR__ . '/../models/Gabarito.php';

class GabaritoController {

    private const ALLOWED_SCHEMA_TABLES = [
        'gabaritos' => ['pedido_site', 'status_pedido', 'reservado_em', 'finalizado_em'],
        'modelos_catalogo' => ['tamanhos_json', 'origem', 'ativo', 'criado_em', 'atualizado_em']
    ];

    private const TAMANHOS_PADRAO = ['2', '4', '6', '8', '10', '12', '14', '16', 'PP', 'P', 'M', 'G', 'GG', 'G1', 'G2', 'G3', 'UNIC'];

    private function tabelaExiste(string $tabela): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
        $stmt->execute([$tabela]);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    private function colunaExiste(string $tabela, string $coluna): bool {
        if (!isset(self::ALLOWED_SCHEMA_TABLES[$tabela]) || !in_array($coluna, self::ALLOWED_SCHEMA_TABLES[$tabela], true)) {
            return false;
        }

        $pdo = Database::getConnection();
        $sql = 'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tabela, $coluna]);
        return ((int)$stmt->fetchColumn() > 0);
    }

    private function garantirEstruturaGabaritos(): void {
        $pdo = Database::getConnection();
        if (!$this->colunaExiste('gabaritos', 'pedido_site')) {
            $pdo->exec("ALTER TABLE gabaritos ADD COLUMN pedido_site VARCHAR(100) NULL AFTER meio_pagamento");
        }

        if (!$this->colunaExiste('gabaritos', 'status_pedido')) {
            $pdo->exec("ALTER TABLE gabaritos ADD COLUMN status_pedido VARCHAR(20) NOT NULL DEFAULT 'finalizado' AFTER status");
        }

        if (!$this->colunaExiste('gabaritos', 'reservado_em')) {
            $pdo->exec("ALTER TABLE gabaritos ADD COLUMN reservado_em DATETIME NULL AFTER status_pedido");
        }

        if (!$this->colunaExiste('gabaritos', 'finalizado_em')) {
            $pdo->exec("ALTER TABLE gabaritos ADD COLUMN finalizado_em DATETIME NULL AFTER reservado_em");
        }
    }

    private function garantirTabelaSequenciasPedidos(): void {
        $pdo = Database::getConnection();
        if (!$this->tabelaExiste('sequencias_pedidos')) {
            $pdo->exec("CREATE TABLE sequencias_pedidos (tipo VARCHAR(50) NOT NULL PRIMARY KEY, proximo_numero INT NOT NULL DEFAULT 1, atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
    }

    private function garantirTabelaModelosCatalogo(): void {
        $pdo = Database::getConnection();
        if (!$this->tabelaExiste('modelos_catalogo')) {
            $pdo->exec("CREATE TABLE modelos_catalogo (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(150) NOT NULL, tamanhos_json TEXT DEFAULT NULL, origem VARCHAR(20) NOT NULL DEFAULT 'manual', ativo TINYINT(1) NOT NULL DEFAULT 1, criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uk_modelos_catalogo_nome (nome)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
    }

    private function garantirEstruturasPedidos(): void {
        $this->garantirEstruturaGabaritos();
        $this->garantirTabelaSequenciasPedidos();
        $this->garantirTabelaModelosCatalogo();
    }

    private function obterProximoNumeroBase(PDO $pdo, string $tipo): int {
        $origem = $tipo === 'gabarito' ? 'gabaritos' : 'pedidos_dtf';
        $stmt = $pdo->query("SELECT COALESCE(MAX(CAST(numero_pedido AS UNSIGNED)), 0) + 1 FROM {$origem}");
        $numero = (int) $stmt->fetchColumn();
        return $numero > 0 ? $numero : 1;
    }

    private function reservarNumeroSequencial(PDO $pdo, string $tipo): string {
        $numeroBase = $this->obterProximoNumeroBase($pdo, $tipo);
        $stmt = $pdo->prepare("INSERT IGNORE INTO sequencias_pedidos (tipo, proximo_numero) VALUES (?, ?)");
        $stmt->execute([$tipo, $numeroBase]);

        $stmt = $pdo->prepare("SELECT proximo_numero FROM sequencias_pedidos WHERE tipo = ? FOR UPDATE");
        $stmt->execute([$tipo]);
        $numero = (int) $stmt->fetchColumn();

        if ($numero <= 0) {
            $numero = $numeroBase;
        }

        $stmt = $pdo->prepare("UPDATE sequencias_pedidos SET proximo_numero = ? WHERE tipo = ?");
        $stmt->execute([$numero + 1, $tipo]);

        return str_pad((string) $numero, 2, '0', STR_PAD_LEFT);
    }

    private function sincronizarCatalogoModelos(PDO $pdo): void {
        $this->garantirTabelaModelosCatalogo();

        $produtos = $pdo->query("SELECT nome, tamanho FROM produtos WHERE ativo = 1 ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        if (!$produtos) {
            return;
        }

        [$modelos, $tamanhosPorModelo] = $this->prepararModelosETamanhos($produtos);
        $insert = $pdo->prepare("INSERT IGNORE INTO modelos_catalogo (nome, tamanhos_json, origem, ativo) VALUES (?, ?, 'produto', 1)");
        $update = $pdo->prepare("UPDATE modelos_catalogo SET tamanhos_json = ?, ativo = 1 WHERE nome = ? AND (tamanhos_json IS NULL OR tamanhos_json = '' OR tamanhos_json = '[]')");

        foreach ($modelos as $modelo) {
            $tamanhos = $tamanhosPorModelo[$modelo] ?? self::TAMANHOS_PADRAO;
            $json = json_encode(array_values($tamanhos));
            $insert->execute([$modelo, $json]);
            $update->execute([$json, $modelo]);
        }
    }

    private function carregarModelosDisponiveis(PDO $pdo): array {
        $this->sincronizarCatalogoModelos($pdo);
        $rows = $pdo->query("SELECT nome, tamanhos_json FROM modelos_catalogo WHERE ativo = 1 ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            $produtos = $pdo->query("SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
            return $this->prepararModelosETamanhos($produtos);
        }

        $modelos = [];
        $tamanhosPorModelo = [];

        foreach ($rows as $row) {
            $nome = trim((string) ($row['nome'] ?? ''));
            if ($nome === '') {
                continue;
            }

            $modelos[] = $nome;
            $tamanhos = json_decode((string) ($row['tamanhos_json'] ?? '[]'), true);
            if (!is_array($tamanhos) || !$tamanhos) {
                $tamanhos = self::TAMANHOS_PADRAO;
            }
            $tamanhosPorModelo[$nome] = array_values(array_unique($tamanhos));
        }

        return [$modelos, $tamanhosPorModelo];
    }

    public function iniciarRascunho() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $this->garantirEstruturasPedidos();
        $pdo = Database::getConnection();
        $temPedidoSite = $this->colunaExiste('gabaritos', 'pedido_site');

        try {
            $pdo->beginTransaction();
            $numeroPedido = $this->reservarNumeroSequencial($pdo, 'gabarito');

            if ($temPedidoSite) {
                $sql = "INSERT INTO gabaritos (cliente, numero_pedido, plataforma, contato, data_pedido, modelo, cor, tamanho, quantidade, valor_unit, valor_total, data_entrega, imagem_mockup, observacoes, itens_json, meio_pagamento, pedido_site, caminho_comprovante, vendedor_id, data_criacao, status, status_pedido, reservado_em) VALUES ('Rascunho', ?, '', '', CURDATE(), '', '', '', 0, 0, 0, NULL, NULL, '', '{}', '', '', NULL, NULL, NOW(), 'Mockup', 'rascunho', NOW())";
            } else {
                $sql = "INSERT INTO gabaritos (cliente, numero_pedido, plataforma, contato, data_pedido, modelo, cor, tamanho, quantidade, valor_unit, valor_total, data_entrega, imagem_mockup, observacoes, itens_json, meio_pagamento, caminho_comprovante, vendedor_id, data_criacao, status, status_pedido, reservado_em) VALUES ('Rascunho', ?, '', '', CURDATE(), '', '', '', 0, 0, 0, NULL, NULL, '', '{}', '', NULL, NULL, NOW(), 'Mockup', 'rascunho', NOW())";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$numeroPedido]);
            $id = (int) $pdo->lastInsertId();
            $pdo->commit();

            header("Location: index.php?rota=novo_gabarito&id={$id}");
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['erro_salvar_gabarito'] = 'Nao foi possivel reservar um novo pedido.';
            header('Location: index.php?rota=listar_gabaritos');
            exit;
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
        $this->garantirEstruturasPedidos();
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
        $this->garantirEstruturasPedidos();
        $pdo = Database::getConnection();

        $ficha = [];
        $itens_pedido = [];

        if (!empty($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM gabaritos WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $ficha = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $num = $ficha['numero_pedido'] ?? '';
        } elseif (!isset($_GET['numero_pedido'])) {
            header('Location: index.php?rota=iniciar_gabarito');
            exit;
        } else {
            $num = $_GET['numero_pedido'];
        }

        if ($num !== '') {
            $stmt = $pdo->prepare("SELECT id, modelo, cor, quantidade FROM gabaritos WHERE numero_pedido = ? ORDER BY id ASC");
            $stmt->execute([$num]);
            $itens_pedido = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

        [$modelos_unicos, $tamanhos_por_modelo] = $this->carregarModelosDisponiveis($pdo);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/novo_gabarito.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // 3. EDITAR GABARITO (Com Barra Lateral de Itens)
    public function editar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->garantirEstruturasPedidos();
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
        
        $usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

        [$modelos_unicos, $tamanhos_por_modelo] = $this->carregarModelosDisponiveis($pdo);
        
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/producao/novo_gabarito.php';
        require __DIR__ . '/../views/geral/footer.php';
    }

    // 4. SALVAR GABARITO
    public function salvar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->garantirEstruturasPedidos();
        $pdo = Database::getConnection();

        $id = $_POST['id'] ?? null;
        $acao = $_POST['acao'] ?? 'finalizar'; 
        $statusPedido = $acao === 'finalizar' ? 'finalizado' : 'rascunho';
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
            $statusPedido,
            $comprovanteNome,
            $vendedorId
        ];

        try {
            if ($id) {
                if ($temPedidoSite) {
                    $sql = "UPDATE gabaritos SET cliente=?, numero_pedido=?, plataforma=?, contato=?, data_pedido=?, modelo=?, cor=?, tamanho=?, quantidade=?, valor_unit=?, valor_total=?, data_entrega=?, imagem_mockup=?, observacoes=?, itens_json=?, meio_pagamento=?, pedido_site=?, status_pedido=?, caminho_comprovante=?, vendedor_id=? WHERE id=?";
                    $dados = $dadosBase;
                    array_splice($dados, 16, 0, [$pedidoSite]);
                } else {
                    $sql = "UPDATE gabaritos SET cliente=?, numero_pedido=?, plataforma=?, contato=?, data_pedido=?, modelo=?, cor=?, tamanho=?, quantidade=?, valor_unit=?, valor_total=?, data_entrega=?, imagem_mockup=?, observacoes=?, itens_json=?, meio_pagamento=?, status_pedido=?, caminho_comprovante=?, vendedor_id=? WHERE id=?";
                    $dados = $dadosBase;
                }

                $dados[] = $id;
                $pdo->prepare($sql)->execute($dados);
                $lastId = $id;
            } else {
                if ($temPedidoSite) {
                    $sql = "INSERT INTO gabaritos (cliente, numero_pedido, plataforma, contato, data_pedido, modelo, cor, tamanho, quantidade, valor_unit, valor_total, data_entrega, imagem_mockup, observacoes, itens_json, meio_pagamento, pedido_site, status_pedido, caminho_comprovante, vendedor_id, data_criacao, reservado_em, finalizado_em, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, 'Mockup')";
                    $dados = $dadosBase;
                    array_splice($dados, 16, 0, [$pedidoSite]);
                } else {
                    $sql = "INSERT INTO gabaritos (cliente, numero_pedido, plataforma, contato, data_pedido, modelo, cor, tamanho, quantidade, valor_unit, valor_total, data_entrega, imagem_mockup, observacoes, itens_json, meio_pagamento, status_pedido, caminho_comprovante, vendedor_id, data_criacao, reservado_em, finalizado_em, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, 'Mockup')";
                    $dados = $dadosBase;
                }

                $dados[] = $statusPedido === 'finalizado' ? date('Y-m-d H:i:s') : null;

                $stmt = $pdo->prepare($sql);
                $stmt->execute($dados);
                $lastId = $pdo->lastInsertId();
            }

            // Mantem o canal de venda igual em todas as folhas do mesmo pedido.
            if ($numeroPedido !== '' && $plataforma !== '') {
                $stmt = $pdo->prepare("UPDATE gabaritos SET plataforma = ? WHERE numero_pedido = ?");
                $stmt->execute([$plataforma, $numeroPedido]);
            }

            if ($numeroPedido !== '') {
                $stmt = $pdo->prepare("UPDATE gabaritos SET status_pedido = ?, finalizado_em = CASE WHEN ? = 'finalizado' THEN NOW() ELSE finalizado_em END WHERE numero_pedido = ?");
                $stmt->execute([$statusPedido, $statusPedido, $numeroPedido]);
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

    public function adicionarModelo() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['sucesso' => false, 'erro' => 'Metodo nao permitido']);
            exit;
        }

        $this->garantirEstruturasPedidos();
        $pdo = Database::getConnection();

        $nomeModelo = trim((string) ($_POST['nome_modelo'] ?? ''));
        $nomeModelo = preg_replace('/\s+/', ' ', $nomeModelo);

        if ($nomeModelo === '') {
            http_response_code(422);
            echo json_encode(['sucesso' => false, 'erro' => 'Informe um nome de modelo valido']);
            exit;
        }

        $tamanhos = self::TAMANHOS_PADRAO;
        $jsonTamanhos = json_encode($tamanhos);

        try {
            $stmt = $pdo->prepare("SELECT id, nome, tamanhos_json FROM modelos_catalogo WHERE nome = ? LIMIT 1");
            $stmt->execute([$nomeModelo]);
            $existente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existente) {
                $tamanhosExistentes = json_decode((string) ($existente['tamanhos_json'] ?? '[]'), true);
                if (!is_array($tamanhosExistentes) || !$tamanhosExistentes) {
                    $tamanhosExistentes = $tamanhos;
                }

                echo json_encode([
                    'sucesso' => true,
                    'dados' => [
                        'id' => (int) $existente['id'],
                        'nome' => $existente['nome'],
                        'tamanhos' => array_values($tamanhosExistentes),
                        'ja_existia' => true,
                    ],
                ]);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO modelos_catalogo (nome, tamanhos_json, origem, ativo) VALUES (?, ?, 'manual', 1)");
            $stmt->execute([$nomeModelo, $jsonTamanhos]);

            echo json_encode([
                'sucesso' => true,
                'dados' => [
                    'id' => (int) $pdo->lastInsertId(),
                    'nome' => $nomeModelo,
                    'tamanhos' => $tamanhos,
                    'ja_existia' => false,
                ],
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Nao foi possivel salvar o modelo agora']);
        }
        exit;
    }


    // 5. IMPRIMIR (A que estava faltando!)
    public function imprimir() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->garantirEstruturasPedidos();
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