<?php
require_once __DIR__ . '/src/config/database.php';

$sqlFile = __DIR__ . '/assets/migration_pedidos_rascunho_20260427.sql';

if (!file_exists($sqlFile)) {
    die("Arquivo de migration nao encontrado: {$sqlFile}\n");
}

$sql = file_get_contents($sqlFile);
$sql = preg_replace('/^--.*$/m', '', $sql);

$statements = array_filter(
    array_map(
        static fn($statement) => trim($statement),
        explode(';', $sql)
    ),
    static fn($statement) => $statement !== ''
);

try {
    $pdo = Database::getConnection();
    $executados = 0;
    $avisos = 0;

    echo "Iniciando migration de pedidos em rascunho...\n";

    foreach ($statements as $statement) {
        try {
            $pdo->exec($statement);
            $executados++;
        } catch (PDOException $e) {
            $avisos++;
            echo "Aviso: {$e->getMessage()}\n";
        }
    }

    echo "Migration concluida. Statements executados: {$executados}. Avisos: {$avisos}.\n";
} catch (Throwable $e) {
    echo "Erro ao executar migration: {$e->getMessage()}\n";
    exit(1);
}