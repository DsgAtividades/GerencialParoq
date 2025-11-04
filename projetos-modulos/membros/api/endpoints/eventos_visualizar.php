<?php
/**
 * Endpoint: Visualizar Evento Geral (Buscar por ID)
 * Método: GET
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
    
    // Buscar evento com informações adicionais
    $query = "
        SELECT 
            e.id,
            e.nome,
            e.nome as titulo,
            e.descricao,
            e.data_evento,
            e.horario,
            e.horario as hora_inicio,
            e.local,
            e.tipo,
            e.responsavel_id,
            e.ativo,
            e.created_at,
            e.updated_at,
            m.nome_completo as responsavel_nome,
            m.apelido as responsavel_apelido,
            0 as total_inscritos
        FROM membros_eventos e
        LEFT JOIN membros_membros m ON e.responsavel_id = m.id
        WHERE e.id = ?
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$evento_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        Response::error('Evento não encontrado', 404);
    }
    
    // Formatar nome do responsável (usar apelido se nome completo não existir)
    if ($evento['responsavel_id']) {
        $evento['responsavel_nome'] = $evento['responsavel_nome'] ?: $evento['responsavel_apelido'];
        unset($evento['responsavel_apelido']);
    }
    
    Response::success($evento);
    
} catch (Exception $e) {
    error_log("Erro ao buscar evento geral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

