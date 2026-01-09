<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/../includes/verifica_permissao.php';

header('Content-Type: application/json');

// Somente acesso autenticado (módulo escondido, mas protegido)
verificarLogin();

$data_inicio = $_GET['data_inicio'] ?? date('Y-m-d');
$data_fim    = $_GET['data_fim'] ?? date('Y-m-d');

// Valida datas simples (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_fim)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Período inválido']);
    exit;
}

try {
    // 1) KPIs principais
    // Custo de cartão na tabela historico_saldo (há variantes com e sem acento)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(ABS(valor)),0) as total, COUNT(*) as qtde FROM cafe_historico_saldo WHERE tipo_operacao IN ('custo cartao','custo cartão') AND DATE(data_operacao) BETWEEN :i AND :f");
    $stmt->execute([':i' => $data_inicio, ':f' => $data_fim]);
    $rowCartao = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'qtde' => 0];

    // Estornos: somar de historico_transacoes_sistema (tipo = 'Estorno') e historico_saldo (tipo_operacao = 'debito' e motivo = 'Estorno')
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(ABS(valor)),0) as total FROM cafe_historico_transacoes_sistema WHERE tipo = 'Estorno' AND DATE(create_at) BETWEEN :i AND :f");
    $stmt->execute([':i' => $data_inicio, ':f' => $data_fim]);
    $rowEstornoHTS = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0];

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(ABS(valor)),0) as total FROM cafe_historico_saldo WHERE tipo_operacao = 'debito' AND motivo = 'Estorno' AND DATE(data_operacao) BETWEEN :i AND :f");
    $stmt->execute([':i' => $data_inicio, ':f' => $data_fim]);
    $rowEstornoHS = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0];

    $custo_cartao_total = (float)$rowCartao['total'];
    $qtd_cartoes        = (int)$rowCartao['qtde'];
    
    // Receita de cartões (custo inicial cobrado) - valor absoluto dos custos
    $receita_cartoes = $custo_cartao_total;
    $estornos_total     = (float)$rowEstornoHTS['total'] + (float)$rowEstornoHS['total'];
    $custos_total       = $custo_cartao_total + $estornos_total;
    $custo_medio        = $qtd_cartoes > 0 ? $custo_cartao_total / $qtd_cartoes : 0.0;

    // Receita total (créditos em historico_saldo) - considera apenas valores positivos
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN valor > 0 THEN valor ELSE 0 END),0) as total FROM cafe_historico_saldo WHERE tipo_operacao = 'credito' AND DATE(data_operacao) BETWEEN :i AND :f");
    $stmt->execute([':i' => $data_inicio, ':f' => $data_fim]);
    $rowReceita = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0];
    $receita_total = (float)$rowReceita['total'];

    // Vendas (itens_venda * valor_unitario) e itens vendidos
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(vi.quantidade * vi.valor_unitario),0) as total, COALESCE(SUM(vi.quantidade),0) as itens, COUNT(DISTINCT v.id_venda) as qtd_vendas_total FROM cafe_itens_venda vi JOIN cafe_vendas v ON vi.id_venda = v.id_venda WHERE v.estornada is null AND DATE(v.data_venda) BETWEEN :i AND :f");
    $stmt->execute([':i' => $data_inicio, ':f' => $data_fim]);
    $rowVendas = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'itens' => 0, 'qtd_vendas_total' => 0];
    $vendas_total = (float)$rowVendas['total'];
    $itens_vendidos = (int)$rowVendas['itens'];
    $num_vendas_total = (int)$rowVendas['qtd_vendas_total'];

    $resultado = $receita_total - $custos_total;
    $ticket_medio_global = $num_vendas_total > 0 ? ($vendas_total / $num_vendas_total) : 0.0;
    
    // Cálculos adicionais para KPIs detalhados
    $margem_liquida = $receita_total > 0 ? (($resultado / $receita_total) * 100) : 0.0;
    $taxa_conversao = $receita_total > 0 ? (($vendas_total / $receita_total) * 100) : 0.0;

    // 2) Evolução diária (custo cartão + estorno)
    $stmt = $pdo->prepare("SELECT DATE(data_operacao) as dia, COALESCE(SUM(ABS(valor)),0) as total FROM cafe_historico_saldo WHERE tipo_operacao IN ('custo cartao','custo cartão') AND DATE(data_operacao) BETWEEN :i AND :f GROUP BY DATE(data_operacao) ORDER BY dia");
    $stmt->execute([':i' => $data_inicio, ':f' => $data_fim]);
    $evolCartao = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT DATE(create_at) as dia, COALESCE(SUM(ABS(valor)),0) as total FROM cafe_historico_transacoes_sistema WHERE tipo = 'Estorno' AND DATE(create_at) BETWEEN :i AND :f GROUP BY DATE(create_at) ORDER BY dia");
    $stmt->execute([':i' => $data_inicio, ':f' => $data_fim]);
    $evolEstorno = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Evolução de receitas (créditos) e vendas
    $stmt = $pdo->prepare("SELECT DATE(data_operacao) as dia, COALESCE(SUM(valor),0) as total FROM cafe_historico_saldo WHERE tipo_operacao = 'credito' AND DATE(data_operacao) BETWEEN :i AND :f GROUP BY DATE(data_operacao) ORDER BY dia");
    $stmt->execute([':i' => $data_inicio, ':f' => $data_fim]);
    $evolReceita = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT DATE(v.data_venda) as dia, COALESCE(SUM(vi.quantidade * vi.valor_unitario),0) as total, COUNT(DISTINCT v.id_venda) as qtd_vendas FROM cafe_itens_venda vi JOIN cafe_vendas v ON vi.id_venda = v.id_venda WHERE v.estornada is null AND DATE(v.data_venda) BETWEEN :i AND :f GROUP BY DATE(v.data_venda) ORDER BY dia");
    $stmt->execute([':i' => $data_inicio, ':f' => $data_fim]);
    $evolVendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mescla por dia (receita/vendas/custos)
    $daily = [];
    foreach ($evolReceita as $r) { $daily[$r['dia']]['receita'] = ($daily[$r['dia']]['receita'] ?? 0) + (float)$r['total']; }
    foreach ($evolVendas as $r)  { $daily[$r['dia']]['vendas']  = ($daily[$r['dia']]['vendas']  ?? 0) + (float)$r['total']; $daily[$r['dia']]['qtd_vendas'] = ($daily[$r['dia']]['qtd_vendas'] ?? 0) + (int)$r['qtd_vendas']; }
    foreach ($evolCartao as $r)  { $daily[$r['dia']]['custos']  = ($daily[$r['dia']]['custos']  ?? 0) + (float)$r['total']; }
    foreach ($evolEstorno as $r) { $daily[$r['dia']]['custos']  = ($daily[$r['dia']]['custos']  ?? 0) + (float)$r['total']; }
    ksort($daily);
    $evolucao = [];
    foreach ($daily as $dia => $vals) {
        $ticket = (!empty($vals['qtd_vendas'])) ? ($vals['vendas'] / max($vals['qtd_vendas'],1)) : 0;
        $evolucao[] = [
            'dia' => date('d/m', strtotime($dia)),
            'receita' => $vals['receita'] ?? 0,
            'vendas'  => $vals['vendas']  ?? 0,
            'custos'  => $vals['custos']  ?? 0,
            'qtd_vendas' => (int)($vals['qtd_vendas'] ?? 0),
            'ticket_medio' => $ticket,
        ];
    }

    // 3b) Créditos por dia por meio de pagamento (PIX/Dinheiro/Cartão) - incluindo todas as variações possíveis
    $stmt = $pdo->prepare("SELECT DATE(create_at) as dia, tipo, COALESCE(SUM(ABS(valor)),0) as total FROM cafe_historico_transacoes_sistema WHERE tipo IN ('PIX','Dinheiro','Cartão','Cartao','Credito','Débito','Debito') AND DATE(create_at) BETWEEN :i AND :f GROUP BY DATE(create_at), tipo ORDER BY dia");
    $stmt->execute([':i' => $data_inicio, ':f' => $data_fim]);
    $rowsPayDaily = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $payDaily = [];
    foreach ($rowsPayDaily as $r) {
        $d = $r['dia'];
        if (!isset($payDaily[$d])) $payDaily[$d] = ['PIX' => 0, 'Dinheiro' => 0, 'Cartão' => 0, 'Cartao' => 0, 'Credito' => 0, 'Débito' => 0, 'Debito' => 0];
        $payDaily[$d][$r['tipo']] = (float)$r['total'];
    }
    ksort($payDaily);
    $pagamentos_diario = [];
    foreach ($payDaily as $dia => $vals) {
        $pagamentos_diario[] = [
            'dia' => date('d/m', strtotime($dia)),
            'pix' => $vals['PIX'] ?? 0,
            'dinheiro' => $vals['Dinheiro'] ?? 0,
            'cartao' => (($vals['Cartão'] ?? 0) + ($vals['Cartao'] ?? 0) + ($vals['Credito'] ?? 0) + ($vals['Débito'] ?? 0) + ($vals['Debito'] ?? 0))
        ];
    }

    // 3) Pagamentos (PIX/Dinheiro/Cartão de historico_transacoes_sistema)
    $stmt = $pdo->prepare("SELECT tipo, COALESCE(SUM(ABS(valor)),0) as total FROM cafe_historico_transacoes_sistema WHERE tipo IN ('PIX','Dinheiro','Cartão','Cartao','Credito','Débito','Debito') AND DATE(create_at) BETWEEN :i AND :f GROUP BY tipo");
    $stmt->execute([':i' => $data_inicio, ':f' => $data_fim]);
    $rowsPay = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $pagamentos = [
        'pix' => (float)($rowsPay['PIX'] ?? 0),
        'dinheiro' => (float)($rowsPay['Dinheiro'] ?? 0),
        'cartao' => (float)(($rowsPay['Cartão'] ?? 0) + ($rowsPay['Cartao'] ?? 0) + ($rowsPay['Credito'] ?? 0) + ($rowsPay['Débito'] ?? 0) + ($rowsPay['Debito'] ?? 0))
    ];

    // 3c) Saldo deixado em cartões (como no dashboard original)
    // Saldo deixado = Total créditos - Total vendas - Custo cartões
    $saldo_deixado = $receita_total - $vendas_total - $custo_cartao_total;
    
    // Cartões ativos (com saldo > 0)
    $stmt = $pdo->query("SELECT COUNT(*) as ativos FROM cafe_saldos_cartao WHERE saldo > 0");
    $rowAtivos = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['ativos' => 0];
    $cartoes_ativos = (int)$rowAtivos['ativos'];

    // 4) Top produtos e categorias por valor vendido
    $stmt = $pdo->prepare("SELECT p.nome_produto, SUM(vi.quantidade * vi.valor_unitario) as valor_vendido FROM cafe_itens_venda vi JOIN cafe_produtos p ON vi.id_produto = p.id JOIN cafe_vendas v ON vi.id_venda = v.id_venda WHERE v.estornada is null AND DATE(v.data_venda) BETWEEN :i AND :f GROUP BY p.nome_produto ORDER BY valor_vendido DESC LIMIT 10");
    $stmt->execute([':i' => $data_inicio, ':f' => $data_fim]);
    $top_produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT c.nome as categoria, SUM(vi.quantidade * vi.valor_unitario) as valor_vendido FROM cafe_itens_venda vi JOIN cafe_produtos p ON vi.id_produto = p.id LEFT JOIN cafe_categorias c ON p.categoria_id = c.id JOIN cafe_vendas v ON vi.id_venda = v.id_venda WHERE v.estornada is null AND DATE(v.data_venda) BETWEEN :i AND :f GROUP BY c.nome ORDER BY valor_vendido DESC LIMIT 10");
    $stmt->execute([':i' => $data_inicio, ':f' => $data_fim]);
    $top_categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'kpis' => [
            'custo_cartao_total' => $custo_cartao_total,
            'estornos_total' => $estornos_total,
            'custos_total' => $custos_total,
            'qtd_cartoes' => $qtd_cartoes,
            'custo_medio_por_cartao' => $custo_medio,
            'receita_total' => $receita_total,
            'vendas_total' => $vendas_total,
            'itens_vendidos' => $itens_vendidos,
            'num_vendas' => $num_vendas_total,
            'ticket_medio' => $ticket_medio_global,
            'resultado' => $resultado,
            'saldo_cartoes' => $saldo_deixado,
            'cartoes_ativos' => $cartoes_ativos,
            'margem_liquida' => $margem_liquida,
            'taxa_conversao' => $taxa_conversao,
            'receita_cartoes' => $receita_cartoes
        ],
        'evolucao' => $evolucao,
        'pagamentos' => $pagamentos,
        'pagamentos_diario' => $pagamentos_diario,
        'top_produtos' => $top_produtos,
        'top_categorias' => $top_categorias,
        'periodo' => [ 'inicio' => $data_inicio, 'fim' => $data_fim ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao montar relatório.']);
}


