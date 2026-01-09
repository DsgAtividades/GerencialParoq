<?php
/**
 * Arquivo de Diagnóstico - Dashboard
 * Use este arquivo para identificar problemas
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO DASHBOARD ===\n\n";

// 1. Verificar includes
echo "1. Verificando includes...\n";
try {
    require_once __DIR__ . '/../../config/database.php';
    echo "   ✓ database.php carregado\n";
} catch (Exception $e) {
    echo "   ✗ Erro ao carregar database.php: " . $e->getMessage() . "\n";
    exit;
}

try {
    require_once __DIR__ . '/../utils/Response.php';
    echo "   ✓ Response.php carregado\n";
} catch (Exception $e) {
    echo "   ✗ Erro ao carregar Response.php: " . $e->getMessage() . "\n";
    exit;
}

try {
    require_once __DIR__ . '/../utils/Cache.php';
    echo "   ✓ Cache.php carregado\n";
} catch (Exception $e) {
    echo "   ✗ Erro ao carregar Cache.php: " . $e->getMessage() . "\n";
    exit;
}

// 2. Verificar conexão com banco
echo "\n2. Verificando conexão com banco...\n";
try {
    $db = new MembrosDatabase();
    echo "   ✓ MembrosDatabase instanciado\n";
    
    // Testar query simples
    $test = $db->query("SELECT 1 as test")->fetch();
    if ($test && $test['test'] == 1) {
        echo "   ✓ Query de teste funcionou\n";
    } else {
        echo "   ✗ Query de teste falhou\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
    exit;
}

// 3. Verificar tabelas
echo "\n3. Verificando tabelas...\n";
$tabelas = ['membros_membros', 'membros_pastorais', 'membros_eventos'];
foreach ($tabelas as $tabela) {
    try {
        $count = $db->query("SELECT COUNT(*) as total FROM {$tabela}")->fetch()['total'];
        echo "   ✓ {$tabela}: {$count} registros\n";
    } catch (Exception $e) {
        echo "   ✗ {$tabela}: Erro - " . $e->getMessage() . "\n";
    }
}

// 4. Verificar cache
echo "\n4. Verificando cache...\n";
try {
    $cache = new Cache();
    echo "   ✓ Cache instanciado\n";
    echo "   Diretório: " . $cache->getStats()['cache_dir'] . "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n";
}

// 5. Testar queries do dashboard
echo "\n5. Testando queries do dashboard...\n";
try {
    $totalMembros = $db->query("SELECT COUNT(*) as total FROM membros_membros")->fetch()['total'];
    echo "   ✓ Total membros: {$totalMembros}\n";
} catch (Exception $e) {
    echo "   ✗ Erro ao contar membros: " . $e->getMessage() . "\n";
}

try {
    $membrosAtivos = $db->query("SELECT COUNT(*) as total FROM membros_membros WHERE status = 'ativo'")->fetch()['total'];
    echo "   ✓ Membros ativos: {$membrosAtivos}\n";
} catch (Exception $e) {
    echo "   ✗ Erro ao contar membros ativos: " . $e->getMessage() . "\n";
}

try {
    $pastoraisAtivas = $db->query("SELECT COUNT(*) as total FROM membros_pastorais WHERE ativo = 1")->fetch()['total'];
    echo "   ✓ Pastorais ativas: {$pastoraisAtivas}\n";
} catch (Exception $e) {
    echo "   ✗ Erro ao contar pastorais: " . $e->getMessage() . "\n";
}

try {
    $eventosHoje = $db->query("SELECT COUNT(*) as total FROM membros_eventos WHERE DATE(data_evento) = CURDATE()")->fetch()['total'];
    echo "   ✓ Eventos hoje: {$eventosHoje}\n";
} catch (Exception $e) {
    echo "   ✗ Erro ao contar eventos: " . $e->getMessage() . "\n";
}

// 6. Testar Response
echo "\n6. Testando Response...\n";
try {
    $testData = ['test' => 'ok'];
    ob_start();
    Response::success($testData);
    $output = ob_get_clean();
    
    $json = json_decode($output, true);
    if ($json && $json['success'] === true) {
        echo "   ✓ Response funciona corretamente\n";
    } else {
        echo "   ✗ Response retornou JSON inválido\n";
        echo "   Output: " . substr($output, 0, 200) . "\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erro ao testar Response: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO DIAGNÓSTICO ===\n";

