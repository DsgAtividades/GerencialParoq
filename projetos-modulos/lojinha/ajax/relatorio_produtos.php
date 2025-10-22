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

    // 1. RANKING GERAL DE PRODUTOS MAIS VENDIDOS
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            p.descricao,
            c.nome as categoria,
            f.nome as fornecedor,
            SUM(vi.quantidade) as quantidade_vendida,
            COALESCE(SUM(vi.subtotal), 0) as receita_total,
            COALESCE(AVG(vi.preco_unitario), 0) as preco_medio_venda,
            COALESCE(AVG(p.preco_compra), 0) as preco_medio_custo,
            COALESCE(SUM(vi.subtotal - (vi.quantidade * p.preco_compra)), 0) as lucro_total,
            COALESCE(
                (SUM(vi.subtotal - (vi.quantidade * p.preco_compra)) / SUM(vi.subtotal)) * 100, 0
            ) as margem_lucro_percentual,
            COUNT(DISTINCT v.id) as total_vendas,
            p.estoque_atual,
            p.estoque_minimo
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        LEFT JOIN lojinha_fornecedores f ON p.fornecedor_id = f.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
        GROUP BY p.id, p.codigo, p.nome, p.descricao, c.nome, f.nome, p.estoque_atual, p.estoque_minimo
        ORDER BY quantidade_vendida DESC
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $ranking_produtos = $stmt->fetchAll();

    // 2. RANKING POR RECEITA (FATURAMENTO)
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            c.nome as categoria,
            SUM(vi.quantidade) as quantidade_vendida,
            COALESCE(SUM(vi.subtotal), 0) as receita_total,
            COALESCE(AVG(vi.preco_unitario), 0) as preco_medio_venda,
            COUNT(DISTINCT v.id) as total_vendas
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
        GROUP BY p.id, p.codigo, p.nome, c.nome
        ORDER BY receita_total DESC
        LIMIT 20
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $ranking_receita = $stmt->fetchAll();

    // 3. RANKING POR LUCRO
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            c.nome as categoria,
            SUM(vi.quantidade) as quantidade_vendida,
            COALESCE(SUM(vi.subtotal), 0) as receita_total,
            COALESCE(SUM(vi.subtotal - (vi.quantidade * p.preco_compra)), 0) as lucro_total,
            COALESCE(
                (SUM(vi.subtotal - (vi.quantidade * p.preco_compra)) / SUM(vi.subtotal)) * 100, 0
            ) as margem_lucro_percentual
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
        GROUP BY p.id, p.codigo, p.nome, c.nome
        HAVING lucro_total > 0
        ORDER BY lucro_total DESC
        LIMIT 20
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $ranking_lucro = $stmt->fetchAll();

    // 4. RANKING POR MARGEM DE LUCRO
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            c.nome as categoria,
            SUM(vi.quantidade) as quantidade_vendida,
            COALESCE(SUM(vi.subtotal), 0) as receita_total,
            COALESCE(SUM(vi.subtotal - (vi.quantidade * p.preco_compra)), 0) as lucro_total,
            COALESCE(
                (SUM(vi.subtotal - (vi.quantidade * p.preco_compra)) / SUM(vi.subtotal)) * 100, 0
            ) as margem_lucro_percentual
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
        GROUP BY p.id, p.codigo, p.nome, c.nome
        HAVING margem_lucro_percentual > 0
        ORDER BY margem_lucro_percentual DESC
        LIMIT 20
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $ranking_margem = $stmt->fetchAll();

    // 5. ANÁLISE POR CATEGORIA
    $stmt = $pdo->prepare("
        SELECT 
            c.nome as categoria,
            COUNT(DISTINCT p.id) as total_produtos,
            SUM(vi.quantidade) as quantidade_vendida,
            COALESCE(SUM(vi.subtotal), 0) as receita_total,
            COALESCE(SUM(vi.subtotal - (vi.quantidade * p.preco_compra)), 0) as lucro_total,
            COALESCE(AVG(vi.preco_unitario), 0) as preco_medio,
            COALESCE(
                (SUM(vi.subtotal - (vi.quantidade * p.preco_compra)) / SUM(vi.subtotal)) * 100, 0
            ) as margem_media,
            COUNT(DISTINCT v.id) as total_vendas
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
        GROUP BY c.id, c.nome
        ORDER BY receita_total DESC
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $analise_categorias = $stmt->fetchAll();

    // 6. PRODUTOS EM ALTA (crescimento de vendas)
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            c.nome as categoria,
            SUM(vi.quantidade) as quantidade_vendida,
            COALESCE(SUM(vi.subtotal), 0) as receita_total,
            COUNT(DISTINCT v.id) as total_vendas,
            COALESCE(AVG(vi.preco_unitario), 0) as preco_medio_venda
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
        GROUP BY p.id, p.codigo, p.nome, c.nome
        HAVING quantidade_vendida >= 5
        ORDER BY quantidade_vendida DESC
        LIMIT 15
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $produtos_em_alta = $stmt->fetchAll();

    // 7. PRODUTOS COM BAIXA PERFORMANCE
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            c.nome as categoria,
            COALESCE(SUM(vi.quantidade), 0) as quantidade_vendida,
            COALESCE(SUM(vi.subtotal), 0) as receita_total,
            COALESCE(COUNT(DISTINCT v.id), 0) as total_vendas,
            p.estoque_atual,
            p.preco_venda,
            p.created_at as data_cadastro
        FROM lojinha_produtos p
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        LEFT JOIN lojinha_vendas_itens vi ON p.id = vi.produto_id
        LEFT JOIN lojinha_vendas v ON vi.venda_id = v.id 
            AND DATE(v.data_venda) BETWEEN ? AND ? 
            AND v.status = 'finalizada'
        WHERE p.ativo = 1
        GROUP BY p.id, p.codigo, p.nome, c.nome, p.estoque_atual, p.preco_venda, p.created_at
        HAVING quantidade_vendida = 0 OR quantidade_vendida < 3
        ORDER BY quantidade_vendida ASC, p.created_at ASC
        LIMIT 20
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $produtos_baixa_performance = $stmt->fetchAll();

    // 8. ESTATÍSTICAS GERAIS
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT p.id) as total_produtos_vendidos,
            SUM(vi.quantidade) as total_unidades_vendidas,
            COALESCE(SUM(vi.subtotal), 0) as receita_total_geral,
            COALESCE(AVG(vi.quantidade), 0) as media_unidades_por_venda,
            COALESCE(AVG(vi.preco_unitario), 0) as preco_medio_geral,
            COUNT(DISTINCT v.id) as total_vendas_geral
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $estatisticas_gerais = $stmt->fetch();

    // 9. PRODUTOS MAIS VENDIDOS POR MÊS (últimos 6 meses)
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(v.data_venda, '%Y-%m') as mes,
            p.nome as produto,
            SUM(vi.quantidade) as quantidade_vendida,
            COALESCE(SUM(vi.subtotal), 0) as receita_total
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        WHERE v.data_venda >= DATE_SUB(?, INTERVAL 6 MONTH)
        AND v.status = 'finalizada'
        GROUP BY DATE_FORMAT(v.data_venda, '%Y-%m'), p.id, p.nome
        ORDER BY mes DESC, quantidade_vendida DESC
    ");
    $stmt->execute([$data_fim]);
    $produtos_por_mes = $stmt->fetchAll();

    // 10. ANÁLISE DE ROTATIVIDADE DE ESTOQUE
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.codigo,
            p.nome,
            p.estoque_atual,
            COALESCE(SUM(vi.quantidade), 0) as quantidade_vendida,
            CASE 
                WHEN p.estoque_atual > 0 THEN 
                    ROUND(SUM(vi.quantidade) / p.estoque_atual, 2)
                ELSE 0 
            END as rotatividade,
            CASE 
                WHEN p.estoque_atual > 0 AND SUM(vi.quantidade) > 0 THEN 
                    ROUND((p.estoque_atual / (SUM(vi.quantidade) / 30)), 1)
                ELSE NULL 
            END as dias_estoque
        FROM lojinha_produtos p
        LEFT JOIN lojinha_vendas_itens vi ON p.id = vi.produto_id
        LEFT JOIN lojinha_vendas v ON vi.venda_id = v.id 
            AND DATE(v.data_venda) BETWEEN ? AND ? 
            AND v.status = 'finalizada'
        WHERE p.ativo = 1
        GROUP BY p.id, p.codigo, p.nome, p.estoque_atual
        HAVING quantidade_vendida > 0
        ORDER BY rotatividade DESC
        LIMIT 20
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $rotatividade_estoque = $stmt->fetchAll();

    // 11. TOP PRODUTOS POR FORNECEDOR
    $stmt = $pdo->prepare("
        SELECT 
            f.nome as fornecedor,
            COUNT(DISTINCT p.id) as total_produtos,
            SUM(vi.quantidade) as quantidade_vendida,
            COALESCE(SUM(vi.subtotal), 0) as receita_total,
            COALESCE(AVG(vi.preco_unitario), 0) as preco_medio
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        LEFT JOIN lojinha_fornecedores f ON p.fornecedor_id = f.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
        AND f.nome IS NOT NULL
        GROUP BY f.id, f.nome
        ORDER BY receita_total DESC
        LIMIT 10
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $produtos_por_fornecedor = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'periodo' => [
            'inicio' => $data_inicio,
            'fim' => $data_fim
        ],
        'ranking_produtos' => $ranking_produtos,
        'ranking_receita' => $ranking_receita,
        'ranking_lucro' => $ranking_lucro,
        'ranking_margem' => $ranking_margem,
        'analise_categorias' => $analise_categorias,
        'produtos_em_alta' => $produtos_em_alta,
        'produtos_baixa_performance' => $produtos_baixa_performance,
        'estatisticas_gerais' => $estatisticas_gerais,
        'produtos_por_mes' => $produtos_por_mes,
        'rotatividade_estoque' => $rotatividade_estoque,
        'produtos_por_fornecedor' => $produtos_por_fornecedor
    ]);

} catch (Exception $e) {
    error_log("Erro no relatório de produtos: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao gerar relatório de produtos: ' . $e->getMessage()
    ]);
}
?>
