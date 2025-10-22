<?php
require_once 'config/database.php';

function executarQuery($conn, $sql, $descricao) {
    try {
        $conn->exec($sql);
        echo "✅ Sucesso: $descricao<br>";
        return true;
    } catch (PDOException $e) {
        echo "❌ Erro em $descricao: " . $e->getMessage() . "<br>";
        return false;
    }
}

function colunaExiste($conn, $tabela, $coluna) {
    $sql = "SELECT COUNT(*) as existe
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = 'festa'
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$tabela, $coluna]);
    return (bool)$stmt->fetch()['existe'];
}

$conn = getConnection();

// Desabilitar foreign key checks
$conn->exec('SET FOREIGN_KEY_CHECKS=0');

try {
    // 1. Adicionar coluna valor_total em vendas se não existir
    if (!colunaExiste($conn, 'vendas', 'valor_total')) {
        $conn->beginTransaction();
        if (executarQuery($conn, 
            "ALTER TABLE vendas ADD COLUMN valor_total DECIMAL(10,2) DEFAULT 0",
            "Adicionando coluna valor_total em vendas"
        )) {
            $conn->commit();
        } else {
            $conn->rollBack();
        }
    } else {
        echo "✅ Coluna valor_total já existe em vendas<br>";
    }

    // 2. Adicionar coluna valor_total em itens_venda se não existir
    if (!colunaExiste($conn, 'itens_venda', 'valor_total')) {
        $conn->beginTransaction();
        if (executarQuery($conn, 
            "ALTER TABLE itens_venda ADD COLUMN valor_total DECIMAL(10,2)",
            "Adicionando coluna valor_total em itens_venda"
        )) {
            $conn->commit();
        } else {
            $conn->rollBack();
        }
    } else {
        echo "✅ Coluna valor_total já existe em itens_venda<br>";
    }

    // 3. Remover triggers antigos
    $conn->beginTransaction();
    $success = true;
    $success &= executarQuery($conn, "DROP TRIGGER IF EXISTS after_item_venda_insert", "Removendo trigger insert antigo");
    $success &= executarQuery($conn, "DROP TRIGGER IF EXISTS after_item_venda_update", "Removendo trigger update antigo");
    if ($success) {
        $conn->commit();
    } else {
        $conn->rollBack();
    }
    
    // 4. Configurar coluna calculada
    $conn->beginTransaction();
    if (executarQuery($conn, 
        "ALTER TABLE itens_venda MODIFY COLUMN valor_total DECIMAL(10,2) 
         GENERATED ALWAYS AS (quantidade * valor_unitario) STORED",
        "Configurando coluna calculada valor_total"
    )) {
        $conn->commit();
    } else {
        $conn->rollBack();
    }
    
    // 5. Criar novos triggers
    $triggers = [
        "CREATE TRIGGER after_item_venda_insert AFTER INSERT ON itens_venda
         FOR EACH ROW
         BEGIN
             UPDATE vendas 
             SET valor_total = (
                 SELECT COALESCE(SUM(valor_total), 0)
                 FROM itens_venda 
                 WHERE id_venda = NEW.id_venda
             )
             WHERE id_venda = NEW.id_venda;
         END" => "Criando trigger para INSERT",

        "CREATE TRIGGER after_item_venda_update AFTER UPDATE ON itens_venda
         FOR EACH ROW
         BEGIN
             UPDATE vendas 
             SET valor_total = (
                 SELECT COALESCE(SUM(valor_total), 0)
                 FROM itens_venda 
                 WHERE id_venda = NEW.id_venda
             )
             WHERE id_venda = NEW.id_venda;
         END" => "Criando trigger para UPDATE"
    ];

    // Executar triggers
    $conn->beginTransaction();
    $success = true;
    foreach ($triggers as $sql => $descricao) {
        if (!executarQuery($conn, $sql, $descricao)) {
            $success = false;
            break;
        }
    }
    if ($success) {
        $conn->commit();
        echo "✅ Triggers criados com sucesso!<br>";
    } else {
        $conn->rollBack();
        echo "❌ Erro ao criar triggers<br>";
    }

    echo "<br>✅ Processo de correção concluído!<br>";
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "<br>❌ Erro durante as correções: " . $e->getMessage() . "<br>";
} finally {
    // Garantir que o foreign key check seja reativado
    $conn->exec('SET FOREIGN_KEY_CHECKS=1');
}

// Recalcular todos os valores totais das vendas
try {
    echo "<br>Recalculando valores totais das vendas...<br>";
    
    $conn->beginTransaction();
    $sql = "UPDATE vendas v 
            SET valor_total = (
                SELECT COALESCE(SUM(valor_total), 0)
                FROM itens_venda 
                WHERE id_venda = v.id_venda
            )";
    
    if (executarQuery($conn, $sql, "Atualizando valores totais")) {
        $conn->commit();
        echo "✅ Valores totais das vendas atualizados com sucesso!<br>";
    } else {
        $conn->rollBack();
        echo "❌ Erro ao atualizar valores totais<br>";
    }
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "❌ Erro ao recalcular valores: " . $e->getMessage() . "<br>";
}
?>
