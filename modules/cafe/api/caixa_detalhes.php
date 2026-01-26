<?php
require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';

header('Content-Type: application/json');

verificarPermissaoApi('visualizar_caixa');

$caixa_id = $_GET['id'] ?? null;

if (!$caixa_id) {
    echo json_encode(['success' => false, 'message' => 'ID do caixa não fornecido']);
    exit;
}

try {
    // Buscar informações do caixa
    $stmt = $pdo->prepare("
        SELECT 
            *,
            DATE_FORMAT(data_abertura, '%d/%m/%Y %H:%i') as data_abertura_formatada,
            DATE_FORMAT(data_fechamento, '%d/%m/%Y %H:%i') as data_fechamento_formatada
        FROM vw_cafe_caixas_resumo WHERE id = ?
    ");
    $stmt->execute([$caixa_id]);
    $caixa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$caixa) {
        throw new Exception('Caixa não encontrado');
    }
    
    // Buscar vendas do caixa com detalhes
    $stmt = $pdo->prepare("
        SELECT 
            v.id_venda,
            v.valor_total,
            v.Tipo_venda as tipo_venda,
            COALESCE(v.Atendente, 'Sistema') as atendente,
            v.data_venda,
            DATE_FORMAT(v.data_venda, '%d/%m/%Y %H:%i') as data_venda_formatada,
            FORMAT(v.valor_total, 2, 'pt_BR') as valor_formatado,
            COUNT(iv.id_item) as total_itens
        FROM cafe_vendas v
        LEFT JOIN cafe_itens_venda iv ON v.id_venda = iv.id_venda
        WHERE v.caixa_id = ?
            AND (v.estornada IS NULL OR v.estornada = 0)
        GROUP BY v.id_venda, v.valor_total, v.Tipo_venda, v.Atendente, v.data_venda
        ORDER BY v.data_venda DESC
    ");
    $stmt->execute([$caixa_id]);
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Para cada venda, buscar os itens
    foreach ($vendas as &$venda) {
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
        $stmt->execute([$venda['id_venda']]);
        $venda['itens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'caixa' => $caixa,
        'vendas' => $vendas
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao buscar detalhes do caixa: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar detalhes: ' . $e->getMessage()
    ]);
}

