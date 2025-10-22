<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

try {
    $pdo = getConnection();
    
    // Buscar vendas recentes
    $stmt = $pdo->query("
        SELECT 
            numero_venda,
            cliente_nome,
            total,
            status,
            data_venda
        FROM lojinha_vendas
        WHERE status = 'finalizada'
        ORDER BY data_venda DESC
        LIMIT 10
    ");
    
    $vendas = $stmt->fetchAll();
    
    echo json_encode($vendas, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([]);
}
?>