<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Verificar se as colunas já existem
    $result = $conn->query("SHOW COLUMNS FROM obras_obras LIKE 'comprovante_pagamento'")->fetch();
    if (!$result) {
        $conn->exec("ALTER TABLE obras ADD COLUMN comprovante_pagamento VARCHAR(255) DEFAULT NULL COMMENT 'Caminho do arquivo de comprovante de pagamento'");
        echo "Coluna comprovante_pagamento adicionada com sucesso!\n";
    }

    $result = $conn->query("SHOW COLUMNS FROM obras_obras LIKE 'nota_fiscal'")->fetch();
    if (!$result) {
        $conn->exec("ALTER TABLE obras ADD COLUMN nota_fiscal VARCHAR(255) DEFAULT NULL COMMENT 'Caminho do arquivo da nota fiscal'");
        echo "Coluna nota_fiscal adicionada com sucesso!\n";
    }

    // Criar índices
    try {
        $conn->exec("CREATE INDEX idx_comprovante_pagamento ON obras(comprovante_pagamento)");
        echo "Índice para comprovante_pagamento criado com sucesso!\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') === false) {
            throw $e;
        }
    }

    try {
        $conn->exec("CREATE INDEX idx_nota_fiscal ON obras(nota_fiscal)");
        echo "Índice para nota_fiscal criado com sucesso!\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') === false) {
            throw $e;
        }
    }

    echo "Atualização concluída com sucesso!";

} catch (PDOException $e) {
    die("Erro ao atualizar a tabela: " . $e->getMessage());
}
?>
