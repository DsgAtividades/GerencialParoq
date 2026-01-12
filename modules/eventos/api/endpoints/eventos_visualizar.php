<?php
/**
 * Endpoint: Visualizar Evento Geral (Buscar por ID)
 * Método: GET
 * URL: /api/eventos/{id}
 */

require_once __DIR__ . '/../../config/database.php';

try {
    // O ID vem via GET ou pode vir do router
    $evento_id = isset($_GET['id']) ? $_GET['id'] : null;
    
    // Verificar se o ID foi fornecido
    if (!isset($evento_id) || empty($evento_id)) {
        error_log("eventos_visualizar.php: ID do evento não fornecido");
        Response::error('ID do evento é obrigatório', 400);
    }
    
    $db = new EventosDatabase();
    
    error_log("eventos_visualizar.php: Buscando evento com ID: " . $evento_id);
    
    // Buscar evento com informações adicionais
    $query = "
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
            m.apelido as responsavel_apelido,
            0 as total_inscritos
        FROM membros_eventos e
        LEFT JOIN membros_membros m ON e.responsavel_id = m.id
        WHERE e.id = ?
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$evento_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento || $evento === false) {
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

