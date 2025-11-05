<?php
/**
 * Endpoint: Eventos da Pastoral
 * Método: GET
 * URL: /api/pastorais/{id}/eventos
 */

require_once '../config/database.php';

try {
    // A variável $pastoral_id é definida pelo routes.php
    global $pastoral_id;
    
    // Verificar se o ID foi fornecido
    if (!isset($pastoral_id) || empty($pastoral_id)) {
        error_log("pastoral_eventos.php: ID da pastoral não fornecido");
        Response::error('ID da pastoral é obrigatório', 400);
    }
    
    error_log("pastoral_eventos.php: Buscando eventos para pastoral_id = " . $pastoral_id);
    
    $db = new MembrosDatabase();
    
    // Buscar eventos específicos da pastoral da nova tabela
    $query = "
        SELECT 
            e.id,
            e.nome,
            e.data_evento as data,
            e.horario,
            COALESCE(e.local, 'A definir') as local,
            COALESCE(e.descricao, '') as descricao,
            e.tipo,
            e.responsavel_id,
            m.nome_completo as responsavel_nome,
            0 as total_inscritos
        FROM membros_eventos_pastorais e
        LEFT JOIN membros_membros m ON e.responsavel_id = m.id
        WHERE e.pastoral_id = ? AND e.ativo = 1
        ORDER BY e.data_evento ASC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$pastoral_id]);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("pastoral_eventos.php: " . count($eventos) . " eventos encontrados para a pastoral");
    
    Response::success($eventos);
    
} catch (Exception $e) {
    error_log("Erro ao buscar eventos da pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>


