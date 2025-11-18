<?php
/**
 * Endpoint: Excluir Pastoral
 * Método: DELETE
 * URL: /api/pastorais/{id}
 */

require_once '../config/database.php';
require_once 'utils/Permissions.php';

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar permissão de administrador para excluir pastorais
Permissions::requireAdmin('excluir pastorais');

try {
    // A variável $pastoral_id é definida pelo routes.php
    global $pastoral_id;
    
    // Verificar se o ID foi fornecido
    if (!isset($pastoral_id) || empty($pastoral_id)) {
        Response::error('ID da pastoral é obrigatório', 400);
    }
    
    $db = new MembrosDatabase();
    
    // Verificar se a pastoral existe
    $checkQuery = "SELECT id, nome FROM membros_pastorais WHERE id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$pastoral_id]);
    $pastoral = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pastoral) {
        Response::error('Pastoral não encontrada', 404);
    }
    
    // Iniciar transação
    $db->beginTransaction();
    
    try {
        // Excluir relacionamentos primeiro
        
        // 1. Excluir vínculos de membros com a pastoral
        $db->prepare("DELETE FROM membros_membros_pastorais WHERE pastoral_id = ?")->execute([$pastoral_id]);
        
        // 2. Excluir eventos vinculados à pastoral
        $db->prepare("DELETE FROM membros_eventos_pastorais WHERE pastoral_id = ?")->execute([$pastoral_id]);
        
        // 3. Excluir escalas de eventos da pastoral
        $escalasStmt = $db->prepare("SELECT id FROM membros_escalas_eventos WHERE pastoral_id = ?");
        $escalasStmt->execute([$pastoral_id]);
        $escalas = $escalasStmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($escalas)) {
            $escalasPlaceholders = implode(',', array_fill(0, count($escalas), '?'));
            
            // Excluir membros atribuídos às funções
            $db->prepare("DELETE FROM membros_escalas_funcao_membros WHERE funcao_id IN (SELECT id FROM membros_escalas_funcoes WHERE evento_id IN ($escalasPlaceholders))")->execute($escalas);
            
            // Excluir funções das escalas
            $db->prepare("DELETE FROM membros_escalas_funcoes WHERE evento_id IN ($escalasPlaceholders)")->execute($escalas);
            
            // Excluir logs das escalas
            $db->prepare("DELETE FROM membros_escalas_logs WHERE evento_id IN ($escalasPlaceholders)")->execute($escalas);
            
            // Excluir as escalas
            $db->prepare("DELETE FROM membros_escalas_eventos WHERE pastoral_id = ?")->execute([$pastoral_id]);
        }
        
        // 4. Excluir candidaturas relacionadas
        $db->prepare("DELETE FROM membros_candidaturas WHERE pastoral_id = ?")->execute([$pastoral_id]);
        
        // 5. Finalmente, excluir a pastoral
        $deleteStmt = $db->prepare("DELETE FROM membros_pastorais WHERE id = ?");
        $deleteStmt->execute([$pastoral_id]);
        
        if ($deleteStmt->rowCount() === 0) {
            throw new Exception('Falha ao excluir pastoral');
        }
        
        // Confirmar transação
        $db->commit();
        
        // Invalidar cache
        try {
            require_once 'utils/Cache.php';
            $cache = new Cache();
            $cacheFiles = glob($cache->getCacheDir() . '*pastorais*');
            foreach ($cacheFiles as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        } catch (Exception $e) {
            // Não falhar por causa do cache
            error_log("Erro ao invalidar cache: " . $e->getMessage());
        }
        
        // Log da exclusão
        error_log("Pastoral excluída: {$pastoral['nome']} (ID: {$pastoral_id})");
        
        Response::success([
            'message' => 'Pastoral excluída com sucesso',
            'pastoral' => [
                'id' => $pastoral_id,
                'nome' => $pastoral['nome']
            ]
        ]);
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Erro ao excluir pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

