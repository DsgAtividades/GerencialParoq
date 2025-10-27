<?php
/**
 * Endpoint: Coordenadores da Pastoral
 * Método: GET
 * URL: /api/pastorais/{id}/coordenadores
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    
    // Verificar se o ID foi fornecido
    if (!isset($pastoral_id) || empty($pastoral_id)) {
        Response::error('ID da pastoral é obrigatório', 400);
    }
    
    // Buscar coordenadores da pastoral
    $query = "
        SELECT 
            m.id,
            m.nome_completo,
            m.apelido,
            m.email,
            COALESCE(m.celular_whatsapp, m.telefone_fixo) as telefone,
            m.foto_url,
            f.nome as funcao,
            mp.data_inicio,
            mp.prioridade
        FROM membros_membros_pastorais mp
        INNER JOIN membros_membros m ON mp.membro_id = m.id
        LEFT JOIN membros_funcoes f ON mp.funcao_id = f.id
        WHERE mp.pastoral_id = ? 
        AND mp.status = 'ativo'
        AND (mp.prioridade >= 5 OR f.tipo = 'coordenacao')
        ORDER BY mp.prioridade DESC, m.nome_completo
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$pastoral_id]);
    $coordenadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    Response::success($coordenadores);
    
} catch (Exception $e) {
    error_log("Erro ao buscar coordenadores da pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>


