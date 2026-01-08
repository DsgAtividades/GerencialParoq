<?php
/**
 * Endpoint: Relatório - Crescimento Temporal
 * Retorna novos membros por mês (últimos 12 meses)
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
            DATE_FORMAT(data_entrada, '%Y-%m') as mes,
            DATE_FORMAT(data_entrada, '%b/%Y') as mes_formatado,
            COUNT(*) as total
        FROM membros_membros
        WHERE status != 'bloqueado'
            AND data_entrada IS NOT NULL
            AND data_entrada >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY mes
        ORDER BY mes ASC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Criar array associativo para facilitar busca
    $dadosPorMes = [];
    foreach ($resultados as $row) {
        $dadosPorMes[$row['mes']] = (int)$row['total'];
    }
    
    // Gerar todos os meses dos últimos 12 meses (meses completos)
    $labels = [];
    $data = [];
    $mesesFormatados = [
        '01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr',
        '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
        '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez'
    ];
    
    // Gerar últimos 12 meses
    for ($i = 11; $i >= 0; $i--) {
        $dataMes = date('Y-m', strtotime("-$i months"));
        $ano = substr($dataMes, 0, 4);
        $mes = substr($dataMes, 5, 2);
        $mesFormatado = $mesesFormatados[$mes] . '/' . $ano;
        
        $labels[] = $mesFormatado;
        $data[] = isset($dadosPorMes[$dataMes]) ? $dadosPorMes[$dataMes] : 0;
    }
    
    ob_end_clean();
    Response::success([
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Novos Membros',
            'data' => $data,
            'borderColor' => '#36A2EB',
            'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
            'fill' => true,
            'tension' => 0.4
        ]],
        'total' => array_sum($data)
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    error_log("Relatório crescimento temporal error: " . $e->getMessage());
    Response::error('Erro ao gerar relatório: ' . $e->getMessage(), 500);
}
?>

