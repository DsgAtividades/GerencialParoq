<?php
/**
 * Endpoint: Remover Membro da Pastoral
 * Método: DELETE
 * URL: /api/pastorais/remover-membro
 */

// Limpar qualquer output anterior
if (ob_get_level() > 0) {
    ob_clean();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Permissions.php';
require_once __DIR__ . '/../utils/Response.php';

// Verificar permissão de administrador
try {
    Permissions::requireAdmin('remover membros de pastorais');
} catch (Exception $e) {
    error_log("Erro de permissão em remover_membro: " . $e->getMessage());
    Response::error('Erro de permissão: ' . $e->getMessage(), 403);
}

try {
    // Obter dados do body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        Response::error('Dados inválidos', 400);
    }
    
    // Validar campos obrigatórios
    if (!isset($input['membro_id']) || !isset($input['pastoral_id'])) {
        Response::error('membro_id e pastoral_id são obrigatórios', 400);
    }
    
    $membro_id = $input['membro_id'];
    $pastoral_id = $input['pastoral_id'];
    
    error_log("remover_membro.php: Remover membro $membro_id da pastoral $pastoral_id");
    
    $db = new MembrosDatabase();
    
    // Verificar se o membro existe
    $checkMembro = $db->prepare("SELECT id, nome_completo FROM membros_membros WHERE id = ?");
    $checkMembro->execute([$membro_id]);
    $membro = $checkMembro->fetch(PDO::FETCH_ASSOC);
    
    if (!$membro) {
        Response::error('Membro não encontrado', 404);
    }
    
    // Verificar se a pastoral existe
    $checkPastoral = $db->prepare("SELECT id, nome FROM membros_pastorais WHERE id = ?");
    $checkPastoral->execute([$pastoral_id]);
    $pastoral = $checkPastoral->fetch(PDO::FETCH_ASSOC);
    
    if (!$pastoral) {
        Response::error('Pastoral não encontrada', 404);
    }
    
    // Verificar se está vinculado
    $checkVinculo = $db->prepare("SELECT id FROM membros_membros_pastorais WHERE membro_id = ? AND pastoral_id = ?");
    $checkVinculo->execute([$membro_id, $pastoral_id]);
    $vinculo = $checkVinculo->fetch(PDO::FETCH_ASSOC);
    
    if (!$vinculo) {
        Response::error('Este membro não está vinculado a esta pastoral', 400);
    }
    
    // Verificar se o membro é coordenador ou vice-coordenador
    $checkCoordenador = $db->prepare("SELECT coordenador_id, vice_coordenador_id FROM membros_pastorais WHERE id = ?");
    $checkCoordenador->execute([$pastoral_id]);
    $coordenadores = $checkCoordenador->fetch(PDO::FETCH_ASSOC);
    
    if ($coordenadores) {
        if (($coordenadores['coordenador_id'] && $coordenadores['coordenador_id'] === $membro_id) || 
            ($coordenadores['vice_coordenador_id'] && $coordenadores['vice_coordenador_id'] === $membro_id)) {
            Response::error('Não é possível remover um coordenador ou vice-coordenador da pastoral. Remova primeiro a função de coordenação.', 400);
        }
    }
    
    // Remover vínculo
    $deleteQuery = "DELETE FROM membros_membros_pastorais WHERE membro_id = ? AND pastoral_id = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $success = $deleteStmt->execute([$membro_id, $pastoral_id]);
    
    error_log("remover_membro.php: Execute result: " . ($success ? 'SUCCESS' : 'FAILED'));
    error_log("remover_membro.php: Rows affected: " . $deleteStmt->rowCount());
    
    if (!$success || $deleteStmt->rowCount() === 0) {
        error_log("remover_membro.php: Erro ao remover vínculo");
        Response::error('Erro ao remover vínculo', 500);
    }
    
    error_log("remover_membro.php: Membro removido com sucesso");
    
    Response::success([
        'message' => 'Membro removido da pastoral com sucesso',
        'membro' => [
            'id' => $membro_id,
            'nome' => $membro['nome_completo']
        ],
        'pastoral' => [
            'id' => $pastoral_id,
            'nome' => $pastoral['nome']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao remover membro da pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
