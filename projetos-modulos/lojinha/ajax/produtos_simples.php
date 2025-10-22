<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Verificar se está logado no módulo
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não está logado no módulo']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Consulta simplificada sem JOINs primeiro
    $stmt = $pdo->prepare("SELECT * FROM lojinha_produtos ORDER BY nome ASC");
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Adicionar informações de categoria e fornecedor separadamente
    foreach ($produtos as &$produto) {
        // Buscar categoria
        if ($produto['categoria_id']) {
            $stmt = $pdo->prepare("SELECT nome FROM lojinha_categorias WHERE id = ?");
            $stmt->execute([$produto['categoria_id']]);
            $categoria = $stmt->fetch();
            $produto['categoria_nome'] = $categoria ? $categoria['nome'] : null;
        } else {
            $produto['categoria_nome'] = null;
        }
        
        // Buscar fornecedor
        if ($produto['fornecedor_id']) {
            $stmt = $pdo->prepare("SELECT nome FROM lojinha_fornecedores WHERE id = ?");
            $stmt->execute([$produto['fornecedor_id']]);
            $fornecedor = $stmt->fetch();
            $produto['fornecedor_nome'] = $fornecedor ? $fornecedor['nome'] : null;
        } else {
            $produto['fornecedor_nome'] = null;
        }
    }
    
    echo json_encode([
        'success' => true,
        'produtos' => $produtos,
        'debug' => [
            'total_produtos' => count($produtos),
            'tabelas_existem' => true
        ]
    ]);
    
} catch(Exception $e) {
    error_log("Erro ao carregar produtos: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
