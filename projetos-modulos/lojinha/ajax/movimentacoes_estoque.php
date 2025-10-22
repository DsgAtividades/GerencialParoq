<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

try {
    // Conexão direta com PDO
    $pdo = getConnection();
    
    // Buscar movimentações de estoque
    $stmt = $pdo->query("
        SELECT 
            m.id,
            m.tipo,
            m.quantidade,
            m.motivo,
            m.data_movimentacao,
            p.nome as produto_nome,
            p.codigo as produto_codigo,
            'Admin' as usuario_nome
        FROM lojinha_estoque_movimentacoes m
        INNER JOIN lojinha_produtos p ON m.produto_id = p.id
        ORDER BY m.data_movimentacao DESC
        LIMIT 100
    ");
    
    $movimentacoes = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'movimentacoes' => $movimentacoes
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar movimentações',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
