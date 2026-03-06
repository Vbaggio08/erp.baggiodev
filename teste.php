<?php
// Arquivo de teste simples para verificar se PHP está funcionando
echo "<h1>Teste PHP - HostGator</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// Testa conexão com banco
try {
    $pdo = new PDO("mysql:host=localhost;dbname=vin31871_ripfire;charset=utf8mb4", "vin31871_ripfire", "COLOQUE_SUA_SENHA_REAL_AQUI");
    echo "<p style='color: green;'>✅ Conexão com banco OK!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no banco: " . $e->getMessage() . "</p>";
}

// Lista arquivos na pasta atual
echo "<h2>Arquivos na pasta atual:</h2><ul>";
$files = scandir('.');
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "<li>$file</li>";
    }
}
echo "</ul>";
?>