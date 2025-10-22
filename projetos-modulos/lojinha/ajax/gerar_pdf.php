<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

// Incluir a biblioteca FPDF simplificada
require_once '../fpdf_simples.php';

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

    // Criar instância do FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Cabeçalho do relatório
    $pdf->Cell(0, 10, 'RELATÓRIO DE ' . strtoupper($tipo_relatorio), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, 'Período: ' . date('d/m/Y', strtotime($data_inicio)) . ' a ' . date('d/m/Y', strtotime($data_fim)), 0, 1, 'C');
    $pdf->Cell(0, 8, 'Gerado em: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    $pdf->Ln(10);

    switch ($tipo_relatorio) {
        case 'vendas':
            gerarRelatorioVendasPDF($pdf, $pdo, $data_inicio, $data_fim);
            break;
        case 'estoque':
            gerarRelatorioEstoquePDF($pdf, $pdo, $data_inicio, $data_fim);
            break;
        case 'financeiro':
            gerarRelatorioFinanceiroPDF($pdf, $pdo, $data_inicio, $data_fim);
            break;
        case 'produtos':
            gerarRelatorioProdutosPDF($pdf, $pdo, $data_inicio, $data_fim);
            break;
        default:
            throw new Exception('Tipo de relatório inválido');
    }

    // Gerar nome do arquivo
    $nome_arquivo = 'relatorio_' . $tipo_relatorio . '_' . date('Y-m-d_H-i-s') . '.pdf';
    $caminho_arquivo = '../temp/' . $nome_arquivo;

    // Criar diretório temp se não existir
    if (!file_exists('../temp')) {
        mkdir('../temp', 0755, true);
    }

    // Salvar PDF
    $pdf->Output('F', $caminho_arquivo);

    echo json_encode([
        'success' => true,
        'message' => 'PDF gerado com sucesso!',
        'arquivo' => $nome_arquivo,
        'url' => 'temp/' . $nome_arquivo
    ]);

} catch (Exception $e) {
    error_log("Erro ao gerar PDF: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
    ]);
}

function gerarRelatorioVendasPDF($pdf, $pdo, $data_inicio, $data_fim) {
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

    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'RESUMO GERAL', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    
    $pdf->Cell(50, 8, 'Total de Vendas:', 0, 0);
    $pdf->Cell(30, 8, $dados['total_vendas'], 0, 1);
    
    $pdf->Cell(50, 8, 'Faturamento Total:', 0, 0);
    $pdf->Cell(30, 8, 'R$ ' . number_format($dados['faturamento_total'], 2, ',', '.'), 0, 1);
    
    $pdf->Cell(50, 8, 'Ticket Médio:', 0, 0);
    $pdf->Cell(30, 8, 'R$ ' . number_format($dados['ticket_medio'], 2, ',', '.'), 0, 1);
    
    $pdf->Cell(50, 8, 'Total Descontos:', 0, 0);
    $pdf->Cell(30, 8, 'R$ ' . number_format($dados['total_descontos'], 2, ',', '.'), 0, 1);
    
    $pdf->Ln(10);

    // Vendas por forma de pagamento
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'VENDAS POR FORMA DE PAGAMENTO', 0, 1);
    
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

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Forma de Pagamento', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Quantidade', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Valor Total', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 10);
    foreach ($vendas_pagamento as $venda) {
        $pdf->Cell(60, 8, ucfirst($venda['forma_pagamento']), 1, 0);
        $pdf->Cell(30, 8, $venda['quantidade'], 1, 0, 'C');
        $pdf->Cell(40, 8, 'R$ ' . number_format($venda['valor_total'], 2, ',', '.'), 1, 1, 'R');
    }
}

function gerarRelatorioEstoquePDF($pdf, $pdo, $data_inicio, $data_fim) {
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

    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'RESUMO DO ESTOQUE', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    
    $pdf->Cell(60, 8, 'Total de Produtos:', 0, 0);
    $pdf->Cell(30, 8, $dados['total_produtos'], 0, 1);
    
    $pdf->Cell(60, 8, 'Total em Estoque:', 0, 0);
    $pdf->Cell(30, 8, $dados['total_estoque'] . ' unidades', 0, 1);
    
    $pdf->Cell(60, 8, 'Valor Total Estoque:', 0, 0);
    $pdf->Cell(30, 8, 'R$ ' . number_format($dados['valor_total_estoque'], 2, ',', '.'), 0, 1);
    
    $pdf->Cell(60, 8, 'Produtos em Falta:', 0, 0);
    $pdf->Cell(30, 8, $dados['produtos_em_falta'], 0, 1);
    
    $pdf->Ln(10);

    // Produtos em falta
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'PRODUTOS EM FALTA', 0, 1);
    
    $stmt = $pdo->prepare("
        SELECT 
            p.codigo,
            p.nome,
            p.estoque_atual,
            p.estoque_minimo,
            (p.estoque_minimo - p.estoque_atual) as quantidade_faltante
        FROM lojinha_produtos p
        WHERE p.ativo = 1 
        AND p.estoque_atual <= p.estoque_minimo
        ORDER BY quantidade_faltante DESC
        LIMIT 20
    ");
    $stmt->execute();
    $produtos_falta = $stmt->fetchAll();

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 8, 'Código', 1, 0, 'C');
    $pdf->Cell(80, 8, 'Produto', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Estoque Atual', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Estoque Mín.', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Faltante', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 9);
    foreach ($produtos_falta as $produto) {
        $pdf->Cell(20, 8, $produto['codigo'], 1, 0, 'C');
        $pdf->Cell(80, 8, substr($produto['nome'], 0, 35), 1, 0);
        $pdf->Cell(25, 8, $produto['estoque_atual'], 1, 0, 'C');
        $pdf->Cell(25, 8, $produto['estoque_minimo'], 1, 0, 'C');
        $pdf->Cell(25, 8, $produto['quantidade_faltante'], 1, 1, 'C');
    }
}

function gerarRelatorioFinanceiroPDF($pdf, $pdo, $data_inicio, $data_fim) {
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

    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'RESUMO FINANCEIRO', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    
    $pdf->Cell(60, 8, 'Total de Vendas:', 0, 0);
    $pdf->Cell(30, 8, $dados['total_vendas'], 0, 1);
    
    $pdf->Cell(60, 8, 'Faturamento Bruto:', 0, 0);
    $pdf->Cell(30, 8, 'R$ ' . number_format($dados['faturamento_bruto'], 2, ',', '.'), 0, 1);
    
    $pdf->Cell(60, 8, 'Total Descontos:', 0, 0);
    $pdf->Cell(30, 8, 'R$ ' . number_format($dados['total_descontos'], 2, ',', '.'), 0, 1);
    
    $pdf->Cell(60, 8, 'Faturamento Líquido:', 0, 0);
    $pdf->Cell(30, 8, 'R$ ' . number_format($dados['faturamento_liquido'], 2, ',', '.'), 0, 1);
    
    $pdf->Ln(10);

    // Análise de lucro
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(vi.quantidade * p.preco_compra), 0) as custo_total_vendas,
            COALESCE(SUM(vi.subtotal), 0) as receita_total_vendas,
            COALESCE(SUM(vi.subtotal - (vi.quantidade * p.preco_compra)), 0) as lucro_bruto
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $lucro = $stmt->fetch();

    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'ANÁLISE DE LUCRO', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    
    $pdf->Cell(60, 8, 'Custo Total Vendas:', 0, 0);
    $pdf->Cell(30, 8, 'R$ ' . number_format($lucro['custo_total_vendas'], 2, ',', '.'), 0, 1);
    
    $pdf->Cell(60, 8, 'Receita Total Vendas:', 0, 0);
    $pdf->Cell(30, 8, 'R$ ' . number_format($lucro['receita_total_vendas'], 2, ',', '.'), 0, 1);
    
    $pdf->Cell(60, 8, 'Lucro Bruto:', 0, 0);
    $pdf->Cell(30, 8, 'R$ ' . number_format($lucro['lucro_bruto'], 2, ',', '.'), 0, 1);
    
    $margem = $lucro['receita_total_vendas'] > 0 ? 
        round(($lucro['lucro_bruto'] / $lucro['receita_total_vendas']) * 100, 2) : 0;
    $pdf->Cell(60, 8, 'Margem de Lucro:', 0, 0);
    $pdf->Cell(30, 8, $margem . '%', 0, 1);
}

function gerarRelatorioProdutosPDF($pdf, $pdo, $data_inicio, $data_fim) {
    // Top produtos mais vendidos
    $stmt = $pdo->prepare("
        SELECT 
            p.codigo,
            p.nome,
            SUM(vi.quantidade) as quantidade_vendida,
            COALESCE(SUM(vi.subtotal), 0) as receita_total
        FROM lojinha_vendas_itens vi
        INNER JOIN lojinha_vendas v ON vi.venda_id = v.id
        INNER JOIN lojinha_produtos p ON vi.produto_id = p.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ? 
        AND v.status = 'finalizada'
        GROUP BY p.id, p.codigo, p.nome
        ORDER BY quantidade_vendida DESC
        LIMIT 20
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $produtos = $stmt->fetchAll();

    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'TOP 20 PRODUTOS MAIS VENDIDOS', 0, 1);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 8, 'Código', 1, 0, 'C');
    $pdf->Cell(100, 8, 'Produto', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Qtd. Vendida', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Receita Total', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 9);
    foreach ($produtos as $produto) {
        $pdf->Cell(20, 8, $produto['codigo'], 1, 0, 'C');
        $pdf->Cell(100, 8, substr($produto['nome'], 0, 50), 1, 0);
        $pdf->Cell(30, 8, $produto['quantidade_vendida'], 1, 0, 'C');
        $pdf->Cell(30, 8, 'R$ ' . number_format($produto['receita_total'], 2, ',', '.'), 1, 1, 'R');
    }
}
?>
