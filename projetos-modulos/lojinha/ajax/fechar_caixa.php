<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Conexão direta com PDO
    $pdo = getConnection();
    
    // Buscar caixa aberto
    $stmt = $pdo->query("
        SELECT id, saldo_inicial, saldo_atual 
        FROM lojinha_caixa 
        WHERE DATE(data_abertura) = CURDATE() AND status = 'aberto'
        ORDER BY data_abertura DESC
        LIMIT 1
    ");
    
    $caixa = $stmt->fetch();
    
    if (!$caixa) {
        echo json_encode(['success' => false, 'message' => 'Nenhum caixa aberto encontrado']);
        exit;
    }
    
    // Calcular saldo final
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total), 0) as total_vendas
        FROM lojinha_vendas
        WHERE DATE(data_venda) = CURDATE() AND status = 'finalizada'
    ");
    $stmt->execute();
    $total_vendas = $stmt->fetch()['total_vendas'];
    
    $saldo_final = $caixa['saldo_inicial'] + $total_vendas;
    
    // Fechar caixa
    $stmt = $pdo->prepare("
        UPDATE lojinha_caixa 
        SET status = 'fechado', 
            saldo_final = ?,
            data_fechamento = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$saldo_final, $caixa['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Caixa fechado com sucesso!',
        'saldo_inicial' => $caixa['saldo_inicial'],
        'total_vendas' => $total_vendas,
        'saldo_final' => $saldo_final
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao fechar caixa',
        'error' => $e->getMessage()
    ]);
}
?>
