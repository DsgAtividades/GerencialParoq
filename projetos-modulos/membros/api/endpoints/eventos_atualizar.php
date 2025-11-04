<?php
/**
 * Endpoint: Atualizar Evento Geral
 * Método: PUT
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
    $checkQuery = "SELECT id FROM membros_eventos WHERE id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$evento_id]);
    $eventoExiste = $checkStmt->fetch();
    
    if (!$eventoExiste) {
        Response::error('Evento não encontrado', 404);
    }
    
    // Obter dados do corpo da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        Response::error('Dados inválidos no corpo da requisição', 400);
    }
    
    // Validações
    if (isset($input['nome']) && empty(trim($input['nome']))) {
        Response::error('Nome do evento não pode ser vazio', 400);
    }
    
    if (isset($input['tipo'])) {
        $tiposPermitidos = ['missa', 'reuniao', 'formacao', 'acao_social', 'feira', 'festa_patronal', 'outro'];
        if (!in_array($input['tipo'], $tiposPermitidos)) {
            Response::error('Tipo de evento inválido', 400);
        }
    }
    
    if (isset($input['data_evento'])) {
        // Validar formato de data
        $data = DateTime::createFromFormat('Y-m-d', $input['data_evento']);
        if (!$data || $data->format('Y-m-d') !== $input['data_evento']) {
            Response::error('Formato de data inválido. Use YYYY-MM-DD', 400);
        }
    }
    
    // Construir query de atualização dinâmica
    $campos = [];
    $valores = [];
    
    // Campos permitidos para atualização
    $camposPermitidos = [
        'nome', 'tipo', 'data_evento', 'horario', 
        'local', 'responsavel_id', 'descricao', 'ativo'
    ];
    
    foreach ($camposPermitidos as $campo) {
        if (isset($input[$campo])) {
            $campos[] = "$campo = ?";
            // Converter string vazia em null para campos opcionais
            if (in_array($campo, ['horario', 'local', 'responsavel_id', 'descricao']) && $input[$campo] === '') {
                $valores[] = null;
            } else {
                $valores[] = $input[$campo];
            }
        }
    }
    
    if (empty($campos)) {
        Response::error('Nenhum campo para atualizar', 400);
    }
    
    // Adicionar updated_at
    $campos[] = "updated_at = CURRENT_TIMESTAMP";
    
    // Adicionar ID ao final para o WHERE
    $valores[] = $evento_id;
    
    // Construir query
    $camposStr = implode(', ', $campos);
    $query = "UPDATE membros_eventos SET $camposStr WHERE id = ?";
    
    error_log("eventos_atualizar.php: Atualizando evento ID: $evento_id");
    error_log("eventos_atualizar.php: Query: $query");
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute($valores);
    
    if (!$success) {
        error_log("eventos_atualizar.php: Erro ao executar query");
        Response::error('Erro ao atualizar evento', 500);
    }
    
    // Buscar o evento atualizado
    $buscarQuery = "
        SELECT 
            e.id,
            e.nome,
            e.nome as titulo,
            e.descricao,
            e.data_evento,
            e.horario as hora_inicio,
            e.local,
            e.tipo,
            e.responsavel_id,
            e.ativo,
            e.created_at,
            e.updated_at,
            m.nome_completo as responsavel_nome,
            0 as total_inscritos
        FROM membros_eventos e
        LEFT JOIN membros_membros m ON e.responsavel_id = m.id
        WHERE e.id = ?
    ";
    
    $buscarStmt = $db->prepare($buscarQuery);
    $buscarStmt->execute([$evento_id]);
    $evento = $buscarStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        Response::error('Erro ao recuperar evento atualizado', 500);
    }
    
    error_log("eventos_atualizar.php: Evento atualizado com sucesso");
    
    Response::success($evento, 'Evento atualizado com sucesso');
    
} catch (Exception $e) {
    error_log("Erro ao atualizar evento geral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

