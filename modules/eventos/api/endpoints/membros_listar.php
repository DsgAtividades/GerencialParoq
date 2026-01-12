<?php
/**
 * Endpoint: Listar Membros (para select de responsável)
 * Método: GET
 * URL: /api/membros
 */

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new EventosDatabase();
    
    $query = "
        SELECT 
            id,
            nome_completo,
            apelido,
            email,
            celular_whatsapp as telefone
        FROM membros_membros
        WHERE status = 'ativo'
        ORDER BY nome_completo ASC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    Response::success($membros);
    
} catch (Exception $e) {
    Response::error('Erro ao carregar membros: ' . $e->getMessage(), 500);
}
?>

