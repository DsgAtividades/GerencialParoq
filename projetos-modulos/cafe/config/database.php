<?php
/**
 * Arquivo de configuração de banco de dados do módulo Cafe
 * 
 * OPÇÃO 1: Usar conexão centralizada do projeto (padrão)
 * OPÇÃO 2: Usar banco de dados personalizado (descomente a seção abaixo)
 */

/* ============================================
// OPÇÃO 1: CONEXÃO CENTRALIZADA (ATIVA)
// ============================================
// Incluir o arquivo principal de conexão da raiz
*/ 

require_once __DIR__ . '/../../../config/database_connection.php';

/**
 * Classe Database para compatibilidade com código existente
 * Usa a conexão centralizada do projeto principal
 */

 class Database {
    private $conn;

    public function getConnection() {
        if ($this->conn === null) {
            // Usa a conexão centralizada do projeto principal
            $this->conn = DatabaseConnection::getInstance()->getConnection();
        }
        return $this->conn;
    }
}

// ============================================
// OPÇÃO 2: BANCO DE DADOS PERSONALIZADO
// ============================================
// Para usar um banco de dados específico, comente a seção acima (OPÇÃO 1)
// e descomente a seção abaixo, configurando suas credenciais:


/*
// Configurações do banco de dados personalizado (paroquia n.s praga)
$db_host = 'dbjuninapnsp.mysql.dbaas.com.br';   // Host do banco de dados
$db_name = 'dbjuninapnsp';      // Nome do banco de dados
$db_user = 'dbjuninapnsp';             // Usuário do banco de dados
$db_pass = 'NJFEFkEp825j@#';               // Senha do banco de dados
$db_charset = 'utf8mb4';          // Charset (recomendado: utf8mb4)

/**
 * Classe Database com conexão personalizada
 */

 /*
class Database {
    private $conn;
    private $host;
    private $db_name;
    private $username;
    private $password;

    public function __construct() {
        $this->host = $db_host;
        $this->db_name = $db_name;
        $this->username = $db_user;
        $this->password = $db_pass;
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $db_charset,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            echo "Erro na conexão: " . $e->getMessage();
        }

        return $this->conn;
    }
}
*/
?>
