<?php

namespace App\Models;

use PDO;
use DateTime;

/**
 * Model: PontoCalculador  
 * Centraliza todos os cálculos de horas, saldo, DSR, horas extras, etc.
 * Implementa lógica CLT brasileira
 */
class PontoCalculador {
    private static PDO $db;
    private static ConfiguracaoPontos $config;
    
    public function __construct(PDO $database) {
        self::$db = $database;
        self::$config = new ConfiguracaoPontos($database);
    }
    
    /**
     * Calcular saldo de horas do mês
     * Returns: saldo_em_horas (positivo = banco, negativo = devido)
     */
    public static function calcularSaldoMensalUsuario(int $usuario_id, ?string $mes_ano = null): array {
        if (!$mes_ano) {
            $mes_ano = date('Y-m');
        }
        
        // Obter usuário e sua carga horária
        $stmt = self::$db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            throw new \Exception("Usuário não encontrado");
        }
        
        $carga_horaria = floatval(str_replace(',', '.', $usuario['carga_horaria_diaria']));
        
        // Obter apontamentos do mês
        $stmt = self::$db->prepare("
            SELECT ap.*, f.descricao as feriado_desc
            FROM apontamentos_ponto ap
            LEFT JOIN feriados f ON DATE(ap.data) = f.data
            WHERE ap.usuario_id = ?
            AND DATE_FORMAT(ap.data, '%Y-%m') = ?
            ORDER BY ap.data ASC
        ");
        
        $stmt->execute([$usuario_id, $mes_ano]);
        $apontamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular totais
        $horas_trabalhadas = 0;
        $dias_trabalhados = 0;
        $faltas = 0;
        $atestados = 0;
        
        foreach ($apontamentos as $apt) {
            if ($apt['status'] === 'falta' && !$apt['feriado_desc']) {
                $faltas++;
            } elseif ($apt['status'] === 'atestado') {
                $atestados++;
                $horas_trabalhadas += $carga_horaria;
            } else {
                // Calcular horas do apontamento
                $horas = self::calcularHorasApontamento($apt);
                $horas_trabalhadas += $horas;
                if ($horas > 0) {
                    $dias_trabalhados++;
                }
            }
        }
        
        // Calcular horas esperadas (apenas dias úteis)
        $data_inicio = DateTime::createFromFormat('Y-m-d', $mes_ano . '-01');
        $data_fim = $data_inicio->format('t'); // últimodiadomês
        $data_fim = DateTime::createFromFormat('Y-m-d', $mes_ano . '-' . $data_fim);
        
        $dias_uteis = Feriados::contarDiasUteis($data_inicio, $data_fim);
        $horas_esperadas = $dias_uteis * $carga_horaria;
        
        // Calcular saldo
        $saldo = $horas_trabalhadas - $horas_esperadas;
        
        // Contabilizar horas extras já aprovadas
        $horas_extras = HorasExtras::calcularTotalAprovado($usuario_id, $mes_ano);
        
        return [
            'usuario_id' => $usuario_id,
            'mes_ano' => $mes_ano,
            'carga_horaria_diaria' => $carga_horaria,
            'horas_trabalhadas' => round($horas_trabalhadas, 2),
            'horas_esperadas' => round($horas_esperadas, 2),
            'saldo_base' => round($saldo, 2),
            'horas_extras_aprovadas' => round($horas_extras, 2),
            'saldo_final' => round($saldo + $horas_extras, 2),
            'dias_trabalhados' => $dias_trabalhados,
            'dias_uteis' => $dias_uteis,
            'faltas' => $faltas,
            'atestados' => $atestados,
            'desconto_faltas' => round($faltas * $carga_horaria, 2)
        ];
    }
    
    /**
     * Calcular horas de um apontamento individual
     */
    public static function calcularHorasApontamento(array $apontamento): float {
        $horas = 0;
        
        // Primeira batida
        if ($apontamento['hora_entrada_1'] && $apontamento['hora_saida_1']) {
            $entrada = new DateTime('2000-01-01 ' . $apontamento['hora_entrada_1']);
            $saida = new DateTime('2000-01-01 ' . $apontamento['hora_saida_1']);
            
            // Se saída é no dia seguinte
            if ($saida < $entrada) {
                $saida->modify('+1 day');
            }
            
            $diff = $saida->getTimestamp() - $entrada->getTimestamp();
            $horas += $diff / 3600;
        }
        
        // Segunda batida
        if ($apontamento['hora_entrada_2'] && $apontamento['hora_saida_2']) {
            $entrada = new DateTime('2000-01-01 ' . $apontamento['hora_entrada_2']);
            $saida = new DateTime('2000-01-01 ' . $apontamento['hora_saida_2']);
            
            if ($saida < $entrada) {
                $saida->modify('+1 day');
            }
            
            $diff = $saida->getTimestamp() - $entrada->getTimestamp();
            $horas += $diff / 3600;
        }
        
        return $horas;
    }
    
    /**
     * Calcular DSR (Descanso Semanal Remunerado) CLT
     * Lei 605/49 - pago mesmo sem trabalhar no domingo
     */
    public static function calcularDSRSemana(int $usuario_id, DateTime $data_semana): array {
        $usuario_stmt = self::$db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $usuario_stmt->execute([$usuario_id]);
        $usuario = $usuario_stmt->fetch(PDO::FETCH_ASSOC);
        
        $carga_diaria = floatval(str_replace(',', '.', $usuario['carga_horaria_diaria']));
        
        // Período: Segunda a Domingo da semana
        $data = clone $data_semana;
        $domingo_semana = $data->format('N') !== '7' 
            ? clone $data->modify('next Sunday')
            : clone $data;
        
        $segunda = clone $domingo_semana->modify('-6 days');
        
        // Contar dias trabalhados na semana
        $stmt = self::$db->prepare("
            SELECT COUNT(DISTINCT DATE(data)) as dias
            FROM apontamentos_ponto
            WHERE usuario_id = ?
            AND data BETWEEN ? AND ?
            AND status IN ('presente', 'atestado')
        ");
        
        $stmt->execute([
            $usuario_id,
            $segunda->format('Y-m-d'),
            $domingo_semana->format('Y-m-d')
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $dias_trabalhados = intval($result['dias'] ?? 0);
        
        // DSR é pago se trabalhou 6 dias na semana (Lei 605/49)
        $valor_dsr = 0;
        $descricao = "Sem DSR (trabalhou apenas {$dias_trabalhados} dia(s))";
        
        if ($dias_trabalhados >= 6) {
            // Basicamente 1 dia de folga paga por semana de 6 dias trabalhados
            $valor_dsr = $carga_diaria;
            $descricao = "DSR devido (trabalhou {$dias_trabalhados} dias)";
        }
        
        return [
            'usuario_id' => $usuario_id,
            'semana_inicio' => $segunda->format('Y-m-d'),
            'semana_fim' => $domingo_semana->format('Y-m-d'),
            'dias_trabalhados' => $dias_trabalhados,
            'valor_hora' => round($carga_diaria / 8, 2),
            'valor_dsr' => round($valor_dsr, 2),
            'status' => $valor_dsr > 0 ? 'pendente_compensacao' : 'completo',
            'descricao' => $descricao
        ];
    }
    
    /**
     * Detectar possíveis horas extras
     */
    public static function detectarHorasExtras(int $usuario_id, ?string $mes_ano = null): array {
        if (!$mes_ano) {
            $mes_ano = date('Y-m');
        }
        
        $usuario_stmt = self::$db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $usuario_stmt->execute([$usuario_id]);
        $usuario = $usuario_stmt->fetch(PDO::FETCH_ASSOC);
        
        $carga_diaria = floatval(str_replace(',', '.', $usuario['carga_horaria_diaria']));
        
        // Obter apontamentos do mês
        $stmt = self::$db->prepare("
            SELECT * FROM apontamentos_ponto
            WHERE usuario_id = ?
            AND DATE_FORMAT(data, '%Y-%m') = ?
            AND status = 'presente'
            ORDER BY data ASC
        ");
        
        $stmt->execute([$usuario_id, $mes_ano]);
        $apontamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $horas_extras_detectadas = [];
        
        foreach ($apontamentos as $apt) {
            $horas = self::calcularHorasApontamento($apt);
            $extra = $horas - $carga_diaria;
            
            if ($extra > 0.25) { // Mínimo de 15 minutos
                $horas_extras_detectadas[] = [
                    'data' => $apt['data'],
                    'horas_trabalhadas' => round($horas, 2),
                    'carga_esperada' => $carga_diaria,
                    'horas_extra' => round($extra, 2),
                    'tipo' => $extra > 2 ? '100' : '50',
                    'apontamento_id' => $apt['id']
                ];
            }
        }
        
        return $horas_extras_detectadas;
    }
    
    /**
     * Gerar relatório mensal completo
     */
    public static function gerarRelatoriomMensal(int $usuario_id, string $mes_ano): array {
        $saldo = self::calcularSaldoMensalUsuario($usuario_id, $mes_ano);
        $horas_extras = HorasExtras::listarPorUsuario($usuario_id, $mes_ano);
        $potenciais_extras = self::detectarHorasExtras($usuario_id, $mes_ano);
        
        // Calcular DSR para cada semana
        $data_inicio = DateTime::createFromFormat('Y-m-d', $mes_ano . '-01');
        $data_fim = $data_inicio->format('t');
        $data_fim = DateTime::createFromFormat('Y-m-d', $mes_ano . '-' . $data_fim);
        
        $dsrs = [];
        $data_atual = clone $data_inicio;
        
        while ($data_atual <= $data_fim) {
            if ($data_atual->format('N') === '1') { // Segunda-feira
                $dsr_semana = self::calcularDSRSemana($usuario_id, $data_atual);
                $dsrs[] = $dsr_semana;
            }
            $data_atual->modify('+1 day');
        }
        
        return [
            'saldo_mensal' => $saldo,
            'horas_extras_registradas' => $horas_extras,
            'horas_extras_potenciais' => $potenciais_extras,
            'dsr_semanas' => $dsrs,
            'periodo' => $mes_ano,
            'gerado_em' => date('Y-m-d H:i:s')
        ];
    }
}
