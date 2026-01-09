<?php
/**
 * Arquivo de configuração de banco de dados do módulo Cafe
 * Usa o arquivo principal de conexão da raiz do projeto
 */

// Incluir o arquivo principal de conexão da raiz
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
?>
