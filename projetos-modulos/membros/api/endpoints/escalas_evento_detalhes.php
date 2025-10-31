<?php
/**
 * Endpoint: Detalhes do evento (funcoes + membros atribuídos)
 * Método: GET
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
    
    $evStmt = $db->prepare("SELECT * FROM membros_escalas_eventos WHERE id = ?");
    $evStmt->execute([$evento_id]);
    $evento = $evStmt->fetch(PDO::FETCH_ASSOC);
    if (!$evento) {
        Response::error('Evento não encontrado', 404);
    }
    
    $funStmt = $db->prepare("SELECT * FROM membros_escalas_funcoes WHERE evento_id = ? ORDER BY ordem, nome");
    $funStmt->execute([$evento_id]);
    $funcoes = $funStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $idsFuncoes = array_column($funcoes, 'id');
    $atribuicoes = [];
    if (!empty($idsFuncoes)) {
        $ph = implode(',', array_fill(0, count($idsFuncoes), '?'));
        $attStmt = $db->prepare("SELECT fm.*, m.nome_completo as membro_nome
                                  FROM membros_escalas_funcao_membros fm
                                  LEFT JOIN membros_membros m ON m.id = fm.membro_id
                                  WHERE fm.funcao_id IN ($ph)");
        $attStmt->execute($idsFuncoes);
        $atribuicoes = $attStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Mapear membros por funcao
    $membrosPorFuncao = [];
    foreach ($atribuicoes as $a) {
        $fid = $a['funcao_id'];
        if (!isset($membrosPorFuncao[$fid])) $membrosPorFuncao[$fid] = [];
        $membrosPorFuncao[$fid][] = [ 'id' => $a['membro_id'], 'nome' => $a['membro_nome'] ];
    }
    
    foreach ($funcoes as &$f) {
        $f['membros'] = $membrosPorFuncao[$f['id']] ?? [];
    }
    unset($f);
    
    Response::success(['evento' => $evento, 'funcoes' => $funcoes]);
} catch (Exception $e) {
    error_log('Erro ao buscar detalhes do evento: ' . $e->getMessage());
    Response::error('Erro interno do servidor', 500);
}
?>

