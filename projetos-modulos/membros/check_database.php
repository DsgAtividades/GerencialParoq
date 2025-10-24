<?php
/**
 * Script de Verificação do Banco de Dados
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

function printWarning($message) {
    echo colorize("⚠ ", 'yellow') . $message . "\n";
}

function printInfo($message) {
    echo colorize("ℹ ", 'blue') . $message . "\n";
}

try {
    printHeader("VERIFICAÇÃO DO BANCO DE DADOS");
    
    // Conectar ao banco
    printStep("1", "Conectando ao banco de dados...");
    $db = new MembrosDatabase();
    
    if (!$db->testConnection()) {
        throw new Exception("Falha na conexão com o banco de dados");
    }
    printSuccess("Conexão estabelecida com sucesso!");
    
    // Verificar tabelas obrigatórias
    printStep("2", "Verificando tabelas obrigatórias...");
    $requiredTables = [
        'membros_membros' => 'Tabela principal de membros',
        'membros_pastorais' => 'Tabela de pastorais e movimentos',
        'membros_funcoes' => 'Tabela de funções e roles',
        'membros_membros_pastorais' => 'Tabela de vínculos membro-pastoral',
        'membros_eventos' => 'Tabela de eventos',
        'membros_itens_escala' => 'Tabela de itens de escala',
        'membros_alocacoes' => 'Tabela de alocações',
        'membros_checkins' => 'Tabela de check-ins',
        'membros_vagas' => 'Tabela de vagas',
        'membros_candidaturas' => 'Tabela de candidaturas',
        'membros_comunicados' => 'Tabela de comunicados',
        'membros_anexos' => 'Tabela de anexos',
        'membros_auditoria_logs' => 'Tabela de logs de auditoria',
        'membros_habilidades_tags' => 'Tabela de habilidades',
        'membros_formacoes' => 'Tabela de formações',
        'membros_membros_formacoes' => 'Tabela de formações dos membros',
        'membros_requisitos_funcao' => 'Tabela de requisitos por função',
        'membros_enderecos_membro' => 'Tabela de endereços',
        'membros_contatos_membro' => 'Tabela de contatos',
        'membros_documentos_membro' => 'Tabela de documentos',
        'membros_consentimentos_lgpd' => 'Tabela de consentimentos LGPD'
    ];
    
    $missingTables = [];
    $existingTables = [];
    
    foreach ($requiredTables as $table => $description) {
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM `$table`");
            $existingTables[] = $table;
            printSuccess("$table: OK ({$result['count']} registros)");
        } catch (Exception $e) {
            $missingTables[] = $table;
            printError("$table: FALTANDO - $description");
        }
    }
    
    if (!empty($missingTables)) {
        printWarning("Encontradas " . count($missingTables) . " tabelas faltando!");
        echo "Execute o script setup_database.php para criar as tabelas faltantes.\n";
    } else {
        printSuccess("Todas as tabelas obrigatórias estão presentes!");
    }
    
    // Verificar índices
    printStep("3", "Verificando índices de performance...");
    $indexes = [
        'idx_membros_nome' => 'membros_membros',
        'idx_membros_cpf' => 'membros_membros',
        'idx_membros_email' => 'membros_membros',
        'idx_membros_status' => 'membros_membros',
        'idx_membros_pastorais_membro' => 'membros_membros_pastorais',
        'idx_eventos_data' => 'membros_eventos',
        'idx_checkins_evento' => 'membros_checkins'
    ];
    
    $missingIndexes = [];
    foreach ($indexes as $index => $table) {
        try {
            $result = $db->fetchOne("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$index]);
            if ($result) {
                printSuccess("Índice $index: OK");
            } else {
                $missingIndexes[] = $index;
                printWarning("Índice $index: FALTANDO");
            }
        } catch (Exception $e) {
            $missingIndexes[] = $index;
            printWarning("Índice $index: ERRO - " . $e->getMessage());
        }
    }
    
    // Verificar dados iniciais
    printStep("4", "Verificando dados iniciais...");
    
    $initialData = [
        'Habilidades' => 'membros_habilidades_tags',
        'Formações' => 'membros_formacoes',
        'Funções' => 'membros_funcoes',
        'Pastorais' => 'membros_pastorais',
        'Membros' => 'membros_membros'
    ];
    
    foreach ($initialData as $name => $table) {
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM `$table`");
            $count = $result['count'];
            
            if ($count > 0) {
                printSuccess("$name: $count registros");
            } else {
                printWarning("$name: Nenhum registro encontrado");
            }
        } catch (Exception $e) {
            printError("$name: Erro ao verificar - " . $e->getMessage());
        }
    }
    
    // Testar funcionalidades
    printStep("5", "Testando funcionalidades básicas...");
    
    // Teste 1: Inserir membro de teste
    try {
        $testData = [
            'nome_completo' => 'Teste Verificação',
            'sexo' => 'M',
            'email' => 'teste.verificacao@email.com',
            'paroquiano' => true,
            'status' => 'ativo',
            'created_by' => 'check_script'
        ];
        
        $sql = "INSERT INTO membros_membros (nome_completo, sexo, email, paroquiano, status, created_by) VALUES (?, ?, ?, ?, ?, ?)";
        $db->execute($sql, array_values($testData));
        $membroId = $db->lastInsertId();
        printSuccess("Inserção de membro: OK (ID: $membroId)");
        
        // Teste 2: Atualizar membro
        $sql = "UPDATE membros_membros SET apelido = ? WHERE id = ?";
        $db->execute($sql, ['Teste', $membroId]);
        printSuccess("Atualização de membro: OK");
        
        // Teste 3: Consulta complexa
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
            WHERE m.id = ?
        ";
        
        $result = $db->fetchOne($sql, [$membroId]);
        if ($result) {
            printSuccess("Consulta complexa: OK");
        } else {
            printWarning("Consulta complexa: Sem resultados");
        }
        
        // Teste 4: Excluir membro de teste
        $sql = "DELETE FROM membros_membros WHERE id = ?";
        $db->execute($sql, [$membroId]);
        printSuccess("Exclusão de membro: OK");
        
    } catch (Exception $e) {
        printError("Teste de funcionalidades: FALHOU - " . $e->getMessage());
    }
    
    // Verificar integridade referencial
    printStep("6", "Verificando integridade referencial...");
    
    $integrityChecks = [
        'Vínculos órfãos' => "
            SELECT COUNT(*) as count 
            FROM membros_membros_pastorais mp 
            LEFT JOIN membros_membros m ON mp.membro_id = m.id 
            WHERE m.id IS NULL
        ",
        'Alocações órfãs' => "
            SELECT COUNT(*) as count 
            FROM membros_alocacoes a 
            LEFT JOIN membros_membros m ON a.membro_id = m.id 
            WHERE m.id IS NULL
        ",
        'Check-ins órfãos' => "
            SELECT COUNT(*) as count 
            FROM membros_checkins c 
            LEFT JOIN membros_membros m ON c.membro_id = m.id 
            WHERE m.id IS NULL
        "
    ];
    
    foreach ($integrityChecks as $check => $sql) {
        try {
            $result = $db->fetchOne($sql);
            $count = $result['count'];
            
            if ($count == 0) {
                printSuccess("$check: OK");
            } else {
                printWarning("$check: $count registros órfãos encontrados");
            }
        } catch (Exception $e) {
            printError("$check: Erro - " . $e->getMessage());
        }
    }
    
    // Relatório final
    printHeader("RELATÓRIO DE VERIFICAÇÃO");
    
    $totalTables = count($requiredTables);
    $existingCount = count($existingTables);
    $missingCount = count($missingTables);
    
    echo colorize("📊 ", 'blue') . "Resumo da verificação:\n";
    echo "  • Tabelas encontradas: " . colorize("$existingCount/$totalTables", $existingCount == $totalTables ? 'green' : 'yellow') . "\n";
    echo "  • Tabelas faltando: " . colorize("$missingCount", $missingCount == 0 ? 'green' : 'red') . "\n";
    echo "  • Índices faltando: " . colorize(count($missingIndexes), count($missingIndexes) == 0 ? 'green' : 'yellow') . "\n";
    
    if ($missingCount == 0) {
        echo "\n" . colorize("🎉 ", 'green') . "O banco de dados está configurado corretamente!\n";
        echo "O módulo de Membros está pronto para uso.\n";
    } else {
        echo "\n" . colorize("⚠️ ", 'yellow') . "O banco de dados precisa de configuração.\n";
        echo "Execute o script setup_database.php para completar a instalação.\n";
    }
    
    echo "\n" . colorize("💡 ", 'blue') . "Dicas de manutenção:\n";
    echo "  • Execute este script regularmente para verificar a integridade\n";
    echo "  • Monitore os logs de auditoria para rastrear alterações\n";
    echo "  • Faça backup regular do banco de dados\n";
    echo "  • Verifique os índices de performance periodicamente\n\n";
    
} catch (Exception $e) {
    printHeader("ERRO NA VERIFICAÇÃO");
    printError("Erro: " . $e->getMessage());
    echo "\n" . colorize("💡 ", 'yellow') . "Sugestões:\n";
    echo "  • Verifique as credenciais do banco de dados\n";
    echo "  • Certifique-se de que o MySQL está rodando\n";
    echo "  • Execute o script setup_database.php primeiro\n";
    echo "  • Consulte os logs de erro do MySQL\n\n";
    exit(1);
}
?>

