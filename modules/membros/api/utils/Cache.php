<?php
/**
 * Sistema de Cache Server-Side
 * Módulo de Membros - Sistema de Gestão Paroquial
 * 
 * Cache baseado em arquivos com suporte a TTL (Time To Live)
 */

class Cache {
    private $cacheDir;
    private $defaultTTL = 300; // 5 minutos padrão
    
    public function __construct($cacheDir = null) {
        // Diretório padrão para cache
        $this->cacheDir = $cacheDir ?? __DIR__ . '/../../cache/';
        
        // Normalizar caminho
        $this->cacheDir = rtrim($this->cacheDir, '/\\') . DIRECTORY_SEPARATOR;
        
        // Criar diretório se não existir
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
        
        // Verificar se diretório existe e é gravável
        if (!is_dir($this->cacheDir) || !is_writable($this->cacheDir)) {
            error_log("Cache: Diretório não é gravável: " . $this->cacheDir);
        }
    }
    
    /**
     * Obter valor do cache
     * 
     * @param string $key Chave do cache
     * @return mixed|null Valor do cache ou null se não existir/expirado
     */
    public function get($key) {
        try {
            $filePath = $this->getFilePath($key);
            
            // Verificar se arquivo existe
            if (!file_exists($filePath) || !is_readable($filePath)) {
                return null;
            }
            
            // Ler conteúdo do arquivo
            $content = @file_get_contents($filePath);
            if ($content === false || empty($content)) {
                return null;
            }
            
            $data = @json_decode($content, true);
            
            // Verificar se JSON foi decodificado corretamente
            if ($data === null || json_last_error() !== JSON_ERROR_NONE) {
                // Arquivo corrompido - remover
                @unlink($filePath);
                return null;
            }
            
            // Verificar se tem estrutura esperada
            if (!isset($data['value']) || !isset($data['expires_at'])) {
                @unlink($filePath);
                return null;
            }
            
            // Verificar se expirou
            if (time() > $data['expires_at']) {
                // Cache expirado - remover arquivo
                @unlink($filePath);
                return null;
            }
            
            return $data['value'];
        } catch (Exception $e) {
            error_log("Cache get error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Armazenar valor no cache
     * 
     * @param string $key Chave do cache
     * @param mixed $value Valor a armazenar
     * @param int|null $ttl Tempo de vida em segundos (null = usar padrão)
     * @return bool Sucesso da operação
     */
    public function set($key, $value, $ttl = null) {
        // Verificar se diretório é gravável
        if (!is_dir($this->cacheDir) || !is_writable($this->cacheDir)) {
            error_log("Cache: Não é possível escrever no diretório: " . $this->cacheDir);
            return false;
        }
        
        $filePath = $this->getFilePath($key);
        
        $ttl = $ttl ?? $this->defaultTTL;
        $expiresAt = time() + $ttl;
        
        $data = [
            'value' => $value,
            'expires_at' => $expiresAt,
            'created_at' => time(),
            'ttl' => $ttl
        ];
        
        $content = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($content === false) {
            error_log("Cache: Erro ao codificar JSON para chave: " . $key);
            return false;
        }
        
        $result = @file_put_contents($filePath, $content);
        return $result !== false;
    }
    
    /**
     * Remover item do cache
     * 
     * @param string $key Chave do cache
     * @return bool Sucesso da operação
     */
    public function delete($key) {
        $filePath = $this->getFilePath($key);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true;
    }
    
    /**
     * Limpar todo o cache
     * 
     * @return int Número de arquivos removidos
     */
    public function clear() {
        $files = glob($this->cacheDir . '*.cache');
        $count = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Limpar cache expirado
     * 
     * @return int Número de arquivos removidos
     */
    public function cleanExpired() {
        $files = glob($this->cacheDir . '*.cache');
        $count = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $content = file_get_contents($file);
                $data = json_decode($content, true);
                
                if ($data !== null && isset($data['expires_at']) && time() > $data['expires_at']) {
                    unlink($file);
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Verificar se chave existe no cache
     * 
     * @param string $key Chave do cache
     * @return bool True se existe e não expirou
     */
    public function has($key) {
        return $this->get($key) !== null;
    }
    
    /**
     * Obter estatísticas do cache
     * 
     * @return array Estatísticas
     */
    public function getStats() {
        $files = glob($this->cacheDir . '*.cache');
        $total = count($files);
        $expired = 0;
        $totalSize = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $content = file_get_contents($file);
            $data = json_decode($content, true);
            
            if ($data !== null && isset($data['expires_at']) && time() > $data['expires_at']) {
                $expired++;
            }
        }
        
        return [
            'total_files' => $total,
            'expired_files' => $expired,
            'valid_files' => $total - $expired,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'cache_dir' => $this->cacheDir
        ];
    }
    
    /**
     * Gerar chave de cache a partir de parâmetros
     * 
     * @param string $prefix Prefixo da chave
     * @param array $params Parâmetros para gerar chave única
     * @return string Chave gerada
     */
    public function generateKey($prefix, $params = []) {
        $key = $prefix;
        
        if (!empty($params)) {
            ksort($params); // Ordenar para consistência
            $key .= '_' . md5(json_encode($params));
        }
        
        return $key;
    }
    
    /**
     * Obter diretório de cache
     * 
     * @return string Diretório de cache
     */
    public function getCacheDir() {
        return $this->cacheDir;
    }
    
    /**
     * Obter caminho do arquivo de cache
     * 
     * @param string $key Chave do cache
     * @return string Caminho completo do arquivo
     */
    private function getFilePath($key) {
        // Sanitizar chave para nome de arquivo seguro
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->cacheDir . $safeKey . '.cache';
    }
    
    /**
     * Formatar bytes para formato legível
     * 
     * @param int $bytes Bytes
     * @return string Formato legível
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Obter ou executar callback com cache
     * 
     * @param string $key Chave do cache
     * @param callable $callback Função a executar se cache não existir
     * @param int|null $ttl Tempo de vida em segundos
     * @return mixed Resultado do cache ou callback
     */
    public function remember($key, $callback, $ttl = null) {
        $cached = $this->get($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        // Executar callback
        $value = call_user_func($callback);
        
        // Armazenar no cache
        $this->set($key, $value, $ttl);
        
        return $value;
    }
}

