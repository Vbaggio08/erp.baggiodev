<?php
/**
 * Script de Migration para Sistema de Ponto Eletrônico
 * Executa o arquivo SQL de migration
 */

require_once __DIR__ . '/src/config/database.php';

// Lê o arquivo SQL
$sqlFile = __DIR__ . '/assets/migration_ponto_20260314.sql';

if (!file_exists($sqlFile)) {
    die("❌ Arquivo de migration não encontrado: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);

// Remove BOM se existir
$sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);

// Remove comentários SQL completos de linha (-- ...)
$sql = preg_replace('/^--.*$/m', '', $sql);

// Remove comentários /* */ mas MANTÉM os COMMENT em coluna (isso é SQL válido)
// Vamos ser mais sutil aqui
$lines = explode("\n", $sql);
$cleanedLines = [];
foreach ($lines as $line) {
    // Se a linha começar com --, pula
    if (strpos(trim($line), '--') === 0) {
        continue;
    }
    $cleanedLines[] = $line;
}
$sql = implode("\n", $cleanedLines);

// Split por ponto-e-vírgula
$statements = array_filter(
    array_map(
        fn($stmt) => trim($stmt),
        explode(';', $sql)
    ),
    fn($stmt) => !empty($stmt) && strlen($stmt) > 5
);

try {
    $pdo = Database::getConnection();
    
    echo "🔄 Iniciando migration...\n";
    echo "📊 Total de statements: " . count($statements) . "\n\n";
    
    $executed = 0;
    $errors = [];
    
    foreach ($statements as $idx => $statement) {
        if (trim($statement) === '' || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executed++;
            echo "✅ Statement " . ($idx + 1) . " executado\n";
        } catch (PDOException $e) {
            $errors[] = [
                'stmt' => ($idx + 1),
                'error' => $e->getMessage()
            ];
            echo "⚠️  Statement " . ($idx + 1) . ": " . $e->getMessage() . "\n";
            // Continua mesmo com erro (CREATE IF NOT EXISTS)
        }
    }
    
    echo "\n✅ Migration concluída!\n";
    echo "📝 Total de statements executados: $executed\n";
    
    if (count($errors) > 0) {
        echo "⚠️  Total de erros: " . count($errors) . "\n";
    }
    
    echo "\n📁 Verificando tabelas criadas:\n";
    
    // Lista as tabelas criadas
    $tables = [
        'usuarios',
        'apontamentos_ponto',
        'historico_alteracoes_ponto',
        'atestados',
        'dispositivos_autorizados',
        'geolocation_empresa',
        'configuracao_ponto',
        'sincronizacoes_offline'
    ];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "  ✅ $table (OK)\n";
        } catch (PDOException $e) {
            echo "  ❌ $table (ERRO: " . $e->getMessage() . ")\n";
        }
    }
    
    echo "\n✨ Tudo pronto para começar!\n";
    
} catch (PDOException $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "\n";
    exit(1);
}
?>
