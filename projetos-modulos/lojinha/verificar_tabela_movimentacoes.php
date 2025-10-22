<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Verificação da Tabela de Movimentações</h2>";

try {
    require_once 'config/config.php';
    $pdo = getConnection();
    
    echo "<h3>1. Verificando se a tabela lojinha_caixa_movimentacoes existe</h3>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'lojinha_caixa_movimentacoes'");
    if (!$stmt->fetch()) {
        echo "❌ Tabela não existe. Criando...<br>";
        
        $sql = "
        CREATE TABLE lojinha_caixa_movimentacoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            caixa_id INT NOT NULL,
            tipo ENUM('entrada', 'saida') NOT NULL,
            valor DECIMAL(10,2) NOT NULL,
            descricao VARCHAR(255) NOT NULL,
            categoria VARCHAR(50) DEFAULT NULL,
            usuario_id INT DEFAULT NULL,
            data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (caixa_id) REFERENCES lojinha_caixa(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($sql);
        echo "✅ Tabela criada com sucesso!<br>";
    } else {
        echo "✅ Tabela já existe<br>";
    }
    
    echo "<h3>2. Verificando estrutura da tabela</h3>";
    
    $stmt = $pdo->query("DESCRIBE lojinha_caixa_movimentacoes");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
    }
    echo "</table>";
    
    echo "<h3>3. Testando inserção de movimentação</h3>";
    
    // Verificar se há caixa aberto
    $stmt = $pdo->query("
        SELECT id FROM lojinha_caixa 
        WHERE DATE(data_abertura) = CURDATE() AND status = 'aberto'
        ORDER BY data_abertura DESC
        LIMIT 1
    ");
    
    $caixa = $stmt->fetch();
    if (!$caixa) {
        echo "⚠️ Nenhum caixa aberto encontrado. Abra um caixa primeiro.<br>";
        echo "<a href='index.php'>← Voltar para a Lojinha</a>";
        exit;
    }
    
    echo "✅ Caixa aberto encontrado (ID: {$caixa['id']})<br>";
    
    // Testar inserção
    $stmt = $pdo->prepare("
        INSERT INTO lojinha_caixa_movimentacoes 
        (caixa_id, tipo, valor, descricao, categoria, usuario_id) 
        VALUES (?, 'entrada', 10.00, 'Teste de movimentação', 'Teste', 1)
    ");
    
    $result = $stmt->execute([$caixa['id']]);
    
    if ($result) {
        $mov_id = $pdo->lastInsertId();
        echo "✅ Movimentação de teste inserida com sucesso! (ID: $mov_id)<br>";
        
        // Limpar teste
        $pdo->prepare("DELETE FROM lojinha_caixa_movimentacoes WHERE id = ?")->execute([$mov_id]);
        echo "✅ Teste limpo<br>";
    } else {
        echo "❌ Erro na inserção de teste<br>";
    }
    
    echo "<h3>4. Status Final</h3>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<strong>✅ Tabela de Movimentações Pronta!</strong><br>";
    echo "A funcionalidade de Nova Movimentação está pronta para uso.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Voltar para a Lojinha</a></p>";
?>

