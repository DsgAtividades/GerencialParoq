<?php
/**
 * Endpoint: Listar eventos de escala por semana
 * Método: GET
 * URL: /api/escalas/semana?pastoral_id=UUID&start=YYYY-MM-DD&end=YYYY-MM-DD
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Response.php';

try {
    $db = new MembrosDatabase();
    
    $pastoralId = $_GET['pastoral_id'] ?? '';
    $start = $_GET['start'] ?? '';
    $end = $_GET['end'] ?? '';
    
    if (!$pastoralId || !$start || !$end) {
        Response::error('Parâmetros obrigatórios: pastoral_id, start, end', 400);
    }
    
    $sql = "
        SELECT 
            id,
            titulo,
            descricao,
            data,
            hora
        FROM membros_escalas_eventos
        WHERE pastoral_id = ?
          AND data BETWEEN ? AND ?
        ORDER BY data ASC, hora ASC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([$pastoralId, $start, $end]);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    Response::success($eventos);
} catch (Exception $e) {
    error_log('Erro ao listar escalas semana: ' . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

