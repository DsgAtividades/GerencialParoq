<?php
header('Content-Type: application/json');
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

// Verificar permissão (aceita produtos_bloquear OU gerenciar_produtos)
if (!temPermissao('produtos_bloquear') && !temPermissao('gerenciar_produtos')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Você não tem permissão para realizar esta ação']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);
$id = (int)$data['id'];
$status = (int)$data['status'];

try {
    // Atualiza o status do produto
    $sql = "UPDATE cafe_produtos SET bloqueado = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':status' => $status,
        ':id' => $id
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao alterar status do produto '.$e->getMessage()]);
}
