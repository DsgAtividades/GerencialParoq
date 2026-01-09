<?php
/**
 * Configuração de banco de dados do módulo Café
 * Usa a conexão centralizada do sistema
 */

require_once __DIR__ . '/../../../config/database_connection.php';

// Função helper para obter conexão
function getCafeConnection() {
    return getConnection();
}

// Função helper para obter instância do Database
function getCafeDatabase() {
    return getDatabase();
}

?>
