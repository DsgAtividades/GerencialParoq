<?php
// Script para inserir produto de teste para testar finalização de venda
require_once 'config/database.php';

echo "<h2>📦 Inserindo Produto de Teste</h2>";
echo "<hr>";

try {
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn) {
        echo "✅ Conexão estabelecida<br><br>";

        // Primeiro verificar se já existe produto com ID 1
        $stmt = $conn->prepare("SELECT id, nome, estoque_atual FROM lojinha_produtos WHERE id = 1");
        $stmt->execute();
        $produto_existente = $stmt->fetch();

        if ($produto_existente) {
            echo "✅ Produto já existe:<br>";
            echo "ID: " . $produto_existente['id'] . "<br>";
            echo "Nome: " . $produto_existente['nome'] . "<br>";
            echo "Estoque: " . $produto_existente['estoque_atual'] . "<br>";

            // Atualizar estoque se necessário
            if ($produto_existente['estoque_atual'] < 10) {
                $stmt = $conn->prepare("UPDATE lojinha_produtos SET estoque_atual = 10 WHERE id = 1");
                $stmt->execute();
                echo "✅ Estoque atualizado para 10 unidades<br>";
            }
        } else {
            // Inserir produto de teste
            $stmt = $conn->prepare("
                INSERT INTO lojinha_produtos
                (codigo, nome, descricao, categoria_id, fornecedor, preco_compra, preco_venda, estoque_atual, estoque_minimo, ativo, data_criacao)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
            ");

            $stmt->execute([
                'TESTE001',
                'Produto de Teste',
                'Produto criado para testar finalização de venda',
                1, // categoria_id (Livros)
                'Fornecedor Teste',
                5.00,
                10.00,
                10,
                2
            ]);

            echo "✅ Produto de teste inserido com sucesso!<br>";
            echo "ID: " . $conn->lastInsertId() . "<br>";
        }

        echo "<br><h3>🎯 Dados do Produto:</h3>";
        echo "<pre>";
        echo "ID: 1\n";
        echo "Nome: Produto de Teste\n";
        echo "Preço: R$ 10,00\n";
        echo "Estoque: 10 unidades\n";
        echo "</pre>";

        echo "<br><strong>✅ Pronto para testar finalização de venda!</strong>";

    } else {
        echo "❌ Erro na conexão com banco de dados";
    }

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}

echo "<hr>";
echo "<p><a href='teste_finalizar_venda.php'>← Testar Finalização de Venda</a> | <a href='index.php'>← Voltar ao Módulo</a></p>";
?>
