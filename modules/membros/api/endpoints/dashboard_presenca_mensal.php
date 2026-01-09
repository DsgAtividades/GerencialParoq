<?php
/**
 * Endpoint: Dashboard - Presença Mensal
 * Retorna dados para gráfico de presença mensal
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    
    // Últimos 6 meses
    $result = $db->query("
        SELECT 
            DATE_FORMAT(data_checkin, '%Y-%m') as mes,
            COUNT(DISTINCT id_membro) as total_checkins,
            (SELECT COUNT(DISTINCT id_membro) FROM membros_membros WHERE status = 'ativo') as total_membros
        FROM membros_checkins 
        WHERE data_checkin >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(data_checkin, '%Y-%m')
        ORDER BY mes
    ")->fetchAll();
    
    $labels = [];
    $data = [];
    
    // Gerar dados para os últimos 6 meses
    for ($i = 5; $i >= 0; $i--) {
        $mes = date('Y-m', strtotime("-{$i} months"));
        $mesLabel = date('M', strtotime("-{$i} months"));
        
        $labels[] = $mesLabel;
        
        // Buscar dados do mês
        $mesData = array_filter($result, function($row) use ($mes) {
            return $row['mes'] === $mes;
        });
        
        if (!empty($mesData)) {
            $row = reset($mesData);
            $percentual = $row['total_membros'] > 0 ? 
                round(($row['total_checkins'] / $row['total_membros']) * 100, 1) : 0;
            $data[] = $percentual;
        } else {
            $data[] = 0;
        }
    }
    
    Response::success([
        'labels' => $labels,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    Response::error('Erro ao carregar dados de presença: ' . $e->getMessage(), 500);
}
?>
