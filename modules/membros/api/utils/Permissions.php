<?php
/**
 * Sistema de Verificação de Permissões - Módulo Membros
 * 
 * Duas camadas de segurança:
 * - Madmin: Acesso total ao sistema
 * - membros: Acesso limitado (visualização e operações específicas)
 */

class Permissions {
    
    /**
     * Verifica se o usuário atual tem permissões de administrador
     * 
     * @return bool True se o usuário é "Madmin", false caso contrário
     */
    public static function isAdmin() {
        // Verificar se a sessão está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar se o usuário está logado no módulo
        if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
            return false;
        }
        
        // Verificar se o username é "Madmin"
        $username = $_SESSION['module_username'] ?? '';
        return strtolower(trim($username)) === 'madmin';
    }
    
    /**
     * Retorna o role/papel do usuário atual
     * 
     * @return string|null 'Madmin', 'membros' ou null se não autenticado
     */
    public static function getUserRole() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
            return null;
        }
        
        $username = strtolower(trim($_SESSION['module_username'] ?? ''));
        
        if ($username === 'madmin') {
            return 'Madmin';
        } elseif ($username === 'membros') {
            return 'membros';
        }
        
        return null;
    }
    
    /**
     * Verifica se o usuário está autenticado (qualquer role)
     * 
     * @return bool
     */
    public static function isAuthenticated() {
        return self::getUserRole() !== null;
    }
    
    // =====================================================
    // PERMISSÕES DE MEMBROS
    // =====================================================
    
    /**
     * Pode visualizar membros
     * Madmin: SIM | membros: SIM
     */
    public static function canViewMembros() {
        $role = self::getUserRole();
        return in_array($role, ['Madmin', 'membros']);
    }
    
    /**
     * Pode criar membros
     * Madmin: SIM | membros: NÃO
     */
    public static function canCreateMembros() {
        return self::isAdmin();
    }
    
    /**
     * Pode editar membros
     * Madmin: SIM | membros: NÃO
     */
    public static function canEditMembros() {
        return self::isAdmin();
    }
    
    /**
     * Pode excluir membros
     * Madmin: SIM | membros: NÃO
     */
    public static function canDeleteMembros() {
        return self::isAdmin();
    }
    
    /**
     * Pode exportar membros
     * Madmin: SIM | membros: SIM
     */
    public static function canExportMembros() {
        $role = self::getUserRole();
        return in_array($role, ['Madmin', 'membros']);
    }
    
    /**
     * Pode importar membros
     * Madmin: SIM | membros: NÃO
     */
    public static function canImportMembros() {
        return self::isAdmin();
    }
    
    // =====================================================
    // PERMISSÕES DE PASTORAIS
    // =====================================================
    
    /**
     * Pode visualizar pastorais
     * Madmin: SIM | membros: SIM
     */
    public static function canViewPastorais() {
        $role = self::getUserRole();
        return in_array($role, ['Madmin', 'membros']);
    }
    
    /**
     * Pode criar pastorais
     * Madmin: SIM | membros: NÃO
     */
    public static function canCreatePastorais() {
        return self::isAdmin();
    }
    
    /**
     * Pode editar pastorais (nome, descrição, etc)
     * Madmin: SIM | membros: NÃO
     */
    public static function canEditPastorais() {
        return self::isAdmin();
    }
    
    /**
     * Pode excluir pastorais
     * Madmin: SIM | membros: NÃO
     */
    public static function canDeletePastorais() {
        return self::isAdmin();
    }
    
    /**
     * Pode adicionar/remover membros de pastorais
     * Madmin: SIM | membros: SIM
     */
    public static function canManagePastoralMembros() {
        $role = self::getUserRole();
        return in_array($role, ['Madmin', 'membros']);
    }
    
    /**
     * Pode gerenciar eventos de pastorais (criar/editar/excluir)
     * Madmin: SIM | membros: SIM
     */
    public static function canManagePastoralEventos() {
        $role = self::getUserRole();
        return in_array($role, ['Madmin', 'membros']);
    }
    
    /**
     * Pode gerenciar escalas de pastorais (criar/editar/excluir)
     * Madmin: SIM | membros: SIM
     */
    public static function canManagePastoralEscalas() {
        $role = self::getUserRole();
        return in_array($role, ['Madmin', 'membros']);
    }
    
    // =====================================================
    // PERMISSÕES DE EVENTOS
    // =====================================================
    
    /**
     * Pode visualizar eventos
     * Madmin: SIM | membros: SIM
     */
    public static function canViewEventos() {
        $role = self::getUserRole();
        return in_array($role, ['Madmin', 'membros']);
    }
    
    /**
     * Pode criar eventos (na aba eventos principal)
     * Madmin: SIM | membros: NÃO
     */
    public static function canCreateEventos() {
        return self::isAdmin();
    }
    
    /**
     * Pode editar eventos (na aba eventos principal)
     * Madmin: SIM | membros: NÃO
     */
    public static function canEditEventos() {
        return self::isAdmin();
    }
    
    /**
     * Pode excluir eventos (na aba eventos principal)
     * Madmin: SIM | membros: NÃO
     */
    public static function canDeleteEventos() {
        return self::isAdmin();
    }
    
    // =====================================================
    // PERMISSÕES DE RELATÓRIOS
    // =====================================================
    
    /**
     * Pode visualizar relatórios
     * Madmin: SIM | membros: SIM
     */
    public static function canViewRelatorios() {
        $role = self::getUserRole();
        return in_array($role, ['Madmin', 'membros']);
    }
    
    // =====================================================
    // MÉTODOS LEGADOS (mantidos para compatibilidade)
    // =====================================================
    
    /**
     * Verifica se o usuário tem permissão para criar/editar/excluir
     * (método legado - usar métodos específicos acima)
     * 
     * @return bool True se tem permissão, false caso contrário
     */
    public static function canModify() {
        return self::isAdmin();
    }
    
    /**
     * Retorna erro de permissão negada
     * 
     * @param string $action Ação que foi negada (ex: "criar membro")
     * @return void Envia resposta JSON e encerra execução
     */
    public static function denyAccess($action = 'realizar esta operação') {
        // Limpar buffer de saída antes de enviar resposta
        if (ob_get_level()) {
            ob_end_clean();
        }
        require_once __DIR__ . '/Response.php';
        Response::error(
            "Acesso negado. Apenas o administrador (Madmin) pode {$action}.", 
            403
        );
        exit;
    }
    
    /**
     * Verifica permissão e retorna erro se não tiver acesso
     * 
     * @param string $action Ação que está sendo verificada
     * @return void Envia resposta JSON e encerra execução se não tiver permissão
     */
    public static function requireAdmin($action = 'realizar esta operação') {
        if (!self::isAdmin()) {
            self::denyAccess($action);
        }
    }
    
    /**
     * Obtém informações do usuário atual
     * 
     * @return array Informações do usuário ou array vazio se não logado
     */
    public static function getCurrentUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
            return [];
        }
        
        return [
            'id' => $_SESSION['module_user_id'] ?? null,
            'username' => $_SESSION['module_username'] ?? null,
            'module_access' => $_SESSION['module_access'] ?? null,
            'is_admin' => self::isAdmin()
        ];
    }
}

