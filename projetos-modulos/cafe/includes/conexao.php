<?php
/**
 * Arquivo de conexão do módulo Cafe
 * Usa o arquivo principal de conexão da raiz do projeto
 */

// Incluir o arquivo principal de conexão da raiz
require_once __DIR__ . '/../../../config/database_connection.php';

// Criar variável $pdo para compatibilidade com código existente
// Usa a conexão centralizada do projeto principal
try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch(Exception $e) {
    die("Erro na conexão: " . $e->getMessage());
}
