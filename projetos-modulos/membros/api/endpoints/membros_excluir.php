<?php
/**
 * Endpoint: Excluir Membro
 * Método: DELETE
 * URL: /api/membros/{id}
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    
    // Verificar se o ID foi fornecido
    if (!isset($membro_id) || empty($membro_id)) {
        Response::error('ID do membro é obrigatório', 400);
    }
    
    // Validar formato do UUID
    if (!preg_match('/^[a-f0-9\-]{36}$/', $membro_id)) {
        Response::error('ID inválido', 400);
    }
    
    // Verificar se o membro existe
    $stmt = $db->prepare("SELECT id, nome_completo FROM membros_membros WHERE id = ?");
    $stmt->execute([$membro_id]);
    $membro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$membro) {
        Response::error('Membro não encontrado', 404);
    }
    
    // Iniciar transação para exclusão em cascata
    $db->beginTransaction();
    
    try {
        // Excluir relacionamentos primeiro (devido às foreign keys)
        
        // 1. Excluir vínculos com pastorais
        $db->prepare("DELETE FROM membros_membros_pastorais WHERE membro_id = ?")->execute([$membro_id]);
        
        // 2. Excluir formações do membro
        $db->prepare("DELETE FROM membros_membros_formacoes WHERE membro_id = ?")->execute([$membro_id]);
        
        // 3. Excluir endereços
        $db->prepare("DELETE FROM membros_enderecos_membro WHERE membro_id = ?")->execute([$membro_id]);
        
        // 4. Excluir contatos
        $db->prepare("DELETE FROM membros_contatos_membro WHERE membro_id = ?")->execute([$membro_id]);
        
        // 5. Excluir documentos
        $db->prepare("DELETE FROM membros_documentos_membro WHERE membro_id = ?")->execute([$membro_id]);
        
        // 6. Excluir consentimentos LGPD
        $db->prepare("DELETE FROM membros_consentimentos_lgpd WHERE membro_id = ?")->execute([$membro_id]);
        
        // 7. Excluir check-ins
        $db->prepare("DELETE FROM membros_checkins WHERE membro_id = ?")->execute([$membro_id]);
        
        // 8. Excluir alocações
        $db->prepare("DELETE FROM membros_alocacoes WHERE membro_id = ?")->execute([$membro_id]);
        
        // 9. Excluir candidaturas
        $db->prepare("DELETE FROM membros_candidaturas WHERE membro_id = ?")->execute([$membro_id]);
        
        // 10. Excluir comunicados criados pelo membro (se a tabela existir)
        try {
            $db->prepare("DELETE FROM membros_comunicados WHERE created_by = ?")->execute([$membro_id]);
        } catch (Exception $e) {
            // Ignorar se a tabela não existir
            error_log("Aviso: Tabela membros_comunicados não encontrada ou sem campo created_by");
        }
        
        // 11. Excluir anexos criados pelo membro (se a tabela existir)
        try {
            $db->prepare("DELETE FROM membros_anexos WHERE created_by = ?")->execute([$membro_id]);
        } catch (Exception $e) {
            // Ignorar se a tabela não existir
            error_log("Aviso: Tabela membros_anexos não encontrada ou sem campo created_by");
        }
        
        // 12. Excluir logs de auditoria (se a tabela existir)
        try {
            $db->prepare("DELETE FROM membros_auditoria_logs WHERE usuario_id = ?")->execute([$membro_id]);
        } catch (Exception $e) {
            // Ignorar se a tabela não existir
            error_log("Aviso: Tabela membros_auditoria_logs não encontrada ou sem campo usuario_id");
        }
        
        // 13. Finalmente, excluir o membro
        $stmt = $db->prepare("DELETE FROM membros_membros WHERE id = ?");
        $stmt->execute([$membro_id]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Falha ao excluir membro');
        }
        
        // Confirmar transação
        $db->commit();
        
        // Log da exclusão
        error_log("Membro excluído: {$membro['nome_completo']} (ID: {$membro_id})");
        
        Response::success([
            'message' => 'Membro excluído com sucesso',
            'membro' => [
                'id' => $membro_id,
                'nome' => $membro['nome_completo']
            ]
        ]);
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Erro ao excluir membro: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>
