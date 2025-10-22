<?php
// Teste da funcionalidade de ajuste de estoque
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Teste de Ajuste de Estoque</h2>";
echo "<hr>";

echo "<h3>📋 Dados de Teste:</h3>";
echo "<pre>";
echo "Produto ID: 1 (Biblia)\n";
echo "Tipo: entrada\n";
echo "Quantidade: 5\n";
echo "Motivo: Teste de ajuste de estoque\n";
echo "</pre>";

echo "<hr>";

echo "<h3>🔧 Executando Teste:</h3>";

// Simular os dados que seriam enviados pelo JavaScript
$_POST = [
    'produto_id' => 1,
    'tipo' => 'entrada',
    'quantidade' => 5,
    'motivo' => 'Teste de ajuste de estoque',
    'produto_nome' => 'Biblia'
];

// Incluir o arquivo de ajuste de estoque
try {
    ob_start(); // Capturar saída
    include 'ajax/ajuste_estoque.php';
    $output = ob_get_clean();

    echo "<h4>📤 Resposta do Sistema:</h4>";
    echo "<pre>$output</pre>";

    // Tentar decodificar JSON
    $json = json_decode($output, true);
    if ($json) {
        echo "<h4>✅ JSON Válido:</h4>";
        echo "<pre>" . print_r($json, true) . "</pre>";

        if (isset($json['success']) && $json['success']) {
            echo "<h3>🎉 Teste PASSOU! Ajuste de estoque realizado com sucesso.</h3>";
        } else {
            echo "<h3>❌ Teste FALHOU!</h3>";
            if (isset($json['error'])) {
                echo "<p><strong>Erro específico:</strong> " . $json['error'] . "</p>";
            }
        }
    } else {
        echo "<h4>❌ Resposta não é JSON válido</h4>";
    }

} catch (Exception $e) {
    echo "<h3>❌ Erro ao executar teste:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<h4>📋 Stack Trace:</h4>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Voltar ao Módulo Lojinha</a></p>";
?>
