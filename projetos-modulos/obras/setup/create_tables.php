<?php
require_once __DIR__ . '/../config/database.php';

function logSetup($message) {
    echo $message . "\n";
    
    $logFile = __DIR__ . '/../logs/setup.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    
    // Criar diretório de logs se não existir
    $logDir = dirname($logFile);
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

try {
    // Criar tabela servicos_arquivos
    $sql = "CREATE TABLE IF NOT EXISTS servicos_arquivos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        servico_id INT,
        tipo ENUM('comprovante_pagamento', 'nota_fiscal', 'ordem_servico'),
        nome_arquivo VARCHAR(255),
        caminho_arquivo VARCHAR(255),
        data_upload DATETIME,
        FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE
    )";

    $pdo->exec($sql);
    logSetup("✓ Tabela servicos_arquivos criada com sucesso");

    // Verificar se as colunas antigas existem
    $stmt = $pdo->query("SHOW COLUMNS FROM obras_servicos");
    $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $colunasParaMigrar = [
        'comprovante_pagamento' => 'comprovante_pagamento',
        'nota_fiscal' => 'nota_fiscal',
        'ordem_servico' => 'ordem_servico'
    ];
    
    $colunasExistentes = array_intersect($colunasParaMigrar, $colunas);
    
    if (!empty($colunasExistentes)) {
        logSetup("Iniciando migração de dados...");
        
        // Construir query dinâmica
        $campos = implode(', ', $colunasExistentes);
        $stmt = $pdo->query("SELECT id, $campos FROM obras_servicos WHERE " . 
            implode(' IS NOT NULL OR ', $colunasExistentes) . ' IS NOT NULL');
        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($servicos as $servico) {
            foreach ($colunasExistentes as $coluna) {
                if (!empty($servico[$coluna])) {
                    $stmt = $pdo->prepare("INSERT INTO obras_servicos_arquivos (servico_id, tipo, nome_arquivo, caminho_arquivo, data_upload) VALUES (?, ?, ?, ?, NOW())");
                    $nomeArquivo = basename($servico[$coluna]);
                    $stmt->execute([$servico['id'], $coluna, $nomeArquivo, $servico[$coluna]]);
                    logSetup("✓ Migrado {$coluna} do serviço {$servico['id']}");
                }
            }
        }

        // Remover colunas antigas após migração
        if (!empty($colunasExistentes)) {
            $sql = "ALTER TABLE servicos " . 
                   implode(", ", array_map(function($col) { 
                       return "DROP COLUMN $col";
                   }, $colunasExistentes));
            $pdo->exec($sql);
            logSetup("✓ Colunas antigas removidas com sucesso");
        }
    } else {
        logSetup("Nenhuma coluna antiga encontrada para migrar");
    }

    logSetup("\n✓ Setup concluído com sucesso!");

} catch (PDOException $e) {
    logSetup("✗ Erro durante o setup: " . $e->getMessage());
    exit(1);
}
?>
