<?php
/**
 * Endpoint: Relatório - Membros por Status
 * Retorna distribuição de membros por status
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (ob_get_level()) {
    ob_clean();
}
ob_start();

try {
    require_once __DIR__ . '/../../../config/database.php';
    require_once __DIR__ . '/../../utils/Response.php';
    
    $db = new MembrosDatabase();
    
    $query = "
        SELECT 
            status,
            COUNT(*) as total
        FROM membros_membros
        WHERE status != 'bloqueado'
        GROUP BY status
        ORDER BY total DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mapear status para português
    $statusMap = [
        'ativo' => 'Ativo',
        'afastado' => 'Afastado',
        'em_discernimento' => 'Em Discernimento',
        'bloqueado' => 'Bloqueado'
    ];
    
    $labels = [];
    $data = [];
    $colors = ['#28a745', '#ffc107', '#17a2b8', '#dc3545'];
    
    foreach ($resultados as $index => $row) {
        $status = $row['status'];
        $labels[] = $statusMap[$status] ?? ucfirst($status);
        $data[] = (int)$row['total'];
    }
    
    ob_end_clean();
    Response::success([
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Membros',
            'data' => $data,
            'backgroundColor' => array_slice($colors, 0, count($data))
        ]],
        'total' => array_sum($data)
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    error_log("Relatório membros por status error: " . $e->getMessage());
    Response::error('Erro ao gerar relatório: ' . $e->getMessage(), 500);
}
?>

