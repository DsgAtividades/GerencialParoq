<?php
/**
 * Endpoint: Atualizar Evento de Pastoral
 * Método: PUT
 * URL: /api/pastorais/{pastoral_id}/eventos/{evento_id}
 */

require_once '../config/database.php';
require_once 'utils/Permissions.php';

// Verificar permissão específica para gerenciar eventos de pastorais
// Tanto Madmin quanto 'membros' podem gerenciar eventos de pastorais
if (!Permissions::canManagePastoralEventos()) {
    Permissions::denyAccess('editar eventos de pastorais');
}

try {
    global $pastoral_id, $evento_id;
    
    if (!isset($pastoral_id) || empty($pastoral_id)) {
        Response::error('ID da pastoral é obrigatório', 400);
    }
    
    if (!isset($evento_id) || empty($evento_id)) {
        Response::error('ID do evento é obrigatório', 400);
    }
    
    // Obter dados do body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        Response::error('Dados inválidos', 400);
    }
    
    error_log("pastoral_eventos_atualizar.php: Atualizando evento $evento_id da pastoral $pastoral_id");
    
    $db = new MembrosDatabase();
    
    // Verificar se o evento existe e pertence à pastoral
    $checkEvento = $db->prepare("SELECT id FROM membros_eventos_pastorais WHERE id = ? AND pastoral_id = ?");
    $checkEvento->execute([$evento_id, $pastoral_id]);
    $eventoExiste = $checkEvento->fetch(PDO::FETCH_ASSOC);
    
    if (!$eventoExiste) {
        Response::error('Evento não encontrado ou não pertence a esta pastoral', 404);
    }
    
    // Construir query de atualização dinamicamente
    $campos = [];
    $valores = [];
    
    // Campos permitidos para atualização
    $camposPermitidos = ['nome', 'tipo', 'data_evento', 'horario', 'local', 'responsavel_id', 'descricao', 'ativo'];
    
    foreach ($camposPermitidos as $campo) {
        if (isset($input[$campo])) {
            $campos[] = "{$campo} = ?";
            $valores[] = $input[$campo] === '' ? null : $input[$campo];
        }
    }
    
    // Adicionar updated_at
    $campos[] = "updated_at = NOW()";
    
    if (empty($campos)) {
        Response::error('Nenhum campo para atualizar', 400);
    }
    
    $valores[] = $evento_id;
    $valores[] = $pastoral_id;
    
    $query = "UPDATE membros_eventos_pastorais SET " . implode(', ', $campos) . " WHERE id = ? AND pastoral_id = ?";
    
    error_log("pastoral_eventos_atualizar.php: Query - " . $query);
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute($valores);
    
    if (!$success) {
        Response::error('Erro ao atualizar evento', 500);
    }
    
    // Buscar o evento atualizado
    $buscarQuery = "
        SELECT 
            e.*,
            p.nome as pastoral_nome,
            m.nome_completo as responsavel_nome
        FROM membros_eventos_pastorais e
        LEFT JOIN membros_pastorais p ON e.pastoral_id = p.id
        LEFT JOIN membros_membros m ON e.responsavel_id = m.id
        WHERE e.id = ? AND e.pastoral_id = ?
    ";
    
    $buscarStmt = $db->prepare($buscarQuery);
    $buscarStmt->execute([$evento_id, $pastoral_id]);
    $evento = $buscarStmt->fetch(PDO::FETCH_ASSOC);
    
    Response::success($evento, 'Evento atualizado com sucesso');
    
} catch (Exception $e) {
    error_log("Erro ao atualizar evento da pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

