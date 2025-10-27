<?php
/**
 * Endpoint: Coordenadores da Pastoral
 * Método: GET
 * URL: /api/pastorais/{id}/coordenadores
 */

require_once '../config/database.php';

try {
    // A variável $pastoral_id é definida pelo routes.php
    global $pastoral_id;
    
    // Verificar se o ID foi fornecido
    if (!isset($pastoral_id) || empty($pastoral_id)) {
        error_log("pastoral_coordenadores.php: ID da pastoral não fornecido");
        Response::error('ID da pastoral é obrigatório', 400);
    }
    
    error_log("pastoral_coordenadores.php: Buscando coordenadores para pastoral_id = " . $pastoral_id);
    
    $db = new MembrosDatabase();
    
    // Buscar coordenadores da pastoral (membros com prioridade >= 5)
    $query = "
        SELECT 
            m.id,
            m.nome_completo,
            m.apelido,
            m.email,
            COALESCE(m.celular_whatsapp, m.telefone_fixo) as telefone,
            m.foto_url,
            COALESCE(f.nome, 'Membro') as funcao,
            mp.data_inicio,
            mp.prioridade
        FROM membros_membros_pastorais mp
        INNER JOIN membros_membros m ON mp.membro_id = m.id
        LEFT JOIN membros_funcoes f ON mp.funcao_id = f.id
        WHERE mp.pastoral_id = ? 
        AND mp.status = 'ativo'
        AND mp.prioridade >= 5
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


