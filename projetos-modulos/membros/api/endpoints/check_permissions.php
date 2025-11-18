<?php
/**
 * Endpoint: Verificar Permissões do Usuário
 * Método: GET
 * URL: /api/check-permissions
 * 
 * Retorna informações sobre as permissões do usuário atual
 */

require_once __DIR__ . '/../utils/Permissions.php';
require_once __DIR__ . '/../utils/Response.php';

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $user = Permissions::getCurrentUser();
    
    if (empty($user)) {
        Response::error('Usuário não autenticado', 401);
    }
    
    Response::success([
        'is_admin' => Permissions::isAdmin(),
        'can_modify' => Permissions::canModify(),
        'user' => $user
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao verificar permissões: " . $e->getMessage());
    Response::error('Erro interno do servidor', 500);
}
?>

