<?php
// Arquivo de teste para verificar conexão com banco de dados na Locaweb
require_once 'config/database.php';

echo "<h2>🧪 Teste de Conexão - Módulo Lojinha</h2>";
echo "<hr>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ <strong>Conexão estabelecida com sucesso!</strong><br><br>";
        
        // Testar consulta nas tabelas da lojinha
        echo "<h3>📊 Verificando Tabelas:</h3>";
        
        // Verificar se as tabelas existem
        $tabelas = [
            'lojinha_categorias',
            'lojinha_fornecedores', 
            'lojinha_produtos',
            'lojinha_vendas',
            'lojinha_vendas_itens',
            'lojinha_estoque_movimentacoes',
            'lojinha_caixa'
        ];
        
        foreach ($tabelas as $tabela) {
            try {
                $stmt = $conn->query("SELECT COUNT(*) as total FROM $tabela");
                $result = $stmt->fetch();
                echo "✅ $tabela: " . $result['total'] . " registros<br>";
            } catch (Exception $e) {
                echo "❌ $tabela: Erro - " . $e->getMessage() . "<br>";
            }
        }
        
        echo "<br><h3>🎯 Teste de Consulta:</h3>";
        
        // Testar consulta específica de produtos
        try {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM lojinha_produtos");
            $result = $stmt->fetch();
            echo "✅ Total de produtos cadastrados: " . $result['total'] . "<br>";
        } catch (Exception $e) {
            echo "❌ Erro ao consultar produtos: " . $e->getMessage() . "<br>";
        }
        
        // Testar consulta de categorias
        try {
            $stmt = $conn->query("SELECT nome FROM lojinha_categorias LIMIT 3");
            $categorias = $stmt->fetchAll();
            echo "✅ Categorias disponíveis: ";
            foreach ($categorias as $cat) {
                echo $cat['nome'] . ", ";
            }
            echo "<br>";
        } catch (Exception $e) {
            echo "❌ Erro ao consultar categorias: " . $e->getMessage() . "<br>";
        }
        
        echo "<br><strong>🎉 Banco de dados funcionando corretamente!</strong>";
        
    } else {
        echo "❌ <strong>Erro ao conectar ao banco de dados</strong><br>";
        echo "Verifique as configurações em config/database.php";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Erro geral:</strong> " . $e->getMessage() . "<br>";
    echo "<br><strong>🔧 Verificações necessárias:</strong><br>";
    echo "1. Host correto (gerencialparoq.mysql.dbaas.com.br)<br>";
    echo "2. Nome do banco (gerencialparoq)<br>";
    echo "3. Usuário e senha corretos<br>";
    echo "4. Tabelas criadas no banco<br>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Voltar para o módulo</a></p>";
?>
