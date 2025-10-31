<?php
/**
 * Endpoint: Exportar TXT do evento
 * Método: GET
 * URL: /api/eventos/{id}/export/txt
 */

require_once __DIR__ . '/../../config/database.php';

try {
    global $evento_id;
    if (empty($evento_id)) {
        http_response_code(400);
        echo 'Evento não informado';
        exit;
    }
    $db = new MembrosDatabase();
    $ev = $db->prepare("SELECT titulo, data, hora FROM membros_escalas_eventos WHERE id = ?");
    $ev->execute([$evento_id]);
    $evento = $ev->fetch(PDO::FETCH_ASSOC);
    if (!$evento) { http_response_code(404); echo 'Evento não encontrado'; exit; }
    $fx = $db->prepare("SELECT id, nome FROM membros_escalas_funcoes WHERE evento_id = ? ORDER BY ordem, nome");
    $fx->execute([$evento_id]);
    $funcoes = $fx->fetchAll(PDO::FETCH_ASSOC);
    $conteudo = [];
    $conteudo[] = $evento['titulo'] . ' - ' . $evento['data'] . ' ' . substr($evento['hora'],0,5);
    foreach ($funcoes as $f) {
        $mx = $db->prepare("SELECT m.nome_completo as nome FROM membros_escalas_funcao_membros fm LEFT JOIN membros_membros m ON m.id = fm.membro_id WHERE fm.funcao_id = ? ORDER BY m.nome_completo");
        $mx->execute([$f['id']]);
        $ms = $mx->fetchAll(PDO::FETCH_COLUMN);
        $linha = $f['nome'] . ': ' . (empty($ms) ? '-' : implode(', ', $ms));
        $conteudo[] = $linha;
    }
    $txt = implode("\r\n", $conteudo);
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="escala.txt"');
    echo $txt;
} catch (Exception $e) {
    http_response_code(500);
    echo 'Erro na exportação';
}
?>

