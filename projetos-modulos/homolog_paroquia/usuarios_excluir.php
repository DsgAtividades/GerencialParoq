<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('gerenciar_usuarios');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;

    // Não permitir excluir o próprio usuário
    if ($id == $_SESSION['usuario_id']) {
        exibirAlerta("Você não pode excluir seu próprio usuário", "danger");
        header("Location: usuarios_lista.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            exibirAlerta("Usuário excluído com sucesso!");
        } else {
            exibirAlerta("Usuário não encontrado", "danger");
        }
    } catch (PDOException $e) {
        exibirAlerta("Erro ao excluir usuário: " . $e->getMessage(), "danger");
    }
}

header("Location: usuarios_lista.php");
exit;
