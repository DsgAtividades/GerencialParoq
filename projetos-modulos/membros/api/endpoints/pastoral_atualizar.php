<?php
/**
 * Endpoint: Atualizar Pastoral
 * Método: PUT
 * URL: /api/pastorais/{id}
 */

require_once '../config/database.php';

try {
    // A variável $pastoral_id é definida pelo routes.php
    global $pastoral_id;
    
    // Verificar se o ID foi fornecido
    if (!isset($pastoral_id) || empty($pastoral_id)) {
        error_log("pastoral_atualizar.php: ID da pastoral não fornecido");
        Response::error('ID da pastoral é obrigatório', 400);
    }
    
    // Obter dados do body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        Response::error('Dados inválidos', 400);
    }
    
    error_log("pastoral_atualizar.php: Dados recebidos - " . json_encode($input));
    
    $db = new MembrosDatabase();
    
    // Aceita tanto UUIDs, IDs numéricos ou IDs com prefixo
    if (!preg_match('/^[a-f0-9\-]{36}$/', $pastoral_id) && !is_numeric($pastoral_id) && !preg_match('/^[a-z]+\-\d+$/', $pastoral_id)) {
        Response::error('ID inválido', 400);
    }
    
    // Verificar se a pastoral existe
    $checkQuery = "SELECT id FROM membros_pastorais WHERE id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$pastoral_id]);
    $existe = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existe) {
        Response::error('Pastoral não encontrada', 404);
    }
    
    // Construir query de atualização dinamicamente
    $campos = [];
    $valores = [];
    
    // Campos permitidos para atualização
    $camposPermitidos = [
        'nome',
        'tipo',
        'comunidade',
        'finalidade_descricao',
        'contato_whatsapp',
        'contato_email',
        'responsavel',
        'ativo'
    ];
    
    foreach ($camposPermitidos as $campo) {
        if (isset($input[$campo])) {
            $campos[] = "{$campo} = ?";
            $valores[] = $input[$campo];
        }
    }
    
    // Adicionar updated_at
    $campos[] = "updated_at = NOW()";
    
    if (empty($campos)) {
        Response::error('Nenhum campo para atualizar', 400);
    }
    
    $valores[] = $pastoral_id;
    
    $query = "UPDATE membros_pastorais SET " . implode(', ', $campos) . " WHERE id = ?";
    
    error_log("pastoral_atualizar.php: Query - " . $query);
    error_log("pastoral_atualizar.php: Valores - " . json_encode($valores));
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute($valores);
    
    if (!$success) {
        Response::error('Erro ao atualizar pastoral', 500);
    }
    
    // Buscar a pastoral atualizada
    $buscarQuery = "SELECT * FROM membros_pastorais WHERE id = ?";
    $buscarStmt = $db->prepare($buscarQuery);
    $buscarStmt->execute([$pastoral_id]);
    $pastoral = $buscarStmt->fetch(PDO::FETCH_ASSOC);
    
    Response::success($pastoral, 'Pastoral atualizada com sucesso');
    
} catch (Exception $e) {
    error_log("Erro ao atualizar pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

