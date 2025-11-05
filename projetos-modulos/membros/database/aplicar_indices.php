<?php
/**
 * Script para aplicar índices de performance
 * Módulo de Membros - Sistema de Gestão Paroquial
 * 
 * ATENÇÃO: Execute este script apenas uma vez
 * Pode levar alguns minutos dependendo do tamanho da base de dados
 */

require_once __DIR__ . '/../config/database.php';

// Verificar se está sendo executado via linha de comando ou navegador
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Aplicar Índices</title>";
    echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;}";
    echo ".success{color:#4ec9b0;}.error{color:#f48771;}.info{color:#569cd6;}</style></head><body>";
    echo "<h2>Aplicando Índices de Performance</h2>";
}

try {
    $db = new MembrosDatabase();
    $conn = $db->getConnection();
    
    // Ler o arquivo SQL
    $sqlFile = __DIR__ . '/performance_indices.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Arquivo SQL não encontrado: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remover comentários e dividir em statements
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) { return !empty($stmt); }
    );
    
    $totalStatements = count($statements);
    $successCount = 0;
    $skipCount = 0;
    $errorCount = 0;
    $errors = [];
    
    echo $isCLI ? "Encontrados {$totalStatements} statements SQL\n\n" : "<p class='info'>Encontrados {$totalStatements} statements SQL</p>";
    
    foreach ($statements as $index => $statement) {
        $statementNumber = $index + 1;
        
        // Extrair nome do índice do statement
        preg_match('/CREATE INDEX\s+(?:IF NOT EXISTS\s+)?([a-zA-Z0-9_]+)/i', $statement, $matches);
        $indexName = $matches[1] ?? "Statement #{$statementNumber}";
        
        try {
            $conn->exec($statement);
            $successCount++;
            $message = "[{$statementNumber}/{$totalStatements}] ✓ Criado: {$indexName}";
            echo $isCLI ? "{$message}\n" : "<p class='success'>{$message}</p>";
            
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            
            // Se índice já existe, não é erro crítico
            if (strpos($errorMsg, 'Duplicate key name') !== false || 
                strpos($errorMsg, 'already exists') !== false) {
                $skipCount++;
                $message = "[{$statementNumber}/{$totalStatements}] ⊘ Já existe: {$indexName}";
                echo $isCLI ? "{$message}\n" : "<p class='info'>{$message}</p>";
            } else {
                $errorCount++;
                $errors[] = [
                    'index' => $indexName,
                    'error' => $errorMsg
                ];
                $message = "[{$statementNumber}/{$totalStatements}] ✗ Erro: {$indexName}";
                echo $isCLI ? "{$message}\n" : "<p class='error'>{$message}</p>";
            }
        }
        
        // Flush output para mostrar progresso
        if (!$isCLI) {
            ob_flush();
            flush();
        }
    }
    
    // Relatório final
    echo $isCLI ? "\n" : "<hr>";
    echo $isCLI ? "=== RELATÓRIO FINAL ===\n" : "<h3>Relatório Final</h3>";
    echo $isCLI ? "Total de statements: {$totalStatements}\n" : "<p><strong>Total de statements:</strong> {$totalStatements}</p>";
    echo $isCLI ? "Criados com sucesso: {$successCount}\n" : "<p class='success'><strong>Criados com sucesso:</strong> {$successCount}</p>";
    echo $isCLI ? "Já existiam: {$skipCount}\n" : "<p class='info'><strong>Já existiam:</strong> {$skipCount}</p>";
    echo $isCLI ? "Erros: {$errorCount}\n" : "<p class='error'><strong>Erros:</strong> {$errorCount}</p>";
    
    if ($errorCount > 0) {
        echo $isCLI ? "\nDETALHES DOS ERROS:\n" : "<h3>Detalhes dos Erros</h3><ul>";
        foreach ($errors as $error) {
            if ($isCLI) {
                echo "  - {$error['index']}: {$error['error']}\n";
            } else {
                echo "<li><strong>{$error['index']}</strong>: {$error['error']}</li>";
            }
        }
        if (!$isCLI) echo "</ul>";
    }
    
    // Estatísticas de índices
    echo $isCLI ? "\n=== ESTATÍSTICAS DE ÍNDICES ===\n" : "<hr><h3>Estatísticas de Índices</h3>";
    
    $statsQuery = "
        SELECT 
            TABLE_NAME,
            COUNT(*) as total_indices
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME LIKE 'membros_%'
        GROUP BY TABLE_NAME
        ORDER BY TABLE_NAME
    ";
    
    $stmt = $conn->query($statsQuery);
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($isCLI) {
        foreach ($stats as $stat) {
            echo sprintf("  %-40s: %d índices\n", $stat['TABLE_NAME'], $stat['total_indices']);
        }
    } else {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse;'>";
        echo "<tr><th>Tabela</th><th>Total de Índices</th></tr>";
        foreach ($stats as $stat) {
            echo "<tr><td>{$stat['TABLE_NAME']}</td><td>{$stat['total_indices']}</td></tr>";
        }
        echo "</table>";
    }
    
    echo $isCLI ? "\n✓ Script concluído com sucesso!\n" : "<p class='success'><strong>✓ Script concluído com sucesso!</strong></p>";
    
} catch (Exception $e) {
    $errorMsg = "ERRO FATAL: " . $e->getMessage();
    echo $isCLI ? "{$errorMsg}\n" : "<p class='error'><strong>{$errorMsg}</strong></p>";
    exit(1);
}

if (!$isCLI) {
    echo "</body></html>";
}
?>

