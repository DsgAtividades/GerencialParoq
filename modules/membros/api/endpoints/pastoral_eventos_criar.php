<?php
/**
 * Endpoint: Criar Evento de Pastoral
 * Método: POST
 * URL: /api/pastorais/{id}/eventos
 */

require_once '../config/database.php';
require_once 'utils/Permissions.php';

// Verificar permissão específica para gerenciar eventos de pastorais
// Tanto Madmin quanto 'membros' podem gerenciar eventos de pastorais
if (!Permissions::canManagePastoralEventos()) {
    Permissions::denyAccess('criar eventos de pastorais');
}

try {
    global $pastoral_id;
    
    if (!isset($pastoral_id) || empty($pastoral_id)) {
        Response::error('ID da pastoral é obrigatório', 400);
    }
    
    // Obter dados do body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        Response::error('Dados inválidos', 400);
    }
    
    error_log("pastoral_eventos_criar.php: Criando evento para pastoral_id = " . $pastoral_id);
    
    // Validações obrigatórias
    if (empty($input['nome']) || !isset($input['nome'])) {
        Response::error('Nome do evento é obrigatório', 400);
    }
    
    if (empty($input['data_evento']) || !isset($input['data_evento'])) {
        Response::error('Data do evento é obrigatória', 400);
    }
    
    $db = new MembrosDatabase();
    
    // Verificar se a pastoral existe
    $checkPastoral = $db->prepare("SELECT id FROM membros_pastorais WHERE id = ?");
    $checkPastoral->execute([$pastoral_id]);
    $pastoralExiste = $checkPastoral->fetch(PDO::FETCH_ASSOC);
    
    if (!$pastoralExiste) {
        Response::error('Pastoral não encontrada', 404);
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
    $campos = ['id', 'pastoral_id', 'nome', 'data_evento'];
    $valores = [$evento_id, $pastoral_id, trim($input['nome']), $input['data_evento']];
    
    // Campos opcionais
    $camposOpcionais = ['tipo', 'horario', 'local', 'responsavel_id', 'descricao', 'ativo'];
    
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
    
    $query = "INSERT INTO membros_eventos_pastorais ($camposStr) VALUES ($placeholders)";
    
    error_log("pastoral_eventos_criar.php: Query - " . $query);
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute($valores);
    
    if (!$success) {
        error_log("pastoral_eventos_criar.php: Erro ao executar query");
        Response::error('Erro ao criar evento', 500);
    }
    
    // Buscar o evento criado com informações adicionais
    $buscarQuery = "
        SELECT 
            e.*,
            p.nome as pastoral_nome,
            m.nome_completo as responsavel_nome
        FROM membros_eventos_pastorais e
        LEFT JOIN membros_pastorais p ON e.pastoral_id = p.id
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
    error_log("Erro ao criar evento da pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

