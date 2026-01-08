<?php
/**
 * Endpoint: Verificar Permissões do Usuário
 * Método: GET
 * URL: /api/check-permissions
 * 
 * Retorna informações sobre as permissões do usuário atual
 * Sistema de duas camadas: Madmin (acesso total) e membros (acesso limitado)
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
        // Informações básicas
        'is_admin' => Permissions::isAdmin(),
        'can_modify' => Permissions::canModify(), // Mantido para compatibilidade
        'user_role' => Permissions::getUserRole(),
        'user' => $user,
        
        // Permissões de Membros
        'membros' => [
            'view' => Permissions::canViewMembros(),
            'create' => Permissions::canCreateMembros(),
            'edit' => Permissions::canEditMembros(),
            'delete' => Permissions::canDeleteMembros(),
            'export' => Permissions::canExportMembros(),
            'import' => Permissions::canImportMembros()
        ],
        
        // Permissões de Pastorais
        'pastorais' => [
            'view' => Permissions::canViewPastorais(),
            'create' => Permissions::canCreatePastorais(),
            'edit' => Permissions::canEditPastorais(),
            'delete' => Permissions::canDeletePastorais(),
            'manage_membros' => Permissions::canManagePastoralMembros(),
            'manage_eventos' => Permissions::canManagePastoralEventos(),
            'manage_escalas' => Permissions::canManagePastoralEscalas()
        ],
        
        // Permissões de Eventos
        'eventos' => [
            'view' => Permissions::canViewEventos(),
            'create' => Permissions::canCreateEventos(),
            'edit' => Permissions::canEditEventos(),
            'delete' => Permissions::canDeleteEventos()
        ],
        
        // Permissões de Relatórios
        'relatorios' => [
            'view' => Permissions::canViewRelatorios()
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao verificar permissões: " . $e->getMessage());
    Response::error('Erro interno do servidor', 500);
}
?>

