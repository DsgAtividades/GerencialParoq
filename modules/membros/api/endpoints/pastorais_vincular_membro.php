<?php
/**
 * Endpoint: Vincular Membro à Pastoral
 * Método: POST
 * URL: /api/pastorais/vincular-membro
 */

require_once '../config/database.php';

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
    
    error_log("vincular_membro.php: Vincular membro $membro_id à pastoral $pastoral_id");
    
    $db = new MembrosDatabase();
    
    // Verificar se o membro existe
    $checkMembro = $db->prepare("SELECT id FROM membros_membros WHERE id = ?");
    $checkMembro->execute([$membro_id]);
    $membroExiste = $checkMembro->fetch(PDO::FETCH_ASSOC);
    
    if (!$membroExiste) {
        Response::error('Membro não encontrado', 404);
    }
    
    // Verificar se a pastoral existe
    $checkPastoral = $db->prepare("SELECT id FROM membros_pastorais WHERE id = ?");
    $checkPastoral->execute([$pastoral_id]);
    $pastoralExiste = $checkPastoral->fetch(PDO::FETCH_ASSOC);
    
    if (!$pastoralExiste) {
        Response::error('Pastoral não encontrada', 404);
    }
    
    // Verificar se já está vinculado
    $checkVinculo = $db->prepare("SELECT id FROM membros_membros_pastorais WHERE membro_id = ? AND pastoral_id = ?");
    $checkVinculo->execute([$membro_id, $pastoral_id]);
    $jaVinculado = $checkVinculo->fetch(PDO::FETCH_ASSOC);
    
    if ($jaVinculado) {
        Response::error('Este membro já está vinculado a esta pastoral', 400);
    }
    
    // Gerar UUID para o ID
    $vinculo_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    // Inserir vínculo
    $query = "
        INSERT INTO membros_membros_pastorais 
        (id, membro_id, pastoral_id, data_inicio, status, prioridade) 
        VALUES (?, ?, ?, CURDATE(), 'ativo', 'secundaria')
    ";
    
    error_log("vincular_membro.php: Query: " . $query);
    error_log("vincular_membro.php: Parâmetros: id=$vinculo_id, membro_id=$membro_id, pastoral_id=$pastoral_id");
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute([$vinculo_id, $membro_id, $pastoral_id]);
    
    error_log("vincular_membro.php: Execute result: " . ($success ? 'SUCCESS' : 'FAILED'));
    
    if (!$success) {
        error_log("vincular_membro.php: Erro ao executar query");
        Response::error('Erro ao vincular membro à pastoral', 500);
    }
    
    Response::success(['id' => $vinculo_id], 'Membro vinculado à pastoral com sucesso');
    
} catch (Exception $e) {
    error_log("Erro ao vincular membro: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

