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

    // 1. RESUMO FINANCEIRO GERAL
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_vendas,
            COALESCE(SUM(total), 0) as faturamento_bruto,
            COALESCE(SUM(desconto), 0) as total_descontos,
            COALESCE(SUM(total + desconto), 0) as faturamento_liquido,
            COALESCE(AVG(total), 0) as ticket_medio
        FROM lojinha_vendas 
        WHERE DATE(data_venda) BETWEEN ? AND ? 
        AND status = 'finalizada'
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $resumo_financeiro = $stmt->fetch();

    // 2. CÁLCULO DE CUSTOS E LUCRO
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(vi.quantidade * p.preco_compra), 0) as custo_total_vendas,
            COALESCE(SUM(vi.subtotal), 0) as receita_total_vendas,
            COALESCE(SUM(vi.subtotal - (vi.quantidade * p.preco_compra)), 0) as lucro_bruto,
            COALESCE(
                (SUM(vi.subtotal - (vi.quantidade * p.preco_compra)) / SUM(vi.subtotal)) * 100, 0
            ) as margem_lucro_percentual
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $analise_lucro = $stmt->fetch();

    // 3. FATURAMENTO POR FORMA DE PAGAMENTO
    $stmt = $pdo->prepare("
        SELECT 
            forma_pagamento,
            COUNT(*) as quantidade_vendas,
            COALESCE(SUM(total), 0) as valor_total,
            COALESCE(AVG(total), 0) as ticket_medio,
            ROUND((SUM(total) / (SELECT SUM(total) FROM lojinha_vendas WHERE DATE(data_venda) BETWEEN ? AND ? AND status = 'finalizada')) * 100, 2) as percentual_faturamento
        FROM lojinha_vendas 
        WHERE DATE(data_venda) BETWEEN ? AND ? 
        AND status = 'finalizada'
        GROUP BY forma_pagamento
        ORDER BY valor_total DESC
    ");
    $stmt->execute([$data_inicio, $data_fim, $data_inicio, $data_fim]);
    $faturamento_por_pagamento = $stmt->fetchAll();

    // 4. EVOLUÇÃO DO FATURAMENTO POR DIA
    $stmt = $pdo->prepare("
        SELECT 
            DATE(data_venda) as data,
            COUNT(*) as vendas,
            COALESCE(SUM(total), 0) as faturamento,
            COALESCE(SUM(desconto), 0) as descontos,
            COALESCE(SUM(total + desconto), 0) as faturamento_bruto
        FROM lojinha_vendas 
        WHERE DATE(data_venda) BETWEEN ? AND ? 
        AND status = 'finalizada'
        GROUP BY DATE(data_venda)
        ORDER BY data ASC
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $evolucao_faturamento = $stmt->fetchAll();

    // 5. ANÁLISE DE MARGEM POR PRODUTO
    $stmt = $pdo->prepare("
        SELECT 
            p.nome as produto,
            p.codigo,
            SUM(vi.quantidade) as quantidade_vendida,
            COALESCE(SUM(vi.quantidade * p.preco_compra), 0) as custo_total,
            COALESCE(SUM(vi.subtotal), 0) as receita_total,
            COALESCE(SUM(vi.subtotal - (vi.quantidade * p.preco_compra)), 0) as lucro_total,
            COALESCE(
                (SUM(vi.subtotal - (vi.quantidade * p.preco_compra)) / SUM(vi.subtotal)) * 100, 0
            ) as margem_percentual,
            COALESCE(AVG(vi.preco_unitario), 0) as preco_medio_venda,
            COALESCE(AVG(p.preco_compra), 0) as preco_medio_custo
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
        GROUP BY p.id, p.nome, p.codigo
        HAVING quantidade_vendida > 0
        ORDER BY lucro_total DESC
        LIMIT 20
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $margem_por_produto = $stmt->fetchAll();

    // 6. ANÁLISE DE DESCONTOS
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN desconto > 0 THEN 1 END) as vendas_com_desconto,
            COUNT(*) as total_vendas,
            COALESCE(SUM(desconto), 0) as total_descontos,
            COALESCE(AVG(desconto), 0) as desconto_medio,
            COALESCE(MAX(desconto), 0) as maior_desconto,
            ROUND(
                (COUNT(CASE WHEN desconto > 0 THEN 1 END) / COUNT(*)) * 100, 2
            ) as percentual_vendas_desconto,
            ROUND(
                (SUM(desconto) / SUM(total + desconto)) * 100, 2
            ) as percentual_desconto_sobre_faturamento
        FROM lojinha_vendas 
        WHERE DATE(data_venda) BETWEEN ? AND ? 
        AND status = 'finalizada'
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $analise_descontos = $stmt->fetch();

    // 7. COMPARAÇÃO COM PERÍODO ANTERIOR
    $dias_periodo = (strtotime($data_fim) - strtotime($data_inicio)) / (60 * 60 * 24) + 1;
    $data_inicio_anterior = date('Y-m-d', strtotime($data_inicio) - $dias_periodo);
    $data_fim_anterior = date('Y-m-d', strtotime($data_inicio) - 1);

    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_vendas,
            COALESCE(SUM(total), 0) as faturamento_bruto,
            COALESCE(SUM(desconto), 0) as total_descontos,
            COALESCE(SUM(total + desconto), 0) as faturamento_liquido
        FROM lojinha_vendas 
        WHERE DATE(data_venda) BETWEEN ? AND ? 
        AND status = 'finalizada'
    ");
    $stmt->execute([$data_inicio_anterior, $data_fim_anterior]);
    $dados_anterior = $stmt->fetch();

    // Calcular variações
    $variacao_faturamento = $dados_anterior['faturamento_liquido'] > 0 
        ? round((($resumo_financeiro['faturamento_liquido'] - $dados_anterior['faturamento_liquido']) / $dados_anterior['faturamento_liquido']) * 100, 2)
        : 0;
    
    $variacao_vendas = $dados_anterior['total_vendas'] > 0 
        ? round((($resumo_financeiro['total_vendas'] - $dados_anterior['total_vendas']) / $dados_anterior['total_vendas']) * 100, 2)
        : 0;

    // 8. PROJEÇÃO DE FATURAMENTO (baseada na média diária)
    $dias_periodo_atual = (strtotime($data_fim) - strtotime($data_inicio)) / (60 * 60 * 24) + 1;
    $faturamento_medio_diario = $resumo_financeiro['faturamento_liquido'] / $dias_periodo_atual;
    
    $dias_restantes_mes = date('t') - date('j');
    $projecao_mes_atual = $resumo_financeiro['faturamento_liquido'] + ($faturamento_medio_diario * $dias_restantes_mes);
    
    $projecao_proximo_mes = $faturamento_medio_diario * 30;

    // 9. ANÁLISE DE SAZONALIDADE (últimos 12 meses)
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(data_venda, '%Y-%m') as mes,
            COUNT(*) as vendas,
            COALESCE(SUM(total), 0) as faturamento
        FROM lojinha_vendas 
        WHERE data_venda >= DATE_SUB(?, INTERVAL 12 MONTH)
        AND status = 'finalizada'
        GROUP BY DATE_FORMAT(data_venda, '%Y-%m')
        ORDER BY mes ASC
    ");
    $stmt->execute([$data_fim]);
    $sazonalidade = $stmt->fetchAll();

    // 10. TOP 10 CLIENTES POR FATURAMENTO
    $stmt = $pdo->prepare("
        SELECT 
            cliente_nome,
            COUNT(*) as total_compras,
            COALESCE(SUM(total), 0) as total_gasto,
            COALESCE(AVG(total), 0) as ticket_medio,
            MAX(data_venda) as ultima_compra
        FROM lojinha_vendas 
        WHERE DATE(data_venda) BETWEEN ? AND ? 
        AND status = 'finalizada'
        AND cliente_nome IS NOT NULL
        AND cliente_nome != ''
        GROUP BY cliente_nome
        ORDER BY total_gasto DESC
        LIMIT 10
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $top_clientes = $stmt->fetchAll();

    // 11. ANÁLISE DE RENTABILIDADE
    $rentabilidade = [
        'faturamento_bruto' => $resumo_financeiro['faturamento_bruto'],
        'custo_vendas' => $analise_lucro['custo_total_vendas'],
        'lucro_bruto' => $analise_lucro['lucro_bruto'],
        'margem_lucro' => $analise_lucro['margem_lucro_percentual'],
        'descontos' => $resumo_financeiro['total_descontos'],
        'faturamento_liquido' => $resumo_financeiro['faturamento_liquido']
    ];

    echo json_encode([
        'success' => true,
        'periodo' => [
            'inicio' => $data_inicio,
            'fim' => $data_fim
        ],
        'resumo_financeiro' => $resumo_financeiro,
        'analise_lucro' => $analise_lucro,
        'faturamento_por_pagamento' => $faturamento_por_pagamento,
        'evolucao_faturamento' => $evolucao_faturamento,
        'margem_por_produto' => $margem_por_produto,
        'analise_descontos' => $analise_descontos,
        'comparacao' => [
            'periodo_anterior' => [
                'inicio' => $data_inicio_anterior,
                'fim' => $data_fim_anterior,
                'dados' => $dados_anterior
            ],
            'variacao_faturamento' => $variacao_faturamento,
            'variacao_vendas' => $variacao_vendas
        ],
        'projecoes' => [
            'faturamento_medio_diario' => round($faturamento_medio_diario, 2),
            'projecao_mes_atual' => round($projecao_mes_atual, 2),
            'projecao_proximo_mes' => round($projecao_proximo_mes, 2)
        ],
        'sazonalidade' => $sazonalidade,
        'top_clientes' => $top_clientes,
        'rentabilidade' => $rentabilidade
    ]);

} catch (Exception $e) {
    error_log("Erro no relatório financeiro: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao gerar relatório financeiro: ' . $e->getMessage()
    ]);
}
?>
