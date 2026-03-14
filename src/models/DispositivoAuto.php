<?php
require_once __DIR__ . '/../config/database.php';

class DispositivoAuto {
    
    /**
     * Registra ou atualiza um dispositivo
     */
    public static function registrarDispositivo($usuario_id, $device_id, $device_nome, $ip, $user_agent, $tipo) {
        $pdo = Database::getConnection();
        
        try {
            // Verifica se já existe
            $sql = "SELECT id FROM dispositivos_autorizados WHERE usuario_id = ? AND device_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id, $device_id]);
            $dispositivo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($dispositivo) {
                // Atualiza último uso
                $sql = "UPDATE dispositivos_autorizados SET ultimo_uso = NOW() WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$dispositivo['id']]);
            } else {
                // Insere novo dispositivo
                $sql = "INSERT INTO dispositivos_autorizados 
                        (usuario_id, device_id, device_nome, ip_address, user_agent, tipo_dispositivo, ativo)
                        VALUES (?, ?, ?, ?, ?, ?, 1)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usuario_id, $device_id, $device_nome, $ip, $user_agent, $tipo]);
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao registrar dispositivo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista dispositivos de um usuário
     */
    public static function obterDispositivosUsuario($usuario_id) {
        $pdo = Database::getConnection();
        
        $sql = "SELECT * FROM dispositivos_autorizados WHERE usuario_id = ? ORDER BY ultimo_uso DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Autoriza um dispositivo para um usuário (por admin)
     */
    public static function autorizarDispositivo($usuario_id, $device_id, $admin_id) {
        $pdo = Database::getConnection();
        
        try {
            $sql = "UPDATE dispositivos_autorizados SET autorizado_por_admin = NOW() WHERE usuario_id = ? AND device_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id, $device_id]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao autorizar dispositivo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Desautoriza um dispositivo
     */
    public static function desautorizarDispositivo($usuario_id, $device_id, $admin_id) {
        $pdo = Database::getConnection();
        
        try {
            $sql = "UPDATE dispositivos_autorizados SET ativo = 0 WHERE usuario_id = ? AND device_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id, $device_id]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao desautorizar dispositivo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Valida se um dispositivo está autorizado para bater ponto
     */
    public static function validarDispositivoAutorizado($usuario_id, $device_id, $modo_multiplas = false) {
        $pdo = Database::getConnection();
        
        if ($modo_multiplas) {
            // Modo permissivo: qualquer dispositivo é permitido
            return true;
        }
        
        // Modo restrito: verifica se dispositivo está autorizado
        $sql = "SELECT COUNT(*) FROM dispositivos_autorizados 
                WHERE usuario_id = ? AND device_id = ? AND ativo = 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $device_id]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Atualiza último uso de um dispositivo
     */
    public static function atualizarUltimoUso($usuario_id, $device_id) {
        $pdo = Database::getConnection();
        
        try {
            $sql = "UPDATE dispositivos_autorizados SET ultimo_uso = NOW() WHERE usuario_id = ? AND device_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id, $device_id]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar último uso: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtém um dispositivo específico
     */
    public static function obterDispositivo($usuario_id, $device_id) {
        $pdo = Database::getConnection();
        
        $sql = "SELECT * FROM dispositivos_autorizados WHERE usuario_id = ? AND device_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $device_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
