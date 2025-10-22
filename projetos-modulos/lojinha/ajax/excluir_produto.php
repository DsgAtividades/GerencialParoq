<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$id = intval($_GET['id']);

try {
    // Conexão direta com PDO
    $pdo = getConnection();
    
    // Soft delete - apenas marcar como inativo
    $stmt = $pdo->prepare("UPDATE lojinha_produtos SET ativo = 0 WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Produto excluído com sucesso!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Produto não encontrado'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao excluir produto',
        'error' => $e->getMessage()
    ]);
}
?>
