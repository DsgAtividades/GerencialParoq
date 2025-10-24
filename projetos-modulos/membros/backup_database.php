<?php
/**
 * Script de Backup e Restore do Banco de Dados
 * Módulo de Cadastro de Membros - Sistema de Gestão Paroquial
 */

// Incluir configuração do banco
require_once 'config/database.php';

// Cores para output
$colors = [
    'red' => "\033[31m",
    'green' => "\033[32m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
    'magenta' => "\033[35m",
    'cyan' => "\033[36m",
    'white' => "\033[37m",
    'reset' => "\033[0m"
];

function colorize($text, $color) {
    global $colors;
    return $colors[$color] . $text . $colors['reset'];
}

function printHeader($title) {
    echo "\n" . colorize("=" . str_repeat("=", 60) . "=", 'cyan') . "\n";
    echo colorize("  " . strtoupper($title), 'cyan') . "\n";
    echo colorize("=" . str_repeat("=", 60) . "=", 'cyan') . "\n\n";
}

function printStep($step, $description) {
    echo colorize("[$step] ", 'yellow') . $description . "\n";
}

function printSuccess($message) {
    echo colorize("✓ ", 'green') . $message . "\n";
}

function printError($message) {
    echo colorize("✗ ", 'red') . $message . "\n";
}

function printInfo($message) {
    echo colorize("ℹ ", 'blue') . $message . "\n";
}

// Configurações
$backupDir = __DIR__ . '/backups';
$timestamp = date('Y-m-d_H-i-s');

// Criar diretório de backup se não existir
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Função para fazer backup
function createBackup($backupDir, $timestamp) {
    try {
        printHeader("CRIANDO BACKUP DO MÓDULO DE MEMBROS");
        
        // Conectar ao banco
        printStep("1", "Conectando ao banco de dados...");
        $db = new MembrosDatabase();
        
        if (!$db->testConnection()) {
            throw new Exception("Falha na conexão com o banco de dados");
        }
        printSuccess("Conexão estabelecida com sucesso!");
        
        // Listar tabelas do módulo
        printStep("2", "Identificando tabelas do módulo...");
        $tables = $db->fetchAll("SHOW TABLES LIKE 'membros_%'");
        $tableNames = array_column($tables, 'Tables_in_gerencialparoq (membros_%)');
        
        if (empty($tableNames)) {
            throw new Exception("Nenhuma tabela do módulo encontrada!");
        }
        
        printSuccess("Encontradas " . count($tableNames) . " tabelas do módulo");
        foreach ($tableNames as $table) {
            echo "  - " . colorize($table, 'cyan') . "\n";
        }
        
        // Criar arquivo de backup
        $backupFile = $backupDir . "/membros_backup_$timestamp.sql";
        printStep("3", "Criando arquivo de backup: " . basename($backupFile));
        
        $backupContent = "-- Backup do Módulo de Membros\n";
        $backupContent .= "-- Data: " . date('d/m/Y H:i:s') . "\n";
        $backupContent .= "-- Sistema: GerencialParoq\n\n";
        
        $backupContent .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        
        // Backup de estrutura e dados
        foreach ($tableNames as $table) {
            printStep("4", "Fazendo backup da tabela: $table");
            
            // Estrutura da tabela
            $createTable = $db->fetchOne("SHOW CREATE TABLE `$table`");
            $backupContent .= "-- Estrutura da tabela $table\n";
            $backupContent .= "DROP TABLE IF EXISTS `$table`;\n";
            $backupContent .= $createTable['Create Table'] . ";\n\n";
            
            // Dados da tabela
            $rows = $db->fetchAll("SELECT * FROM `$table`");
            if (!empty($rows)) {
                $backupContent .= "-- Dados da tabela $table\n";
                
                // Obter nomes das colunas
                $columns = array_keys($rows[0]);
                $columnNames = '`' . implode('`, `', $columns) . '`';
                
                // Inserir dados em lotes
                $batchSize = 100;
                $totalRows = count($rows);
                $batches = ceil($totalRows / $batchSize);
                
                for ($i = 0; $i < $batches; $i++) {
                    $start = $i * $batchSize;
                    $end = min($start + $batchSize, $totalRows);
                    $batch = array_slice($rows, $start, $end - $start);
                    
                    $backupContent .= "INSERT INTO `$table` ($columnNames) VALUES\n";
                    
                    $values = [];
                    foreach ($batch as $row) {
                        $rowValues = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $rowValues[] = 'NULL';
                            } else {
                                $rowValues[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $values[] = '(' . implode(', ', $rowValues) . ')';
                    }
                    
                    $backupContent .= implode(",\n", $values) . ";\n\n";
                }
                
                printSuccess("$table: " . count($rows) . " registros");
            } else {
                printInfo("$table: Tabela vazia");
            }
        }
        
        $backupContent .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";
        $backupContent .= "-- Backup concluído em " . date('d/m/Y H:i:s') . "\n";
        
        // Salvar arquivo
        if (file_put_contents($backupFile, $backupContent) === false) {
            throw new Exception("Erro ao salvar arquivo de backup");
        }
        
        $fileSize = filesize($backupFile);
        $fileSizeFormatted = formatBytes($fileSize);
        
        printSuccess("Backup criado com sucesso!");
        printInfo("Arquivo: " . basename($backupFile));
        printInfo("Tamanho: $fileSizeFormatted");
        printInfo("Localização: $backupFile");
        
        // Criar arquivo de metadados
        $metadataFile = $backupDir . "/membros_backup_$timestamp.json";
        $metadata = [
            'timestamp' => $timestamp,
            'date' => date('d/m/Y H:i:s'),
            'tables' => $tableNames,
            'table_count' => count($tableNames),
            'file_size' => $fileSize,
            'file_size_formatted' => $fileSizeFormatted,
            'version' => '1.0',
            'system' => 'GerencialParoq - Módulo de Membros'
        ];
        
        file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));
        printSuccess("Metadados salvos: " . basename($metadataFile));
        
        return $backupFile;
        
    } catch (Exception $e) {
        printError("Erro ao criar backup: " . $e->getMessage());
        return false;
    }
}

// Função para restaurar backup
function restoreBackup($backupFile) {
    try {
        printHeader("RESTAURANDO BACKUP DO MÓDULO DE MEMBROS");
        
        if (!file_exists($backupFile)) {
            throw new Exception("Arquivo de backup não encontrado: $backupFile");
        }
        
        // Conectar ao banco
        printStep("1", "Conectando ao banco de dados...");
        $db = new MembrosDatabase();
        
        if (!$db->testConnection()) {
            throw new Exception("Falha na conexão com o banco de dados");
        }
        printSuccess("Conexão estabelecida com sucesso!");
        
        // Ler arquivo de backup
        printStep("2", "Lendo arquivo de backup...");
        $backupContent = file_get_contents($backupFile);
        
        if ($backupContent === false) {
            throw new Exception("Erro ao ler arquivo de backup");
        }
        
        $fileSize = filesize($backupFile);
        $fileSizeFormatted = formatBytes($fileSize);
        printSuccess("Arquivo lido: " . basename($backupFile) . " ($fileSizeFormatted)");
        
        // Executar backup
        printStep("3", "Executando restauração...");
        $statements = explode(';', $backupContent);
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement) || strpos($statement, '--') === 0) {
                continue;
            }
            
            try {
                $db->execute($statement);
                $successCount++;
            } catch (Exception $e) {
                $errorCount++;
                printError("Erro ao executar: " . substr($statement, 0, 50) . "... - " . $e->getMessage());
            }
        }
        
        printSuccess("Restauração concluída! ($successCount comandos executados, $errorCount erros)");
        
        // Verificar restauração
        printStep("4", "Verificando restauração...");
        $tables = $db->fetchAll("SHOW TABLES LIKE 'membros_%'");
        $tableNames = array_column($tables, 'Tables_in_gerencialparoq (membros_%)');
        
        printSuccess("Tabelas restauradas: " . count($tableNames));
        foreach ($tableNames as $table) {
            $count = $db->fetchOne("SELECT COUNT(*) as count FROM `$table`")['count'];
            echo "  - " . colorize("$table", 'cyan') . ": $count registros\n";
        }
        
        return true;
        
    } catch (Exception $e) {
        printError("Erro ao restaurar backup: " . $e->getMessage());
        return false;
    }
}

// Função para listar backups
function listBackups($backupDir) {
    printHeader("LISTANDO BACKUPS DISPONÍVEIS");
    
    $backups = glob($backupDir . "/membros_backup_*.sql");
    
    if (empty($backups)) {
        printInfo("Nenhum backup encontrado.");
        return;
    }
    
    // Ordenar por data de modificação (mais recente primeiro)
    usort($backups, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    echo colorize("📁 ", 'blue') . "Backups encontrados: " . count($backups) . "\n\n";
    
    foreach ($backups as $i => $backup) {
        $filename = basename($backup);
        $fileSize = formatBytes(filesize($backup));
        $fileDate = date('d/m/Y H:i:s', filemtime($backup));
        
        echo colorize("[" . ($i + 1) . "] ", 'yellow') . "$filename\n";
        echo "    Data: $fileDate\n";
        echo "    Tamanho: $fileSize\n";
        
        // Verificar se existe arquivo de metadados
        $metadataFile = str_replace('.sql', '.json', $backup);
        if (file_exists($metadataFile)) {
            $metadata = json_decode(file_get_contents($metadataFile), true);
            if ($metadata) {
                echo "    Tabelas: " . $metadata['table_count'] . "\n";
                echo "    Sistema: " . $metadata['system'] . "\n";
            }
        }
        echo "\n";
    }
}

// Função para formatar bytes
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

// Menu principal
function showMenu() {
    echo "\n" . colorize("🔧 ", 'cyan') . "MENU DE BACKUP E RESTORE\n";
    echo "1. Criar backup\n";
    echo "2. Restaurar backup\n";
    echo "3. Listar backups\n";
    echo "4. Sair\n\n";
}

// Execução principal
if (php_sapi_name() === 'cli') {
    // Modo CLI
    $action = $argv[1] ?? 'menu';
    
    switch ($action) {
        case 'backup':
            createBackup($backupDir, $timestamp);
            break;
            
        case 'restore':
            if (isset($argv[2])) {
                $backupFile = $backupDir . '/' . $argv[2];
                restoreBackup($backupFile);
            } else {
                printError("Especifique o arquivo de backup para restaurar.");
                echo "Uso: php backup_database.php restore arquivo.sql\n";
            }
            break;
            
        case 'list':
            listBackups($backupDir);
            break;
            
        case 'menu':
        default:
            showMenu();
            $choice = readline("Escolha uma opção (1-4): ");
            
            switch ($choice) {
                case '1':
                    createBackup($backupDir, $timestamp);
                    break;
                case '2':
                    listBackups($backupDir);
                    $backupFile = readline("Digite o nome do arquivo para restaurar: ");
                    if ($backupFile) {
                        restoreBackup($backupDir . '/' . $backupFile);
                    }
                    break;
                case '3':
                    listBackups($backupDir);
                    break;
                case '4':
                    echo "Saindo...\n";
                    exit(0);
                default:
                    printError("Opção inválida!");
            }
            break;
    }
} else {
    // Modo web
    printHeader("BACKUP E RESTORE - MÓDULO DE MEMBROS");
    echo "Este script deve ser executado via linha de comando.\n";
    echo "Uso: php backup_database.php [backup|restore|list]\n";
}
?>

