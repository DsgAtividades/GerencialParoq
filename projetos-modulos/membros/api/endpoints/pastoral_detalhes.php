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
    
    // Buscar dados da pastoral
    $query = "SELECT * FROM membros_pastorais WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$pastoral_id]);
    $pastoral = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pastoral) {
        Response::error('Pastoral não encontrada', 404);
    }
    
    // Buscar nome do coordenador se houver
    if ($pastoral['coordenador_id']) {
        $coordQuery = "SELECT nome_completo, apelido FROM membros_membros WHERE id = ?";
        $coordStmt = $db->prepare($coordQuery);
        $coordStmt->execute([$pastoral['coordenador_id']]);
        $coordenador = $coordStmt->fetch(PDO::FETCH_ASSOC);
        if ($coordenador) {
            $pastoral['coordenador_nome'] = $coordenador['nome_completo'] ?: $coordenador['apelido'];
        }
    }
    
    // Buscar nome do vice-coordenador se houver
    if ($pastoral['vice_coordenador_id']) {
        $viceCoordQuery = "SELECT nome_completo, apelido FROM membros_membros WHERE id = ?";
        $viceCoordStmt = $db->prepare($viceCoordQuery);
        $viceCoordStmt->execute([$pastoral['vice_coordenador_id']]);
        $vice_coordenador = $viceCoordStmt->fetch(PDO::FETCH_ASSOC);
        if ($vice_coordenador) {
            $pastoral['vice_coordenador_nome'] = $vice_coordenador['nome_completo'] ?: $vice_coordenador['apelido'];
        }
    }
    
    Response::success($pastoral);
    
} catch (Exception $e) {
    error_log("Erro ao buscar pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>


