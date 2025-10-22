<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

try {
    // Conexão direta com PDO
    $pdo = getConnection();
    
    // Buscar produtos ativos com estoque
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            p.preco_venda,
            p.estoque_atual,
            c.nome as categoria_nome
        FROM lojinha_produtos p
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        WHERE p.ativo = 1 AND p.estoque_atual > 0
        ORDER BY p.nome ASC
    ");
    
    $produtos = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'produtos' => $produtos
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar produtos',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
