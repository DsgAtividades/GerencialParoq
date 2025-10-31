<?php
/**
 * Arquivo de conexão com banco de dados - Módulo Membros
 * Este arquivo é específico para o módulo membros e não afeta outros módulos.
 */

// Carregar configurações
require_once __DIR__ . '/config.php';

/**
 * Classe para gerenciar conexões com banco de dados - Módulo Membros
 */
class MembrosDatabaseConnection {
    private static $instance = null;
    private $pdo = null;
    
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Obtém a instância única da conexão (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Estabelece a conexão com o banco de dados
     */
    private function connect() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false
                ]
            );
            
            error_log("Módulo Membros: Conexão estabelecida com " . DB_HOST);
        } catch(PDOException $e) {
            error_log("Erro de conexão no módulo membros: " . $e->getMessage());
            throw new Exception("Erro de conexão com banco de dados: " . $e->getMessage());
        }
    }
    
    /**
     * Retorna a instância PDO
     */
    public function getConnection() {
        if ($this->pdo === null) {
            $this->connect();
        }
        return $this->pdo;
    }
    
    /**
     * Executa uma query preparada
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Erro na query (módulo membros): " . $e->getMessage());
            throw new Exception("Erro na query: " . $e->getMessage());
        }
    }
    
    /**
     * Executa uma query e retorna todos os resultados
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Executa uma query e retorna um único resultado
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Executa uma query e retorna o número de linhas afetadas
     */
    public function execute($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Retorna o último ID inserido
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Inicia uma transação
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Confirma uma transação
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Desfaz uma transação
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
}

/**
 * Função de conveniência para obter a conexão
 */
function getMembrosConnection() {
    return MembrosDatabaseConnection::getInstance()->getConnection();
}

/**
 * Função de conveniência para obter a instância da classe
 */
function getMembrosDatabase() {
    return MembrosDatabaseConnection::getInstance();
}

/**
 * Função para testar a conexão
 */
function testMembrosConnection() {
    try {
        $db = MembrosDatabaseConnection::getInstance();
        $result = $db->fetchOne("SELECT 1 as test");
        return $result['test'] == 1;
    } catch(Exception $e) {
        return false;
    }
}
?>


