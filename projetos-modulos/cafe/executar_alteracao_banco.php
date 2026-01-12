<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Desabilitar foreign key checks temporariamente
    $db->exec("SET FOREIGN_KEY_CHECKS=0");
    
    echo "<h3>Verificando dados...</h3>";
    
    // 1. Verificar registros inconsistentes
    $stmt = $db->query("SELECT p.id_pessoa, p.nome, p.cpf, p.qrcode, c.codigo 
FROM cafe_pessoas p
                        LEFT JOIN cafe_cartoes c ON p.qrcode = c.codigo
                        WHERE c.codigo IS NULL");
    $inconsistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($inconsistencias)) {
        echo "<p>Encontradas " . count($inconsistencias) . " pessoas com qrcode inválido:</p>";
        foreach ($inconsistencias as $i) {
            echo "Pessoa: " . $i['nome'] . " (CPF: " . $i['cpf'] . ") - QRCode: " . $i['qrcode'] . "<br>";
        }
        throw new Exception("Existem pessoas com qrcode que não existe na tabela cartoes. Corrija os dados antes de continuar.");
    } else {
        echo "<p>Todos os qrcodes são válidos.</p>";
    }
    
    // 2. Verificar se a foreign key existe
    $stmt = $db->prepare("SELECT COUNT(*) as count
                         FROM information_schema.TABLE_CONSTRAINTS 
                         WHERE CONSTRAINT_SCHEMA = 'festa'
                         AND TABLE_NAME = 'pessoas' 
                         AND CONSTRAINT_NAME = 'fk_pessoas_cartao'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        $sql = "ALTER TABLE cafe_pessoas DROP FOREIGN KEY fk_pessoas_cartao";
        $db->exec($sql);
        echo "<p>Foreign key removida com sucesso</p>";
    } else {
        echo "<p>Foreign key não existe, continuando...</p>";
    }
    
    // 3. Verificar se a coluna id_pessoa já existe
    $stmt = $db->prepare("SELECT COUNT(*) as count 
                         FROM information_schema.COLUMNS 
                         WHERE TABLE_SCHEMA = 'festa'
                         AND TABLE_NAME = 'cartoes' 
                         AND COLUMN_NAME = 'id_pessoa'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        $sql = "ALTER TABLE cafe_cartoes ADD COLUMN id_pessoa INT NULL";
        $db->exec($sql);
        echo "<p>Coluna id_pessoa adicionada na tabela cartoes</p>";
    } else {
        echo "<p>Coluna id_pessoa já existe, continuando...</p>";
    }
    
    // 4. Atualizar id_pessoa nos cartões existentes
    $sql = "UPDATE cafe_cartoes c
            INNER JOIN cafe_pessoas p ON c.codigo = p.qrcode
            SET c.id_pessoa = p.id_pessoa 
            WHERE c.id_pessoa IS NULL";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $atualizados = $stmt->rowCount();
    echo "<p>Atualizados " . $atualizados . " cartões com seus respectivos id_pessoa</p>";
    
    // 5. Remover foreign key em cartoes se existir
    $stmt = $db->prepare("SELECT COUNT(*) as count
                         FROM information_schema.TABLE_CONSTRAINTS 
                         WHERE CONSTRAINT_SCHEMA = 'festa'
                         AND TABLE_NAME = 'cartoes' 
                         AND CONSTRAINT_NAME = 'fk_cartoes_pessoa'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        $sql = "ALTER TABLE cafe_cartoes DROP FOREIGN KEY fk_cartoes_pessoa";
        $db->exec($sql);
        echo "<p>Foreign key antiga removida da tabela cartoes</p>";
    }
    
    // 6. Adicionar foreign key em cartoes
    $sql = "ALTER TABLE cafe_cartoes
            ADD CONSTRAINT fk_cartoes_pessoa FOREIGN KEY (id_pessoa) REFERENCES cafe_pessoas(id_pessoa) ON DELETE RESTRICT";
    $db->exec($sql);
    echo "<p>Foreign key adicionada na tabela cartoes</p>";
    
    // 7. Adicionar foreign key em pessoas
    $sql = "ALTER TABLE cafe_pessoas
            ADD CONSTRAINT fk_pessoas_cartao FOREIGN KEY (qrcode) REFERENCES cafe_cartoes(codigo) ON DELETE RESTRICT";
    $db->exec($sql);
    echo "<p>Foreign key recriada na tabela pessoas</p>";
    
    // Reabilitar foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS=1");
    
    echo "<h3 style='color: green;'>Todas as alterações foram realizadas com sucesso!</h3>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Erro ao executar alterações: " . $e->getMessage() . "</h3>";
    
    // Tentar reabilitar foreign key checks em caso de erro
    try {
        if ($db) {
            $db->exec("SET FOREIGN_KEY_CHECKS=1");
        }
    } catch (Exception $e2) {
        echo "<p style='color: red;'>Erro ao reabilitar foreign key checks: " . $e2->getMessage() . "</p>";
    }
}
?>
