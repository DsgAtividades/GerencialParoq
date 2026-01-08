<?php
/**
 * Endpoint: Buscar Membros para Autocomplete
 * Método: GET
 * URL: /api/membros/buscar?q=nome
 * 
 * Retorna lista simples de membros para autocomplete (id, nome_completo, apelido)
 */

// Usar a conexão do módulo (fora da pasta api)
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Response.php';

try {
    $db = new MembrosDatabase();
    
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    
    error_log("membros_buscar.php: Busca recebida - q='$query', limit=$limit");
    
    if (empty($query)) {
        error_log("membros_buscar.php: Query vazia, retornando array vazio");
        Response::success([]);
    }
    
    // Observação: LIMIT com placeholder pode falhar quando emulation está desativado.
    // Por isso interpolamos o inteiro já sanetizado no SQL.
    $limitInt = (int)$limit;
    $sql = "
        SELECT 
            id,
            nome_completo,
            apelido,
            COALESCE(nome_completo, apelido) as nome_exibicao
        FROM membros_membros
        WHERE (nome_completo LIKE ? OR apelido LIKE ?)
        AND status = 'ativo'
        ORDER BY nome_completo ASC, apelido ASC
        LIMIT $limitInt
    ";
    
    $searchTerm = "%{$query}%";
    error_log("membros_buscar.php: Executando query com termo: $searchTerm");
    $stmt = $db->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm]);
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("membros_buscar.php: " . count($membros) . " membros encontrados");
    
    // Formatar resposta
    $resultado = array_map(function($membro) {
        return [
            'id' => $membro['id'],
            'nome' => $membro['nome_exibicao'] ?: ($membro['nome_completo'] ?: $membro['apelido'] ?: 'Sem nome'),
            'nome_completo' => $membro['nome_completo'],
            'apelido' => $membro['apelido']
        ];
    }, $membros);
    
    error_log("membros_buscar.php: Retornando " . count($resultado) . " resultados");
    Response::success($resultado);
    
} catch (Exception $e) {
    error_log("Erro ao buscar membros: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>
