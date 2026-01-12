<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Backup dos dados existentes
    $stmt = $db->query("SELECT * FROM cafe_cartoes");
    $cartoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Drop foreign key da tabela pessoas
    $db->exec("ALTER TABLE cafe_pessoas DROP FOREIGN KEY fk_pessoas_cartao");
    
    // Drop index único do qrcode
    $db->exec("ALTER TABLE cafe_pessoas DROP INDEX qrcode");
    
    // Recriar tabela cartoes com collation correta
    $db->exec("DROP TABLE IF EXISTS cafe_cartoes");
    $db->exec("
        CREATE TABLE cafe_cartoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(255) NOT NULL,
            data_geracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            usado BOOLEAN DEFAULT FALSE,
            CONSTRAINT uk_cartoes_codigo UNIQUE (codigo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Recriar índices
    $db->exec("CREATE INDEX idx_cartoes_codigo ON cafe_cartoes(codigo)");
    $db->exec("CREATE INDEX idx_cartoes_usado ON cafe_cartoes(usado)");
    
    // Restaurar dados
    if (!empty($cartoes)) {
        $stmt = $db->prepare("
            INSERT INTO cafe_cartoes (id, codigo, data_geracao, usado) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($cartoes as $cartao) {
            $stmt->execute([
                $cartao['id'],
                $cartao['codigo'],
                $cartao['data_geracao'],
                $cartao['usado']
            ]);
        }
    }
    
    // Recriar foreign key e índice único
    $db->exec("ALTER TABLE cafe_pessoas ADD CONSTRAINT uk_pessoas_qrcode UNIQUE (qrcode)");
    $db->exec("
ALTER TABLE cafe_pessoas
        ADD CONSTRAINT fk_pessoas_cartao
        FOREIGN KEY (qrcode)
        REFERENCES cafe_cartoes(codigo)
        ON DELETE RESTRICT
    ");
    
    echo "Collation corrigida com sucesso!\n";
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    // Se houver erro, tentar restaurar a estrutura original
    try {
        if (!isset($cartoes)) {
            echo "Erro ocorreu antes do backup, não é possível restaurar.\n";
            exit(1);
        }
        
        $db->exec("DROP TABLE IF EXISTS cafe_cartoes");
        $db->exec("
            CREATE TABLE cafe_cartoes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                codigo VARCHAR(255) NOT NULL,
                data_geracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                usado BOOLEAN DEFAULT FALSE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        ");
        
        $stmt = $db->prepare("
            INSERT INTO cafe_cartoes (id, codigo, data_geracao, usado) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($cartoes as $cartao) {
            $stmt->execute([
                $cartao['id'],
                $cartao['codigo'],
                $cartao['data_geracao'],
                $cartao['usado']
            ]);
        }
        
        echo "Estrutura original restaurada.\n";
        
    } catch (PDOException $e2) {
        echo "Erro na restauração: " . $e2->getMessage() . "\n";
        exit(1);
    }
}
