<?php
// Desabilitar exibição de erros
error_reporting(0);
ini_set('display_errors', 0);

// Definir header JSON ANTES de qualquer output
header('Content-Type: application/json');

require_once '../config/config.php';

try {
    $pdo = getConnection();
    
    // Buscar fornecedores
    $stmt = $pdo->query("SELECT id, nome, contato, telefone, email FROM lojinha_fornecedores WHERE ativo = 1 ORDER BY nome ASC");
    $fornecedores = $stmt->fetchAll();
    
    // Retornar JSON
    echo json_encode([
        'success' => true,
        'fornecedores' => $fornecedores
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Retornar erro em JSON
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar fornecedores',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>