<?php
/**
 * Configurações gerais do módulo Café
 */

// Incluir configuração de banco
require_once __DIR__ . '/database.php';

// Configurações do módulo
define('CAFE_MODULE_NAME', 'Café Paroquial');
define('CAFE_MODULE_DESCRIPTION', 'Sistema de vendas e controle de estoque do café');

// Configurações de upload (se necessário no futuro)
define('CAFE_UPLOAD_DIR', __DIR__ . '/../uploads/');
define('CAFE_MAX_UPLOAD_SIZE', 5242880); // 5MB

?>
