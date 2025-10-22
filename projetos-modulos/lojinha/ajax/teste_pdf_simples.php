<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../fpdf_simples.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$tipo_relatorio = $_POST['tipo'] ?? 'teste';
$data_inicio = $_POST['data_inicio'] ?? date('Y-m-01');
$data_fim = $_POST['data_fim'] ?? date('Y-m-d');

try {
    // Criar PDF simples
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'TESTE DE PDF - ' . strtoupper($tipo_relatorio), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, 'Período: ' . $data_inicio . ' a ' . $data_fim, 0, 1, 'C');
    $pdf->Cell(0, 8, 'Gerado em: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Dados do Relatório', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, 'Tipo: ' . $tipo_relatorio, 0, 1);
    $pdf->Cell(0, 8, 'Data Início: ' . $data_inicio, 0, 1);
    $pdf->Cell(0, 8, 'Data Fim: ' . $data_fim, 0, 1);
    $pdf->Ln(10);
    
    $pdf->Cell(0, 8, 'Este é um teste de geração de PDF.', 0, 1);
    $pdf->Cell(0, 8, 'Se você está vendo este PDF, a geração está funcionando!', 0, 1);

    // Gerar nome do arquivo
    $nome_arquivo = 'teste_pdf_' . $tipo_relatorio . '_' . date('Y-m-d_H-i-s') . '.pdf';
    $caminho_arquivo = '../temp/' . $nome_arquivo;

    // Criar diretório temp se não existir
    if (!file_exists('../temp')) {
        mkdir('../temp', 0755, true);
    }

    // Salvar PDF
    $pdf->Output('F', $caminho_arquivo);

    echo json_encode([
        'success' => true,
        'message' => 'PDF de teste gerado com sucesso!',
        'arquivo' => $nome_arquivo,
        'url' => 'temp/' . $nome_arquivo
    ]);

} catch (Exception $e) {
    error_log("Erro ao gerar PDF de teste: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao gerar PDF de teste: ' . $e->getMessage()
    ]);
}
?>
