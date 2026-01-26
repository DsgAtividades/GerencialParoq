<?php
require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';

header('Content-Type: application/json');

verificarPermissaoApi('visualizar_caixa');

$id_venda = $_GET['id_venda'] ?? null;

if (!$id_venda) {
    echo json_encode(['success' => false, 'message' => 'ID da venda não fornecido']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            iv.id_item,
            iv.quantidade,
            iv.valor_unitario,
            p.nome_produto,
            FORMAT(iv.valor_unitario, 2, 'pt_BR') as valor_unitario_formatado,
            FORMAT(iv.quantidade * iv.valor_unitario, 2, 'pt_BR') as subtotal_formatado
        FROM cafe_itens_venda iv
        INNER JOIN cafe_produtos p ON iv.id_produto = p.id
        WHERE iv.id_venda = ?
        ORDER BY iv.id_item
    ");
    $stmt->execute([$id_venda]);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'itens' => $itens
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao buscar itens da venda: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar itens: ' . $e->getMessage()
    ]);
}


