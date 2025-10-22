<?php
require_once __DIR__ . '/config/database.php';

try {
    // Verificar se os campos já existem
    $stmt = $pdo->query("DESCRIBE servicos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Adicionar campos que não existem
    if (!in_array('comprovante_pagamento', $columns)) {
        $pdo->exec("ALTER TABLE servicos ADD COLUMN comprovante_pagamento VARCHAR(255) DEFAULT NULL");
        echo "Campo comprovante_pagamento adicionado.\n";
    }
    
    if (!in_array('nota_fiscal', $columns)) {
        $pdo->exec("ALTER TABLE servicos ADD COLUMN nota_fiscal VARCHAR(255) DEFAULT NULL");
        echo "Campo nota_fiscal adicionado.\n";
    }
    
    if (!in_array('ordem_servico', $columns)) {
        $pdo->exec("ALTER TABLE servicos ADD COLUMN ordem_servico VARCHAR(255) DEFAULT NULL");
        echo "Campo ordem_servico adicionado.\n";
    }
    
    echo "\nOperação concluída com sucesso!";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
