<?php
/**
 * Endpoint: Buscar Pastorais de um Membro
 * Método: GET
 * URL: /api/membros/{id}/pastorais
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
    
    // Buscar pastorais do membro
    $query = "
        SELECT 
            p.id,
            p.nome,
            p.tipo,
            mp.funcao_id,
            mp.data_inicio,
            mp.data_fim,
            mp.status as status_vinculo,
            mp.prioridade,
            mp.carga_horaria_semanal
        FROM membros_membros_pastorais mp
        LEFT JOIN membros_pastorais p ON mp.pastoral_id = p.id
        WHERE mp.membro_id = ?
        ORDER BY mp.data_inicio DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$membro_id]);
    $pastorais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    Response::success($pastorais);
    
} catch (Exception $e) {
    error_log("Erro ao buscar pastorais do membro: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

