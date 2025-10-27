<?php
/**
 * Endpoint: Membros da Pastoral
 * Método: GET
 * URL: /api/pastorais/{id}/membros
 */

require_once '../config/database.php';

try {
    // A variável $pastoral_id é definida pelo routes.php
    global $pastoral_id;
    
    // Verificar se o ID foi fornecido
    if (!isset($pastoral_id) || empty($pastoral_id)) {
        error_log("pastoral_membros.php: ID da pastoral não fornecido");
        Response::error('ID da pastoral é obrigatório', 400);
    }
    
    error_log("pastoral_membros.php: Buscando membros para pastoral_id = " . $pastoral_id);
    
    $db = new MembrosDatabase();
    
    // Buscar membros da pastoral
    $query = "
        SELECT 
            m.id,
            m.nome_completo,
            m.apelido,
            m.email,
            COALESCE(m.celular_whatsapp, m.telefone_fixo) as telefone,
            m.status,
            m.foto_url,
            mp.funcao_id,
            mp.data_inicio,
            mp.data_fim,
            mp.status as status_vinculo
        FROM membros_membros_pastorais mp
        INNER JOIN membros_membros m ON mp.membro_id = m.id
        WHERE mp.pastoral_id = ? AND mp.status = 'ativo'
        ORDER BY m.nome_completo
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$pastoral_id]);
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    Response::success($membros);
    
} catch (Exception $e) {
    error_log("Erro ao buscar membros da pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>


