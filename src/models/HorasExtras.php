<?php

namespace App\Models;

use PDO;

/**
 * Model: HorasExtras
 * Gerencia registro, aprovação e compensação de horas extras
 */
class HorasExtras {
    private static PDO $db;
    
    public function __construct(PDO $database) {
        self::$db = $database;
    }
    
    /**
     * Registrar nova solicitação de hora extra
     */
    public static function registrarHoraExtra(
        int $usuario_id,
        string $data_referencia,
        float $horas_extras,
        string $tipo = '50',
        ?string $motivo = null,
        ?int $apontamento_id = null
    ): int {
        $stmt = self::$db->prepare("
            INSERT INTO horas_extras 
            (usuario_id, apontamento_id, data_referencia, horas_extras, tipo, motivo, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pendente')
        ");
        
        $stmt->execute([$usuario_id, $apontamento_id, $data_referencia, $horas_extras, $tipo, $motivo]);
        
        // Registrar auditoria
        AuditoriaAlteracao::registrarAlteracao(
            $usuario_id,
            $usuario_id,
            'hora_extra_registrada',
            "Hora extra de {$horas_extras}h registrada para {$data_referencia} (tipo: {$tipo}%)",
            null,
            null,
            null,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
        );
        
        return (int)self::$db->lastInsertId();
    }
    
    /**
     * Obter hora extra por ID
     */
    public static function obterPorId(int $id): ?array {
        $stmt = self::$db->prepare("
            SELECT he.*, u.nome as usuario_nome 
            FROM horas_extras he
            JOIN usuarios u ON he.usuario_id = u.id
            WHERE he.id = ?
        ");
        
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
    
    /**
     * Listar horas extras pendentes de aprovação
     */
    public static function listarPendentes(?int $usuario_id = null, ?string $mes = null): array {
        $query = "
            SELECT he.*, u.nome as usuario_nome, u.departamento
            FROM horas_extras he
            JOIN usuarios u ON he.usuario_id = u.id
            WHERE he.status = 'pendente'
        ";
        
        $params = [];
        
        if ($usuario_id) {
            $query .= " AND he.usuario_id = ?";
            $params[] = $usuario_id;
        }
        
        if ($mes) {
            $query .= " AND DATE_FORMAT(he.data_referencia, '%Y-%m') = ?";
            $params[] = $mes;
        }
        
        $query .= " ORDER BY he.data_referencia DESC";
        
        $stmt = self::$db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Aprovar hora extra
     */
    public static function aprovar(int $id, int $usuario_aprovador_id): bool {
        $he = self::obterPorId($id);
        if (!$he) return false;
        
        $stmt = self::$db->prepare("
            UPDATE horas_extras 
            SET status = 'aprovado', 
                aprovado_por = ?, 
                data_aprovacao = NOW()
            WHERE id = ?
        ");
        
        $resultado = $stmt->execute([$usuario_aprovador_id, $id]);
        
        if ($resultado) {
            AuditoriaAlteracao::registrarAlteracao(
                $he['usuario_id'],
                $usuario_aprovador_id,
                'hora_extra_aprovada',
                "Hora extra #{$id} aprovada ({$he['horas_extras']}h em {$he['data_referencia']})",
                null,
                null,
                null,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            );
        }
        
        return $resultado;
    }
    
    /**
     * Rejeitar hora extra
     */
    public static function rejeitar(int $id, int $usuario_rejeitor_id, string $motivo = ''): bool {
        $he = self::obterPorId($id);
        if (!$he) return false;
        
        $stmt = self::$db->prepare("
            UPDATE horas_extras 
            SET status = 'rejeitado', 
                motivo = CONCAT(COALESCE(motivo, ''), ' [REJEIÇÃO: ', ?, ']')
            WHERE id = ?
        ");
        
        $resultado = $stmt->execute([$motivo, $id]);
        
        if ($resultado) {
            AuditoriaAlteracao::registrarAlteracao(
                $he['usuario_id'],
                $usuario_rejeitor_id,
                'hora_extra_rejeitada',
                "Hora extra #{$id} rejeitada. Motivo: {$motivo}",
                null,
                null,
                null,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            );
        }
        
        return $resultado;
    }
    
    /**
     * Marcar como pago
     */
    public static function marcarComoPago(int $id): bool {
        return self::$db->prepare("
            UPDATE horas_extras 
            SET status = 'pago' 
            WHERE id = ?
        ")->execute([$id]);
    }
    
    /**
     * Listar horas extras por usuário e período
     */
    public static function listarPorUsuario(int $usuario_id, string $mes_ano = null): array {
        if (!$mes_ano) {
            $mes_ano = date('Y-m');
        }
        
        $stmt = self::$db->prepare("
            SELECT * FROM horas_extras
            WHERE usuario_id = ? 
            AND DATE_FORMAT(data_referencia, '%Y-%m') = ?
            ORDER BY data_referencia DESC
        ");
        
        $stmt->execute([$usuario_id, $mes_ano]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcular total de horas extras aprovadas no mês
     */
    public static function calcularTotalAprovado(int $usuario_id, string $mes_ano = null): float {
        if (!$mes_ano) {
            $mes_ano = date('Y-m');
        }
        
        $stmt = self::$db->prepare("
            SELECT COALESCE(SUM(horas_extras), 0) as total
            FROM horas_extras
            WHERE usuario_id = ? 
            AND status IN ('aprovado', 'pago')
            AND DATE_FORMAT(data_referencia, '%Y-%m') = ?
        ");
        
        $stmt->execute([$usuario_id, $mes_ano]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return floatval($result['total'] ?? 0);
    }
    
    /**
     * Validar limite mensal de horas extras
     */
    public static function validarLimite(int $usuario_id, float $nova_hora, string $mes_ano = null): bool {
        if (!$mes_ano) {
            $mes_ano = date('Y-m');
        }
        
        // Obter configuração
        $config = ConfiguracaoPontos::obterConfiguracao();
        $limite = $config['limite_horas_extras_mensais'] ?? 20.0;
        
        $total_atual = self::calcularTotalAprovado($usuario_id, $mes_ano);
        
        return ($total_atual + $nova_hora) <= $limite;
    }
}
