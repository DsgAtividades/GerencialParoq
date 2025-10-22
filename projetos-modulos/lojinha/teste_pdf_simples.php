<?php
require_once 'fpdf_simples.php';

// Criar PDF simples para teste
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Teste de PDF - Módulo Lojinha', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Data: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
$pdf->Cell(0, 10, 'Este é um teste de geração de PDF', 0, 1, 'C');

// Salvar PDF
$nome_arquivo = 'teste_pdf_' . date('Y-m-d_H-i-s') . '.pdf';
$caminho_arquivo = 'temp/' . $nome_arquivo;

// Criar diretório temp se não existir
if (!file_exists('temp')) {
    mkdir('temp', 0755, true);
}

$pdf->Output('F', $caminho_arquivo);

echo "<h2>✅ Teste de PDF Concluído!</h2>";
echo "<p><strong>Arquivo gerado:</strong> {$nome_arquivo}</p>";
echo "<p><strong>Caminho:</strong> {$caminho_arquivo}</p>";
echo "<p><a href='{$caminho_arquivo}' target='_blank'>📄 Abrir PDF</a></p>";
echo "<p><a href='index.php'>← Voltar para o módulo</a></p>";
?>
