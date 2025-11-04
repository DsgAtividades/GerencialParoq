<?php
/**
 * Script para criar tabela membros_anexos se não existir
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = new MembrosDatabase();
    $conn = $db->getConnection();
    
    // Verificar se a tabela existe
    $stmt = $conn->query("SHOW TABLES LIKE 'membros_anexos'");
    $existe = $stmt->rowCount() > 0;
    
    if ($existe) {
        echo "Tabela membros_anexos já existe.\n";
        
        // Mostrar estrutura atual
        echo "\nEstrutura atual:\n";
        $stmt = $conn->query("DESCRIBE membros_anexos");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            printf("  %-30s | %-20s | Null: %-3s | Key: %-3s\n",
                $col['Field'],
                $col['Type'],
                $col['Null'],
                $col['Key']
            );
        }
    } else {
        echo "Criando tabela membros_anexos...\n";
        
        $sql = "
        CREATE TABLE IF NOT EXISTS `membros_anexos` (
          `id` varchar(36) NOT NULL,
          `membro_id` varchar(36) DEFAULT NULL,
          `tipo` enum('foto','documento','outro') DEFAULT 'outro',
          `nome_arquivo` varchar(255) NOT NULL,
          `caminho_arquivo` varchar(500) NOT NULL,
          `tamanho` int(11) DEFAULT NULL COMMENT 'Tamanho em bytes',
          `mime_type` varchar(100) DEFAULT NULL COMMENT 'image/jpeg, image/png, etc',
          `created_at` timestamp NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `membro_id` (`membro_id`),
          KEY `tipo` (`tipo`),
          CONSTRAINT `fk_anexos_membro` FOREIGN KEY (`membro_id`) REFERENCES `membros_membros` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        
        $conn->exec($sql);
        echo "Tabela membros_anexos criada com sucesso!\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}

?>

