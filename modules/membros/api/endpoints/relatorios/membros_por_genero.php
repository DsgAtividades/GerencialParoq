<?php
/**
 * Endpoint: Relatório - Membros por Gênero
 * Retorna distribuição de membros por sexo
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
                WHEN sexo = 'M' THEN 'Masculino'
                WHEN sexo = 'F' THEN 'Feminino'
                ELSE 'Não informado'
            END as genero,
            COUNT(*) as total
        FROM membros_membros
        WHERE status != 'bloqueado'
            AND status IS NOT NULL
        GROUP BY 
            CASE 
                WHEN sexo = 'M' THEN 'Masculino'
                WHEN sexo = 'F' THEN 'Feminino'
                ELSE 'Não informado'
            END
        ORDER BY 
            CASE 
                WHEN sexo = 'M' THEN 1
                WHEN sexo = 'F' THEN 2
                ELSE 3
            END
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $data = [];
    $colors = ['#36A2EB', '#FF6384', '#C9CBCF'];
    
    foreach ($resultados as $row) {
        $labels[] = $row['genero'];
        $data[] = (int)$row['total'];
    }
    
    ob_end_clean();
    Response::success([
        'labels' => $labels,
        'datasets' => [[
            'data' => $data,
            'backgroundColor' => array_slice($colors, 0, count($data))
        ]],
        'total' => array_sum($data)
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    error_log("Relatório membros por gênero error: " . $e->getMessage());
    Response::error('Erro ao gerar relatório: ' . $e->getMessage(), 500);
}
?>

