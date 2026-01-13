<?php
/**
 * Script para adicionar coluna eventos_url na tabela membros_eventos
 */

require_once __DIR__ . '/../../../config/database_connection.php';

try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
    
    // Verificar se a coluna já existe
    $stmt = $pdo->query("SHOW COLUMNS FROM membros_eventos LIKE 'eventos_url'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "A coluna 'eventos_url' já existe na tabela membros_eventos.\n";
    } else {
        // Adicionar a coluna
        $sql = "ALTER TABLE membros_eventos 
                ADD COLUMN eventos_url VARCHAR(500) DEFAULT NULL 
                COMMENT 'URL relacionada ao evento (link externo, página, etc.)' 
                AFTER descricao";
        
        $pdo->exec($sql);
        echo "Coluna 'eventos_url' adicionada com sucesso à tabela membros_eventos!\n";
    }
    
    // Mostrar estrutura da tabela
    echo "\nEstrutura atualizada da tabela membros_eventos:\n";
    $stmt = $pdo->query("DESCRIBE membros_eventos");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
} catch (PDOException $e) {
    echo "Erro ao adicionar coluna: " . $e->getMessage() . "\n";
    exit(1);
}
?>


