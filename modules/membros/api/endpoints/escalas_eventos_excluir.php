<?php
/**
 * Endpoint: Excluir Evento de Escala
 * Método: DELETE
 * URL: /api/eventos/{evento_id}
 */

require_once '../config/database.php';
require_once 'utils/Permissions.php';

// Verificar permissão específica para gerenciar escalas de pastorais
// Tanto Madmin quanto 'membros' podem gerenciar escalas de pastorais
if (!Permissions::canManagePastoralEscalas()) {
    Permissions::denyAccess('excluir eventos de escalas');
}

try {
    global $evento_id;
    
    if (!isset($evento_id) || empty($evento_id)) {
        Response::error('ID do evento é obrigatório', 400);
    }
    
    error_log("escalas_eventos_excluir.php: Excluindo evento de escala $evento_id");
    
    $db = new MembrosDatabase();
    
    // Verificar se o evento existe
    $checkEvento = $db->prepare("SELECT id, pastoral_id FROM membros_escalas_eventos WHERE id = ?");
    $checkEvento->execute([$evento_id]);
    $eventoExiste = $checkEvento->fetch(PDO::FETCH_ASSOC);
    
    if (!$eventoExiste) {
        Response::error('Evento não encontrado', 404);
    }
    
    // Excluir o evento (as funções e atribuições serão excluídas automaticamente via CASCADE)
    $deleteQuery = "DELETE FROM membros_escalas_eventos WHERE id = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $success = $deleteStmt->execute([$evento_id]);
    
    if (!$success) {
        Response::error('Erro ao excluir evento', 500);
    }
    
    Response::success(['id' => $evento_id], 'Evento excluído com sucesso');
    
} catch (Exception $e) {
    error_log("Erro ao excluir evento de escala: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

