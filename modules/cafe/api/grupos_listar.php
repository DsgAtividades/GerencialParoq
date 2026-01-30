<?php
/**
 * API para listar grupos de usuários
 * 
 * Endpoint: GET api/grupos_listar.php
 * Permissão requerida: gerenciar_usuarios
 */

    // #region agent log
    $logFile = __DIR__ . '/../../../.cursor/debug.log';
$logEntry = json_encode([
    'id' => 'log_' . time() . '_' . uniqid(),
    'timestamp' => time() * 1000,
    'location' => 'grupos_listar.php:15',
    'message' => 'API iniciada',
    'data' => ['session_id' => session_id()],
    'sessionId' => 'debug-session',
    'runId' => 'run1',
    'hypothesisId' => 'D'
]) . "\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);
// #endregion

// Habilitar exibição de erros temporariamente para debug
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';

// #region agent log
$logEntry = json_encode([
    'id' => 'log_' . time() . '_' . uniqid(),
    'timestamp' => time() * 1000,
    'location' => 'grupos_listar.php:30',
    'message' => 'Includes carregados',
    'data' => [
        'pdo_exists' => isset($pdo),
        'session_started' => session_status() === PHP_SESSION_ACTIVE,
        'usuario_id' => $_SESSION['usuario_id'] ?? 'N/A',
        'projeto' => $_SESSION['projeto'] ?? 'N/A'
    ],
    'sessionId' => 'debug-session',
    'runId' => 'run1',
    'hypothesisId' => 'D,E'
]) . "\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);
// #endregion

header('Content-Type: application/json; charset=utf-8');

// Verificar login
if (!isset($_SESSION['usuario_id'])) {
    // #region agent log
    $logEntry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'grupos_listar.php:45',
        'message' => 'Usuário não autenticado',
        'data' => ['session_status' => session_status()],
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'D'
    ]) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    // #endregion
    
    http_response_code(401);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário não autenticado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar permissão usando a função API (não faz redirect)
try {
    // #region agent log
    $logEntry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'grupos_listar.php:60',
        'message' => 'Antes de verificarPermissaoApi',
        'data' => [
            'usuario_id' => $_SESSION['usuario_id'],
            'projeto' => $_SESSION['projeto'] ?? 'N/A'
        ],
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'A,D'
    ]) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    // #endregion
    
    $permissao = verificarPermissaoApi('gerenciar_usuarios');
    
    // #region agent log
    $logEntry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'grupos_listar.php:75',
        'message' => 'Depois de verificarPermissaoApi',
        'data' => ['permissao' => $permissao],
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'A'
    ]) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    // #endregion
    
    if (!isset($permissao['tem_permissao']) || $permissao['tem_permissao'] == 0) {
        http_response_code(403);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Sem permissão para acessar esta funcionalidade'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
} catch (Exception $e) {
    // #region agent log
    $logEntry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'grupos_listar.php:90',
        'message' => 'Exceção ao verificar permissão',
        'data' => [
            'exception_message' => $e->getMessage(),
            'exception_type' => get_class($e),
            'trace' => $e->getTraceAsString()
        ],
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'A'
    ]) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    // #endregion
    
    error_log("Erro ao verificar permissão: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao verificar permissão: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // #region agent log
    $logEntry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'grupos_listar.php:105',
        'message' => 'Antes de verificaGrupoPermissao',
        'data' => ['usuario_id' => $_SESSION['usuario_id']],
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'B'
    ]) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    // #endregion
    
    // Verificar grupo do usuário logado
    $grupoLogado = verificaGrupoPermissao();
    
    // #region agent log
    $logEntry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'grupos_listar.php:115',
        'message' => 'Depois de verificaGrupoPermissao',
        'data' => [
            'grupo_logado' => $grupoLogado,
            'tipo' => gettype($grupoLogado)
        ],
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'B'
    ]) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    // #endregion
    
    // Se não conseguir determinar o grupo, assumir que não é administrador
    if ($grupoLogado === false) {
        error_log("Não foi possível determinar o grupo do usuário ID: " . $_SESSION['usuario_id']);
        $grupoLogado = '';
    }
    
    // Se não for administrador, não pode ver o grupo Administrador
    $where = "";
    if ($grupoLogado !== 'Administrador') {
        $where = "WHERE nome NOT LIKE 'Administrador'";
    }

    // #region agent log
    $logEntry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'grupos_listar.php:135',
        'message' => 'Antes de executar query SQL',
        'data' => [
            'where_clause' => $where,
            'pdo_exists' => isset($pdo)
        ],
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'C,E'
    ]) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    // #endregion

    // Buscar grupos
    $sql = "
        SELECT 
            g.id,
            g.nome,
            COUNT(DISTINCT gp.permissao_id) as total_permissoes,
            COUNT(DISTINCT u.id) as total_usuarios
        FROM cafe_grupos g
        LEFT JOIN cafe_grupos_permissoes gp ON g.id = gp.grupo_id
        LEFT JOIN cafe_usuarios u ON g.id = u.grupo_id AND u.ativo = 1
        $where
        GROUP BY g.id, g.nome
        ORDER BY g.nome
    ";
    
    $stmt = $pdo->query($sql);
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // #region agent log
    $logEntry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'grupos_listar.php:160',
        'message' => 'Depois de executar query SQL',
        'data' => [
            'grupos_count' => count($grupos),
            'sql' => $sql
        ],
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'C'
    ]) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    // #endregion

    // Converter valores numéricos
    foreach ($grupos as &$grupo) {
        $grupo['id'] = (int)$grupo['id'];
        $grupo['total_permissoes'] = (int)$grupo['total_permissoes'];
        $grupo['total_usuarios'] = (int)$grupo['total_usuarios'];
    }

    echo json_encode([
        'sucesso' => true,
        'grupos' => $grupos
    ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    // #region agent log
    $logEntry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'grupos_listar.php:175',
        'message' => 'PDOException capturada',
        'data' => [
            'exception_message' => $e->getMessage(),
            'exception_code' => $e->getCode(),
            'sql_state' => $e->errorInfo[0] ?? 'N/A',
            'driver_code' => $e->errorInfo[1] ?? 'N/A',
            'driver_message' => $e->errorInfo[2] ?? 'N/A'
        ],
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'C,E'
    ]) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    // #endregion
    
    error_log("Erro ao listar grupos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao listar grupos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    // #region agent log
    $logEntry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'grupos_listar.php:195',
        'message' => 'Exception genérica capturada',
        'data' => [
            'exception_message' => $e->getMessage(),
            'exception_type' => get_class($e),
            'trace' => $e->getTraceAsString()
        ],
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'A,B,C'
    ]) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    // #endregion
    
    error_log("Erro geral ao listar grupos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao processar requisição: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}



