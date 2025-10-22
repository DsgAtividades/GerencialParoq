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

    // 1. DADOS GERAIS DE VENDAS
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_vendas,
            COALESCE(SUM(total), 0) as faturamento_total,
            COALESCE(AVG(total), 0) as ticket_medio,
            COALESCE(SUM(desconto), 0) as total_descontos
        FROM lojinha_vendas 
        WHERE DATE(data_venda) BETWEEN ? AND ? 
        AND status = 'finalizada'
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $dados_gerais = $stmt->fetch();

    // 2. VENDAS POR FORMA DE PAGAMENTO
    $stmt = $pdo->prepare("
        SELECT 
            forma_pagamento,
            COUNT(*) as quantidade,
            COALESCE(SUM(total), 0) as valor_total,
            COALESCE(AVG(total), 0) as ticket_medio
        FROM lojinha_vendas 
        WHERE DATE(data_venda) BETWEEN ? AND ? 
        AND status = 'finalizada'
        GROUP BY forma_pagamento
        ORDER BY valor_total DESC
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $vendas_por_pagamento = $stmt->fetchAll();

    // 3. VENDAS POR DIA (últimos 30 dias)
    $stmt = $pdo->prepare("
        SELECT 
            DATE(data_venda) as data,
            COUNT(*) as vendas,
            COALESCE(SUM(total), 0) as faturamento
        FROM lojinha_vendas 
        WHERE DATE(data_venda) BETWEEN ? AND ? 
        AND status = 'finalizada'
        GROUP BY DATE(data_venda)
        ORDER BY data DESC
        LIMIT 30
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $vendas_por_dia = $stmt->fetchAll();

    // 4. TOP 10 PRODUTOS MAIS VENDIDOS
    $stmt = $pdo->prepare("
        SELECT 
            p.nome as produto,
            p.codigo,
            SUM(vi.quantidade) as quantidade_vendida,
            SUM(vi.subtotal) as receita_total,
            COALESCE(AVG(vi.preco_unitario), 0) as preco_medio
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
        GROUP BY p.id, p.nome, p.codigo
        ORDER BY quantidade_vendida DESC
        LIMIT 10
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $top_produtos = $stmt->fetchAll();

    // 5. VENDAS COM MAIOR DESCONTO
    $stmt = $pdo->prepare("
        SELECT 
            numero_venda,
            cliente_nome,
            total,
            desconto,
            ROUND((desconto / (total + desconto)) * 100, 2) as percentual_desconto,
            data_venda
        FROM lojinha_vendas 
        WHERE DATE(data_venda) BETWEEN ? AND ? 
        AND status = 'finalizada'
        AND desconto > 0
        ORDER BY desconto DESC
        LIMIT 10
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $vendas_desconto = $stmt->fetchAll();

    // 6. COMPARAÇÃO COM PERÍODO ANTERIOR
    $dias_periodo = (strtotime($data_fim) - strtotime($data_inicio)) / (60 * 60 * 24) + 1;
    $data_inicio_anterior = date('Y-m-d', strtotime($data_inicio) - $dias_periodo);
    $data_fim_anterior = date('Y-m-d', strtotime($data_inicio) - 1);

    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_vendas,
            COALESCE(SUM(total), 0) as faturamento_total
        FROM lojinha_vendas 
        WHERE DATE(data_venda) BETWEEN ? AND ? 
        AND status = 'finalizada'
    ");
    $stmt->execute([$data_inicio_anterior, $data_fim_anterior]);
    $dados_anterior = $stmt->fetch();

    // Calcular variações
    $variacao_vendas = $dados_anterior['total_vendas'] > 0 
        ? round((($dados_gerais['total_vendas'] - $dados_anterior['total_vendas']) / $dados_anterior['total_vendas']) * 100, 2)
        : 0;
    
    $variacao_faturamento = $dados_anterior['faturamento_total'] > 0 
        ? round((($dados_gerais['faturamento_total'] - $dados_anterior['faturamento_total']) / $dados_anterior['faturamento_total']) * 100, 2)
        : 0;

    // 7. ESTATÍSTICAS ADICIONAIS
    $stmt = $pdo->prepare("
        SELECT 
            MIN(total) as menor_venda,
            MAX(total) as maior_venda,
            COUNT(CASE WHEN desconto > 0 THEN 1 END) as vendas_com_desconto,
            ROUND(COUNT(CASE WHEN desconto > 0 THEN 1 END) / COUNT(*) * 100, 2) as percentual_desconto
        FROM lojinha_vendas 
        WHERE DATE(data_venda) BETWEEN ? AND ? 
        AND status = 'finalizada'
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $estatisticas = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'periodo' => [
            'inicio' => $data_inicio,
            'fim' => $data_fim
        ],
        'dados_gerais' => $dados_gerais,
        'vendas_por_pagamento' => $vendas_por_pagamento,
        'vendas_por_dia' => $vendas_por_dia,
        'top_produtos' => $top_produtos,
        'vendas_desconto' => $vendas_desconto,
        'comparacao' => [
            'periodo_anterior' => [
                'inicio' => $data_inicio_anterior,
                'fim' => $data_fim_anterior,
                'dados' => $dados_anterior
            ],
            'variacao_vendas' => $variacao_vendas,
            'variacao_faturamento' => $variacao_faturamento
        ],
        'estatisticas' => $estatisticas
    ]);

} catch (Exception $e) {
    error_log("Erro no relatório de vendas: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao gerar relatório de vendas: ' . $e->getMessage()
    ]);
}
?>
