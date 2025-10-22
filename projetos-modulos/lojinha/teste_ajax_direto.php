<?php
// Teste direto dos arquivos AJAX para identificar problemas
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔌 Teste Direto dos Arquivos AJAX</h2>";
echo "<hr>";

$ajax_files = [
    'produtos_direto.php',
    'categorias.php',
    'teste_direto.php'
];

foreach ($ajax_files as $file) {
    echo "<h3>📋 Testando: $file</h3>";

    $url = "ajax/$file";
    $full_path = __DIR__ . "/ajax/$file";

    if (file_exists($full_path)) {
        echo "<p>✅ Arquivo existe: $full_path</p>";

        // Testar se consegue executar o arquivo
        try {
            ob_start();
            include $full_path;
            $output = ob_get_clean();

            echo "<p>✅ Arquivo executado com sucesso</p>";
            echo "<p><strong>Resposta:</strong></p>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($output) . "</pre>";

            // Verificar se é JSON válido
            $json = json_decode($output, true);
            if ($json !== null) {
                echo "<p>✅ Resposta é JSON válido</p>";
                if (isset($json['success'])) {
                    echo "<p>📊 Status: " . ($json['success'] ? 'Sucesso' : 'Erro') . "</p>";
                    if (!$json['success'] && isset($json['message'])) {
                        echo "<p>💬 Mensagem: " . $json['message'] . "</p>";
                    }
                }
            } else {
                echo "<p>⚠️ Resposta não é JSON válido</p>";
            }

        } catch (Exception $e) {
            echo "<p>❌ Erro ao executar arquivo: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ Arquivo não encontrado: $full_path</p>";
    }

    echo "<hr>";
}

echo "<h3>🎯 Próximos Passos:</h3>";
echo "<ol>";
echo "<li>Abra o console do navegador (F12)</li>";
echo "<li>Acesse o módulo lojinha</li>";
echo "<li>Abra a aba 'Network' no DevTools</li>";
echo "<li>Tente finalizar uma venda</li>";
echo "<li>Veja se as requisições AJAX estão sendo feitas</li>";
echo "<li>Verifique as respostas das requisições</li>";
echo "</ol>";

echo "<p><a href='diagnostico_completo.php'>← Diagnóstico Completo</a> | <a href='index.php'>← Voltar ao Módulo</a></p>";
?>
