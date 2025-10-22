<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM system_users WHERE username = ? AND ativo = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nome_completo'] = $user['nome_completo'];
            $_SESSION['tipo_acesso'] = $user['tipo_acesso'];
            
            header('Location: index.php');
            exit;
        }

        header('Location: login.php?error=1');
        exit;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header('Location: login.php?error=2');
        exit;
    }
}

// Logout
if (isset($_GET['logout'])) {
    // Destruir apenas as variáveis de sessão da pastoral social
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['nome_completo']);
    unset($_SESSION['tipo_acesso']);
    header('Location: login.php');
    exit;
}
