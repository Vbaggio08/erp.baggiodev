<?php
// Debug simples para HostGator
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Debug - Ripfire System</h1>";
echo "<h2>PHP Info:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br><br>";

// Testa se .env existe
echo "<h2>Arquivo .env:</h2>";
if (file_exists('.env')) {
    echo "✅ .env encontrado<br>";
    $env_content = file_get_contents('.env');
    echo "<pre>" . htmlspecialchars($env_content) . "</pre>";
} else {
    echo "❌ .env NÃO encontrado!<br>";
}

// Testa se src/config existe
echo "<h2>Diretórios:</h2>";
$dirs = ['src', 'src/config', 'src/views', 'assets'];
foreach ($dirs as $dir) {
    echo "$dir: " . (is_dir($dir) ? "✅ existe" : "❌ não existe") . "<br>";
}

// Testa includes
echo "<h2>Teste de includes:</h2>";
try {
    require_once 'src/config/env.php';
    echo "✅ env.php carregado<br>";
} catch (Exception $e) {
    echo "❌ Erro em env.php: " . $e->getMessage() . "<br>";
}

try {
    require_once 'src/config/database.php';
    echo "✅ database.php carregado<br>";
} catch (Exception $e) {
    echo "❌ Erro em database.php: " . $e->getMessage() . "<br>";
}
?>