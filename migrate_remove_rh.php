<?php
/**
 * Script de Migration para retirada total do modulo RH/Ponto.
 * Executa o arquivo SQL de remocao das estruturas de RH.
 */

require_once __DIR__ . '/src/config/database.php';

$sqlFile = __DIR__ . '/assets/migration_remove_rh_20260413.sql';

if (!file_exists($sqlFile)) {
    die("Arquivo de migration nao encontrado: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    die("Falha ao ler arquivo SQL: $sqlFile\n");
}

$lines = explode("\n", $sql);
$cleanedLines = [];
foreach ($lines as $line) {
    if (strpos(trim($line), '--') === 0) {
        continue;
    }
    $cleanedLines[] = $line;
}
$sql = implode("\n", $cleanedLines);

$statements = array_filter(
    array_map(
        static fn($stmt) => trim($stmt),
        explode(';', $sql)
    ),
    static fn($stmt) => $stmt !== ''
);

try {
    $pdo = Database::getConnection();

    echo "Iniciando migration de retirada RH...\n";
    echo "Total de statements: " . count($statements) . "\n\n";

    $executed = 0;
    foreach ($statements as $idx => $statement) {
        try {
            $pdo->exec($statement);
            $executed++;
            echo "OK statement " . ($idx + 1) . "\n";
        } catch (PDOException $e) {
            echo "ERRO statement " . ($idx + 1) . ": " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    echo "\nMigration concluida com sucesso. Statements executados: $executed\n";
} catch (PDOException $e) {
    echo "Erro geral: " . $e->getMessage() . "\n";
    exit(1);
}
