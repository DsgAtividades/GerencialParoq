<?php
// Arquivo de teste para verificar finalização de venda
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Teste de Finalização de Venda</h2>";
echo "<hr>";

echo "<h3>📋 Dados de Teste:</h3>";
echo "<pre>";
echo "Cliente: Teste Cliente<br>";
echo "Telefone: (11) 99999-9999<br>";
echo "Forma de Pagamento: dinheiro<br>";
echo "Desconto: 0<br>";
echo "Itens: 1 produto de exemplo<br>";
echo "</pre>";

echo "<hr>";

echo "<h3>🔧 Executando Teste:</h3>";

// Simular os dados que seriam enviados pelo JavaScript
$_POST = [
    'cliente_nome' => 'Teste Cliente',
    'cliente_telefone' => '(11) 99999-9999',
    'forma_pagamento' => 'dinheiro',
    'desconto' => 0,
    'observacoes' => 'Teste de finalização de venda',
    'itens' => json_encode([
        [
            'id' => 1,
            'nome' => 'Produto de Teste',
            'preco' => 10.00,
            'quantidade' => 1
        ]
    ])
];

// Incluir o arquivo de finalização de venda
try {
    ob_start(); // Capturar saída
    include 'ajax/finalizar_venda.php';
    $output = ob_get_clean();

    echo "<h4>📤 Resposta do Sistema:</h4>";
    echo "<pre>$output</pre>";

    // Tentar decodificar JSON
    $json = json_decode($output, true);
    if ($json) {
        echo "<h4>✅ JSON Válido:</h4>";
        echo "<pre>" . print_r($json, true) . "</pre>";

        if (isset($json['success']) && $json['success']) {
            echo "<h3>🎉 Teste PASSSOU! Venda finalizada com sucesso.</h3>";
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
echo "<h3>🔍 Debug Adicional:</h3>";
echo "<p>Arquivo executado: " . __FILE__ . "</p>";
echo "<p>Diretório atual: " . __DIR__ . "</p>";
echo "<p>Data/Hora: " . date('Y-m-d H:i:s') . "</p>";

echo "<hr>";
echo "<p><a href='debug_conexao.php'>← Teste de Conexão</a> | <a href='index.php'>← Voltar ao Módulo</a></p>";
?>
