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
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Buscar produtos
        $stmt = $pdo->query("
            SELECT p.*, 
                   COUNT(DISTINCT v.id) as total_vendas,
                   SUM(CASE WHEN e.tipo = 'entrada' THEN e.quantidade ELSE -e.quantidade END) as movimentacoes
            FROM cafe_produtos p
            LEFT JOIN cafe_vendas_itens vi ON p.id = vi.produto_id
            LEFT JOIN cafe_vendas v ON vi.venda_id = v.id
            LEFT JOIN cafe_estoque_movimentacoes e ON p.id = e.produto_id
            WHERE p.ativo = 1
            GROUP BY p.id
            ORDER BY p.nome
        ");
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $produtos]);
    }
    
} catch(Exception $e) {
    error_log("Erro em produtos.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar produtos']);
}
?>
