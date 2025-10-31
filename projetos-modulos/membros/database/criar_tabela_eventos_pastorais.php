<?php
require_once __DIR__ . '/../config/database.php';

echo "=== CRIANDO TABELA membros_eventos_pastorais ===\n\n";

try {
    $db = new MembrosDatabase();
    $conn = $db->getConnection();
    
    // Criar tabela sem foreign keys primeiro
    $sql = "
    CREATE TABLE IF NOT EXISTS membros_eventos_pastorais (
        id VARCHAR(36) NOT NULL PRIMARY KEY,
        pastoral_id VARCHAR(36) NOT NULL,
        nome VARCHAR(255) NOT NULL,
        tipo VARCHAR(50) DEFAULT NULL COMMENT 'Tipo do evento em texto livre',
        data_evento DATE NOT NULL,
        horario TIME DEFAULT NULL,
        local VARCHAR(255) DEFAULT NULL,
        responsavel_id VARCHAR(36) DEFAULT NULL,
        descricao TEXT DEFAULT NULL,
        ativo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_evento_pastoral (pastoral_id),
        INDEX idx_evento_data (data_evento),
        INDEX idx_evento_ativo (ativo),
        INDEX idx_evento_responsavel (responsavel_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $conn->exec($sql);
    echo "✓ Tabela 'membros_eventos_pastorais' criada com sucesso!\n";
    
    // Tentar adicionar foreign keys se ainda não existirem
    try {
        $fk1 = "ALTER TABLE membros_eventos_pastorais 
                ADD CONSTRAINT fk_evento_pastoral 
                FOREIGN KEY (pastoral_id) REFERENCES membros_pastorais(id) ON DELETE CASCADE";
        $conn->exec($fk1);
        echo "✓ Foreign key para pastoral adicionada\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate foreign key') === false && strpos($e->getMessage(), 'already exists') === false) {
            echo "⚠ Foreign key pastoral: " . $e->getMessage() . "\n";
        }
    }
    
    try {
        $fk2 = "ALTER TABLE membros_eventos_pastorais 
                ADD CONSTRAINT fk_evento_responsavel 
                FOREIGN KEY (responsavel_id) REFERENCES membros_membros(id) ON DELETE SET NULL";
        $conn->exec($fk2);
        echo "✓ Foreign key para responsável adicionada\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate foreign key') === false && strpos($e->getMessage(), 'already exists') === false) {
            echo "⚠ Foreign key responsável: " . $e->getMessage() . "\n";
        }
    }
    
    // Verificar estrutura
    $stmt = $conn->query("DESCRIBE membros_eventos_pastorais");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nEstrutura da tabela:\n";
    foreach($cols as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "✓ Tabela já existe\n";
    } else {
        echo "✗ Erro: " . $e->getMessage() . "\n";
    }
}
?>
