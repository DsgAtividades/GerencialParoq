<?php
/**
 * Endpoint: Dashboard - Atividades Recentes
 * Retorna atividades recentes do sistema
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    
    $atividades = [];
    
    // Novos membros (últimos 7 dias)
    $novosMembros = $db->query("
        SELECT 
            nome_completo,
            created_at
        FROM membros_membros 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY created_at DESC
        LIMIT 5
    ")->fetchAll();
    
    foreach ($novosMembros as $membro) {
        $atividades[] = [
            'icone' => 'fa-user-plus',
            'titulo' => 'Novo membro cadastrado',
            'descricao' => $membro['nome_completo'] . ' foi cadastrado no sistema',
            'data' => 'Há ' . tempoRelativo($membro['created_at']),
            'tipo' => 'membro'
        ];
    }
    
    // Eventos próximos
    $eventosProximos = $db->query("
        SELECT 
            titulo,
            data_evento,
            local
        FROM membros_eventos 
        WHERE data_evento BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
        ORDER BY data_evento ASC
        LIMIT 3
    ")->fetchAll();
    
    foreach ($eventosProximos as $evento) {
        $atividades[] = [
            'icone' => 'fa-calendar-check',
            'titulo' => 'Evento próximo',
            'descricao' => $evento['titulo'] . ' - ' . $evento['local'],
            'data' => 'Em ' . tempoRelativo($evento['data_evento']),
            'tipo' => 'evento'
        ];
    }
    
    // Check-ins recentes
    $checkinsRecentes = $db->query("
        SELECT 
            m.nome_completo,
            c.data_checkin,
            e.titulo as evento
        FROM membros_checkins c
        JOIN membros_membros m ON c.id_membro = m.id
        LEFT JOIN membros_eventos e ON c.id_evento = e.id
        WHERE c.data_checkin >= DATE_SUB(NOW(), INTERVAL 3 DAY)
        ORDER BY c.data_checkin DESC
        LIMIT 3
    ")->fetchAll();
    
    foreach ($checkinsRecentes as $checkin) {
        $atividades[] = [
            'icone' => 'fa-check-circle',
            'titulo' => 'Check-in realizado',
            'descricao' => $checkin['nome_completo'] . ' fez check-in' . 
                          ($checkin['evento'] ? ' em ' . $checkin['evento'] : ''),
            'data' => 'Há ' . tempoRelativo($checkin['data_checkin']),
            'tipo' => 'checkin'
        ];
    }
    
    // Ordenar por data (mais recentes primeiro)
    usort($atividades, function($a, $b) {
        return strtotime($b['data']) - strtotime($a['data']);
    });
    
    // Limitar a 10 atividades
    $atividades = array_slice($atividades, 0, 10);
    
    Response::success([
        'atividades' => $atividades
    ]);
    
} catch (Exception $e) {
    Response::error('Erro ao carregar atividades: ' . $e->getMessage(), 500);
}

/**
 * Função para calcular tempo relativo
 */
function tempoRelativo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'poucos segundos';
    if ($time < 3600) return floor($time/60) . ' minutos';
    if ($time < 86400) return floor($time/3600) . ' horas';
    if ($time < 2592000) return floor($time/86400) . ' dias';
    if ($time < 31536000) return floor($time/2592000) . ' meses';
    return floor($time/31536000) . ' anos';
}
?>
