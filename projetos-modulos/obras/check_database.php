<?php
require_once __DIR__ . '/config/database.php';

try {
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'servicos_arquivos'");
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "✓ Tabela servicos_arquivos existe\n\n";
        
        // Verificar estrutura da tabela
        $stmt = $pdo->query("DESCRIBE servicos_arquivos");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Estrutura da tabela:\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']}: {$column['Type']}\n";
        }
        
        // Verificar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM obras_servicos_arquivos");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\nTotal de registros: {$count['total']}\n";
        
        // Mostrar alguns registros recentes
        $stmt = $pdo->query("SELECT * FROM obras_servicos_arquivos ORDER BY data_upload DESC LIMIT 5");
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($registros) > 0) {
            echo "\nÚltimos registros:\n";
            foreach ($registros as $reg) {
                echo "ID: {$reg['id']}, Serviço: {$reg['servico_id']}, Tipo: {$reg['tipo']}, Arquivo: {$reg['nome_arquivo']}\n";
            }
        }
    } else {
        echo "✗ Tabela servicos_arquivos NÃO existe!\n";
        echo "Execute o script setup/create_tables.php para criar a tabela.\n";
    }
} catch (PDOException $e) {
    echo "Erro ao verificar banco de dados: " . $e->getMessage() . "\n";
}
?>
