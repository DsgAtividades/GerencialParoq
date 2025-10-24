<?php
/**
 * Endpoint: Dashboard Geral
 * Retorna estatísticas gerais do sistema
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    
    // Estatísticas gerais
    $stats = [
        'totalMembros' => $db->query("SELECT COUNT(*) as total FROM membros_membros")->fetch()['total'],
        'membrosAtivos' => $db->query("SELECT COUNT(*) as total FROM membros_membros WHERE status = 'ativo'")->fetch()['total'],
        'pastoraisAtivas' => $db->query("SELECT COUNT(*) as total FROM membros_pastorais WHERE ativo = 1")->fetch()['total'],
        'eventosHoje' => $db->query("SELECT COUNT(*) as total FROM membros_eventos WHERE DATE(data_evento) = CURDATE()")->fetch()['total']
    ];
    
    // Alertas
    $alertas = [];
    
    // Membros sem pastoral
    $semPastoral = $db->query("
        SELECT COUNT(*) as total 
        FROM membros_membros m 
        LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id 
        WHERE mp.membro_id IS NULL AND m.status = 'ativo'
    ")->fetch()['total'];
    
    if ($semPastoral > 0) {
        $alertas[] = [
            'tipo' => 'warning',
            'titulo' => 'Membros sem Pastoral',
            'mensagem' => "{$semPastoral} membros ativos não estão vinculados a nenhuma pastoral"
        ];
    }
    
    // Eventos próximos
    $eventosProximos = $db->query("
        SELECT COUNT(*) as total 
        FROM membros_eventos 
        WHERE data_evento BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
    ")->fetch()['total'];
    
    if ($eventosProximos > 0) {
        $alertas[] = [
            'tipo' => 'info',
            'titulo' => 'Eventos Próximos',
            'mensagem' => "{$eventosProximos} eventos nos próximos 7 dias"
        ];
    }
    
    $stats['alertas'] = $alertas;
    
    Response::success($stats);
    
} catch (Exception $e) {
    Response::error('Erro ao carregar estatísticas: ' . $e->getMessage(), 500);
}
?>
