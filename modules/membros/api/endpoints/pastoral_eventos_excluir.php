<?php
/**
 * Endpoint: Excluir Evento de Pastoral
 * Método: DELETE
 * URL: /api/pastorais/{pastoral_id}/eventos/{evento_id}
 */

require_once '../config/database.php';

try {
    global $pastoral_id, $evento_id;
    
    if (!isset($pastoral_id) || empty($pastoral_id)) {
        Response::error('ID da pastoral é obrigatório', 400);
    }
    
    if (!isset($evento_id) || empty($evento_id)) {
        Response::error('ID do evento é obrigatório', 400);
    }
    
    error_log("pastoral_eventos_excluir.php: Excluindo evento $evento_id da pastoral $pastoral_id");
    
    $db = new MembrosDatabase();
    
    // Verificar se o evento existe e pertence à pastoral
    $checkEvento = $db->prepare("SELECT id FROM membros_eventos_pastorais WHERE id = ? AND pastoral_id = ?");
    $checkEvento->execute([$evento_id, $pastoral_id]);
    $eventoExiste = $checkEvento->fetch(PDO::FETCH_ASSOC);
    
    if (!$eventoExiste) {
        Response::error('Evento não encontrado ou não pertence a esta pastoral', 404);
    }
    
    // Excluir o evento
    $deleteQuery = "DELETE FROM membros_eventos_pastorais WHERE id = ? AND pastoral_id = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $success = $deleteStmt->execute([$evento_id, $pastoral_id]);
    
    if (!$success) {
        Response::error('Erro ao excluir evento', 500);
    }
    
    Response::success(['id' => $evento_id], 'Evento excluído com sucesso');
    
} catch (Exception $e) {
    error_log("Erro ao excluir evento da pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

