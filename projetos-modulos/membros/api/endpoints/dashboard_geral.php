<?php
/**
 * Endpoint: Dashboard Geral
 * Retorna estatísticas gerais do sistema
 * 
 * Cache: 5 minutos (300 segundos)
 */

// Evitar qualquer output antes do JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Limpar qualquer output anterior
if (ob_get_level()) {
    ob_clean();
}

// Iniciar buffer de output para capturar erros
ob_start();

try {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../utils/Response.php';
    require_once __DIR__ . '/../utils/Cache.php';
    
    $db = new MembrosDatabase();
    $cache = new Cache();
    
    // Gerar chave de cache baseada nos parâmetros
    $cacheKey = $cache->generateKey('dashboard_geral', $_GET);
    
    // Tentar obter do cache
    $cachedStats = $cache->get($cacheKey);
    if ($cachedStats !== null) {
        ob_end_clean();
        Response::success($cachedStats);
        exit;
    }
    
    // Estatísticas gerais - Query otimizada
    // Excluir membros bloqueados (soft delete) do total
    $stats = [
        'totalMembros' => (int)$db->query("SELECT COUNT(*) as total FROM membros_membros WHERE status != 'bloqueado'")->fetch()['total'],
        'membrosAtivos' => (int)$db->query("SELECT COUNT(*) as total FROM membros_membros WHERE status = 'ativo'")->fetch()['total'],
        'pastoraisAtivas' => (int)$db->query("SELECT COUNT(*) as total FROM membros_pastorais WHERE ativo = 1")->fetch()['total'],
        'eventosHoje' => (int)$db->query("SELECT COUNT(*) as total FROM membros_eventos WHERE DATE(data_evento) = CURDATE()")->fetch()['total']
    ];
    
    // Alertas
    $alertas = [];
    
    // Membros sem pastoral - Query otimizada com JOIN
    $semPastoral = (int)$db->query("
        SELECT COUNT(*) as total 
        FROM membros_membros m 
        LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id 
        WHERE mp.membro_id IS NULL AND m.status = 'ativo'
    ")->fetch()['total'];
    
    if ($semPastoral > 0) {
        $alertas[] = [
            'tipo' => 'warning',
            'titulo' => 'Membros sem Pastoral',
            'mensagem' => "{$semPastoral} membros ativos não estão vinculados a nenhuma pastoral"
        ];
    }
    
    // Eventos próximos
    $eventosProximos = (int)$db->query("
        SELECT COUNT(*) as total 
        FROM membros_eventos 
        WHERE data_evento BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
    ")->fetch()['total'];
    
    if ($eventosProximos > 0) {
        $alertas[] = [
            'tipo' => 'info',
            'titulo' => 'Eventos Próximos',
            'mensagem' => "{$eventosProximos} eventos nos próximos 7 dias"
        ];
    }
    
    $stats['alertas'] = $alertas;
    
    // Armazenar no cache por 5 minutos (apenas se não houver erro)
    try {
        $cache->set($cacheKey, $stats, 300);
    } catch (Exception $cacheError) {
        // Log do erro mas não interrompe a resposta
        error_log("Cache error: " . $cacheError->getMessage());
    }
    
    ob_end_clean();
    Response::success($stats);
    
} catch (PDOException $e) {
    ob_end_clean();
    error_log("Dashboard PDO error: " . $e->getMessage());
    Response::error('Erro ao conectar com banco de dados', 500);
} catch (Exception $e) {
    ob_end_clean();
    error_log("Dashboard error: " . $e->getMessage());
    error_log("Dashboard error trace: " . $e->getTraceAsString());
    Response::error('Erro ao carregar estatísticas: ' . $e->getMessage(), 500);
} catch (Throwable $e) {
    ob_end_clean();
    error_log("Dashboard fatal error: " . $e->getMessage());
    Response::error('Erro interno do servidor', 500);
}
?>
