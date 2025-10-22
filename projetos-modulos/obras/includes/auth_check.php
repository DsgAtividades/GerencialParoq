<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Se não estiver logado, redirecionar para a página de login
    header('Location: /gerencialParoquia/projetos-modulos/obras/login.php');
    exit;
}

// Verificar se a sessão não expirou
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    // Se passou mais de 1 hora, fazer logout
    session_destroy();
    header('Location: /gerencialParoquia/projetos-modulos/obras/login.php?error=session_expired');
    exit;
}

// Atualizar o timestamp da última atividade
$_SESSION['last_activity'] = time();
?>
