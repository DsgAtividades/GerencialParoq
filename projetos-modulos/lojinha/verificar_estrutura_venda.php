<?php
// Verificar estrutura das tabelas necessárias para vendas
require_once 'config/database.php';

echo "<h2>🔍 Verificação de Estrutura - Vendas</h2>";
echo "<hr>";

try {
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn) {
        echo "✅ Conexão estabelecida<br><br>";

        // Verificar tabelas necessárias
        $tabelas_necessarias = [
            'lojinha_vendas',
            'lojinha_vendas_itens',
            'lojinha_produtos',
            'lojinha_estoque_movimentacoes',
            'lojinha_categorias',
            'lojinha_fornecedores'
        ];

        echo "<h3>📋 Verificação de Tabelas:</h3>";
        foreach ($tabelas_necessarias as $tabela) {
            try {
                $stmt = $conn->query("SHOW TABLES LIKE '$tabela'");
                $existe = $stmt->fetch();

                if ($existe) {
                    echo "✅ $tabela - Existe<br>";

                    // Verificar estrutura básica
                    $stmt = $conn->query("DESCRIBE $tabela");
                    $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    echo "&nbsp;&nbsp;&nbsp;📊 Colunas: " . implode(', ', $colunas) . "<br>";
                } else {
                    echo "❌ $tabela - Não existe<br>";
                }
            } catch (Exception $e) {
                echo "❌ $tabela - Erro: " . $e->getMessage() . "<br>";
            }
        }

        echo "<br><h3>📊 Dados Existentes:</h3>";

        // Verificar produtos
        try {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM lojinha_produtos");
            $produtos = $stmt->fetch();
            echo "📦 Produtos cadastrados: " . $produtos['total'] . "<br>";
        } catch (Exception $e) {
            echo "❌ Erro ao contar produtos: " . $e->getMessage() . "<br>";
        }

        // Verificar vendas anteriores
        try {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM lojinha_vendas");
            $vendas = $stmt->fetch();
            echo "🛒 Vendas realizadas: " . $vendas['total'] . "<br>";
        } catch (Exception $e) {
            echo "❌ Erro ao contar vendas: " . $e->getMessage() . "<br>";
        }

        echo "<br><h3>🎯 Status do Sistema:</h3>";
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";

        if ($produtos['total'] > 0) {
            echo "✅ Sistema pronto para vendas<br>";
            echo "💡 Você pode testar a finalização de venda<br>";
        } else {
            echo "⚠️ Nenhum produto cadastrado<br>";
            echo "💡 Use <a href='inserir_produto_teste.php'>inserir_produto_teste.php</a> primeiro<br>";
        }

        echo "</div>";

    } else {
        echo "❌ Erro na conexão com banco de dados";
    }

} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage();
}

echo "<hr>";
echo "<p><a href='inserir_produto_teste.php'>← Inserir Produto de Teste</a> | <a href='teste_finalizar_venda.php'>← Testar Finalização</a> | <a href='index.php'>← Voltar ao Módulo</a></p>";
?>
