<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Verificar se a coluna já existe
    $stmt = $pdo->query("SHOW COLUMNS FROM obras_servicos LIKE 'nota_fiscal'");
    $exists = $stmt->fetch();

    if (!$exists) {
        // Adicionar a coluna se não existir
        $pdo->exec("ALTER TABLE servicos ADD COLUMN nota_fiscal VARCHAR(255) DEFAULT NULL AFTER comprovante_pagamento");
        echo "Coluna nota_fiscal adicionada com sucesso!";
    } else {
        echo "A coluna nota_fiscal já existe.";
    }
} catch (PDOException $e) {
    die("Erro ao adicionar coluna: " . $e->getMessage());
}
?>
