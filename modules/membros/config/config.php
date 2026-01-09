<?php
/**
 * Configuração de Ambiente - Módulo Membros
 * 
 * Este arquivo gerencia as configurações de banco de dados para o módulo membros.
 * Permite alternar facilmente entre ambiente local (desenvolvimento) e remoto (produção).
 */

// Ambiente de desenvolvimento
// Opções: 'local' ou 'production'
define('MEMBROS_ENVIRONMENT', 'production');

// Configurações para ambiente LOCAL
define('MEMBROS_DB_HOST_LOCAL', 'localhost');
define('MEMBROS_DB_NAME_LOCAL', 'gerencialparoq');
define('MEMBROS_DB_USER_LOCAL', 'root');
define('MEMBROS_DB_PASS_LOCAL', '');

// Configurações para ambiente REMOTO (Locaweb)
define('MEMBROS_DB_HOST_REMOTE', 'gerencialparoq.mysql.dbaas.com.br');
define('MEMBROS_DB_NAME_REMOTE', 'gerencialparoq');
define('MEMBROS_DB_USER_REMOTE', 'gerencialparoq');
define('MEMBROS_DB_PASS_REMOTE', 'Dsg#1806');

// Aplicar configurações baseado no ambiente
if (MEMBROS_ENVIRONMENT === 'local') {
    define('DB_HOST', MEMBROS_DB_HOST_LOCAL);
    define('DB_NAME', MEMBROS_DB_NAME_LOCAL);
    define('DB_USER', MEMBROS_DB_USER_LOCAL);
    define('DB_PASS', MEMBROS_DB_PASS_LOCAL);
    
    // Log para debug
    error_log("Módulo Membros: Usando ambiente LOCAL - host: " . DB_HOST);
} else {
    define('DB_HOST', MEMBROS_DB_HOST_REMOTE);
    define('DB_NAME', MEMBROS_DB_NAME_REMOTE);
    define('DB_USER', MEMBROS_DB_USER_REMOTE);
    define('DB_PASS', MEMBROS_DB_PASS_REMOTE);
    
    // Log para debug
    error_log("Módulo Membros: Usando ambiente REMOTO - host: " . DB_HOST);
}

define('DB_CHARSET', 'utf8mb4');

// Configurações de sessão
define('SESSION_TIMEOUT', 3600); // 1 hora em segundos

// Configurações de segurança
define('PASSWORD_MIN_LENGTH', 4);
define('USERNAME_MIN_LENGTH', 3);
?>


