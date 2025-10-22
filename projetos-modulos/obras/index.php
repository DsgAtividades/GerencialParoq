<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !in_array($_SERVER['REQUEST_URI'], ['/login.php', '/auth.php'])) {
    header('Location: login.php');
    exit;
}

// Basic routing
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = ['dashboard', 'usuarios', 'usuarios_novo', 'usuarios_editar', 'relatorios', 'obras', 'cadastro_obra', 'lista_obras', 'detalhes_obra', 'analytics'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// Include required files
require_once 'config/database.php';
require_once 'includes/db_connection.php';
require_once 'includes/auth_check.php';
include 'includes/header.php';

// Load the requested page
if (file_exists("pages/{$page}.php")) {
    include "pages/{$page}.php";
} else {
    echo '<div class="alert alert-danger">Página não encontrada.</div>';
}

include 'includes/footer.php';
