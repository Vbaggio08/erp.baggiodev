<?php

class ImageOptimizer {

    /**
     * Helper para obter URL base do projeto
     */
    private static function getBaseUrl() {
        $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $path = substr($script, 0, strrpos($script, '/index.php'));
        if (empty($path) || $path === '') {
            $path = '';
        }
        return $protocolo . "://" . $_SERVER['HTTP_HOST'] . $path . "/";
    }
    
    /**
     * Retorna URL optimizada da imagem com redimensionamento em cache
     * @param string $filename - Nome do arquivo na pasta assets/uploads/
     * @param int $max_width - Largura máxima em pixels (padrão 530px = 14cm em 96dpi)
     * @param int $max_height - Altura máxima em pixels
     * @return string - URL da imagem otimizada
     */
    public static function getOptimizedImageUrl($filename, $max_width = 530, $max_height = 530) {
        if (empty($filename)) {
            return '';
        }

        $base_path = __DIR__ . '/../../assets/uploads/';
        $cache_path = __DIR__ . '/../../assets/uploads/.cache/';
        
        // Criar pasta de cache se não existir
        if (!is_dir($cache_path)) {
            mkdir($cache_path, 0755, true);
        }

        $original_file = $base_path . $filename;
        
        // Se arquivo não existe, retorna vazio
        if (!file_exists($original_file)) {
            return '';
        }

        // Nome do cache
        $cache_file = $cache_path . md5($filename . $max_width . $max_height) . '.jpg';
        
        // Se já existe em cache e é mais novo que original, usa cache
        if (file_exists($cache_file) && filemtime($cache_file) > filemtime($original_file)) {
            return self::getBaseUrl() . "assets/uploads/.cache/" . basename($cache_file);
        }

        // Carregar imagem original
        $ext = strtolower(pathinfo($original_file, PATHINFO_EXTENSION));
        
        if ($ext === 'pdf') {
            // PDFs não precisam de otimização
            return self::getBaseUrl() . "assets/uploads/" . $filename;
        }

        // Verificar se GD está disponível
        if (!extension_loaded('gd')) {
            // GD não disponível, retorna URL original
            return self::getBaseUrl() . "assets/uploads/" . $filename;
        }

        // Carregar imagem conforme extensão
        try {
            if ($ext === 'jpg' || $ext === 'jpeg') {
                $image = @imagecreatefromjpeg($original_file);
            } elseif ($ext === 'png') {
                if (!function_exists('imagecreatefrompng')) {
                    throw new Exception('imagecreatefrompng not available');
                }
                $image = @imagecreatefrompng($original_file);
            } elseif ($ext === 'gif') {
                if (!function_exists('imagecreatefromgif')) {
                    throw new Exception('imagecreatefromgif not available');
                }
                $image = @imagecreatefromgif($original_file);
            } elseif ($ext === 'webp') {
                if (!function_exists('imagecreatefromwebp')) {
                    throw new Exception('imagecreatefromwebp not available');
                }
                $image = @imagecreatefromwebp($original_file);
            } else {
                // Extensão não suportada, retorna URL original
                throw new Exception('Unsupported file type');
            }
        } catch (Exception $e) {
            // Se falhar nas funções de imagem, retorna URL original
            return self::getBaseUrl() . "assets/uploads/" . $filename;
        }

        if (!$image) {
            // Falha ao criar imagem, retorna URL original
            return self::getBaseUrl() . "assets/uploads/" . $filename;
        }

        // Calcular novas dimensões mantendo proporção
        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = $width / $height;

        if ($width > $max_width || $height > $max_height) {
            if ($ratio > 1) {
                // Imagem mais larga
                $new_width = $max_width;
                $new_height = $max_width / $ratio;
            } else {
                // Imagem mais alta
                $new_height = $max_height;
                $new_width = $max_height * $ratio;
            }
        } else {
            $new_width = $width;
            $new_height = $height;
        }

        // Criar imagem redimensionada
        $image_resized = imagecreatetruecolor($new_width, $new_height);
        
        // Preservar transparência para PNG
        if ($ext === 'png' || $ext === 'gif') {
            imagealphablending($image_resized, false);
            imagesavealpha($image_resized, true);
            $transparent = imagecolorallocatealpha($image_resized, 255, 255, 255, 127);
            imagefilledrectangle($image_resized, 0, 0, $new_width, $new_height, $transparent);
        }

        imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        // Salvar como JPG otimizado no cache
        imagejpeg($image_resized, $cache_file, 85);
        imagedestroy($image);
        imagedestroy($image_resized);

        // Retornar URL do cache
        return self::getBaseUrl() . "assets/uploads/.cache/" . basename($cache_file);
    }
}
?>
