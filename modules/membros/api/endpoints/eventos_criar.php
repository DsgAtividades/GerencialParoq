<?php
/**
 * Endpoint: Criar Evento Geral
 * Método: POST
 * URL: /api/eventos
 */

require_once '../config/database.php';
require_once 'utils/Permissions.php';

// Verificar permissão específica para criar eventos gerais (aba Eventos)
// Apenas Madmin pode criar eventos gerais
if (!Permissions::canCreateEventos()) {
    Permissions::denyAccess('criar eventos');
}

try {
    $db = new MembrosDatabase();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        Response::error('Dados inválidos', 400);
    }
    
    error_log("eventos_criar.php: Criando evento geral");
    
    // Validações obrigatórias
    if (empty($input['nome']) || !isset($input['nome'])) {
        Response::error('Nome do evento é obrigatório', 400);
    }
    
    if (empty($input['data_evento']) || !isset($input['data_evento'])) {
        Response::error('Data do evento é obrigatória', 400);
    }
    
    if (empty($input['tipo']) || !isset($input['tipo'])) {
        Response::error('Tipo do evento é obrigatório', 400);
    }
    
    // Validar tipo
    $tiposPermitidos = ['missa', 'reuniao', 'formacao', 'acao_social', 'feira', 'festa_patronal', 'outro'];
    if (!in_array($input['tipo'], $tiposPermitidos)) {
        Response::error('Tipo de evento inválido', 400);
    }
    
    // Gerar UUID para o ID
    $evento_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    // Campos permitidos
    $campos = ['id', 'nome', 'tipo', 'data_evento'];
    $valores = [$evento_id, trim($input['nome']), $input['tipo'], $input['data_evento']];
    
    // Campos opcionais
    $camposOpcionais = ['horario', 'local', 'responsavel_id', 'descricao', 'ativo'];
    
    foreach ($camposOpcionais as $campo) {
        if (isset($input[$campo])) {
            $campos[] = $campo;
            $valores[] = $input[$campo] === '' ? null : $input[$campo];
        }
    }
    
    // Se ativo não foi informado, definir como 1 (ativo)
    if (!isset($input['ativo'])) {
        $campos[] = 'ativo';
        $valores[] = 1;
    }
    
    // Construir query
    $placeholders = implode(',', array_fill(0, count($valores), '?'));
    $camposStr = implode(', ', $campos);
    
    $query = "INSERT INTO membros_eventos ($camposStr) VALUES ($placeholders)";
    
    error_log("eventos_criar.php: Query - " . $query);
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute($valores);
    
    if (!$success) {
        error_log("eventos_criar.php: Erro ao executar query");
        Response::error('Erro ao criar evento', 500);
    }
    
    // Buscar o evento criado com informações adicionais
    $buscarQuery = "
        SELECT 
            e.*,
            m.nome_completo as responsavel_nome
        FROM membros_eventos e
        LEFT JOIN membros_membros m ON e.responsavel_id = m.id
        WHERE e.id = ?
    ";
    
    $buscarStmt = $db->prepare($buscarQuery);
    $buscarStmt->execute([$evento_id]);
    $evento = $buscarStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        Response::error('Erro ao recuperar evento criado', 500);
    }
    
    Response::success($evento, 'Evento criado com sucesso');
    
} catch (Exception $e) {
    error_log("Erro ao criar evento geral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

