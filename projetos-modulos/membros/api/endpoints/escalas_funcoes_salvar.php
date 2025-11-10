<?php
/**
 * Endpoint: Salvar funções e atribuições do evento
 * Método: POST
 * URL: /api/eventos/{id}/funcoes
 * Body: { funcoes: [ {id, nome, membros: [membro_id,...]} , ... ] }
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/escalas_helpers.php';

try {
    global $evento_id;
    if (empty($evento_id)) {
        Response::error('Evento não informado', 400);
    }
    $db = new MembrosDatabase();
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $funcoes = $body['funcoes'] ?? [];
    
    // Upsert de funções e membros
    foreach ($funcoes as $idx => $f) {
        $fid = $f['id'] ?? null;
        $nome = trim($f['nome'] ?? '');
        $ordem = $idx;
        if ($fid) {
            $upd = $db->prepare("UPDATE membros_escalas_funcoes SET nome_funcao = ?, ordem = ? WHERE id = ? AND evento_id = ?");
            $upd->execute([$nome, $ordem, $fid, $evento_id]);
        } else {
            $fid = uuid_v4();
            $ins = $db->prepare("INSERT INTO membros_escalas_funcoes (id, evento_id, nome_funcao, ordem) VALUES (?, ?, ?, ?)");
            $ins->execute([$fid, $evento_id, $nome, $ordem]);
        }
        // Reconciliar membros: estratégia simples = limpar e inserir
        $del = $db->prepare("DELETE FROM membros_escalas_funcao_membros WHERE funcao_id = ?");
        $del->execute([$fid]);
        $membros = $f['membros'] ?? [];
        foreach ($membros as $mid) {
            if (!$mid) continue;
            $rid = uuid_v4();
            $insm = $db->prepare("INSERT INTO membros_escalas_funcao_membros (id, funcao_id, membro_id) VALUES (?, ?, ?)");
            $insm->execute([$rid, $fid, $mid]);
        }
    }
    Response::success(['evento_id' => $evento_id], 'Escala salva com sucesso');
} catch (Exception $e) {
    error_log('Erro ao salvar funcoes/atribuicoes: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
