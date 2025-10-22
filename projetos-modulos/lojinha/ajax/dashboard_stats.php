<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

try {
    $pdo = getConnection();
    
    // Total de produtos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM lojinha_produtos WHERE ativo = 1");
    $total_produtos = $stmt->fetch()['total'];
    
    // Vendas hoje
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM lojinha_vendas 
        WHERE DATE(data_venda) = CURDATE() AND status = 'finalizada'
    ");
    $vendas_hoje = $stmt->fetch()['total'];
    
    // Faturamento hoje
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total), 0) as total 
        FROM lojinha_vendas 
        WHERE DATE(data_venda) = CURDATE() AND status = 'finalizada'
    ");
    $faturamento_hoje = $stmt->fetch()['total'];
    
    // Produtos com estoque baixo
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM lojinha_produtos 
        WHERE ativo = 1 AND estoque_atual <= estoque_minimo
    ");
    $estoque_baixo = $stmt->fetch()['total'];
    
    echo json_encode([
        'success' => true,
        'total_produtos' => (int)$total_produtos,
        'vendas_hoje' => (int)$vendas_hoje,
        'faturamento_hoje' => (float)$faturamento_hoje,
        'estoque_baixo' => (int)$estoque_baixo
    ], JSON_UNESCAPED_UNICODE);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar estatísticas',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>