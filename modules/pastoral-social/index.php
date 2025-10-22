<?php
session_start();

// Verificar se o usuário está logado no sistema principal
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.html');
    exit;
}

// Mapear as variáveis de sessão do sistema principal para o sistema de Pastoral Social
if (!isset($_SESSION['nome_completo'])) {
    $_SESSION['nome_completo'] = $_SESSION['username'] ?? 'Usuário';
}

if (!isset($_SESSION['tipo_acesso'])) {
    $_SESSION['tipo_acesso'] = 'Administrador'; // Padrão para usuários do sistema principal
}

// Redirecionamento para o sistema completo de Pastoral Social
header('Location: ../../projetos-modulos/pastoral_social/');
exit;
?>
