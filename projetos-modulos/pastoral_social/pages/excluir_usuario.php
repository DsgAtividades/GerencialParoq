<?php
require_once __DIR__ . '/../config/database.php';

// Verifica se o usuário está logado e tem permissão
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_acesso'] != 'Administrador') {
    $_SESSION['erro'] = "Você não tem permissão para realizar esta ação.";
    header('Location: index.php?page=usuarios');
    exit();
}

// Verifica se foi fornecido um ID
if (!isset($_GET['id'])) {
    $_SESSION['erro'] = "ID do usuário não fornecido.";
    header('Location: index.php?page=usuarios');
    exit();
}

$id = $_GET['id'];

try {
    // Verifica se o usuário existe antes de excluir
    $stmt = $pdo->prepare("SELECT id, nome FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        $_SESSION['erro'] = "Usuário não encontrado.";
        header('Location: index.php?page=usuarios');
        exit();
    }

    // Exclui o usuário
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['mensagem'] = "O usuário " . htmlspecialchars($usuario['nome']) . " foi excluído com sucesso!";
        $_SESSION['mensagem_tipo'] = 'success';
    } else {
        $_SESSION['erro'] = "Erro ao excluir usuário.";
    }
} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro ao excluir usuário: " . $e->getMessage();
}

header('Location: index.php?page=usuarios');
exit(); 