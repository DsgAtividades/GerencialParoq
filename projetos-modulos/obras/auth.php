<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM obras_system_users WHERE username = ? AND ativo = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug - Salvar informações em um arquivo de log
        error_log("=== INICIO DEBUG LOGIN ===");
        error_log("Tentativa de login - Usuario: " . $username);
        error_log("Senha fornecida: " . $password);
        error_log("Query SQL: SELECT * FROM obras_system_users WHERE username = '" . $username . "' AND ativo = 1");
        error_log("Usuario encontrado: " . ($user ? 'Sim' : 'Nao'));
        if ($user) {
            error_log("ID do usuario: " . $user['id']);
            error_log("Nome completo: " . $user['nome_completo']);
            error_log("Tipo acesso: " . $user['tipo_acesso']);
            error_log("Ativo: " . ($user['ativo'] ? 'Sim' : 'Nao'));
            error_log("Hash da senha armazenada: " . $user['password']);
            error_log("Verificacao de senha: " . (password_verify($password, $user['password']) ? 'Correta' : 'Incorreta'));
        }
        error_log("=== FIM DEBUG LOGIN ===");

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
    session_destroy();
    header('Location: login.php');
    exit;
}
