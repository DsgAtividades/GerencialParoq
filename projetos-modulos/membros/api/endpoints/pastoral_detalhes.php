<?php
/**
 * Endpoint: Detalhes da Pastoral
 * Método: GET
 * URL: /api/pastorais/{id}
 */

require_once '../config/database.php';

try {
    // A variável $pastoral_id é definida pelo routes.php
    global $pastoral_id;
    
    // Verificar se o ID foi fornecido
    if (!isset($pastoral_id) || empty($pastoral_id)) {
        error_log("pastoral_detalhes.php: ID da pastoral não fornecido");
        Response::error('ID da pastoral é obrigatório', 400);
    }
    
    $db = new MembrosDatabase();
    
    // Aceita UUIDs, IDs numéricos ou IDs com prefixo (ex: pastoral-2)
    if (!preg_match('/^[a-f0-9\-]{36}$/', $pastoral_id) && !is_numeric($pastoral_id) && !preg_match('/^[a-z]+\-\d+$/', $pastoral_id)) {
        Response::error('ID inválido', 400);
    }
    
    // Buscar dados da pastoral com coordenadores em uma única query (otimização N+1)
    $query = "
        SELECT 
            p.*,
            c.nome_completo as coordenador_nome,
            c.apelido as coordenador_apelido,
            vc.nome_completo as vice_coordenador_nome,
            vc.apelido as vice_coordenador_apelido
        FROM membros_pastorais p
        LEFT JOIN membros_membros c ON p.coordenador_id = c.id
        LEFT JOIN membros_membros vc ON p.vice_coordenador_id = vc.id
        WHERE p.id = ?
    ";
    $stmt = $db->prepare($query);
    $stmt->execute([$pastoral_id]);
    $pastoral = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pastoral) {
        Response::error('Pastoral não encontrada', 404);
    }
    
    // Formatar nome do coordenador (usar apelido se nome completo não existir)
    if ($pastoral['coordenador_id']) {
        $pastoral['coordenador_nome'] = $pastoral['coordenador_nome'] ?: $pastoral['coordenador_apelido'];
        unset($pastoral['coordenador_apelido']); // Remover campo temporário
    }
    
    // Formatar nome do vice-coordenador (usar apelido se nome completo não existir)
    if ($pastoral['vice_coordenador_id']) {
        $pastoral['vice_coordenador_nome'] = $pastoral['vice_coordenador_nome'] ?: $pastoral['vice_coordenador_apelido'];
        unset($pastoral['vice_coordenador_apelido']); // Remover campo temporário
    }
    
    Response::success($pastoral);
    
} catch (Exception $e) {
    error_log("Erro ao buscar pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>


