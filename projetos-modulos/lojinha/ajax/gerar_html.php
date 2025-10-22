<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$tipo_relatorio = $_POST['tipo'] ?? '';
$data_inicio = $_POST['data_inicio'] ?? date('Y-m-01');
$data_fim = $_POST['data_fim'] ?? date('Y-m-d');

try {
    $database = new Database();
    $pdo = $database->getConnection();

    if (!$pdo) {
        throw new Exception('Erro ao conectar ao banco de dados');
    }

    // Gerar HTML do relatório
    $html = gerarRelatorioHTML($pdo, $tipo_relatorio, $data_inicio, $data_fim);

    // Gerar nome do arquivo
    $nome_arquivo = 'relatorio_' . $tipo_relatorio . '_' . date('Y-m-d_H-i-s') . '.html';
    $caminho_arquivo = '../temp/' . $nome_arquivo;

    // Criar diretório temp se não existir
    if (!file_exists('../temp')) {
        mkdir('../temp', 0755, true);
    }

    // Salvar HTML
    file_put_contents($caminho_arquivo, $html);

    echo json_encode([
        'success' => true,
        'message' => 'Relatório HTML gerado com sucesso!',
        'arquivo' => $nome_arquivo,
        'url' => 'temp/' . $nome_arquivo
    ]);

} catch (Exception $e) {
    error_log("Erro ao gerar relatório HTML: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao gerar relatório HTML: ' . $e->getMessage()
    ]);
}

function gerarRelatorioHTML($pdo, $tipo, $data_inicio, $data_fim) {
    $html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de ' . ucfirst($tipo) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #007bff; }
        .header h1 { color: #007bff; margin: 0; font-size: 28px; }
        .header p { color: #6c757d; margin: 5px 0; }
        .section { margin-bottom: 30px; }
        .section h2 { color: #495057; border-left: 4px solid #007bff; padding-left: 15px; margin-bottom: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; border-left: 4px solid #007bff; }
        .stat-card h3 { margin: 0 0 10px 0; color: #007bff; font-size: 14px; }
        .stat-card .value { font-size: 24px; font-weight: bold; color: #495057; margin: 0; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .table th { background: #f8f9fa; font-weight: bold; color: #495057; }
        .table tr:hover { background: #f8f9fa; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #6c757d; }
        @media print { body { background: white; } .container { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Relatório de ' . ucfirst($tipo) . '</h1>
            <p>Período: ' . date('d/m/Y', strtotime($data_inicio)) . ' a ' . date('d/m/Y', strtotime($data_fim)) . '</p>
            <p>Gerado em: ' . date('d/m/Y H:i:s') . '</p>
        </div>';

    switch ($tipo) {
        case 'vendas':
            $html .= gerarRelatorioVendasHTML($pdo, $data_inicio, $data_fim);
            break;
        case 'estoque':
            $html .= gerarRelatorioEstoqueHTML($pdo, $data_inicio, $data_fim);
            break;
        case 'financeiro':
            $html .= gerarRelatorioFinanceiroHTML($pdo, $data_inicio, $data_fim);
            break;
        case 'produtos':
            $html .= gerarRelatorioProdutosHTML($pdo, $data_inicio, $data_fim);
            break;
    }

    $html .= '
        <div class="footer">
            <p>Relatório gerado automaticamente pelo Sistema de Gestão Paroquial</p>
        </div>
    </div>
</body>
</html>';

    return $html;
}

function gerarRelatorioVendasHTML($pdo, $data_inicio, $data_fim) {
    // Dados gerais de vendas
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
    $dados = $stmt->fetch();

    $html = '
        <div class="section">
            <h2>📊 Resumo de Vendas</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total de Vendas</h3>
                    <p class="value">' . $dados['total_vendas'] . '</p>
                </div>
                <div class="stat-card">
                    <h3>Faturamento Total</h3>
                    <p class="value">R$ ' . number_format($dados['faturamento_total'], 2, ',', '.') . '</p>
                </div>
                <div class="stat-card">
                    <h3>Ticket Médio</h3>
                    <p class="value">R$ ' . number_format($dados['ticket_medio'], 2, ',', '.') . '</p>
                </div>
                <div class="stat-card">
                    <h3>Total Descontos</h3>
                    <p class="value">R$ ' . number_format($dados['total_descontos'], 2, ',', '.') . '</p>
                </div>
            </div>
        </div>';

    // Vendas por forma de pagamento
    $stmt = $pdo->prepare("
        SELECT 
            forma_pagamento,
            COUNT(*) as quantidade,
            COALESCE(SUM(total), 0) as valor_total
        FROM lojinha_vendas 
        WHERE DATE(data_venda) BETWEEN ? AND ? 
        AND status = 'finalizada'
        GROUP BY forma_pagamento
        ORDER BY valor_total DESC
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $vendas_pagamento = $stmt->fetchAll();

    $html .= '
        <div class="section">
            <h2>💳 Vendas por Forma de Pagamento</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Forma de Pagamento</th>
                        <th class="text-center">Quantidade</th>
                        <th class="text-right">Valor Total</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($vendas_pagamento as $venda) {
        $html .= '
                    <tr>
                        <td>' . ucfirst($venda['forma_pagamento']) . '</td>
                        <td class="text-center">' . $venda['quantidade'] . '</td>
                        <td class="text-right">R$ ' . number_format($venda['valor_total'], 2, ',', '.') . '</td>
                    </tr>';
    }

    $html .= '
                </tbody>
            </table>
        </div>';

    return $html;
}

function gerarRelatorioEstoqueHTML($pdo, $data_inicio, $data_fim) {
    // Resumo do estoque
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_produtos,
            SUM(estoque_atual) as total_estoque,
            SUM(estoque_atual * preco_venda) as valor_total_estoque,
            COUNT(CASE WHEN estoque_atual <= estoque_minimo THEN 1 END) as produtos_em_falta
        FROM lojinha_produtos 
        WHERE ativo = 1
    ");
    $stmt->execute();
    $dados = $stmt->fetch();

    $html = '
        <div class="section">
            <h2>📦 Resumo do Estoque</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total de Produtos</h3>
                    <p class="value">' . $dados['total_produtos'] . '</p>
                </div>
                <div class="stat-card">
                    <h3>Total em Estoque</h3>
                    <p class="value">' . $dados['total_estoque'] . ' unidades</p>
                </div>
                <div class="stat-card">
                    <h3>Valor Total Estoque</h3>
                    <p class="value">R$ ' . number_format($dados['valor_total_estoque'], 2, ',', '.') . '</p>
                </div>
                <div class="stat-card">
                    <h3>Produtos em Falta</h3>
                    <p class="value">' . $dados['produtos_em_falta'] . '</p>
                </div>
            </div>
        </div>';

    return $html;
}

function gerarRelatorioFinanceiroHTML($pdo, $data_inicio, $data_fim) {
    // Resumo financeiro
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
    $stmt->execute([$data_inicio, $data_fim]);
    $dados = $stmt->fetch();

    $html = '
        <div class="section">
            <h2>💰 Resumo Financeiro</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Faturamento Bruto</h3>
                    <p class="value">R$ ' . number_format($dados['faturamento_bruto'], 2, ',', '.') . '</p>
                </div>
                <div class="stat-card">
                    <h3>Faturamento Líquido</h3>
                    <p class="value">R$ ' . number_format($dados['faturamento_liquido'], 2, ',', '.') . '</p>
                </div>
                <div class="stat-card">
                    <h3>Total Descontos</h3>
                    <p class="value">R$ ' . number_format($dados['total_descontos'], 2, ',', '.') . '</p>
                </div>
                <div class="stat-card">
                    <h3>Total Vendas</h3>
                    <p class="value">' . $dados['total_vendas'] . '</p>
                </div>
            </div>
        </div>';

    return $html;
}

function gerarRelatorioProdutosHTML($pdo, $data_inicio, $data_fim) {
    // Top produtos mais vendidos
    $stmt = $pdo->prepare("
        SELECT 
            p.nome as produto,
            SUM(vi.quantidade) as quantidade_vendida,
            COALESCE(SUM(vi.subtotal), 0) as receita_total
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
        GROUP BY p.id, p.nome
        ORDER BY quantidade_vendida DESC
        LIMIT 10
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $produtos = $stmt->fetchAll();

    $html = '
        <div class="section">
            <h2>🏆 Top 10 Produtos Mais Vendidos</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Posição</th>
                        <th>Produto</th>
                        <th class="text-center">Qtd. Vendida</th>
                        <th class="text-right">Receita Total</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($produtos as $index => $produto) {
        $posicao = $index + 1;
        $badge = $posicao <= 3 ? 'badge-success' : 'badge-warning';
        $html .= '
                    <tr>
                        <td><span class="badge ' . $badge . '">' . $posicao . 'º</span></td>
                        <td>' . $produto['produto'] . '</td>
                        <td class="text-center">' . $produto['quantidade_vendida'] . '</td>
                        <td class="text-right">R$ ' . number_format($produto['receita_total'], 2, ',', '.') . '</td>
                    </tr>';
    }

    $html .= '
                </tbody>
            </table>
        </div>';

    return $html;
}
?>
