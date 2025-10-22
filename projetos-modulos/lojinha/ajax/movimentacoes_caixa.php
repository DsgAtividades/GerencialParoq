<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

try {
    // Conexão direta com PDO
    $pdo = getConnection();
    
    // Buscar movimentações do caixa de hoje
    $stmt = $pdo->query("
        SELECT 
            'venda' as tipo,
            v.numero_venda as descricao,
            v.total as valor,
            v.data_venda as data_movimentacao,
            'Admin' as usuario_nome
        FROM lojinha_vendas v
        WHERE DATE(v.data_venda) = CURDATE() AND v.status = 'finalizada'
        ORDER BY v.data_venda DESC
        LIMIT 50
    ");
    
    $movimentacoes = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'movimentacoes' => $movimentacoes
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar movimentações do caixa',
        'error' => $e->getMessage()
    ]);
}
?>
