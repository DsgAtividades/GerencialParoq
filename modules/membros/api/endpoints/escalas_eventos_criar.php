<?php
/**
 * Endpoint: Criar evento de escala (pastoral)
 * Método: POST
 * URL: /api/pastorais/{id}/escalas/eventos
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/escalas_helpers.php';

try {
    global $pastoral_id;
    if (empty($pastoral_id)) {
        Response::error('Pastoral não informada', 400);
    }
    
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $titulo = trim($input['titulo'] ?? '');
    $data = $input['data'] ?? '';
    $hora = $input['hora'] ?? '';
    $descricao = $input['descricao'] ?? null;
    
    if ($titulo === '' || $data === '' || $hora === '') {
        Response::error('Campos obrigatórios: titulo, data, hora', 400);
    }
    
    $eventoId = uuid_v4();
    $db = new MembrosDatabase();
    $stmt = $db->prepare("INSERT INTO membros_escalas_eventos (id, pastoral_id, titulo, descricao, data, hora) VALUES (?, ?, ?, ?, ?, ?)");
    $ok = $stmt->execute([$eventoId, $pastoral_id, $titulo, $descricao, $data, $hora]);
    if (!$ok) {
        Response::error('Erro ao criar evento', 500);
    }
    
    $fetch = $db->prepare("SELECT * FROM membros_escalas_eventos WHERE id = ?");
    $fetch->execute([$eventoId]);
    $evento = $fetch->fetch(PDO::FETCH_ASSOC);
    
    Response::success($evento, 'Evento criado com sucesso');
} catch (Exception $e) {
    error_log('Erro ao criar evento escala: ' . $e->getMessage());
    Response::error('Erro interno do servidor', 500);
}
?>

