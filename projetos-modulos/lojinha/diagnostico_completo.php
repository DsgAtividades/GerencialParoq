<?php
// Diagnóstico completo do módulo lojinha
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico Completo - Módulo Lojinha</h1>";
echo "<hr>";

try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception("Falha na conexão inicial");
    }

    echo "<h2>✅ 1. Conexão com Banco</h2>";
    echo "<p><strong>Status:</strong> Conectado com sucesso</p>";

    // Verificar tabelas
    echo "<h2>📋 2. Verificação de Tabelas</h2>";
    $tabelas_necessarias = [
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

    foreach ($tabelas_necessarias as $tabela) {
        try {
            $stmt = $conn->query("SHOW TABLES LIKE '$tabela'");
            $existe = $stmt->fetch();
            if ($existe) {
                $tabelas_existentes[] = $tabela;

                // Verificar estrutura básica
                $stmt = $conn->query("DESCRIBE $tabela");
                $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
                echo "<p>✅ <strong>$tabela</strong> - Colunas: " . implode(', ', $colunas) . "</p>";
            } else {
                $tabelas_faltando[] = $tabela;
                echo "<p>❌ <strong>$tabela</strong> - Tabela não existe</p>";
            }
        } catch (Exception $e) {
            echo "<p>❌ <strong>$tabela</strong> - Erro: " . $e->getMessage() . "</p>";
        }
    }

    // Verificar dados
    echo "<h2>📊 3. Verificação de Dados</h2>";

    if (in_array('lojinha_produtos', $tabelas_existentes)) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM lojinha_produtos");
            $produtos = $stmt->fetch();
            echo "<p>📦 Produtos cadastrados: <strong>" . $produtos['total'] . "</strong></p>";
        } catch (Exception $e) {
            echo "<p>❌ Erro ao contar produtos: " . $e->getMessage() . "</p>";
        }
    }

    if (in_array('lojinha_categorias', $tabelas_existentes)) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM lojinha_categorias");
            $categorias = $stmt->fetch();
            echo "<p>🏷️ Categorias cadastradas: <strong>" . $categorias['total'] . "</strong></p>";
        } catch (Exception $e) {
            echo "<p>❌ Erro ao contar categorias: " . $e->getMessage() . "</p>";
        }
    }

    // Teste de venda
    echo "<h2>🧪 4. Teste de Finalização de Venda</h2>";

    if (count($tabelas_faltando) === 0 && $produtos['total'] > 0) {
        echo "<p>✅ Pré-requisitos atendidos para teste de venda</p>";

        // Simular venda
        try {
            $conn->beginTransaction();

            // Dados de teste
            $produto_id = 1;
            $quantidade = 1;
            $preco_unitario = 10.00;
            $subtotal = $preco_unitario * $quantidade;
            $cliente_nome = 'Teste Cliente';
            $forma_pagamento = 'dinheiro';

            // Verificar estoque
            $stmt = $conn->prepare("SELECT estoque_atual, nome FROM lojinha_produtos WHERE id = ?");
            $stmt->execute([$produto_id]);
            $produto = $stmt->fetch();

            if (!$produto || $produto['estoque_atual'] < $quantidade) {
                throw new Exception("Estoque insuficiente para o produto: " . ($produto['nome'] ?? 'Produto não encontrado'));
            }

            // Inserir venda
            $stmt = $conn->prepare("
                INSERT INTO lojinha_vendas (numero_venda, cliente_nome, forma_pagamento, subtotal, total, status, usuario_id)
                VALUES (?, ?, ?, ?, ?, 'finalizada', 1)
            ");
            $numero_venda = 'TEST' . date('YmdHis');
            $stmt->execute([$numero_venda, $cliente_nome, $forma_pagamento, $subtotal, $subtotal]);

            $venda_id = $conn->lastInsertId();

            // Inserir item da venda
            $stmt = $conn->prepare("
                INSERT INTO lojinha_vendas_itens (venda_id, produto_id, quantidade, preco_unitario, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$venda_id, $produto_id, $quantidade, $preco_unitario, $subtotal]);

            // Atualizar estoque
            $stmt = $conn->prepare("UPDATE lojinha_produtos SET estoque_atual = estoque_atual - ? WHERE id = ?");
            $stmt->execute([$quantidade, $produto_id]);

            // Registrar movimentação
            $stmt = $conn->prepare("
                INSERT INTO lojinha_estoque_movimentacoes (produto_id, tipo, quantidade, motivo, usuario_id)
                VALUES (?, 'saida', ?, ?, 1)
            ");
            $stmt->execute([$produto_id, $quantidade, "Venda teste #$numero_venda"]);

            $conn->commit();

            echo "<p>✅ <strong>Venda de teste executada com sucesso!</strong></p>";
            echo "<p>📋 Número da venda: <strong>$numero_venda</strong></p>";
            echo "<p>💰 Valor: <strong>R$ " . number_format($subtotal, 2, ',', '.') . "</strong></p>";

        } catch (Exception $e) {
            $conn->rollBack();
            echo "<p>❌ Erro no teste de venda: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ Pré-requisitos não atendidos para teste de venda</p>";
        if (count($tabelas_faltando) > 0) {
            echo "<p>💡 Tabelas faltando: " . implode(', ', $tabelas_faltando) . "</p>";
        }
        if ($produtos['total'] === 0) {
            echo "<p>💡 Nenhum produto cadastrado</p>";
        }
    }

    // Verificar arquivos críticos
    echo "<h2>📁 5. Verificação de Arquivos</h2>";

    $arquivos_criticos = [
        'index.php',
        'js/lojinha.js',
        'ajax/finalizar_venda.php',
        'ajax/produtos_direto.php',
        'config/database.php'
    ];

    foreach ($arquivos_criticos as $arquivo) {
        $caminho = __DIR__ . '/' . $arquivo;
        if (file_exists($caminho)) {
            echo "<p>✅ <strong>$arquivo</strong> - Existe (" . filesize($caminho) . " bytes)</p>";
        } else {
            echo "<p>❌ <strong>$arquivo</strong> - Não encontrado</p>";
        }
    }

    // Informações do servidor
    echo "<h2>🔧 6. Informações do Servidor</h2>";
    echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
    echo "<p><strong>Servidor:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
    echo "<p><strong>Diretório:</strong> " . __DIR__ . "</p>";
    echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";

    // Status geral
    echo "<h2>🎯 7. Status Geral</h2>";
    echo "<div style='border: 2px solid #007bff; padding: 15px; margin: 10px 0; background: #e7f3ff;'>";

    $problemas = [];
    if (count($tabelas_faltando) > 0) $problemas[] = "Tabelas faltando: " . implode(', ', $tabelas_faltando);
    if ($produtos['total'] === 0) $problemas[] = "Nenhum produto cadastrado";

    if (empty($problemas)) {
        echo "<h3>✅ Sistema funcionando corretamente!</h3>";
        echo "<p>Todos os componentes estão OK. O módulo lojinha deve estar funcionando.</p>";
    } else {
        echo "<h3>⚠️ Problemas identificados:</h3>";
        echo "<ul>";
        foreach ($problemas as $problema) {
            echo "<li>$problema</li>";
        }
        echo "</ul>";
    }

    echo "</div>";

} catch (Exception $e) {
    echo "<h2>❌ Erro Crítico</h2>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<h3>📋 Próximos Passos:</h3>";
echo "<ol>";
echo "<li>Verifique se há erros no console do navegador (F12)</li>";
echo "<li>Teste cada funcionalidade individualmente</li>";
echo "<li>Se ainda houver erro, compartilhe o resultado completo do diagnóstico</li>";
echo "</ol>";

echo "<p><a href='index.php'>← Voltar ao Módulo</a></p>";
?>
