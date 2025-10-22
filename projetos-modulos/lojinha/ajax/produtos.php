<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Verificar se está logado no módulo
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não está logado no módulo']);
    exit;
}

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            c.nome as categoria_nome,
            f.nome as fornecedor_nome
        FROM lojinha_produtos p
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        LEFT JOIN lojinha_fornecedores f ON p.fornecedor_id = f.id
        ORDER BY p.nome ASC
    ");
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'produtos' => $produtos
    ]);
    
} catch(Exception $e) {
    error_log("Erro ao carregar produtos: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>
