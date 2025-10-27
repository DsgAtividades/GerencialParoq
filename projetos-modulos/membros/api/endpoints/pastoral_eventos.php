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
    
    // Buscar eventos futuros (já que a tabela não tem coluna pastoral_id)
    // Retornando eventos gerais para exibição
    $query = "
        SELECT 
            e.id,
            e.nome,
            e.data_evento as data,
            e.horario,
            COALESCE(e.local, 'A definir') as local,
            COALESCE(e.descricao, '') as descricao,
            0 as total_inscritos
        FROM membros_eventos e
        WHERE e.data_evento >= CURDATE()
        ORDER BY e.data_evento ASC
        LIMIT 20
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("pastoral_eventos.php: " . count($eventos) . " eventos encontrados");
    
    Response::success($eventos);
    
} catch (Exception $e) {
    error_log("Erro ao buscar eventos da pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>


