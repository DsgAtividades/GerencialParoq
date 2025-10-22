<?php
// Script para criar tabela lojinha_vendas_itens que está faltando
require_once 'config/database.php';

echo "<h2>🔧 Criar Tabela - lojinha_vendas_itens</h2>";
echo "<hr>";

try {
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn) {
        echo "✅ Conexão estabelecida<br><br>";

        // Verificar se tabela já existe
        $stmt = $conn->query("SHOW TABLES LIKE 'lojinha_vendas_itens'");
        $existe = $stmt->fetch();

        if ($existe) {
            echo "✅ Tabela 'lojinha_vendas_itens' já existe!<br>";
            echo "💡 Não é necessário criar novamente.<br>";
        } else {
            echo "❌ Tabela 'lojinha_vendas_itens' não existe<br>";
            echo "🔧 Criando tabela...<br><br>";

            // SQL para criar tabela lojinha_vendas_itens
            $sql = "
            CREATE TABLE `lojinha_vendas_itens` (
              `id` int(11) NOT NULL,
              `venda_id` int(11) NOT NULL,
              `produto_id` int(11) NOT NULL,
              `quantidade` int(11) NOT NULL,
              `preco_unitario` decimal(10,2) NOT NULL,
              `subtotal` decimal(10,2) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            -- Índices
            ALTER TABLE `lojinha_vendas_itens`
              ADD PRIMARY KEY (`id`),
              ADD KEY `idx_venda` (`venda_id`),
              ADD KEY `idx_produto` (`produto_id`);

            -- AUTO_INCREMENT
            ALTER TABLE `lojinha_vendas_itens`
              MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

            -- Constraints (se as tabelas pai existirem)
            ALTER TABLE `lojinha_vendas_itens`
              ADD CONSTRAINT `lojinha_vendas_itens_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `lojinha_vendas` (`id`) ON DELETE CASCADE,
              ADD CONSTRAINT `lojinha_vendas_itens_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `lojinha_produtos` (`id`);
            ";

            // Executar SQL
            $conn->exec($sql);

            echo "✅ Tabela 'lojinha_vendas_itens' criada com sucesso!<br><br>";

            // Verificar estrutura criada
            $stmt = $conn->query("DESCRIBE lojinha_vendas_itens");
            $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<h4>📊 Estrutura da tabela criada:</h4>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";

            foreach ($colunas as $coluna) {
                echo "<tr>";
                echo "<td>" . $coluna['Field'] . "</td>";
                echo "<td>" . $coluna['Type'] . "</td>";
                echo "<td>" . $coluna['Null'] . "</td>";
                echo "<td>" . $coluna['Key'] . "</td>";
                echo "<td>" . $coluna['Default'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }

        // Verificar se tabela existe agora
        $stmt = $conn->query("SHOW TABLES LIKE 'lojinha_vendas_itens'");
        $existe_apos = $stmt->fetch();

        if ($existe_apos) {
            echo "<br><h3>🎉 Status Final:</h3>";
            echo "✅ Tabela 'lojinha_vendas_itens' está pronta!<br>";
            echo "💡 Agora você pode testar a finalização de venda.<br>";
        } else {
            echo "<br><h3>❌ Erro:</h3>";
            echo "Não foi possível criar a tabela.<br>";
        }

    } else {
        echo "❌ Erro na conexão com banco de dados";
    }

} catch (Exception $e) {
    echo "❌ Erro ao criar tabela: " . $e->getMessage() . "<br>";
    echo "<h4>💡 Possíveis soluções:</h4>";
    echo "1. Verifique se você tem permissões para criar tabelas<br>";
    echo "2. Execute o comando manualmente no phpMyAdmin<br>";
    echo "3. Entre em contato com o suporte da Locaweb<br>";
}

echo "<hr>";
echo "<p><a href='teste_finalizar_venda.php'>← Testar Finalização de Venda</a> | <a href='verificar_estrutura_venda.php'>← Verificar Estrutura</a> | <a href='index.php'>← Voltar ao Módulo</a></p>";
?>
