<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$id = intval($_GET['id']);

try {
    // Conexão direta com PDO
    $pdo = getConnection();
    
    // Buscar produto
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            p.descricao,
            p.categoria_id,
            p.fornecedor,
            p.preco_compra,
            p.preco_venda,
            p.estoque_atual,
            p.estoque_minimo,
            p.tipo_liturgico,
            p.ativo,
            c.nome as categoria_nome
        FROM lojinha_produtos p
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        WHERE p.id = ?
    ");
    
    $stmt->execute([$id]);
    $produto = $stmt->fetch();
    
    if ($produto) {
        echo json_encode([
            'success' => true,
            'produto' => $produto
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Produto não encontrado'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar produto',
        'error' => $e->getMessage()
    ]);
}
?>
