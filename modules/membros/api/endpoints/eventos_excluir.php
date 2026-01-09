<?php
/**
 * Endpoint: Excluir Evento Geral
 * Método: DELETE
 * URL: /api/eventos/{id}
 */

require_once '../config/database.php';

try {
    // A variável $evento_id é definida pelo routes.php via regex
    global $evento_id;
    
    // Verificar se o ID foi fornecido
    if (!isset($evento_id) || empty($evento_id)) {
        Response::error('ID do evento é obrigatório', 400);
    }
    
    $db = new MembrosDatabase();
    
    // Verificar se o evento existe
    $checkQuery = "SELECT id, nome FROM membros_eventos WHERE id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$evento_id]);
    $evento = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        Response::error('Evento não encontrado', 404);
    }
    
    // Verificar se há relacionamentos que impedem a exclusão
    // Por exemplo, verificar se há escalas vinculadas a este evento
    // (isso depende da estrutura do sistema)
    
    // Excluir o evento
    $deleteQuery = "DELETE FROM membros_eventos WHERE id = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $success = $deleteStmt->execute([$evento_id]);
    
    if (!$success) {
        error_log("eventos_excluir.php: Erro ao executar query de exclusão");
        Response::error('Erro ao excluir evento', 500);
    }
    
    $rowsAffected = $deleteStmt->rowCount();
    
    if ($rowsAffected === 0) {
        Response::error('Nenhum evento foi excluído', 404);
    }
    
    error_log("eventos_excluir.php: Evento '{$evento['nome']}' (ID: $evento_id) excluído com sucesso");
    
    Response::success([
        'id' => $evento_id,
        'message' => 'Evento excluído com sucesso'
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao excluir evento geral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

