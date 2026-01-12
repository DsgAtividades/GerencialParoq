<?php
/**
 * Arquivo de conexão do módulo Cafe
 * 
 * OPÇÃO 1: Usar conexão centralizada do projeto (padrão)
 * OPÇÃO 2: Usar banco de dados personalizado (descomente a seção abaixo)
 */

// ============================================
// OPÇÃO 1: CONEXÃO CENTRALIZADA (ATIVA)
// ============================================
//Incluir o arquivo principal de conexão da raiz
require_once __DIR__ . '/../../../config/database_connection.php';

// Criar variável $pdo para compatibilidade com código existente
// Usa a conexão centralizada do projeto principal
try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch(Exception $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// ============================================
// OPÇÃO 2: BANCO DE DADOS PERSONALIZADO
// ============================================
// Para usar um banco de dados específico, comente a seção acima (OPÇÃO 1)
// e descomente a seção abaixo, configurando suas credenciais:

/*
// Configurações do banco de dados personalizado
$db_host = 'dbjuninapnsp.mysql.dbaas.com.br';           // Host do banco de dados
$db_name = 'dbjuninapnsp';      // Nome do banco de dados
$db_user = 'dbjuninapnsp';             // Usuário do banco de dados
$db_pass = 'NJFEFkEp825j@#';               // Senha do banco de dados
$db_charset = 'utf8mb4';          // Charset (recomendado: utf8mb4)

// Criar conexão PDO personalizada
try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
*/
