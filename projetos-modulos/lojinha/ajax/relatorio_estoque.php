<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');

try {
    $database = new Database();
    $pdo = $database->getConnection();

    if (!$pdo) {
        throw new Exception('Erro ao conectar ao banco de dados');
    }

    // 1. RESUMO GERAL DO ESTOQUE
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_produtos,
            SUM(estoque_atual) as total_estoque,
            SUM(estoque_atual * preco_venda) as valor_total_estoque,
            COUNT(CASE WHEN estoque_atual <= estoque_minimo THEN 1 END) as produtos_em_falta,
            COUNT(CASE WHEN estoque_atual = 0 THEN 1 END) as produtos_zerados,
            COUNT(CASE WHEN estoque_atual > estoque_minimo THEN 1 END) as produtos_ok
        FROM lojinha_produtos 
        WHERE ativo = 1
    ");
    $stmt->execute();
    $resumo_estoque = $stmt->fetch();

    // 2. PRODUTOS EM FALTA (estoque <= estoque mínimo)
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            p.estoque_atual,
            p.estoque_minimo,
            p.preco_venda,
            c.nome as categoria,
            f.nome as fornecedor,
            (p.estoque_minimo - p.estoque_atual) as quantidade_faltante
        FROM lojinha_produtos p
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        LEFT JOIN lojinha_fornecedores f ON p.fornecedor_id = f.id
        WHERE p.ativo = 1 
        AND p.estoque_atual <= p.estoque_minimo
        ORDER BY quantidade_faltante DESC
    ");
    $stmt->execute();
    $produtos_em_falta = $stmt->fetchAll();

    // 3. PRODUTOS ZERADOS
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            p.estoque_atual,
            p.estoque_minimo,
            p.preco_venda,
            c.nome as categoria,
            f.nome as fornecedor
        FROM lojinha_produtos p
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        LEFT JOIN lojinha_fornecedores f ON p.fornecedor_id = f.id
        WHERE p.ativo = 1 
        AND p.estoque_atual = 0
        ORDER BY p.nome
    ");
    $stmt->execute();
    $produtos_zerados = $stmt->fetchAll();

    // 4. MOVIMENTAÇÕES DE ESTOQUE POR PERÍODO
    $stmt = $pdo->prepare("
        SELECT 
            em.tipo,
            COUNT(*) as quantidade_movimentacoes,
            SUM(em.quantidade) as quantidade_total,
            p.nome as produto,
            p.codigo,
            em.motivo,
            em.data_movimentacao
        FROM lojinha_estoque_movimentacoes em
        INNER JOIN lojinha_produtos p ON em.produto_id = p.id
        WHERE DATE(em.data_movimentacao) BETWEEN ? AND ?
        GROUP BY em.tipo, em.produto_id, p.nome, p.codigo, em.motivo, em.data_movimentacao
        ORDER BY em.data_movimentacao DESC
        LIMIT 50
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $movimentacoes = $stmt->fetchAll();

    // 5. RESUMO DE MOVIMENTAÇÕES POR TIPO
    $stmt = $pdo->prepare("
        SELECT 
            tipo,
            COUNT(*) as quantidade,
            SUM(quantidade) as total_unidades
        FROM lojinha_estoque_movimentacoes
        WHERE DATE(data_movimentacao) BETWEEN ? AND ?
        GROUP BY tipo
        ORDER BY total_unidades DESC
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $resumo_movimentacoes = $stmt->fetchAll();

    // 6. PRODUTOS COM MAIOR ROTATIVIDADE (mais vendidos)
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            p.estoque_atual,
            p.estoque_minimo,
            p.preco_venda,
            c.nome as categoria,
            COALESCE(SUM(vi.quantidade), 0) as quantidade_vendida,
            COALESCE(SUM(vi.subtotal), 0) as receita_total
        FROM lojinha_produtos p
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        LEFT JOIN lojinha_vendas_itens vi ON p.id = vi.produto_id
        LEFT JOIN lojinha_vendas v ON vi.venda_id = v.id
        WHERE p.ativo = 1
        AND (v.data_venda IS NULL OR DATE(v.data_venda) BETWEEN ? AND ?)
        GROUP BY p.id, p.codigo, p.nome, p.estoque_atual, p.estoque_minimo, p.preco_venda, c.nome
        ORDER BY quantidade_vendida DESC
        LIMIT 20
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $produtos_rotatividade = $stmt->fetchAll();

    // 7. PRODUTOS PARADOS (sem movimentação no período)
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            p.estoque_atual,
            p.preco_venda,
            c.nome as categoria,
            p.created_at as data_cadastro
        FROM lojinha_produtos p
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        LEFT JOIN lojinha_estoque_movimentacoes em ON p.id = em.produto_id 
            AND DATE(em.data_movimentacao) BETWEEN ? AND ?
        WHERE p.ativo = 1 
        AND em.id IS NULL
        ORDER BY p.created_at ASC
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $produtos_parados = $stmt->fetchAll();

    // 8. ANÁLISE POR CATEGORIA
    $stmt = $pdo->prepare("
        SELECT 
            c.nome as categoria,
            COUNT(p.id) as total_produtos,
            SUM(p.estoque_atual) as estoque_total,
            SUM(p.estoque_atual * p.preco_venda) as valor_total,
            AVG(p.estoque_atual) as estoque_medio,
            COUNT(CASE WHEN p.estoque_atual <= p.estoque_minimo THEN 1 END) as em_falta
        FROM lojinha_categorias c
        LEFT JOIN lojinha_produtos p ON c.id = p.categoria_id AND p.ativo = 1
        GROUP BY c.id, c.nome
        HAVING total_produtos > 0
        ORDER BY valor_total DESC
    ");
    $stmt->execute();
    $analise_categorias = $stmt->fetchAll();

    // 9. PRODUTOS COM ESTOQUE ALTO (acima de 2x o mínimo)
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            p.estoque_atual,
            p.estoque_minimo,
            p.preco_venda,
            c.nome as categoria,
            ROUND((p.estoque_atual / p.estoque_minimo), 2) as multiplicador_estoque
        FROM lojinha_produtos p
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        WHERE p.ativo = 1 
        AND p.estoque_atual > (p.estoque_minimo * 2)
        ORDER BY multiplicador_estoque DESC
    ");
    $stmt->execute();
    $produtos_estoque_alto = $stmt->fetchAll();

    // 10. ESTATÍSTICAS DE VALORIZAÇÃO
    $stmt = $pdo->prepare("
        SELECT 
            SUM(estoque_atual * preco_compra) as valor_custo,
            SUM(estoque_atual * preco_venda) as valor_venda,
            SUM(estoque_atual * (preco_venda - preco_compra)) as lucro_potencial,
            ROUND(
                (SUM(estoque_atual * (preco_venda - preco_compra)) / 
                 SUM(estoque_atual * preco_compra)) * 100, 2
            ) as margem_media
        FROM lojinha_produtos 
        WHERE ativo = 1 AND estoque_atual > 0
    ");
    $stmt->execute();
    $valorizacao = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'periodo' => [
            'inicio' => $data_inicio,
            'fim' => $data_fim
        ],
        'resumo_estoque' => $resumo_estoque,
        'produtos_em_falta' => $produtos_em_falta,
        'produtos_zerados' => $produtos_zerados,
        'movimentacoes' => $movimentacoes,
        'resumo_movimentacoes' => $resumo_movimentacoes,
        'produtos_rotatividade' => $produtos_rotatividade,
        'produtos_parados' => $produtos_parados,
        'analise_categorias' => $analise_categorias,
        'produtos_estoque_alto' => $produtos_estoque_alto,
        'valorizacao' => $valorizacao
    ]);

} catch (Exception $e) {
    error_log("Erro no relatório de estoque: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao gerar relatório de estoque: ' . $e->getMessage()
    ]);
}
?>
