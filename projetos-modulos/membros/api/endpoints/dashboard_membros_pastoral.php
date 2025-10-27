<?php
/**
 * Endpoint: Dashboard - Membros por Pastoral
 * Retorna dados para gráfico de membros por pastoral
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    
    $result = $db->query("
        SELECT 
            p.nome as pastoral,
            COUNT(mp.membro_id) as total
        FROM membros_pastorais p
        LEFT JOIN membros_membros_pastorais mp ON p.id = mp.pastoral_id 
            AND mp.status = 'ativo'
        WHERE p.ativo = 1
        GROUP BY p.id, p.nome
        HAVING total > 0
        ORDER BY total DESC
        LIMIT 10
    ")->fetchAll();
    
    $labels = [];
    $data = [];
    
    foreach ($result as $row) {
        $labels[] = $row['pastoral'];
        $data[] = (int)$row['total'];
    }
    
    Response::success([
        'labels' => $labels,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    Response::error('Erro ao carregar dados de pastoral: ' . $e->getMessage(), 500);
}
?>
