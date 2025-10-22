<?php
require_once __DIR__ . '/../config/database.php';

// Verifica se o usuário está logado e tem permissão
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_acesso'] != 'Administrador') {
    $_SESSION['erro'] = "Você não tem permissão para realizar esta ação.";
    echo "<script>window.location.href = 'index.php?page=equipe';</script>";
    exit();
}

// Verifica se foi fornecido um ID
if (!isset($_GET['id'])) {
    $_SESSION['erro'] = "ID do membro não fornecido.";
    echo "<script>window.location.href = 'index.php?page=equipe';</script>";
    exit();
}

$id = $_GET['id'];

try {
    // Verifica se o membro existe antes de excluir
    $stmt = $pdo->prepare("SELECT id, nome FROM equipe_pastoral WHERE id = ?");
    $stmt->execute([$id]);
    $membro = $stmt->fetch();
    
    if (!$membro) {
        $_SESSION['erro'] = "Membro não encontrado.";
        echo "<script>window.location.href = 'index.php?page=equipe';</script>";
        exit();
    }

    // Exclui o membro
    $stmt = $pdo->prepare("DELETE FROM equipe_pastoral WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['mensagem'] = "O membro " . htmlspecialchars($membro['nome']) . " foi excluído com sucesso!";
        $_SESSION['mensagem_tipo'] = 'success';
    } else {
        $_SESSION['erro'] = "Erro ao excluir membro.";
    }
} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro ao excluir membro: " . $e->getMessage();
}

echo "<script>window.location.href = 'index.php?page=equipe';</script>";
exit(); 