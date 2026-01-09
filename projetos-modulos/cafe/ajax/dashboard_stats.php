<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

require_once '../config/config.php';

try {
    $pdo = getCafeConnection();
    
    // Total de produtos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cafe_produtos WHERE ativo = 1");
    $total_produtos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Vendas hoje
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total), 0) as total 
        FROM cafe_vendas 
        WHERE DATE(data_venda) = CURDATE() AND status = 'finalizada'
    ");
    $vendas_hoje = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Estoque baixo
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM cafe_produtos 
        WHERE ativo = 1 AND estoque_atual <= estoque_minimo
    ");
    $estoque_baixo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de vendas
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM cafe_vendas 
        WHERE status = 'finalizada'
    ");
    $total_vendas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Vendas recentes
    $stmt = $pdo->query("
        SELECT v.*, COUNT(vi.id) as total_itens
        FROM cafe_vendas v
        LEFT JOIN cafe_vendas_itens vi ON v.id = vi.venda_id
        WHERE v.status = 'finalizada'
        GROUP BY v.id
        ORDER BY v.data_venda DESC
        LIMIT 10
    ");
    $vendas_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_produtos' => intval($total_produtos),
            'vendas_hoje' => floatval($vendas_hoje),
            'estoque_baixo' => intval($estoque_baixo),
            'total_vendas' => intval($total_vendas),
            'vendas_recentes' => $vendas_recentes
        ]
    ]);
    
} catch(Exception $e) {
    error_log("Erro em dashboard_stats.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar estatísticas']);
}
?>
