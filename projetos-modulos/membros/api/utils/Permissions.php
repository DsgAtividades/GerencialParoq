<?php
/**
 * Sistema de Verificação de Permissões - Módulo Membros
 * 
 * Apenas o usuário "Madmin" com senha "admin123" tem permissões de administrador
 * e pode realizar operações de criar, editar e excluir membros e pastorais.
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
     * Verifica se o usuário tem permissão para criar/editar/excluir
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

