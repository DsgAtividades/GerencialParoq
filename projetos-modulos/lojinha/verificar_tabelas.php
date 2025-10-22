<?php
// Arquivo para verificar se as tabelas da lojinha foram criadas no banco
require_once 'config/database.php';

echo "<h2>🔍 Verificação de Tabelas - Módulo Lojinha</h2>";
echo "<hr>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ <strong>Conexão estabelecida com sucesso!</strong><br><br>";
        
        // Listar todas as tabelas do banco
        echo "<h3>📋 Todas as tabelas no banco 'gerencialparoq':</h3>";
        $stmt = $conn->query("SHOW TABLES");
        $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<ul>";
        foreach ($tabelas as $tabela) {
            echo "<li>$tabela</li>";
        }
        echo "</ul>";
        
        // Verificar especificamente as tabelas da lojinha
        echo "<h3>🛒 Tabelas da Lojinha:</h3>";
        $tabelas_lojinha = [
            'lojinha_categorias',
            'lojinha_fornecedores', 
            'lojinha_produtos',
            'lojinha_vendas',
            'lojinha_vendas_itens',
            'lojinha_estoque_movimentacoes',
            'lojinha_caixa'
        ];
        
        $tabelas_existentes = [];
        $tabelas_faltando = [];
        
        foreach ($tabelas_lojinha as $tabela) {
            if (in_array($tabela, $tabelas)) {
                $tabelas_existentes[] = $tabela;
                echo "✅ $tabela - <strong>EXISTE</strong><br>";
                
                // Contar registros
                try {
                    $stmt = $conn->query("SELECT COUNT(*) as total FROM $tabela");
                    $result = $stmt->fetch();
                    echo "&nbsp;&nbsp;&nbsp;📊 Registros: " . $result['total'] . "<br>";
                } catch (Exception $e) {
                    echo "&nbsp;&nbsp;&nbsp;❌ Erro ao contar: " . $e->getMessage() . "<br>";
                }
            } else {
                $tabelas_faltando[] = $tabela;
                echo "❌ $tabela - <strong>NÃO EXISTE</strong><br>";
            }
        }
        
        echo "<br><h3>📊 Resumo:</h3>";
        echo "✅ Tabelas existentes: " . count($tabelas_existentes) . "/7<br>";
        echo "❌ Tabelas faltando: " . count($tabelas_faltando) . "/7<br>";
        
        if (count($tabelas_faltando) > 0) {
            echo "<br><h3>🚨 AÇÃO NECESSÁRIA:</h3>";
            echo "As seguintes tabelas precisam ser criadas:<br>";
            echo "<ul>";
            foreach ($tabelas_faltando as $tabela) {
                echo "<li>$tabela</li>";
            }
            echo "</ul>";
            echo "<br><strong>💡 Solução:</strong><br>";
            echo "1. Acesse o phpMyAdmin da Locaweb<br>";
            echo "2. Execute o arquivo SQL: <code>lojinha_completo.sql</code><br>";
            echo "3. Ou use o setup: <a href='database/setup.php'>database/setup.php</a><br>";
        } else {
            echo "<br><h3>🎉 Todas as tabelas estão criadas!</h3>";
            echo "O módulo lojinha deve funcionar corretamente.<br>";
            echo "<br><a href='index.php'>← Acessar módulo lojinha</a>";
        }
        
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
echo "<p><a href='teste_conexao.php'>← Teste de Conexão</a> | <a href='index.php'>← Módulo Lojinha</a></p>";
?>
