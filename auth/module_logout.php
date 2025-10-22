<?php
session_start();

// Limpar apenas as variáveis de sessão do módulo
unset($_SESSION['module_logged_in']);
unset($_SESSION['module_access']);
unset($_SESSION['module_login_time']);
unset($_SESSION['module_user_id']);
unset($_SESSION['module_username']);

// NÃO destruir a sessão global - apenas limpar as variáveis do módulo
// A sessão global (logged_in, username, etc.) permanece ativa

// Redirecionar para o dashboard
header('Location: ../dashboard.html');
exit;
?>
