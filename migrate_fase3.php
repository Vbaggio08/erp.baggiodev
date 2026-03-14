<?php
// Script para executar migration FASE 3
require_once 'src/config/database.php';

try {
    echo "🔄 Iniciando migração FASE 3...\n\n";
    
    // Obter conexão
    $db = Database::getConnection();
    if (!$db) {
        throw new Exception("Falha ao conectar ao banco de dados");
    }
    
    $sql = file_get_contents('assets/migration_fase3_20260314.sql');
    
    // Remover comentários SQL (linhas que começam com --)
    $lines = explode("\n", $sql);
    $cleaned_sql = "";
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && !str_starts_with($line, '--')) {
            $cleaned_sql .= $line . "\n";
        }
    }
    
    // Dividir por semicolon
    $statements = array_filter(array_map('trim', explode(';', $cleaned_sql)));
    
    $count = 0;
    $errors = 0;
    
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (empty($stmt)) continue;
        
        try {
            $db->exec($stmt);
            $count++;
            
            // Determinar o tipo de comando
            $cmd = strtoupper(substr(trim($stmt), 0, 20));
            if (str_contains($cmd, 'CREATE TABLE')) {
                echo "✅ Tabela criada\n";
            } elseif (str_contains($cmd, 'CREATE OR REPLACE VIEW')) {
                echo "✅ View criada\n";
            } elseif (str_contains($cmd, 'INSERT')) {
                echo "✅ Dados inseridos\n";
            } elseif (str_contains($cmd, 'ALTER')) {
                echo "✅ Índice criado\n";
            }
        } catch (PDOException $e) {
            $errors++;
            $msg = $e->getMessage();
            
            // Mensagens esperadas
            if (strpos($msg, 'already exists') !== false || 
                strpos($msg, 'Duplicate') !== false ||
                strpos($msg, 'already exists') !== false) {
                echo "⏭️  Já existe (ignorado)\n";
            } else {
                echo "❌ Erro: {$msg}\n";
            }
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ Migração FASE 3 Concluída!\n";
    echo "📊 Statements executados: {$count}\n";
    echo "⚠️  Erros/Avisos: {$errors}\n";
    echo str_repeat("=", 50) . "\n";
    
} catch (Exception $e) {
    echo "❌ Erro crítico: " . $e->getMessage() . "\n";
    exit(1);
}
