<?php
/**
 * Endpoint: Atualizar Evento Geral
 * Método: PUT
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
    $checkQuery = "SELECT id FROM membros_eventos WHERE id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$evento_id]);
    $eventoExiste = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$eventoExiste || $eventoExiste === false) {
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
    // Nota: A tabela usa 'horario' (singular), não 'hora_inicio' e 'hora_fim'
    $camposPermitidos = [
        'nome', 'tipo', 'data_evento', 'horario',
        'local', 'endereco', 'responsavel_id', 'descricao', 'Eventos_url', 'ativo'
    ];
    
    foreach ($camposPermitidos as $campo) {
        if (isset($input[$campo])) {
            $campos[] = "$campo = ?";
            // Converter string vazia em null para campos opcionais
            if (in_array($campo, ['horario', 'local', 'endereco', 'responsavel_id', 'descricao', 'Eventos_url']) && $input[$campo] === '') {
                $valores[] = null;
            } else {
                $valores[] = $input[$campo];
            }
        }
    }
    
    // Compatibilidade: Se hora_inicio foi enviado, converter para horario
    if (isset($input['hora_inicio']) && !isset($input['horario'])) {
        $campos[] = "horario = ?";
        $valores[] = $input['hora_inicio'] === '' ? null : $input['hora_inicio'];
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
    
    try {
        $success = $stmt->execute($valores);
        
        if (!$success) {
            error_log("eventos_atualizar.php: Erro ao executar query");
            Response::error('Erro ao atualizar evento', 500);
        }
    } catch (PDOException $e) {
        error_log("eventos_atualizar.php: Erro PDO: " . $e->getMessage());
        Response::error('Erro ao atualizar evento: ' . $e->getMessage(), 500);
    }
    
    // Buscar o evento atualizado
    $buscarQuery = "
        SELECT 
            e.id,
            e.nome,
            e.nome as titulo,
            e.descricao,
            e.Eventos_url as eventos_url,
            e.data_evento,
            e.horario,
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
    
    if (!$evento || $evento === false) {
        Response::error('Erro ao recuperar evento atualizado', 500);
    }
    
    error_log("eventos_atualizar.php: Evento atualizado com sucesso");
    
    Response::success($evento, 'Evento atualizado com sucesso');
    
} catch (Exception $e) {
    error_log("Erro ao atualizar evento geral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

