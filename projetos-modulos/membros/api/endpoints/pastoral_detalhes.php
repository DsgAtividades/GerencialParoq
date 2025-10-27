<?php
/**
 * Endpoint: Detalhes da Pastoral
 * Método: GET
 * URL: /api/pastorais/{id}
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    
    // Verificar se o ID foi fornecido
    if (!isset($pastoral_id) || empty($pastoral_id)) {
        Response::error('ID da pastoral é obrigatório', 400);
    }
    
    // Validar formato do UUID
    if (!preg_match('/^[a-f0-9\-]{36}$/', $pastoral_id)) {
        Response::error('ID inválido', 400);
    }
    
    // Buscar dados da pastoral
    $query = "SELECT * FROM membros_pastorais WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$pastoral_id]);
    $pastoral = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pastoral) {
        Response::error('Pastoral não encontrada', 404);
    }
    
    Response::success($pastoral);
    
} catch (Exception $e) {
    error_log("Erro ao buscar pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>


