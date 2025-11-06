<?php
/**
 * Endpoint: Relatório - Membros por Pastoral
 * Retorna distribuição de membros ativos por pastoral
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
            p.id,
            p.nome,
            COUNT(DISTINCT mp.membro_id) as total
        FROM membros_pastorais p
        LEFT JOIN membros_membros_pastorais mp ON p.id = mp.pastoral_id 
            AND mp.status = 'ativo'
        LEFT JOIN membros_membros m ON mp.membro_id = m.id 
            AND m.status != 'bloqueado'
        WHERE p.ativo = 1
        GROUP BY p.id, p.nome
        HAVING total > 0
        ORDER BY total DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar para Chart.js
    $labels = [];
    $data = [];
    $colors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
    ];
    
    foreach ($resultados as $index => $row) {
        $labels[] = $row['nome'];
        $data[] = (int)$row['total'];
    }
    
    ob_end_clean();
    Response::success([
        'labels' => $labels,
        'datasets' => [[
            'data' => $data,
            'backgroundColor' => array_slice($colors, 0, count($data))
        ]],
        'total' => array_sum($data),
        'pastorais' => count($labels)
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    error_log("Relatório membros por pastoral error: " . $e->getMessage());
    Response::error('Erro ao gerar relatório: ' . $e->getMessage(), 500);
}
?>

