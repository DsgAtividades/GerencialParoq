<?php
/**
 * Endpoint: Relatório - Membros por Faixa Etária
 * Retorna distribuição de membros por faixas etárias
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
            CASE 
                WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) < 18 THEN '0-17 anos'
                WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) BETWEEN 18 AND 30 THEN '18-30 anos'
                WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) BETWEEN 31 AND 50 THEN '31-50 anos'
                WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) BETWEEN 51 AND 70 THEN '51-70 anos'
                ELSE '70+ anos'
            END as faixa_etaria,
            COUNT(*) as total
        FROM membros_membros
        WHERE status != 'bloqueado' 
            AND data_nascimento IS NOT NULL
        GROUP BY faixa_etaria
        ORDER BY 
            CASE faixa_etaria
                WHEN '0-17 anos' THEN 1
                WHEN '18-30 anos' THEN 2
                WHEN '31-50 anos' THEN 3
                WHEN '51-70 anos' THEN 4
                ELSE 5
            END
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $data = [];
    $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'];
    
    foreach ($resultados as $row) {
        $labels[] = $row['faixa_etaria'];
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
    error_log("Relatório faixa etária error: " . $e->getMessage());
    Response::error('Erro ao gerar relatório: ' . $e->getMessage(), 500);
}
?>

