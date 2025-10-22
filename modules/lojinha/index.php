<?php
session_start();
require_once '../../config/database.php';

// Verificar se o usuário está logado no módulo lojinha
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true || $_SESSION['module_access'] !== 'lojinha') {
    header('Location: ../../module_login.html?module=lojinha');
    exit;
}

// Redirecionar para o projeto em projetos-modulos
header('Location: ../../projetos-modulos/lojinha/');
exit;
?>
