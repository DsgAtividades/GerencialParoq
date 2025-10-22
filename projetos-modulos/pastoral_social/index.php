<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !in_array($_SERVER['REQUEST_URI'], ['/login.php', '/auth.php'])) {
    header('Location: login.php');
    exit;
}

// Basic routing
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = ['dashboard', 'usuarios', 'usuarios_novo', 'usuarios_editar', 'excluir_usuario', 'relatorios', 'estoque', 'equipe', 'editar_membro', 'calendario', 'equipe_pastoral', 'equipe_pastoral_novo', 'equipe_pastoral_editar', 'excluir_membro'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

include 'includes/header.php';
include "pages/{$page}.php";
include 'includes/footer.php';
?>
