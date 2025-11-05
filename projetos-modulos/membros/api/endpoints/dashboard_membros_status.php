<?php
/**
 * Endpoint: Dashboard - Membros por Status
 * Retorna dados para gráfico de membros por status
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    
    $result = $db->query("
        SELECT 
            status,
            COUNT(*) as total
        FROM membros_membros 
        GROUP BY status
        ORDER BY total DESC
    ")->fetchAll();
    
    $labels = [];
    $data = [];
    
    foreach ($result as $row) {
        $labels[] = ucfirst($row['status']);
        $data[] = (int)$row['total'];
    }
    
    Response::success([
        'labels' => $labels,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    Response::error('Erro ao carregar dados de status: ' . $e->getMessage(), 500);
}
?>
