<?php

namespace App\Models;

use PDO;

/**
 * Model: ConfiguracaoPontos
 * Gerencia configurações do sistema de ponto (horas extras, DSR, tolerâncias, etc)
 */
class ConfiguracaoPontos {
    private static PDO $db;
    private static ?array $cache_config = null;
    
    public function __construct(PDO $database) {
        self::$db = $database;
    }
    
    /**
     * Obter configuração global (ou por empresa)
     */
    public static function obterConfiguracao(?int $empresa_id = null): array {
        // Cache em memória para evitar múltiplas queries
        if (self::$cache_config !== null) {
            return self::$cache_config;
        }
        
        $stmt = self::$db->prepare("
            SELECT * FROM configuracao_pontos_avancado
            WHERE empresa_id = ? OR empresa_id IS NULL
            ORDER BY empresa_id DESC
            LIMIT 1
        ");
        
        $stmt->execute([$empresa_id]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$config) {
            // Retornar valores padrão se não encontrar
            $config = self::obterPadrao();
        } else {
            // Converter booleanos MySQL para PHP
            $config['permite_horas_extras'] = (bool)$config['permite_horas_extras'];
            $config['calcula_dsr'] = (bool)$config['calcula_dsr'];
            $config['desconta_feriado_nao_trabalhado'] = (bool)$config['desconta_feriado_nao_trabalhado'];
            $config['aplicar_dsr_compensado_feriado'] = (bool)$config['aplicar_dsr_compensado_feriado'];
            $config['considerar_lunch_automatico'] = (bool)$config['considerar_lunch_automatico'];
            
            // Converter decimais para float
            $config['limite_horas_extras_diarias'] = floatval($config['limite_horas_extras_diarias']);
            $config['limite_horas_extras_mensais'] = floatval($config['limite_horas_extras_mensais']);
            $config['percentual_hora_extra_50'] = floatval($config['percentual_hora_extra_50']);
            $config['percentual_hora_extra_100'] = floatval($config['percentual_hora_extra_100']);
            $config['indice_dsr'] = floatval($config['indice_dsr'] ?? 1.0);
        }
        
        self::$cache_config = $config;
        return $config;
    }
    
    /**
     * Obter configuração padrão do sistema
     */
    private static function obterPadrao(): array {
        return [
            'id' => 0,
            'empresa_id' => null,
            'permite_horas_extras' => true,
            'limite_horas_extras_diarias' => 2.0,
            'limite_horas_extras_mensais' => 20.0,
            'percentual_hora_extra_50' => 50.0,
            'percentual_hora_extra_100' => 100.0,
            'calcula_dsr' => true,
            'dsr_dias_compensacao' => 1,
            'desconta_feriado_nao_trabalhado' => false,
            'aplicar_dsr_compensado_feriado' => true,
            'tolerancia_entrada_minutos' => 5,
            'tolerancia_saida_minutos' => 5,
            'considerar_lunch_automatico' => false,
            'duracao_lunch_minutos' => 60,
            'indice_dsr' => 1.0
        ];
    }
    
    /**
     * Atualizar configuração
     */
    public static function atualizar(array $dados, ?int $empresa_id = null): bool {
        // Verificar se existe
        $stmt = self::$db->prepare("SELECT id FROM configuracao_pontos_avancado WHERE empresa_id = ? OR (empresa_id IS NULL AND ? IS NULL)");
        $stmt->execute([$empresa_id, $empresa_id]);
        $existe = $stmt->fetch();
        
        $colunas = [];
        $valores = [];
        
        foreach ($dados as $chave => $valor) {
            // Validar entrada
            if (!self::validarConfiguracao($chave, $valor)) {
                continue;
            }
            
            $colunas[] = "{$chave} = ?";
            $valores[] = $valor;
        }
        
        if (empty($colunas)) {
            return false;
        }
        
        $valores[] = $empresa_id;
        
        if ($existe) {
            // UPDATE
            $query = "UPDATE configuracao_pontos_avancado SET " . implode(", ", $colunas);
            $query .= " WHERE empresa_id = ? OR (empresa_id IS NULL AND ? IS NULL)";
        } else {
            // INSERT
            $dados['empresa_id'] = $empresa_id;
            $colunas[] = "empresa_id = ?";
            
            $cols_str = implode(", ", array_keys($dados));
            $vals_str = implode(", ", array_fill(0, count($dados), "?"));
            
            $query = "INSERT INTO configuracao_pontos_avancado ({$cols_str}) VALUES ({$vals_str})";
            $valores = array_values($dados);
        }
        
        $stmt = self::$db->prepare($query);
        $resultado = $stmt->execute($valores);
        
        if ($resultado) {
            // Invalidar cache
            self::$cache_config = null;
        }
        
        return $resultado;
    }
    
    /**
     * Validar se configuração é válida
     */
    private static function validarConfiguracao(string $chave, $valor): bool {
        $validas = [
            'permite_horas_extras',
            'limite_horas_extras_diarias',
            'limite_horas_extras_mensais',
            'percentual_hora_extra_50',
            'percentual_hora_extra_100',
            'calcula_dsr',
            'dsr_dias_compensacao',
            'desconta_feriado_nao_trabalhado',
            'aplicar_dsr_compensado_feriado',
            'tolerancia_entrada_minutos',
            'tolerancia_saida_minutos',
            'considerar_lunch_automatico',
            'duracao_lunch_minutos'
        ];
        
        return in_array($chave, $validas);
    }
    
    /**
     * Obter limite de horas extras diárias
     */
    public static function obterLimitDiario(?int $empresa_id = null): float {
        $config = self::obterConfiguracao($empresa_id);
        return $config['limite_horas_extras_diarias'];
    }
    
    /**
     * Obter limite de horas extras mensais
     */
    public static function obterLimitMensal(?int $empresa_id = null): float {
        $config = self::obterConfiguracao($empresa_id);
        return $config['limite_horas_extras_mensais'];
    }
    
    /**
     * Obter tolerância em minutos (entrada/saída)
     */
    public static function obterTolerancia(?int $empresa_id = null): array {
        $config = self::obterConfiguracao($empresa_id);
        return [
            'entrada' => $config['tolerancia_entrada_minutos'],
            'saida' => $config['tolerancia_saida_minutos']
        ];
    }
    
    /**
     * Verificar se sistema calcula DSR
     */
    public static function calcularDSR(?int $empresa_id = null): bool {
        $config = self::obterConfiguracao($empresa_id);
        return $config['calcula_dsr'];
    }
    
    /**
     * Verificar se sistema permite horas extras
     */
    public static function permiteHorasExtras(?int $empresa_id = null): bool {
        $config = self::obterConfiguracao($empresa_id);
        return $config['permite_horas_extras'];
    }
    
    /**
     * Listar todas as configurações
     */
    public static function listarTodas(): array {
        $stmt = self::$db->query("SELECT * FROM configuracao_pontos_avancado ORDER BY empresa_id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Resetar cache
     */
    public static function resetarCache(): void {
        self::$cache_config = null;
    }
}
