<?php

namespace App\Models;

use PDO;

/**
 * Model: Feriados
 * Gerencia feriados nacionais, estaduais, municipais e pontes
 */
class Feriados {
    private static PDO $db;
    
    public function __construct(PDO $database) {
        self::$db = $database;
    }
    
    /**
     * Verificar se uma data é feriado
     */
    public static function ehFeriado(\DateTime $data): bool {
        $stmt = self::$db->prepare("
            SELECT COUNT(*) as total FROM feriados
            WHERE data = ?
        ");
        
        $stmt->execute([$data->format('Y-m-d')]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['total'] > 0;
    }
    
    /**
     * Obter informações do feriado
     */
    public static function obterFeriado(\DateTime $data): ?array {
        $stmt = self::$db->prepare("
            SELECT * FROM feriados WHERE data = ?
        ");
        
        $stmt->execute([$data->format('Y-m-d')]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado ?: null;
    }
    
    /**
     * Listar feriados de um período
     */
    public static function listarPeriodo(string $data_inicio, string $data_fim): array {
        $stmt = self::$db->prepare("
            SELECT * FROM feriados
            WHERE data BETWEEN ? AND ?
            ORDER BY data ASC
        ");
        
        $stmt->execute([$data_inicio, $data_fim]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Listar feriados móveis de um ano (Páscoa, Carnaval, etc)
     */
    public static function listarFeriadosMoveisAno(int $ano): array {
        $stmt = self::$db->prepare("
            SELECT * FROM feriados
            WHERE YEAR(data) = ?
            AND tipo IN ('ponte', 'móvel')
            ORDER BY data ASC
        ");
        
        $stmt->execute([$ano]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Adicionar novo feriado
     */
    public static function adicionar(
        string $data,
        string $descricao,
        string $tipo = 'nacional',
        ?int $empresa_id = null
    ): int {
        // Validar data
        $d = \DateTime::createFromFormat('Y-m-d', $data);
        if (!$d || $d->format('Y-m-d') !== $data) {
            throw new \Exception("Data inválida: {$data}");
        }
        
        $stmt = self::$db->prepare("
            INSERT INTO feriados (data, descricao, tipo, empresa_id)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([$data, $descricao, $tipo, $empresa_id]);
        
        return (int)self::$db->lastInsertId();
    }
    
    /**
     * Remover feriado
     */
    public static function remover(int $id): bool {
        $stmt = self::$db->prepare("DELETE FROM feriados WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Contar dias úteis entre duas datas (excluindo feriados e domingos)
     */
    public static function contarDiasUteis(\DateTime $data_inicio, \DateTime $data_fim): int {
        $dias_uteis = 0;
        $data_atual = clone $data_inicio;
        
        while ($data_atual <= $data_fim) {
            // Verificar se não é fim de semana (0=domingo, 6=sábado)
            $dia_semana = (int)$data_atual->format('w');
            
            if ($dia_semana !== 0 && $dia_semana !== 6) {
                // Verificar se não é feriado
                if (!self::ehFeriado($data_atual)) {
                    $dias_uteis++;
                }
            }
            
            $data_atual->modify('+1 day');
        }
        
        return $dias_uteis;
    }
    
    /**
     * Listar feriados nacionais 2026-2027
     */
    public static function inserirFeriadosNacionaisPadrao(): void {
        $feriados = [
            '2026-01-01' => 'Ano Novo',
            '2026-02-13' => 'Sexta de Carnaval (Ponte)',
            '2026-02-16' => 'Segunda de Carnaval (Ponte)',
            '2026-04-03' => 'Sexta-feira Santa',
            '2026-04-21' => 'Tiradentes',
            '2026-05-01' => 'Dia do Trabalho',
            '2026-09-07' => 'Independência do Brasil',
            '2026-10-12' => 'Nossa Senhora Aparecida',
            '2026-11-02' => 'Finados',
            '2026-11-20' => 'Consciência Negra',
            '2026-12-25' => 'Natal',
        ];
        
        foreach ($feriados as $data => $descricao) {
            try {
                self::adicionar($data, $descricao, 'nacional');
            } catch (\Exception $e) {
                // Já existe, ignora
            }
        }
    }
    
    /**
     * Calcular próximo dia útil (pulando feriados e fins de semana)
     */
    public static function proximoDiaUtil(\DateTime $data, int $dias = 1): \DateTime {
        $data_resultado = clone $data;
        $contador = 0;
        
        while ($contador < $dias) {
            $data_resultado->modify('+1 day');
            
            $dia_semana = (int)$data_resultado->format('w');
            if ($dia_semana !== 0 && $dia_semana !== 6 && !self::ehFeriado($data_resultado)) {
                $contador++;
            }
        }
        
        return $data_resultado;
    }
    
    /**
     * Validar se é dia útil
     */
    public static function ehDiaUtil(\DateTime $data): bool {
        $dia_semana = (int)$data->format('w');
        
        // Não é domingo (0) nem sábado (6), e não é feriado
        return ($dia_semana !== 0 && $dia_semana !== 6) && !self::ehFeriado($data);
    }
}
