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
        $data_inicio = $_GET['data_inicio'] ?? null;
        $data_fim = $_GET['data_fim'] ?? null;
        
        $sql = "
            SELECT v.*, 
                   COUNT(vi.id) as total_itens,
                   SUM(vi.quantidade) as total_quantidade
            FROM cafe_vendas v
            LEFT JOIN cafe_vendas_itens vi ON v.id = vi.venda_id
            WHERE 1=1
        ";
        $params = [];
        
        if ($data_inicio) {
            $sql .= " AND DATE(v.data_venda) >= ?";
            $params[] = $data_inicio;
        }
        if ($data_fim) {
            $sql .= " AND DATE(v.data_venda) <= ?";
            $params[] = $data_fim;
        }
        
        $sql .= " GROUP BY v.id ORDER BY v.data_venda DESC LIMIT 100";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $vendas]);
    }
    
} catch(Exception $e) {
    error_log("Erro em vendas.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar vendas']);
}
?>
