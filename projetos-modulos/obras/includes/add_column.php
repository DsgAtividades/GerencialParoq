<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Verificar se a coluna já existe
    $stmt = $pdo->query("SHOW COLUMNS FROM obras_servicos LIKE 'comprovante_pagamento'");
    $exists = $stmt->fetch();

    if (!$exists) {
        // Adicionar a coluna se não existir
        $pdo->exec("ALTER TABLE servicos ADD COLUMN comprovante_pagamento VARCHAR(255) DEFAULT NULL AFTER valor_antecipado");
        echo "Coluna comprovante_pagamento adicionada com sucesso!";
    } else {
        echo "A coluna comprovante_pagamento já existe.";
    }
} catch (PDOException $e) {
    die("Erro ao adicionar coluna: " . $e->getMessage());
}
?>
