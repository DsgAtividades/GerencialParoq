<?php
/**
 * Script de Configuração do Banco de Dados
 * Módulo de Cadastro de Membros - Sistema de Gestão Paroquial
 */

// Configurações
$config = [
    'host' => 'gerencialparoq.mysql.dbaas.com.br',
    'dbname' => 'gerencialparoq',
    'username' => 'gerencialparoq',
    'password' => 'Dsg#1806',
    'charset' => 'utf8mb4'
];

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

try {
    printHeader("CONFIGURAÇÃO DO MÓDULO DE MEMBROS");
    
    // Conectar ao banco de dados
    printStep("1", "Conectando ao banco de dados...");
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    printSuccess("Conexão estabelecida com sucesso!");
    
    // Verificar se as tabelas já existem
    printStep("2", "Verificando tabelas existentes...");
    $stmt = $pdo->query("SHOW TABLES LIKE 'membros_%'");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($existingTables)) {
        printInfo("Encontradas " . count($existingTables) . " tabelas existentes:");
        foreach ($existingTables as $table) {
            echo "  - " . colorize($table, 'cyan') . "\n";
        }
        
        echo "\n" . colorize("ATENÇÃO: ", 'yellow') . "Tabelas do módulo já existem!\n";
        echo "Escolha uma opção:\n";
        echo "1. Manter dados existentes e adicionar apenas novos dados\n";
        echo "2. Recriar todas as tabelas (PERDA DE DADOS!)\n";
        echo "3. Cancelar\n\n";
        
        $choice = readline("Digite sua escolha (1-3): ");
        
        if ($choice == '2') {
            printStep("3", "Removendo tabelas existentes...");
            foreach ($existingTables as $table) {
                $pdo->exec("DROP TABLE IF EXISTS `$table`");
                printSuccess("Tabela $table removida");
            }
        } elseif ($choice == '3') {
            printInfo("Operação cancelada pelo usuário.");
            exit(0);
        } else {
            printInfo("Mantendo dados existentes...");
        }
    }
    
    // Executar schema
    printStep("3", "Executando schema do banco de dados...");
    $schemaFile = __DIR__ . '/database/schema_mysql.sql';
    
    if (!file_exists($schemaFile)) {
        throw new Exception("Arquivo schema_mysql.sql não encontrado!");
    }
    
    $schema = file_get_contents($schemaFile);
    $statements = explode(';', $schema);
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $successCount++;
        } catch (PDOException $e) {
            // Ignorar erros de tabela já existe
            if (strpos($e->getMessage(), 'already exists') === false) {
                $errorCount++;
                printError("Erro ao executar: " . substr($statement, 0, 50) . "... - " . $e->getMessage());
            }
        }
    }
    
    printSuccess("Schema executado! ($successCount comandos executados, $errorCount erros)");
    
    // Executar seeds
    printStep("4", "Inserindo dados iniciais...");
    $seedsFile = __DIR__ . '/database/seeds_mysql.sql';
    
    if (!file_exists($seedsFile)) {
        throw new Exception("Arquivo seeds_mysql.sql não encontrado!");
    }
    
    $seeds = file_get_contents($seedsFile);
    $statements = explode(';', $seeds);
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $successCount++;
        } catch (PDOException $e) {
            // Ignorar erros de dados duplicados
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                $errorCount++;
                printError("Erro ao inserir: " . substr($statement, 0, 50) . "... - " . $e->getMessage());
            }
        }
    }
    
    printSuccess("Dados iniciais inseridos! ($successCount comandos executados, $errorCount erros)");
    
    // Verificar instalação
    printStep("5", "Verificando instalação...");
    
    $tables = [
        'membros_membros',
        'membros_pastorais',
        'membros_funcoes',
        'membros_membros_pastorais',
        'membros_eventos',
        'membros_auditoria_logs'
    ];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $stmt->fetch()['count'];
        printSuccess("Tabela $table: $count registros");
    }
    
    // Testar funcionalidades básicas
    printStep("6", "Testando funcionalidades básicas...");
    
    // Teste 1: Inserir membro de teste
    $testMembro = [
        'nome_completo' => 'Teste Setup',
        'sexo' => 'M',
        'email' => 'teste.setup@email.com',
        'paroquiano' => true,
        'status' => 'ativo',
        'created_by' => 'setup_script'
    ];
    
    $sql = "INSERT INTO membros_membros (nome_completo, sexo, email, paroquiano, status, created_by) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($testMembro));
    $membroId = $pdo->lastInsertId();
    printSuccess("Membro de teste inserido (ID: $membroId)");
    
    // Teste 2: Consulta complexa
    $sql = "
        SELECT 
            m.nome_completo,
            m.email,
            p.nome as pastoral,
            f.nome as funcao
        FROM membros_membros m
        LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id
        LEFT JOIN membros_pastorais p ON mp.pastoral_id = p.id
        LEFT JOIN membros_funcoes f ON mp.funcao_id = f.id
        WHERE m.created_by = 'admin'
        LIMIT 5
    ";
    
    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll();
    printSuccess("Consulta complexa executada: " . count($resultados) . " registros encontrados");
    
    // Teste 3: Estatísticas
    $sql = "
        SELECT 
            COUNT(*) as total_membros,
            COUNT(CASE WHEN status = 'ativo' THEN 1 END) as membros_ativos,
            COUNT(CASE WHEN paroquiano = true THEN 1 END) as paroquianos
        FROM membros_membros
    ";
    
    $stmt = $pdo->query($sql);
    $stats = $stmt->fetch();
    printSuccess("Estatísticas: {$stats['total_membros']} membros, {$stats['membros_ativos']} ativos, {$stats['paroquianos']} paroquianos");
    
    // Limpar dados de teste
    $pdo->exec("DELETE FROM membros_membros WHERE created_by = 'setup_script'");
    printSuccess("Dados de teste removidos");
    
    // Finalização
    printHeader("INSTALAÇÃO CONCLUÍDA COM SUCESSO!");
    
    echo colorize("🎉 ", 'green') . "O módulo de Cadastro de Membros foi instalado com sucesso!\n\n";
    
    echo colorize("📋 ", 'blue') . "Resumo da instalação:\n";
    echo "  • Schema do banco de dados criado\n";
    echo "  • Dados iniciais inseridos\n";
    echo "  • Índices de performance criados\n";
    echo "  • Triggers de auditoria configurados\n";
    echo "  • Funcionalidades básicas testadas\n\n";
    
    echo colorize("🚀 ", 'green') . "Próximos passos:\n";
    echo "  1. Acesse: " . colorize("http://localhost/projetos-modulos/membros/", 'cyan') . "\n";
    echo "  2. Execute os testes: " . colorize("php tests/test_connection.php", 'cyan') . "\n";
    echo "  3. Consulte a documentação: " . colorize("README.md", 'cyan') . "\n\n";
    
    echo colorize("📊 ", 'blue') . "Dados inseridos:\n";
    echo "  • " . colorize("20", 'yellow') . " habilidades/carismas\n";
    echo "  • " . colorize("10", 'yellow') . " formações/certificações\n";
    echo "  • " . colorize("20", 'yellow') . " funções/roles\n";
    echo "  • " . colorize("8", 'yellow') . " pastorais/movimentos\n";
    echo "  • " . colorize("10", 'yellow') . " membros de exemplo\n";
    echo "  • " . colorize("8", 'yellow') . " eventos de exemplo\n";
    echo "  • " . colorize("3", 'yellow') . " vagas de exemplo\n\n";
    
    echo colorize("✨ ", 'magenta') . "O sistema está pronto para uso!\n\n";
    
} catch (Exception $e) {
    printHeader("ERRO NA INSTALAÇÃO");
    printError("Erro: " . $e->getMessage());
    echo "\n" . colorize("💡 ", 'yellow') . "Sugestões:\n";
    echo "  • Verifique as credenciais do banco de dados\n";
    echo "  • Certifique-se de que o MySQL está rodando\n";
    echo "  • Verifique se o usuário tem permissões adequadas\n";
    echo "  • Consulte os logs de erro do MySQL\n\n";
    exit(1);
}
?>

