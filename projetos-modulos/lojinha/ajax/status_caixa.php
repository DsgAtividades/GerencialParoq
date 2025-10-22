<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

try {
    // Conexão direta com PDO
    $pdo = getConnection();
    
    // Buscar caixa aberto do dia
    $stmt = $pdo->query("
        SELECT 
            id,
            saldo_inicial,
            saldo_atual,
            status,
            data_abertura
        FROM lojinha_caixa
        WHERE DATE(data_abertura) = CURDATE() AND status = 'aberto'
        ORDER BY data_abertura DESC
        LIMIT 1
    ");
    
    $caixa = $stmt->fetch();
    
    if ($caixa) {
        echo json_encode([
            'success' => true,
            'status' => 'aberto',
            'caixa_id' => $caixa['id'],
            'saldo_inicial' => $caixa['saldo_inicial'],
            'saldo_atual' => $caixa['saldo_atual'],
            'data_abertura' => $caixa['data_abertura']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'status' => 'fechado'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao verificar status do caixa',
        'error' => $e->getMessage()
    ]);
}
?>
