<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Atualizando banco de dados...</h2>";
    
    // 1. Primeiro limpar os valores de id_categoria na tabela produtos
    $sql = "UPDATE produtos SET id_categoria = NULL";
    $db->exec($sql);
    echo "<p style='color: blue;'>→ Valores de id_categoria limpos na tabela produtos.</p>";
    
    // 2. Verificar e remover foreign key existente
    $sql = "SELECT 
                CONSTRAINT_NAME
            FROM 
                information_schema.KEY_COLUMN_USAGE
            WHERE 
                TABLE_SCHEMA = 'festa' AND
                TABLE_NAME = 'produtos' AND
                REFERENCED_TABLE_NAME = 'categorias'";
            
    $stmt = $db->query($sql);
    $constraint = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($constraint) {
        $fk_name = $constraint['CONSTRAINT_NAME'];
        echo "<p style='color: blue;'>→ Foreign key encontrada: " . htmlspecialchars($fk_name) . "</p>";
        
        // Remover a foreign key encontrada
        $sql = "ALTER TABLE `produtos` DROP FOREIGN KEY `" . $fk_name . "`";
        $db->exec($sql);
        echo "<p style='color: green;'>✓ Foreign key removida com sucesso!</p>";
    } else {
        echo "<p style='color: blue;'>→ Nenhuma foreign key encontrada.</p>";
    }

    // 3. Dropar tabela categorias se existir
    $sql = "DROP TABLE IF EXISTS `categorias`";
    $db->exec($sql);
    echo "<p style='color: blue;'>→ Tabela categorias removida (se existia).</p>";

    // 4. Criar nova tabela categorias
    $sql = "CREATE TABLE `categorias` (
        `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
        `nome_categoria` varchar(100) NOT NULL,
        `descricao` text,
        `ativo` tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (`id_categoria`),
        UNIQUE KEY `uk_categoria_nome` (`nome_categoria`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $db->exec($sql);
    echo "<p style='color: green;'>✓ Nova tabela categorias criada!</p>";

    // 5. Verificar/criar coluna id_categoria em produtos
    $stmt = $db->query("SHOW COLUMNS FROM `produtos` LIKE 'id_categoria'");
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE `produtos` 
                ADD COLUMN `id_categoria` int(11) DEFAULT NULL AFTER `nome_produto`";
        $db->exec($sql);
        echo "<p style='color: green;'>✓ Coluna id_categoria adicionada em produtos!</p>";
    } else {
        echo "<p style='color: blue;'>→ Coluna id_categoria já existe em produtos.</p>";
    }

    // 6. Criar nova foreign key
    $sql = "ALTER TABLE `produtos` 
            ADD CONSTRAINT `fk_produto_categoria` 
            FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) 
            ON DELETE RESTRICT";
    $db->exec($sql);
    echo "<p style='color: green;'>✓ Nova foreign key criada!</p>";

    // 7. Inserir categorias padrão
    $categorias = [
        ['Comidas', 'Produtos alimentícios'],
        ['Bebidas', 'Bebidas em geral'],
        ['Doces', 'Doces e sobremesas'],
        ['Salgados', 'Salgados diversos'],
        ['Outros', 'Outros produtos']
    ];

    $stmt = $db->prepare("INSERT INTO categorias (nome_categoria, descricao) VALUES (?, ?)");
    foreach ($categorias as $categoria) {
        $stmt->execute($categoria);
        echo "<p style='color: green;'>✓ Categoria '{$categoria[0]}' inserida!</p>";
    }

    // 8. Atualizar produtos sem categoria
    $sql = "UPDATE produtos p 
            SET p.id_categoria = (
                SELECT id_categoria 
                FROM categorias 
                WHERE nome_categoria = 'Outros'
                LIMIT 1
            )
            WHERE p.id_categoria IS NULL";
    
    $db->exec($sql);
    echo "<p style='color: green;'>✓ Produtos sem categoria atualizados!</p>";

    echo "<p style='color: green; font-weight: bold; margin-top: 20px;'>✓ Atualização concluída com sucesso!</p>";

} catch(PDOException $e) {
    echo "<p style='color: red; font-weight: bold;'>Erro: " . $e->getMessage() . "</p>";
}

// Adicionar estilo e botão de retorno
echo "
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        line-height: 1.6;
        background-color: #f8f9fa;
    }
    h2 {
        color: #0d6efd;
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 10px;
    }
    .btn {
        display: inline-block;
        padding: 10px 20px;
        background-color: #0d6efd;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin-top: 20px;
        transition: background-color 0.3s;
    }
    .btn:hover {
        background-color: #0b5ed7;
    }
    p {
        margin: 10px 0;
        padding: 8px;
        border-radius: 4px;
        background-color: white;
    }
    p[style*='color: green'] {
        border-left: 4px solid #198754;
    }
    p[style*='color: blue'] {
        border-left: 4px solid #0d6efd;
    }
    p[style*='color: red'] {
        border-left: 4px solid #dc3545;
    }
</style>

<div style='margin-top: 20px;'>
    <a href='produtos.php' class='btn'>Voltar para Produtos</a>
</div>";
?>
