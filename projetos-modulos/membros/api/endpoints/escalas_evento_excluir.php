<?php
/**
 * Endpoint: Excluir evento de escala
 * Método: DELETE
 * URL: /api/eventos/{id}
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Response.php';

try {
    global $evento_id;
    if (empty($evento_id)) {
        Response::error('Evento não informado', 400);
    }
    $db = new MembrosDatabase();
    
    // Verificar se o evento existe
    $evStmt = $db->prepare("SELECT id FROM membros_escalas_eventos WHERE id = ?");
    $evStmt->execute([$evento_id]);
    $evento = $evStmt->fetch(PDO::FETCH_ASSOC);
    if (!$evento) {
        Response::error('Evento não encontrado', 404);
    }
    
    // Iniciar transação
    $db->beginTransaction();
    
    try {
        // Buscar IDs das funções do evento
        $funStmt = $db->prepare("SELECT id FROM membros_escalas_funcoes WHERE evento_id = ?");
        $funStmt->execute([$evento_id]);
        $funcoes = $funStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Excluir membros atribuídos às funções
        if (!empty($funcoes)) {
            $ph = implode(',', array_fill(0, count($funcoes), '?'));
            $delMembros = $db->prepare("DELETE FROM membros_escalas_funcao_membros WHERE funcao_id IN ($ph)");
            $delMembros->execute($funcoes);
        }
        
        // Excluir funções do evento
        $delFuncoes = $db->prepare("DELETE FROM membros_escalas_funcoes WHERE evento_id = ?");
        $delFuncoes->execute([$evento_id]);
        
        // Excluir logs do evento
        $delLogs = $db->prepare("DELETE FROM membros_escalas_logs WHERE evento_id = ?");
        $delLogs->execute([$evento_id]);
        
        // Excluir o evento
        $delEvento = $db->prepare("DELETE FROM membros_escalas_eventos WHERE id = ?");
        $delEvento->execute([$evento_id]);
        
        // Confirmar transação
        $db->commit();
        
        Response::success(['evento_id' => $evento_id], 'Evento excluído com sucesso');
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $db->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    error_log('Erro ao excluir evento: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}

