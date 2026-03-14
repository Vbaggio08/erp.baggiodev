<?php
require_once __DIR__ . '/../config/database.php';

class Geolocation {
    
    /**
     * Obtém coordenadas da empresa
     */
    public static function obterCoordenadaEmpresa($empresa_id = 1) {
        $pdo = Database::getConnection();
        
        $sql = "SELECT * FROM geolocation_empresa WHERE empresa_id = ? AND ativo = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$empresa_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcula distância entre dois pontos usando Haversine formula
     * Retorna distância em metros
     */
    public static function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
        $R = 6371000; // Raio da Terra em metros
        
        // Converte para radianos
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        // Fórmula de Haversine
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distancia = $R * $c;
        
        return round($distancia, 2); // Retorna em metros com 2 casas decimais
    }
    
    /**
     * Valida se usuário está dentro do raio permitido
     */
    public static function validarRaioPermitido($usuario_lat, $usuario_lng, $empresa_id = 1, $raio_config_metros = null) {
        $local = self::obterCoordenadaEmpresa($empresa_id);
        
        if (!$local) {
            // Se não tem geolocalização cadastrada, permite
            return [
                'permitido' => true,
                'motivo' => 'Não há geolocalização cadastrada'
            ];
        }
        
        $raio = $raio_config_metros ?? $local['raio_metros'] ?? 500;
        $distancia = self::calcularDistancia($usuario_lat, $usuario_lng, $local['latitude'], $local['longitude']);
        
        return [
            'permitido' => $distancia <= $raio,
            'distancia_metros' => $distancia,
            'raio_permitido' => $raio,
            'dentro_raio' => $distancia <= $raio,
            'endereco_empresa' => $local['endereco'] ?? null
        ];
    }
    
    /**
     * Registra tentativa de ponto fora da zona
     */
    public static function registrarTentativaFora($usuario_id, $apontamento_id, $distancia_metros) {
        $pdo = Database::getConnection();
        
        try {
            // Registra como observação no apontamento
            $sql = "UPDATE apontamentos_ponto SET observacao = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "Ponto batido FORA da zona permitida. Distância: {$distancia_metros}m",
                $apontamento_id
            ]);
            
            // Registra também na auditoria
            AuditoriaAlteracao::registrarAlteracao(
                $apontamento_id,
                $usuario_id,
                'tentativa_fora_zona',
                json_encode(['distancia_metros' => $distancia_metros]),
                json_encode(['distancia_metros' => $distancia_metros]),
                "Ponto batido {$distancia_metros}m fora da zona permitida"
            );
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao registrar tentativa fora da zona: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cadastra ou atualiza geolocalização de uma filial
     */
    public static function cadastrarCoordenada($empresa_id, $latitude, $longitude, $endereco, $raio_metros = 500) {
        $pdo = Database::getConnection();
        
        try {
            // Verifica se já existe
            $sql = "SELECT id FROM geolocation_empresa WHERE empresa_id = ? AND ativo = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$empresa_id]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existe) {
                // Atualiza
                $sql = "UPDATE geolocation_empresa SET latitude = ?, longitude = ?, endereco = ?, raio_metros = ? 
                        WHERE empresa_id = ? AND ativo = 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$latitude, $longitude, $endereco, $raio_metros, $empresa_id]);
            } else {
                // Insere novo
                $sql = "INSERT INTO geolocation_empresa (empresa_id, latitude, longitude, endereco, raio_metros, ativo) 
                        VALUES (?, ?, ?, ?, ?, 1)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$empresa_id, $latitude, $longitude, $endereco, $raio_metros]);
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao cadastrar geolocalização: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Listar todas as coordenadas cadastradas
     */
    public static function listarCoordenadas() {
        $pdo = Database::getConnection();
        
        $sql = "SELECT * FROM geolocation_empresa WHERE ativo = 1 ORDER BY criado_em DESC";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Converte string lat,lng para array
     */
    public static function parseGeoString($geo_string) {
        if (!$geo_string) {
            return null;
        }
        
        $partes = explode(',', $geo_string);
        if (count($partes) !== 2) {
            return null;
        }
        
        return [
            'latitude' => floatval(trim($partes[0])),
            'longitude' => floatval(trim($partes[1]))
        ];
    }
}
?>
