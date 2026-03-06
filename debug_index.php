<?php
// Debug específico para o erro 500 - testa cada passo do index.php

echo "<h1>🔧 Debug Passo-a-Passo - Index.php</h1>";

// Passo 1: Testa require_once 'src/config/env.php'
echo "<h2>Passo 1: Carregando env.php</h2>";
try {
    require_once 'src/config/env.php';
    echo "✅ env.php carregado com sucesso<br>";
} catch (Exception $e) {
    echo "❌ ERRO em env.php: " . $e->getMessage() . "<br>";
    exit;
}

// Passo 2: Testa função env()
echo "<h2>Passo 2: Testando função env()</h2>";
$timezone = env('APP_TIMEZONE', 'America/Sao_Paulo');
$debug = env('APP_DEBUG', false);
echo "APP_TIMEZONE: $timezone<br>";
echo "APP_DEBUG: " . ($debug ? 'true' : 'false') . "<br>";

// Passo 3: Testa date_default_timezone_set
echo "<h2>Passo 3: Configurando timezone</h2>";
try {
    date_default_timezone_set($timezone);
    echo "✅ Timezone configurado: " . date_default_timezone_get() . "<br>";
} catch (Exception $e) {
    echo "❌ ERRO no timezone: " . $e->getMessage() . "<br>";
}

// Passo 4: Testa configurações de debug
echo "<h2>Passo 4: Configurações de debug</h2>";
if ($debug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    echo "✅ Modo DEBUG ativado<br>";
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
    echo "✅ Modo PRODUÇÃO ativado<br>";
}

// Passo 5: Testa session_start
echo "<h2>Passo 5: Iniciando sessão</h2>";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        echo "✅ Sessão iniciada<br>";
    } else {
        echo "✅ Sessão já estava ativa<br>";
    }
} catch (Exception $e) {
    echo "❌ ERRO na sessão: " . $e->getMessage() . "<br>";
}

// Passo 6: Testa variáveis GET
echo "<h2>Passo 6: Verificando rota</h2>";
$rota = $_GET['rota'] ?? 'dashboard';
echo "Rota atual: $rota<br>";

// Passo 7: Testa rotas públicas
echo "<h2>Passo 7: Verificando autenticação</h2>";
$rotasPublicas = ['login', 'autenticar'];
if (!isset($_SESSION['user_id']) && !in_array($rota, $rotasPublicas)) {
    echo "❌ Usuário não logado, redirecionando para login...<br>";
    // Não faz o redirect aqui para teste
} else {
    echo "✅ Autenticação OK ou rota pública<br>";
}

echo "<h2>🎉 Se chegou até aqui, o problema está no switch case!</h2>";
echo "<p>Próximo passo: testar o require do controller específico</p>";
?>