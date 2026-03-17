<?php
/**
 * Carregador de Variáveis de Ambiente (.env)
 * 
 * USAGE:
 *   require_once 'config/env.php';
 *   $db_host = getenv('DB_HOST');
 *   // ou
 *   $db_host = env('DB_HOST', 'localhost'); // com default
 */

function env($key, $default = null) {
    $value = getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    // Converte valores booleanos em string
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return null;
        default:
            return $value;
    }
}

// Carrega arquivo de ambiente da raiz do projeto
$projectRoot = dirname(dirname(__DIR__));
$parentRoot = dirname($projectRoot);
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$isLocalHost = (
    $httpHost === '' ||
    stripos($httpHost, 'localhost') === 0 ||
    stripos($httpHost, '127.0.0.1') === 0
);

$envFile = '';
if (!$isLocalHost) {
    // Em produção, tenta raiz do projeto e pasta pai (hospedagem compartilhada).
    $candidatos = [
        $projectRoot . '/.env.production',
        $projectRoot . '/.env',
        $parentRoot . '/.env.production',
        $parentRoot . '/.env',
    ];
} else {
    // Em localhost, prioriza .env local e usa .env.example como fallback.
    $candidatos = [
        $projectRoot . '/.env',
        $projectRoot . '/.env.example',
    ];
}

foreach ($candidatos as $caminhoEnv) {
    if (file_exists($caminhoEnv)) {
        $envFile = $caminhoEnv;
        break;
    }
}

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignora comentários
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse linha KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove aspas se existirem
            if (preg_match('/^(["\'])(.*)\\1$/m', $value, $matches)) {
                $value = $matches[2];
            }
            
            if (!empty($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// --- Define a URL base da aplicação ---
$httpsAtivo = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$serverPort = $_SERVER['SERVER_PORT'] ?? null;
$protocol = ($httpsAtivo || (string)$serverPort === '443') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

// Garante que o caminho termine com /
$script_name = rtrim(str_replace(basename($scriptPath), '', $scriptPath), '/') . '/';
define('BASE_URL', $protocol . $host . $script_name);
