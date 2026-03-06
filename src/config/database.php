<?php
// Carrega variáveis de ambiente
require_once __DIR__ . '/env.php';

class Database {
    private static $pdo;

    public static function getConnection() {
        if (!self::$pdo) {
            try {
                // Lê credenciais das variáveis de ambiente (.env)
                $host = env('DB_HOST', 'localhost');
                $dbname = env('DB_NAME', 'ripfire');
                $user = env('DB_USER', 'root');
                $password = env('DB_PASSWORD', '');
                $appDebug = env('APP_DEBUG', false);

                self::$pdo = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $user,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => $appDebug ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                
                // Desativa o modo ONLY_FULL_GROUP_BY para compatibilidade com servidores
                self::$pdo->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");
            } catch (PDOException $e) {
                // Se estamos em modo debug e o usuário não é o padrão, tentamos reconectar
                // com as credenciais locais (`root` sem senha). Isso ajuda quando o dev
                // esquece de atualizar o .env após copiar as configurações do servidor.
                $appDebug = env('APP_DEBUG', false);
                if ($appDebug && $user !== 'root') {
                    try {
                        self::$pdo = new PDO(
                            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                            'root',
                            '',
                            [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                PDO::ATTR_EMULATE_PREPARES => false,
                            ]
                        );
                        // novamente desativa ONLY_FULL_GROUP_BY
                        self::$pdo->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");
                        return self::$pdo;
                    } catch (PDOException $e2) {
                        // continua para mostrar mensagem original abaixo
                        $e = $e2;
                    }
                }

                if ($appDebug) {
                    die("Erro de conexão: " . $e->getMessage());
                } else {
                    // Em produção, não mostra detalhes do erro
                    die("Erro ao conectar ao banco de dados. Contate o suporte.");
                }
            }
        }
        return self::$pdo;
    }
}