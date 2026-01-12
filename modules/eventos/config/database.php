<?php
/**
 * Configuração do banco de dados para o módulo Eventos
 * Utiliza as mesmas tabelas do módulo Membros
 */

// Usar a conexão centralizada do sistema
// Calcular o caminho do diretório raiz do projeto
$rootDir = dirname(dirname(dirname(__DIR__)));
$dbConnectionPath = $rootDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database_connection.php';

// Normalizar o caminho para o sistema operacional
$dbConnectionPath = realpath($dbConnectionPath);

if (!$dbConnectionPath || !file_exists($dbConnectionPath)) {
    // Tentar caminho relativo como fallback
    $dbConnectionPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database_connection.php';
    $dbConnectionPath = realpath($dbConnectionPath);
}

if (!$dbConnectionPath || !file_exists($dbConnectionPath)) {
    error_log("Erro: database_connection.php não encontrado. Tentou: " . $rootDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database_connection.php');
    throw new Exception("Arquivo database_connection.php não encontrado. Verifique o caminho do arquivo.");
}

require_once $dbConnectionPath;

/**
 * Classe específica para operações do módulo Eventos
 */
class EventosDatabase {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance();
        $this->ensureConnection();
    }
    
    /**
     * Garante que a conexão está ativa
     */
    private function ensureConnection() {
        try {
            $this->db->getConnection()->query('SELECT 1');
        } catch (Exception $e) {
            // Reconectar se necessário
            $this->db = DatabaseConnection::getInstance();
        }
    }
    
    /**
     * Obtém a conexão PDO
     */
    public function getConnection() {
        return $this->db->getConnection();
    }
    
    /**
     * Executa uma query preparada
     */
    public function query($sql, $params = []) {
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            // Se erro de conexão, tentar reconectar
            if (strpos($e->getMessage(), 'gone away') !== false || 
                strpos($e->getMessage(), 'Lost connection') !== false) {
                $this->ensureConnection();
                return $this->db->query($sql, $params);
            }
            throw $e;
        }
    }
    
    /**
     * Executa uma query e retorna todos os resultados
     */
    public function fetchAll($sql, $params = []) {
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Executa uma query e retorna um único resultado
     */
    public function fetchOne($sql, $params = []) {
        return $this->db->fetchOne($sql, $params);
    }
    
    /**
     * Executa uma query e retorna o número de linhas afetadas
     */
    public function execute($sql, $params = []) {
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Retorna o último ID inserido
     */
    public function lastInsertId() {
        return $this->db->lastInsertId();
    }
    
    /**
     * Inicia uma transação
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Confirma uma transação
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Desfaz uma transação
     */
    public function rollback() {
        return $this->db->rollback();
    }
    
    /**
     * Prepara uma query SQL
     */
    public function prepare($sql) {
        return $this->db->getConnection()->prepare($sql);
    }
    
    /**
     * Testa a conexão com o banco
     */
    public function testConnection() {
        try {
            $result = $this->fetchOne("SELECT 1 as test");
            return $result['test'] == 1;
        } catch(Exception $e) {
            return false;
        }
    }
}

