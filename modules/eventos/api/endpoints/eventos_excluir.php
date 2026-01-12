<?php
/**
 * Endpoint: Excluir Evento Geral
 * Método: DELETE
 * URL: /api/eventos/{id}
 */

require_once __DIR__ . '/../../config/database.php';

try {
    // O ID vem via GET ou POST
    $evento_id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : null);
    
    // Verificar se o ID foi fornecido
    if (!isset($evento_id) || empty($evento_id)) {
        Response::error('ID do evento é obrigatório', 400);
    }
    
    $db = new EventosDatabase();
    
    // Verificar se o evento existe
    $checkQuery = "SELECT id, nome FROM membros_eventos WHERE id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$evento_id]);
    $evento = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento || $evento === false) {
        Response::error('Evento não encontrado', 404);
    }
    
    // Verificar se há relacionamentos que impedem a exclusão
    // Por exemplo, verificar se há escalas vinculadas a este evento
    // (isso depende da estrutura do sistema)
    
    // Excluir o evento
    $deleteQuery = "DELETE FROM membros_eventos WHERE id = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    
    try {
        $success = $deleteStmt->execute([$evento_id]);
        
        if (!$success) {
            error_log("eventos_excluir.php: Erro ao executar query de exclusão");
            Response::error('Erro ao excluir evento', 500);
        }
    } catch (PDOException $e) {
        error_log("eventos_excluir.php: Erro PDO: " . $e->getMessage());
        Response::error('Erro ao excluir evento: ' . $e->getMessage(), 500);
    }
    
    $rowsAffected = $deleteStmt->rowCount();
    
    if ($rowsAffected === 0) {
        Response::error('Nenhum evento foi excluído', 404);
    }
    
    $nomeEvento = htmlspecialchars($evento['nome'] ?? 'Sem nome', ENT_QUOTES, 'UTF-8');
    error_log("eventos_excluir.php: Evento '{$nomeEvento}' (ID: $evento_id) excluído com sucesso");
    
    Response::success([
        'id' => $evento_id,
        'message' => 'Evento excluído com sucesso'
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao excluir evento geral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

