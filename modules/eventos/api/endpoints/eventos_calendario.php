<?php
/**
 * Endpoint: Buscar Todos os Eventos para Calendário
 * Método: GET
 * URL: /api/eventos/calendario
 * 
 * Retorna todos os eventos (gerais + de pastorais) para exibição em calendário
 */

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new EventosDatabase();
    
    // Buscar eventos gerais (membros_eventos)
    $queryGerais = "
        SELECT 
            e.id,
            e.nome,
            e.tipo,
            e.data_evento as data,
            e.horario,
            e.local,
            e.descricao,
            e.Eventos_url as eventos_url,
            e.responsavel_id,
            m.nome_completo as responsavel_nome,
            NULL as pastoral_id,
            NULL as pastoral_nome,
            'geral' as origem
        FROM membros_eventos e
        LEFT JOIN membros_membros m ON e.responsavel_id = m.id
        WHERE e.ativo = 1 AND e.data_evento >= CURDATE()
        ORDER BY e.data_evento ASC
    ";
    
    $stmtGerais = $db->prepare($queryGerais);
    $stmtGerais->execute();
    $eventosGerais = $stmtGerais->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    // Buscar eventos de pastorais (membros_eventos_pastorais)
    $queryPastorais = "
        SELECT 
            e.id,
            e.nome,
            e.tipo,
            e.data_evento as data,
            e.horario,
            e.local,
            e.descricao,
            e.responsavel_id,
            m.nome_completo as responsavel_nome,
            e.pastoral_id,
            p.nome as pastoral_nome,
            'pastoral' as origem
        FROM membros_eventos_pastorais e
        LEFT JOIN membros_membros m ON e.responsavel_id = m.id
        LEFT JOIN membros_pastorais p ON e.pastoral_id = p.id
        WHERE e.ativo = 1 AND e.data_evento >= CURDATE()
        ORDER BY e.data_evento ASC
    ";
    
    $stmtPastorais = $db->prepare($queryPastorais);
    $stmtPastorais->execute();
    $eventosPastorais = $stmtPastorais->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    // Combinar todos os eventos
    $todosEventos = array_merge($eventosGerais, $eventosPastorais);
    
    // Organizar por data para facilitar renderização do calendário
    $eventosPorData = [];
    foreach ($todosEventos as $evento) {
        $data = $evento['data'];
        if (!isset($eventosPorData[$data])) {
            $eventosPorData[$data] = [];
        }
        $eventosPorData[$data][] = $evento;
    }
    
    Response::success([
        'eventos' => $todosEventos,
        'eventos_por_data' => $eventosPorData,
        'total' => count($todosEventos),
        'total_gerais' => count($eventosGerais),
        'total_pastorais' => count($eventosPastorais)
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao buscar eventos para calendário: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

