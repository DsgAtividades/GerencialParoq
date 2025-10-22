<?php
session_start();

// Verificar se o usuário está logado no módulo
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    header('Location: ../../module_login.html?module=obras');
    exit;
}

if (!isset($_SESSION['module_access']) || $_SESSION['module_access'] !== 'obras') {
    header('Location: ../../module_login.html?module=obras');
    exit;
}

// Redirecionar para o projeto de obras
header('Location: ../../projetos-modulos/obras/index.php');
exit;
?>
