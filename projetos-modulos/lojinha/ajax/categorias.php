<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once '../config/config.php';

try {
    $pdo = getConnection();
    
    $stmt = $pdo->query("SELECT id, nome, descricao FROM lojinha_categorias WHERE ativo = 1 ORDER BY nome ASC");
    $categorias = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'categorias' => $categorias
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar categorias',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>