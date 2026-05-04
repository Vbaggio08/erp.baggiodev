<?php
require_once __DIR__ . '/../config/database.php';

class Estoque {

    private static $ultimoErro = '';
    private static $schemaColunas = [];

    public static function getUltimoErro(): string {
        return self::$ultimoErro;
    }

    private static function setUltimoErro(string $mensagem): void {
        self::$ultimoErro = $mensagem;
    }

    private static function tabelaTemColuna(string $tabela, string $coluna): bool {
        $cacheKey = $tabela . '.' . $coluna;
        if (array_key_exists($cacheKey, self::$schemaColunas)) {
            return self::$schemaColunas[$cacheKey];
        }

        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) AS total
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tabela, $coluna]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $exists = ((int)($row['total'] ?? 0) > 0);
        self::$schemaColunas[$cacheKey] = $exists;
        return $exists;
    }

    private static function obterProdutoAtivoPorId(int $produtoId): ?array {
        if ($produtoId <= 0) {
            return null;
        }

        $pdo = Database::getConnection();
        $sql = "SELECT id, nome, tamanho, cor
                FROM produtos
                WHERE id = ? AND ativo = 1
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$produtoId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private static function obterProdutoAtivoPorAtributos(string $produto, string $tamanho, string $cor): ?array {
        $pdo = Database::getConnection();
        $sql = "SELECT id, nome, tamanho, cor
                FROM produtos
                WHERE nome = ? AND tamanho = ? AND cor = ? AND ativo = 1
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$produto, $tamanho, $cor]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    // 1. REGISTRA MOVIMENTAÇÃO (Entrada ou Saída)
    public static function registrarMovimento($dados) {
        $pdo = Database::getConnection();

        try {
            self::setUltimoErro('');

            $tipo = trim((string)($dados['tipo'] ?? ''));
            $produtoId = (int)($dados['produto_id'] ?? 0);
            $produto = trim((string)($dados['produto'] ?? ''));
            $tamanho = trim((string)($dados['tamanho'] ?? ''));
            $cor = trim((string)($dados['cor'] ?? ''));
            $quantidade = (int)($dados['quantidade'] ?? 0);
            $observacao = trim((string)($dados['observacao'] ?? ''));
            $usuarioId = (int)($dados['usuario_id'] ?? 0);

            if (!in_array($tipo, ['entrada', 'saida'], true)) {
                self::setUltimoErro('Tipo de movimentacao invalido');
                return false;
            }

            if ($quantidade <= 0) {
                self::setUltimoErro('Quantidade deve ser maior que zero');
                return false;
            }

            $produtoRow = null;
            if ($produtoId > 0) {
                $produtoRow = self::obterProdutoAtivoPorId($produtoId);
            }

            if (!$produtoRow && $produto !== '' && $tamanho !== '' && $cor !== '') {
                $produtoRow = self::obterProdutoAtivoPorAtributos($produto, $tamanho, $cor);
            }

            if (!$produtoRow) {
                self::setUltimoErro('Produto selecionado nao existe ou esta inativo');
                return false;
            }

            $produtoId = (int)($produtoRow['id'] ?? 0);
            $produto = (string)($produtoRow['nome'] ?? $produto);
            $tamanho = (string)($produtoRow['tamanho'] ?? $tamanho);
            $cor = (string)($produtoRow['cor'] ?? $cor);

            if ($tipo === 'saida') {
                $saldoAtual = self::obterSaldoProduto($produto, $tamanho, $cor, $produtoId);
                if ($saldoAtual < $quantidade) {
                    self::setUltimoErro('Saldo insuficiente para esta saida');
                    return false;
                }
            }

            $pdo->beginTransaction();

            $usuario = $dados['usuario'] ?? 'Sistema';

            $temProdutoId = self::tabelaTemColuna('estoque_movimentacoes', 'produto_id');
            $temUsuarioId = self::tabelaTemColuna('estoque_movimentacoes', 'usuario_id');

            $campos = ['tipo', 'produto', 'tamanho', 'cor', 'quantidade', 'observacao', 'usuario', 'data_movimento'];
            $placeholders = ['?', '?', '?', '?', '?', '?', '?', 'NOW()'];
            $params = [$tipo, $produto, $tamanho, $cor, $quantidade, $observacao, $usuario];

            if ($temProdutoId) {
                $campos[] = 'produto_id';
                $placeholders[] = '?';
                $params[] = $produtoId > 0 ? $produtoId : null;
            }

            if ($temUsuarioId) {
                $campos[] = 'usuario_id';
                $placeholders[] = '?';
                $params[] = $usuarioId > 0 ? $usuarioId : null;
            }

            $sql = 'INSERT INTO estoque_movimentacoes (' . implode(', ', $campos) . ') VALUES (' . implode(', ', $placeholders) . ')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $pdo->commit();
            return true;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            self::setUltimoErro('Falha ao registrar movimentacao');
            return false;
        }
    }

    public static function obterSaldoProduto(string $produto, string $tamanho, string $cor, int $produtoId = 0): int {
        $pdo = Database::getConnection();
        $temProdutoId = self::tabelaTemColuna('estoque_movimentacoes', 'produto_id');

        if ($temProdutoId && $produtoId > 0) {
            $sql = "SELECT IFNULL(SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE -quantidade END), 0) AS saldo
                    FROM estoque_movimentacoes
                    WHERE produto_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$produtoId]);
        } else {
            $sql = "SELECT IFNULL(SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE -quantidade END), 0) AS saldo
                    FROM estoque_movimentacoes
                    WHERE produto = ? AND tamanho = ? AND cor = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$produto, $tamanho, $cor]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['saldo'] ?? 0);
    }

    private static function produtoAtivoExiste(string $produto, string $tamanho, string $cor): bool {
        return self::obterProdutoAtivoPorAtributos($produto, $tamanho, $cor) !== null;
    }

    // 2. CALCULA O SALDO (Agrupado com LEFT JOIN para mostrar os zerados)
    public static function getEstoqueAgrupado($busca = '') {
        $pdo = Database::getConnection();
        $temProdutoId = self::tabelaTemColuna('estoque_movimentacoes', 'produto_id');
        
        // Faz um LEFT JOIN partindo de PRODUTOS para garantir que os zerados apareçam
        // Note que ajustei para estoque_movimentacoes (Plural) para bater com a função de cima
        $sql = "SELECT 
                    p.sku, 
                    p.nome as produto, 
                    p.cor, 
                    p.tamanho, 
                    IFNULL(
                        SUM(
                            CASE 
                                WHEN m.tipo = 'entrada' THEN m.quantidade 
                                WHEN m.tipo = 'saida' THEN -m.quantidade 
                                ELSE 0 
                            END
                        ), 0
                    ) as saldo_total
                FROM produtos p
                LEFT JOIN estoque_movimentacoes m ON " . ($temProdutoId ? "p.id = m.produto_id" : "p.nome = m.produto AND p.cor = m.cor AND p.tamanho = m.tamanho") . "
                WHERE p.ativo = 1";
        
        $params = [];
        if (!empty($busca)) {
            $sql .= " AND (p.sku LIKE ? OR p.nome LIKE ? OR p.cor LIKE ?)";
            $params = ["%$busca%", "%$busca%", "%$busca%"];
        }
        
        $sql .= " GROUP BY p.id, p.nome, p.cor, p.tamanho
                  ORDER BY p.nome ASC, p.tamanho ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. HISTÓRICO COMPLETO
    public static function getHistoricoCompleto($busca = '') {
        $pdo = Database::getConnection();
        $temProdutoId = self::tabelaTemColuna('estoque_movimentacoes', 'produto_id');
        $temUsuarioId = self::tabelaTemColuna('estoque_movimentacoes', 'usuario_id');
        $usaJoin = ($temProdutoId || $temUsuarioId);

        if ($usaJoin) {
            $sql = "SELECT
                        m.*,
                        COALESCE(p.nome, m.produto) AS produto,
                        COALESCE(p.tamanho, m.tamanho) AS tamanho,
                        COALESCE(p.cor, m.cor) AS cor,
                        p.sku,
                        COALESCE(u.nome, m.usuario) AS usuario
                    FROM estoque_movimentacoes m
                    " . ($temProdutoId ? "LEFT JOIN produtos p ON m.produto_id = p.id" : "LEFT JOIN produtos p ON 1=0") . "
                    " . ($temUsuarioId ? "LEFT JOIN usuarios u ON m.usuario_id = u.id" : "LEFT JOIN usuarios u ON 1=0") . "
                    WHERE 1=1";
        } else {
            $sql = "SELECT * FROM estoque_movimentacoes WHERE 1=1";
        }

        $params = [];
        if (!empty($busca)) {
            if ($usaJoin) {
                $sql .= " AND (COALESCE(p.nome, m.produto) LIKE ? OR COALESCE(u.nome, m.usuario) LIKE ? OR m.observacao LIKE ?)";
            } else {
                $sql .= " AND (produto LIKE ? OR usuario LIKE ? OR observacao LIKE ?)";
            }
            $params = ["%$busca%", "%$busca%", "%$busca%"];
        }
        $sql .= $usaJoin ? " ORDER BY m.data_movimento DESC LIMIT 50" : " ORDER BY data_movimento DESC LIMIT 50";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. RELATÓRIO DE PERDAS
    public static function getRelatorioPerdas($busca = '') {
        $pdo = Database::getConnection();
        $temProdutoId = self::tabelaTemColuna('estoque_movimentacoes', 'produto_id');
        $temUsuarioId = self::tabelaTemColuna('estoque_movimentacoes', 'usuario_id');
        $usaJoin = ($temProdutoId || $temUsuarioId);

        if ($usaJoin) {
            $sql = "SELECT
                        m.*,
                        COALESCE(p.nome, m.produto) AS produto,
                        COALESCE(p.tamanho, m.tamanho) AS tamanho,
                        COALESCE(p.cor, m.cor) AS cor,
                        p.sku,
                        COALESCE(u.nome, m.usuario) AS usuario
                    FROM estoque_movimentacoes m
                    " . ($temProdutoId ? "LEFT JOIN produtos p ON m.produto_id = p.id" : "LEFT JOIN produtos p ON 1=0") . "
                    " . ($temUsuarioId ? "LEFT JOIN usuarios u ON m.usuario_id = u.id" : "LEFT JOIN usuarios u ON 1=0") . "
                    WHERE (m.observacao LIKE '%perda%' OR m.observacao LIKE '%quebra%')";
        } else {
            $sql = "SELECT * FROM estoque_movimentacoes 
                    WHERE (observacao LIKE '%perda%' OR observacao LIKE '%quebra%')";
        }

        $params = [];
        if (!empty($busca)) {
            if ($usaJoin) {
                $sql .= " AND (COALESCE(p.nome, m.produto) LIKE ? OR COALESCE(u.nome, m.usuario) LIKE ? OR m.observacao LIKE ?)";
            } else {
                $sql .= " AND (produto LIKE ? OR usuario LIKE ? OR observacao LIKE ?)";
            }
            $params = ["%$busca%", "%$busca%", "%$busca%"];
        }
        $sql .= $usaJoin ? " ORDER BY m.data_movimento DESC" : " ORDER BY data_movimento DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function listarEstoqueBaixo(int $limite = 5): array {
        $pdo = Database::getConnection();
        $temProdutoId = self::tabelaTemColuna('estoque_movimentacoes', 'produto_id');
        $sql = "SELECT
                    p.nome AS produto,
                    p.tamanho,
                    p.cor,
                    IFNULL(SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade WHEN m.tipo = 'saida' THEN -m.quantidade ELSE 0 END), 0) AS saldo
                FROM produtos p
                LEFT JOIN estoque_movimentacoes m ON " . ($temProdutoId ? "p.id = m.produto_id" : "p.nome = m.produto AND p.cor = m.cor AND p.tamanho = m.tamanho") . "
                WHERE p.ativo = 1
                GROUP BY p.id, p.nome, p.tamanho, p.cor
                HAVING saldo <= ?
                ORDER BY saldo ASC, p.nome ASC
                LIMIT 20";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTotalPecas(): int {
        $pdo = Database::getConnection();
        $temProdutoId = self::tabelaTemColuna('estoque_movimentacoes', 'produto_id');
        $sql = "SELECT IFNULL(SUM(saldo_por_item.saldo), 0) AS total_pecas
                FROM (
                    SELECT IFNULL(SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade WHEN m.tipo = 'saida' THEN -m.quantidade ELSE 0 END), 0) AS saldo
                    FROM produtos p
                    LEFT JOIN estoque_movimentacoes m ON " . ($temProdutoId ? "p.id = m.produto_id" : "p.nome = m.produto AND p.cor = m.cor AND p.tamanho = m.tamanho") . "
                    WHERE p.ativo = 1
                    GROUP BY p.id
                ) AS saldo_por_item";
        $stmt = $pdo->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total_pecas'] ?? 0);
    }
}