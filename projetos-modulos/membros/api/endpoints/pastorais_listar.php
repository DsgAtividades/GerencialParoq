<?php
/**
 * Endpoint: Listar Pastorais
 * Retorna lista de pastorais
 * 
 * Cache: 10 minutos (600 segundos)
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
    
    // Gerar chave de cache
    $cacheKey = $cache->generateKey('pastorais_listar', $_GET);
    
    // Tentar obter do cache
    $cachedPastorais = $cache->get($cacheKey);
    if ($cachedPastorais !== null) {
        ob_end_clean();
        Response::success($cachedPastorais);
        exit;
    }
    
    $query = "
        SELECT 
            p.id,
            p.nome,
            p.tipo,
            p.comunidade_ou_capelania,
            p.dia_semana,
            p.horario,
            p.local_reuniao,
            p.coordenador_id,
            p.vice_coordenador_id,
            p.created_at,
            COUNT(mp.membro_id) as total_membros
        FROM membros_pastorais p
        LEFT JOIN membros_membros_pastorais mp ON p.id = mp.pastoral_id
        GROUP BY p.id, p.nome, p.tipo, p.comunidade_ou_capelania, p.dia_semana, p.horario, p.local_reuniao, p.coordenador_id, p.vice_coordenador_id, p.created_at
        ORDER BY p.nome
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $pastorais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar nomes dos coordenadores de uma vez para melhor performance
    $coordenadoresIds = array_values(array_filter(array_unique(array_map('trim', array_merge(
        array_column($pastorais, 'coordenador_id'),
        array_column($pastorais, 'vice_coordenador_id')
    )))));
    $coordenadoresMap = [];
    if (!empty($coordenadoresIds)) {
        error_log("pastorais_listar.php: Buscando coordenadores para IDs: " . implode(', ', $coordenadoresIds));
        $placeholders = implode(',', array_fill(0, count($coordenadoresIds), '?'));
        $coordQuery = "SELECT id, nome_completo, apelido FROM membros_membros WHERE id IN ($placeholders)";
        $coordStmt = $db->prepare($coordQuery);
        $coordStmt->execute($coordenadoresIds);
        $coordenadores = $coordStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($coordenadores as $coord) {
            $coordId = trim($coord['id']);
            $coordenadoresMap[$coordId] = $coord['nome_completo'] ?: $coord['apelido'];
            error_log("pastorais_listar.php: Mapeando coordenador ID '$coordId' -> '{$coordenadoresMap[$coordId]}'");
        }
    }
    
    // Formatar dados para incluir informações adicionais
    $pastoraisFormatadas = array_map(function($pastoral) use ($coordenadoresMap) {
        $coordenadorNome = null;
        $coordenadorId = trim($pastoral['coordenador_id'] ?? '');
        
        if (!empty($coordenadorId)) {
            if (isset($coordenadoresMap[$coordenadorId])) {
                $coordenadorNome = $coordenadoresMap[$coordenadorId];
            } else {
                error_log("pastorais_listar.php: Coordenador ID '$coordenadorId' não encontrado no mapa para pastoral '{$pastoral['nome']}'");
                error_log("pastorais_listar.php: Chaves disponíveis no mapa: " . implode(', ', array_keys($coordenadoresMap)));
            }
        }
        
        return [
            'id' => $pastoral['id'],
            'nome' => $pastoral['nome'],
            'tipo' => $pastoral['tipo'],
            'comunidade' => $pastoral['comunidade_ou_capelania'],
            'total_membros' => (int)$pastoral['total_membros'],
            'total_coordenadores' => 0, // Será calculado separadamente se necessário
            'dia_semana' => $pastoral['dia_semana'],
            'horario' => $pastoral['horario'],
            'local_reuniao' => $pastoral['local_reuniao'],
            'coordenador_nome' => $coordenadorNome,
            'created_at' => $pastoral['created_at']
        ];
    }, $pastorais);
    
    error_log("pastorais_listar.php: Retornando " . count($pastoraisFormatadas) . " pastorais formatadas");
    foreach ($pastoraisFormatadas as $p) {
        if ($p['coordenador_nome']) {
            error_log("pastorais_listar.php: Pastoral '{$p['nome']}' tem coordenador: {$p['coordenador_nome']}");
        }
    }
    
    // Armazenar no cache por 10 minutos (apenas se não houver erro)
    try {
        $cache->set($cacheKey, $pastoraisFormatadas, 600);
    } catch (Exception $cacheError) {
        // Log do erro mas não interrompe a resposta
        error_log("Cache error: " . $cacheError->getMessage());
    }
    
    ob_end_clean();
    Response::success($pastoraisFormatadas);
    
} catch (PDOException $e) {
    ob_end_clean();
    error_log("Pastorais PDO error: " . $e->getMessage());
    Response::error('Erro ao conectar com banco de dados', 500);
} catch (Exception $e) {
    ob_end_clean();
    error_log("Pastorais error: " . $e->getMessage());
    error_log("Pastorais error trace: " . $e->getTraceAsString());
    Response::error('Erro ao carregar pastorais: ' . $e->getMessage(), 500);
} catch (Throwable $e) {
    ob_end_clean();
    error_log("Pastorais fatal error: " . $e->getMessage());
    Response::error('Erro interno do servidor', 500);
}
?>
