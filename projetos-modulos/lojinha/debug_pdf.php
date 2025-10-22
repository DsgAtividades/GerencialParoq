<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Debug - Geração de PDF</h2>";
echo "<hr>";

echo "<h3>📋 Dados Recebidos:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>📋 Método da Requisição:</h3>";
echo "<p>" . $_SERVER['REQUEST_METHOD'] . "</p>";

echo "<h3>📋 Headers:</h3>";
echo "<pre>";
print_r(getallheaders());
echo "</pre>";

echo "<h3>📋 Teste de Conexão com Banco:</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Conexão com banco OK<br>";
    } else {
        echo "❌ Erro na conexão com banco<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<h3>📋 Teste de FPDF:</h3>";
try {
    require_once 'fpdf_simples.php';
    $pdf = new FPDF();
    echo "✅ FPDF carregado OK<br>";
} catch (Exception $e) {
    echo "❌ Erro FPDF: " . $e->getMessage() . "<br>";
}

echo "<h3>📋 Teste de Geração de PDF:</h3>";
try {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Teste de PDF', 0, 1, 'C');
    
    $nome_arquivo = 'teste_debug_' . date('Y-m-d_H-i-s') . '.pdf';
    $caminho_arquivo = 'temp/' . $nome_arquivo;
    
    if (!file_exists('temp')) {
        mkdir('temp', 0755, true);
    }
    
    $pdf->Output('F', $caminho_arquivo);
    echo "✅ PDF gerado com sucesso: {$nome_arquivo}<br>";
    echo "<a href='{$caminho_arquivo}' target='_blank'>📄 Abrir PDF</a><br>";
    
} catch (Exception $e) {
    echo "❌ Erro na geração: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Voltar para o módulo</a></p>";
?>
