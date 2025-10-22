<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'ID do serviço não fornecido.';
    header('Location: /gerencialParoquia/projetos-modulos/obras/index.php?page=relatorios');
    exit;
}

$id = intval($_GET['id']);

try {
    // Excluir o serviço
    $stmt = $pdo->prepare("DELETE FROM obras_servicos WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = 'Serviço excluído com sucesso.';
    } else {
        $_SESSION['error'] = 'Serviço não encontrado.';
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Erro ao excluir o serviço: ' . $e->getMessage();
}

// Redirecionar de volta para a página de relatórios
header('Location: /gerencialParoquia/projetos-modulos/obras/index.php?page=relatorios');
exit;
