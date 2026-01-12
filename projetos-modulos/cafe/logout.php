<?php
require_once 'includes/funcoes.php';

// Armazenar o nome do usuário antes de destruir a sessão
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir o cookie da sessão se existir
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destruir a sessão
session_destroy();

// Iniciar nova sessão para a mensagem de feedback
session_start();
exibirAlerta("Até logo, {$nome_usuario}! Você foi desconectado com sucesso.");

// Redirecionar para a página de login
header("Location: login.php");
exit();
