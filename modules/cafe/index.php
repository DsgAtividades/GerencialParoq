<?php
session_start();
require_once '../../config/database.php';

// Verificar se o usuário está logado no módulo específico
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    header('Location: ../../module_login.html?module=cafe');
    exit;
}

// Verificar se o usuário tem acesso a este módulo específico
if (!isset($_SESSION['module_access']) || $_SESSION['module_access'] !== 'cafe') {
    header('Location: ../../module_login.html?module=cafe');
    exit;
}

// Verificar timeout da sessão do módulo (2 horas)
if (isset($_SESSION['module_login_time']) && (time() - $_SESSION['module_login_time'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: ../../module_login.html?module=cafe');
    exit;
}

// Redirecionar para o projeto completo
header('Location: ../../projetos-modulos/cafe/index.php');
exit;
?>
