<?php
/**
 * Script de Diagnóstico Completo - Módulo Membros
 * Use este arquivo para identificar problemas no módulo
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - Módulo Membros</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; font-weight: bold; }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        h2 {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <h1>🔍 Diagnóstico Completo - Módulo Membros</h1>
    <p><strong>Data:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

<?php

// =====================================================
// 1. VERIFICAR CONFIGURAÇÃO
// =====================================================
echo '<div class="section">';
echo '<h2>1. Verificando Configuração</h2>';

$configPath = __DIR__ . '/config/config.php';
if (file_exists($configPath)) {
    echo '<p class="success">✓ Arquivo config.php encontrado</p>';
    require_once $configPath;
    
    echo '<p><strong>Ambiente:</strong> ' . (defined('MEMBROS_ENVIRONMENT') ? MEMBROS_ENVIRONMENT : 'NÃO DEFINIDO') . '</p>';
    echo '<p><strong>DB Host:</strong> ' . (defined('DB_HOST') ? DB_HOST : 'NÃO DEFINIDO') . '</p>';
    echo '<p><strong>DB Name:</strong> ' . (defined('DB_NAME') ? DB_NAME : 'NÃO DEFINIDO') . '</p>';
    echo '<p><strong>DB User:</strong> ' . (defined('DB_USER') ? DB_USER : 'NÃO DEFINIDO') . '</p>';
    echo '<p><strong>DB Pass:</strong> ' . (defined('DB_PASS') ? (DB_PASS ? '***DEFINIDO***' : 'VAZIO') : 'NÃO DEFINIDO') . '</p>';
} else {
    echo '<p class="error">✗ Arquivo config.php NÃO encontrado em: ' . $configPath . '</p>';
    echo '</div></body></html>';
    exit;
}

echo '</div>';

// =====================================================
// 2. VERIFICAR CONEXÃO COM BANCO
// =====================================================
echo '<div class="section">';
echo '<h2>2. Verificando Conexão com Banco de Dados</h2>';

try {
    require_once __DIR__ . '/config/database_connection.php';
    echo '<p class="success">✓ database_connection.php carregado</p>';
    
    require_once __DIR__ . '/config/database.php';
    echo '<p class="success">✓ database.php carregado</p>';
    
    $db = new MembrosDatabase();
    echo '<p class="success">✓ MembrosDatabase instanciado</p>';
    
    // Testar conexão
    $test = $db->fetchOne("SELECT 1 as test, DATABASE() as db_name, USER() as db_user");
    if ($test && $test['test'] == 1) {
        echo '<p class="success">✓ Conexão com banco funcionando</p>';
        echo '<p><strong>Banco atual:</strong> ' . htmlspecialchars($test['db_name']) . '</p>';
        echo '<p><strong>Usuário atual:</strong> ' . htmlspecialchars($test['db_user']) . '</p>';
    } else {
        echo '<p class="error">✗ Conexão com banco falhou</p>';
    }
} catch (Exception $e) {
    echo '<p class="error">✗ Erro ao conectar: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div></body></html>';
    exit;
}

echo '</div>';

// =====================================================
// 3. VERIFICAR TABELAS
// =====================================================
echo '<div class="section">';
echo '<h2>3. Verificando Tabelas do Banco de Dados</h2>';

$tabelasEsperadas = [
    'membros_membros',
    'membros_funcoes',
    'membros_pastorais',
    'membros_membros_pastorais',
    'membros_eventos',
    'membros_eventos_pastorais',
    'membros_escalas_eventos',
    'membros_escalas_funcoes',
    'membros_escalas_funcao_membros',
    'membros_escalas_logs',
    'membros_consentimentos_lgpd',
    'membros_auditoria_logs',
    'membros_anexos'
];

// Listar todas as tabelas do banco
try {
    $tabelasExistentes = $db->fetchAll("SHOW TABLES LIKE 'membros_%'");
    $tabelasEncontradas = [];
    foreach ($tabelasExistentes as $row) {
        $tabelasEncontradas[] = array_values($row)[0];
    }
    
    echo '<table>';
    echo '<tr><th>Tabela</th><th>Status</th><th>Registros</th><th>Observações</th></tr>';
    
    foreach ($tabelasEsperadas as $tabela) {
        $existe = in_array($tabela, $tabelasEncontradas);
        
        if ($existe) {
            try {
                $count = $db->fetchOne("SELECT COUNT(*) as total FROM `{$tabela}`");
                $total = $count['total'];
                echo '<tr>';
                echo '<td><strong>' . htmlspecialchars($tabela) . '</strong></td>';
                echo '<td class="success">✓ Existe</td>';
                echo '<td>' . $total . '</td>';
                echo '<td>-</td>';
                echo '</tr>';
            } catch (Exception $e) {
                echo '<tr>';
                echo '<td><strong>' . htmlspecialchars($tabela) . '</strong></td>';
                echo '<td class="warning">⚠ Existe mas com erro</td>';
                echo '<td>-</td>';
                echo '<td class="error">' . htmlspecialchars($e->getMessage()) . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr>';
            echo '<td><strong>' . htmlspecialchars($tabela) . '</strong></td>';
            echo '<td class="error">✗ NÃO EXISTE</td>';
            echo '<td>-</td>';
            echo '<td class="error">Tabela não encontrada no banco</td>';
            echo '</tr>';
        }
    }
    
    echo '</table>';
    
    // Verificar tabelas extras (não esperadas)
    $tabelasExtras = array_diff($tabelasEncontradas, $tabelasEsperadas);
    if (!empty($tabelasExtras)) {
        echo '<p class="info">ℹ Tabelas extras encontradas (não esperadas):</p>';
        echo '<ul>';
        foreach ($tabelasExtras as $extra) {
            echo '<li>' . htmlspecialchars($extra) . '</li>';
        }
        echo '</ul>';
    }
    
} catch (Exception $e) {
    echo '<p class="error">✗ Erro ao verificar tabelas: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '</div>';

// =====================================================
// 4. VERIFICAR ESTRUTURA DA TABELA PRINCIPAL
// =====================================================
echo '<div class="section">';
echo '<h2>4. Verificando Estrutura da Tabela Principal (membros_membros)</h2>';

try {
    $estrutura = $db->fetchAll("DESCRIBE membros_membros");
    
    if (!empty($estrutura)) {
        echo '<p class="success">✓ Estrutura da tabela ok</p>';
        echo '<table>';
        echo '<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>';
        
        foreach ($estrutura as $campo) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($campo['Field']) . '</td>';
            echo '<td>' . htmlspecialchars($campo['Type']) . '</td>';
            echo '<td>' . htmlspecialchars($campo['Null']) . '</td>';
            echo '<td>' . htmlspecialchars($campo['Key']) . '</td>';
            echo '<td>' . htmlspecialchars($campo['Default'] ?? 'NULL') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p class="error">✗ Não foi possível obter estrutura da tabela</p>';
    }
} catch (Exception $e) {
    echo '<p class="error">✗ Erro ao verificar estrutura: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '</div>';

// =====================================================
// 5. TESTAR QUERIES DO DASHBOARD
// =====================================================
echo '<div class="section">';
echo '<h2>5. Testando Queries do Dashboard</h2>';

$queries = [
    'Total de Membros' => "SELECT COUNT(*) as total FROM membros_membros WHERE status != 'bloqueado'",
    'Membros Ativos' => "SELECT COUNT(*) as total FROM membros_membros WHERE status = 'ativo'",
    'Pastorais Ativas' => "SELECT COUNT(*) as total FROM membros_pastorais WHERE ativo = 1",
    'Eventos Hoje' => "SELECT COUNT(*) as total FROM membros_eventos WHERE DATE(data_evento) = CURDATE()",
    'Membros sem Pastoral' => "SELECT COUNT(*) as total FROM membros_membros m LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id WHERE mp.membro_id IS NULL AND m.status = 'ativo'"
];

foreach ($queries as $nome => $query) {
    try {
        $result = $db->fetchOne($query);
        $total = $result['total'] ?? 0;
        echo '<p class="success">✓ ' . htmlspecialchars($nome) . ': ' . $total . '</p>';
    } catch (Exception $e) {
        echo '<p class="error">✗ ' . htmlspecialchars($nome) . ': ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($query) . '</pre>';
    }
}

echo '</div>';

// =====================================================
// 6. VERIFICAR ARQUIVOS DA API
// =====================================================
echo '<div class="section">';
echo '<h2>6. Verificando Arquivos da API</h2>';

$arquivosApi = [
    'api/routes.php',
    'api/utils/Response.php',
    'api/utils/Validation.php',
    'api/utils/Cache.php',
    'api/models/Membro.php',
    'api/endpoints/dashboard_geral.php',
    'api/endpoints/membros_listar.php'
];

foreach ($arquivosApi as $arquivo) {
    $caminho = __DIR__ . '/' . $arquivo;
    if (file_exists($caminho)) {
        echo '<p class="success">✓ ' . htmlspecialchars($arquivo) . '</p>';
    } else {
        echo '<p class="error">✗ ' . htmlspecialchars($arquivo) . ' NÃO encontrado</p>';
    }
}

echo '</div>';

// =====================================================
// 7. TESTAR ENDPOINT DA API
// =====================================================
echo '<div class="section">';
echo '<h2>7. Testando Endpoint da API</h2>';

try {
    // Simular chamada ao endpoint
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/projetos-modulos/membros/api/dashboard/geral';
    
    ob_start();
    require_once __DIR__ . '/api/endpoints/dashboard_geral.php';
    $output = ob_get_clean();
    
    $json = json_decode($output, true);
    
    if ($json && isset($json['success'])) {
        if ($json['success']) {
            echo '<p class="success">✓ Endpoint dashboard/geral funcionando</p>';
            echo '<pre>' . htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
        } else {
            echo '<p class="error">✗ Endpoint retornou erro: ' . htmlspecialchars($json['error'] ?? 'Erro desconhecido') . '</p>';
        }
    } else {
        echo '<p class="error">✗ Resposta inválida do endpoint</p>';
        echo '<pre>' . htmlspecialchars(substr($output, 0, 500)) . '</pre>';
    }
} catch (Exception $e) {
    echo '<p class="error">✗ Erro ao testar endpoint: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '</div>';

// =====================================================
// 8. RESUMO E RECOMENDAÇÕES
// =====================================================
echo '<div class="section">';
echo '<h2>8. Resumo e Recomendações</h2>';

$tabelasFaltando = [];
foreach ($tabelasEsperadas as $tabela) {
    if (!in_array($tabela, $tabelasEncontradas)) {
        $tabelasFaltando[] = $tabela;
    }
}

if (empty($tabelasFaltando)) {
    echo '<p class="success">✓ Todas as tabelas necessárias estão presentes</p>';
} else {
    echo '<p class="error">✗ Tabelas faltando:</p>';
    echo '<ul>';
    foreach ($tabelasFaltando as $tabela) {
        echo '<li>' . htmlspecialchars($tabela) . '</li>';
    }
    echo '</ul>';
    echo '<p class="warning">⚠ Execute o script criar_tabelas_membros.sql para criar as tabelas faltantes</p>';
}

echo '</div>';

?>

</body>
</html>
