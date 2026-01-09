<?php
// Configuração de exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>";
echo "Iniciando correção do banco de dados...\n\n";

try {
    // Conexão direta com o banco
    $db = new PDO(
        "mysql:host=localhost;dbname=festa;charset=utf8mb4",
        "root",
        "",
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    
    echo "✓ Conexão estabelecida com sucesso\n";

    // Primeiro, remove qualquer backup antigo se existir
    $db->exec("DROP TABLE IF EXISTS cafe_cartoes_backup");
    $db->exec("DROP TABLE IF EXISTS cafe_pessoas_backup");
    $db->exec("DROP TABLE IF EXISTS cafe_historico_saldo_backup");
    $db->exec("DROP TABLE IF EXISTS cafe_saldos_cartao_backup");
    echo "✓ Limpeza de backups antigos realizada\n";

    // Verificar todas as foreign keys existentes
    $stmt = $db->query("
        SELECT 
            TABLE_NAME,
            CONSTRAINT_NAME
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = 'festa' 
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ");
    $foreignKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mapeamento de foreign keys por tabela
    $fkMap = array();
    foreach ($foreignKeys as $fk) {
        if (!isset($fkMap[$fk['TABLE_NAME']])) {
            $fkMap[$fk['TABLE_NAME']] = array();
        }
        $fkMap[$fk['TABLE_NAME']][] = $fk['CONSTRAINT_NAME'];
    }

    // Verificar se o índice qrcode existe
    $stmt = $db->query("
        SHOW INDEX FROM cafe_pessoas 
        WHERE Key_name = 'qrcode'
    ");
    $hasQrcodeIndex = $stmt->rowCount() > 0;

    // Verificar se o índice uk_pessoas_qrcode existe
    $stmt = $db->query("
        SHOW INDEX FROM cafe_pessoas 
        WHERE Key_name = 'uk_pessoas_qrcode'
    ");
    $hasUniqueIndex = $stmt->rowCount() > 0;

    // Array com todos os comandos SQL
    $comandos = array(
        // Backup das tabelas
        "CREATE TABLE cafe_cartoes_backup LIKE cafe_cartoes" => "Criando backup da tabela cartoes",
        "INSERT INTO cafe_cartoes_backup (id, codigo, data_geracao, usado) SELECT id, codigo, data_geracao, usado FROM cafe_cartoes" => "Copiando dados dos cartoes para backup",
        "CREATE TABLE cafe_pessoas_backup LIKE cafe_pessoas" => "Criando backup da tabela pessoas",
        "INSERT INTO cafe_pessoas_backup (id_pessoa, nome, cpf, telefone, qrcode) SELECT id_pessoa, nome, cpf, telefone, qrcode FROM cafe_pessoas" => "Copiando dados das pessoas para backup",
        "CREATE TABLE cafe_historico_saldo_backup LIKE cafe_historico_saldo" => "Criando backup da tabela historico_saldo",
        "INSERT INTO cafe_historico_saldo_backup (id_historico, id_pessoa, tipo_operacao, valor, saldo_anterior, saldo_novo, motivo, data_operacao) 
         SELECT id_historico, id_pessoa, tipo_operacao, valor, saldo_anterior, saldo_novo, motivo, data_operacao 
         FROM cafe_historico_saldo" => "Copiando dados do historico para backup",
        "CREATE TABLE cafe_saldos_cartao_backup LIKE cafe_saldos_cartao" => "Criando backup da tabela saldos_cartao",
        "INSERT INTO cafe_saldos_cartao_backup (id_saldo, id_pessoa, saldo) SELECT id_saldo, id_pessoa, saldo FROM cafe_saldos_cartao" => "Copiando dados dos saldos para backup",

        // Desabilitando foreign keys
        "SET FOREIGN_KEY_CHECKS = 0" => "Desabilitando verificação de foreign keys"
    );

    // Adiciona comandos para remover foreign keys existentes
    foreach ($fkMap as $table => $constraints) {
        foreach ($constraints as $constraint) {
            $comandos["ALTER TABLE {$table} DROP FOREIGN KEY {$constraint}"] = "Removendo foreign key {$constraint} da tabela {$table}";
        }
    }

    if ($hasQrcodeIndex) {
        $comandos["ALTER TABLE cafe_pessoas DROP INDEX qrcode"] = "Removendo índice qrcode";
    }

    if ($hasUniqueIndex) {
        $comandos["ALTER TABLE cafe_pessoas DROP INDEX uk_pessoas_qrcode"] = "Removendo índice uk_pessoas_qrcode existente";
    }

    // Adiciona o resto dos comandos
    $comandos = array_merge($comandos, array(
        // Recriando tabelas com collation correta
        "DROP TABLE IF EXISTS cafe_cartoes" => "Removendo tabela cartoes antiga",
        "CREATE TABLE cafe_cartoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            data_geracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            usado BOOLEAN DEFAULT FALSE,
            CONSTRAINT uk_cartoes_codigo UNIQUE (codigo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci" => "Criando nova tabela cartoes com collation correta",

        "DROP TABLE IF EXISTS cafe_pessoas" => "Removendo tabela pessoas antiga",
        "CREATE TABLE cafe_pessoas (
            id_pessoa INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            cpf VARCHAR(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            telefone VARCHAR(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            qrcode VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            CONSTRAINT uk_pessoas_cpf UNIQUE (cpf)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci" => "Criando nova tabela pessoas com collation correta",

        "DROP TABLE IF EXISTS cafe_historico_saldo" => "Removendo tabela historico antiga",
        "CREATE TABLE cafe_historico_saldo (
            id_historico INT AUTO_INCREMENT PRIMARY KEY,
            id_pessoa INT NOT NULL,
            tipo_operacao ENUM('credito', 'debito') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            valor DECIMAL(10,2) NOT NULL,
            saldo_anterior DECIMAL(10,2) NOT NULL,
            saldo_novo DECIMAL(10,2) NOT NULL,
            motivo VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            data_operacao DATETIME NOT NULL,
            INDEX idx_historico_pessoa (id_pessoa),
            CONSTRAINT fk_historico_pessoa FOREIGN KEY (id_pessoa) REFERENCES cafe_pessoas(id_pessoa) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci" => "Criando nova tabela historico com collation correta",

        "DROP TABLE IF EXISTS cafe_saldos_cartao" => "Removendo tabela saldos antiga",
        "CREATE TABLE cafe_saldos_cartao (
            id_saldo INT AUTO_INCREMENT PRIMARY KEY,
            id_pessoa INT NOT NULL,
            saldo DECIMAL(10,2) NOT NULL DEFAULT 0.00 CHECK (saldo >= 0),
            CONSTRAINT fk_saldo_pessoa FOREIGN KEY (id_pessoa) REFERENCES cafe_pessoas(id_pessoa) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci" => "Criando nova tabela saldos com collation correta",
        
        // Restaurando dados com colunas explícitas
        "INSERT INTO cafe_cartoes (id, codigo, data_geracao, usado) 
         SELECT id, codigo, data_geracao, usado 
         FROM cafe_cartoes_backup" => "Restaurando dados dos cartoes",

        "INSERT INTO cafe_pessoas (id_pessoa, nome, cpf, telefone, qrcode) 
         SELECT id_pessoa, nome, cpf, telefone, qrcode 
         FROM cafe_pessoas_backup" => "Restaurando dados das pessoas",

        "INSERT INTO cafe_historico_saldo (id_historico, id_pessoa, tipo_operacao, valor, saldo_anterior, saldo_novo, motivo, data_operacao) 
         SELECT id_historico, id_pessoa, tipo_operacao, valor, saldo_anterior, saldo_novo, motivo, data_operacao 
         FROM cafe_historico_saldo_backup" => "Restaurando dados do historico",

        "INSERT INTO cafe_saldos_cartao (id_saldo, id_pessoa, saldo) 
         SELECT id_saldo, id_pessoa, saldo 
         FROM cafe_saldos_cartao_backup" => "Restaurando dados dos saldos",
        
        // Recriando foreign keys
        "ALTER TABLE cafe_pessoas
        ADD CONSTRAINT fk_pessoas_cartao
        FOREIGN KEY (qrcode)
        REFERENCES cafe_cartoes(codigo)
         ON DELETE RESTRICT" => "Recriando foreign key dos cartoes",
         
        // Reabilitando foreign keys
        "SET FOREIGN_KEY_CHECKS = 1" => "Reabilitando verificação de foreign keys",
         
        // Limpeza
        "DROP TABLE cartoes_backup" => "Removendo backup dos cartoes",
        "DROP TABLE pessoas_backup" => "Removendo backup das pessoas",
        "DROP TABLE historico_saldo_backup" => "Removendo backup do historico",
        "DROP TABLE saldos_cartao_backup" => "Removendo backup dos saldos"
    ));

    // Executando cada comando
    foreach ($comandos as $sql => $descricao) {
        echo "\nExecutando: " . $descricao . "... ";
        $db->exec($sql);
        echo "✓ OK\n";
    }

    echo "\n✓ Correção concluída com sucesso!\n";
    echo "\nVocê já pode fechar esta página e voltar a usar o sistema normalmente.";

} catch (PDOException $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    
    // Se der erro, tenta restaurar do backup
    if (isset($db)) {
        echo "\nTentando restaurar do backup...\n";
        try {
            $db->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Restaura cartoes se existir backup
            $stmt = $db->query("SHOW TABLES LIKE 'cartoes_backup'");
            if ($stmt->rowCount() > 0) {
                $db->exec("DROP TABLE IF EXISTS cafe_cartoes");
                $db->exec("CREATE TABLE cafe_cartoes LIKE cartoes_backup");
                $db->exec("INSERT INTO cafe_cartoes (id, codigo, data_geracao, usado) SELECT id, codigo, data_geracao, usado FROM cafe_cartoes_backup");
                $db->exec("DROP TABLE cartoes_backup");
                echo "✓ Tabela cartoes restaurada\n";
            }
            
            // Restaura pessoas se existir backup
            $stmt = $db->query("SHOW TABLES LIKE 'pessoas_backup'");
            if ($stmt->rowCount() > 0) {
                $db->exec("DROP TABLE IF EXISTS cafe_pessoas");
                $db->exec("CREATE TABLE cafe_pessoas LIKE cafe_pessoas_backup");
                $db->exec("INSERT INTO cafe_pessoas (id_pessoa, nome, cpf, telefone, qrcode) SELECT id_pessoa, nome, cpf, telefone, qrcode FROM cafe_pessoas_backup");
                $db->exec("DROP TABLE pessoas_backup");
                echo "✓ Tabela pessoas restaurada\n";
            }

            // Restaura historico se existir backup
            $stmt = $db->query("SHOW TABLES LIKE 'historico_saldo_backup'");
            if ($stmt->rowCount() > 0) {
                $db->exec("DROP TABLE IF EXISTS cafe_historico_saldo");
                $db->exec("CREATE TABLE cafe_historico_saldo LIKE historico_saldo_backup");
                $db->exec("INSERT INTO cafe_historico_saldo (id_historico, id_pessoa, tipo_operacao, valor, saldo_anterior, saldo_novo, motivo, data_operacao) SELECT id_historico, id_pessoa, tipo_operacao, valor, saldo_anterior, saldo_novo, motivo, data_operacao FROM cafe_historico_saldo_backup");
                $db->exec("DROP TABLE historico_saldo_backup");
                echo "✓ Tabela historico restaurada\n";
            }

            // Restaura saldos se existir backup
            $stmt = $db->query("SHOW TABLES LIKE 'saldos_cartao_backup'");
            if ($stmt->rowCount() > 0) {
                $db->exec("DROP TABLE IF EXISTS cafe_saldos_cartao");
                $db->exec("CREATE TABLE cafe_saldos_cartao LIKE saldos_cartao_backup");
                $db->exec("INSERT INTO cafe_saldos_cartao (id_saldo, id_pessoa, saldo) SELECT id_saldo, id_pessoa, saldo FROM cafe_saldos_cartao_backup");
                $db->exec("DROP TABLE saldos_cartao_backup");
                echo "✓ Tabela saldos restaurada\n";
            }
            
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
            echo "✓ Sistema restaurado ao estado anterior com sucesso!\n";
        } catch (PDOException $e2) {
            echo "❌ Erro na restauração: " . $e2->getMessage() . "\n";
        }
    }
}
echo "</pre>";
?>
