<?php
/**
 * Arquivo centralizado de conexão com banco de dados
 * Este arquivo deve ser usado por todo o projeto para manter consistência
 */

// Configurações do banco de dados
define('DB_HOST', 'gerencialparoq.mysql.dbaas.com.br');
define('DB_NAME', 'gerencialparoq');
define('DB_USER', 'gerencialparoq');
define('DB_PASS', 'Dsg#1806');
define('DB_CHARSET', 'utf8mb4');

// Configurações de sessão
define('SESSION_TIMEOUT', 3600); // 1 hora em segundos

// Configurações de segurança
define('PASSWORD_MIN_LENGTH', 4);
define('USERNAME_MIN_LENGTH', 3);

// Configurações dos módulos
define('MODULES', [
    'bazar' => 'Bazar',
    'lojinha' => 'Lojinha de Produtos Católicos',
    'cafe' => 'Café e Lanches',
    'pastoral-social' => 'Pastoral Social',
    'obras' => 'Controle de Obras',
    'contas-pagas' => 'Controle de Contas Pagas',
    'membros' => 'Cadastro de Membros',
    'catequese' => 'Catequese',
    'atividades' => 'Atividades em Execução',
    'secretaria' => 'Secretaria',
    'compras' => 'Compras e Pedidos Entregues',
    'eventos' => 'Cadastro de Eventos e Atividades'
]);

/**
 * Classe para gerenciar conexões com banco de dados
 */
class DatabaseConnection {
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
        } catch(PDOException $e) {
            error_log("Erro de conexão com banco de dados: " . $e->getMessage());
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
            error_log("Erro na query: " . $e->getMessage());
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
function getConnection() {
    return DatabaseConnection::getInstance()->getConnection();
}

/**
 * Função de conveniência para obter a instância da classe
 */
function getDatabase() {
    return DatabaseConnection::getInstance();
}

/**
 * Função para verificar se o usuário está logado
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Função para verificar timeout da sessão
 */
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Função para redirecionar se não estiver logado
 */
function requireLogin() {
    if (!isLoggedIn() || !checkSessionTimeout()) {
        header('Location: ../../index.html');
        exit;
    }
}

/**
 * Função para fazer logout
 */
function logout() {
    session_unset();
    session_destroy();
    header('Location: ../../index.html');
    exit;
}

/**
 * Função para testar a conexão
 */
function testConnection() {
    try {
        $db = DatabaseConnection::getInstance();
        $result = $db->fetchOne("SELECT 1 as test");
        return $result['test'] == 1;
    } catch(Exception $e) {
        return false;
    }
}
?>
