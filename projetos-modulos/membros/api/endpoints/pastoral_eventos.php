<?php
/**
 * Endpoint: Eventos da Pastoral
 * Método: GET
 * URL: /api/pastorais/{id}/eventos
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    
    // Verificar se o ID foi fornecido
    if (!isset($pastoral_id) || empty($pastoral_id)) {
        Response::error('ID da pastoral é obrigatório', 400);
    }
    
    // Buscar eventos da pastoral (próximos 30 dias)
    $query = "
        SELECT 
            e.id,
            e.nome,
            e.data,
            e.horario,
            e.local,
            e.descricao,
            COUNT(DISTINCT ie.membro_id) as total_inscritos
        FROM membros_eventos e
        LEFT JOIN membros_inscricoes_eventos ie ON e.id = ie.evento_id
        WHERE e.pastoral_id = ? AND e.data >= CURDATE()
        GROUP BY e.id
        ORDER BY e.data ASC
        LIMIT 50
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$pastoral_id]);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    Response::success($eventos);
    
} catch (Exception $e) {
    error_log("Erro ao buscar eventos da pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>


