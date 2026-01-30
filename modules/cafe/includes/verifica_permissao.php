<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/permissoes_paginas.php';
session_start();
// Função para verificar se o usuário está logado
function verificarLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        $_SESSION['alerta'] = [
            'tipo' => 'warning',
            'mensagem' => 'Por favor, faça login para continuar.'
        ];
        header("Location: login.php");
        exit;
    }
}

// Função para verificar permissão específica
function verificarPermissao($permissaoNecessaria) {
    global $pdo;
    
    verificarLogin();
    if(isset($_SESSION['projeto']) && $_SESSION['projeto'] == 'paroquianspraga'){
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as tem_permissao 
                FROM cafe_usuarios u
                JOIN cafe_grupos_permissoes gp ON u.grupo_id = gp.grupo_id
                JOIN cafe_permissoes p ON gp.permissao_id = p.id
                WHERE u.id = ? AND p.nome = ? AND u.ativo = 1
            ");
            
            $stmt->execute([$_SESSION['usuario_id'], $permissaoNecessaria]);
            $resultado = $stmt->fetch();
            if (!$resultado['tem_permissao']) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensagem' => 'Você não tem permissão para acessar esta página.'
                ];
                header("Location: index.php");
                //return $resultado['tem_permissao'];
            }
        } catch(PDOException $e) {
            die("Erro ao verificar permissão: " . $e->getMessage());
        }
    }else{
        $_SESSION['alerta'] = [
            'tipo' => 'warning',
            'mensagem' => 'Por favor, faça login para continuar.'
        ];
        header("Location: login.php");
        exit;
    }
    
}

// Função para verificar permissão específica (para APIs - não faz redirect)
function verificarPermissaoApi($permissaoNecessaria) {
    global $pdo;
    
    // #region agent log
    try {
        $logFile = __DIR__ . '/../../../.cursor/debug.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logEntry = json_encode([
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => time() * 1000,
            'location' => 'verifica_permissao.php:60',
            'message' => 'verificarPermissaoApi entrada',
            'data' => [
                'permissao_necessaria' => $permissaoNecessaria,
                'usuario_id' => $_SESSION['usuario_id'] ?? 'N/A',
                'projeto' => $_SESSION['projeto'] ?? 'N/A',
                'pdo_exists' => isset($pdo)
            ],
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A,D,E'
        ]) . "\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    } catch (Exception $e) {
        // Silenciar erros de log
    }
    // #endregion
    
    // Verificar login sem fazer redirect (apenas retornar false se não estiver logado)
    if (!isset($_SESSION['usuario_id'])) {
        return ['tem_permissao' => 0];
    }

    // Verificar se o projeto está configurado na sessão
    if (!isset($_SESSION['projeto']) || $_SESSION['projeto'] != 'paroquianspraga') {
        // #region agent log
        try {
            $logFile = __DIR__ . '/../../../.cursor/debug.log';
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logEntry = json_encode([
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => time() * 1000,
            'location' => 'verifica_permissao.php:75',
            'message' => 'Projeto não configurado',
            'data' => ['projeto' => $_SESSION['projeto'] ?? 'não definido'],
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A'
        ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
        } catch (Exception $e) {
            // Silenciar erros de log
        }
        // #endregion
        
        error_log("Projeto não configurado na sessão. Projeto: " . ($_SESSION['projeto'] ?? 'não definido'));
        return ['tem_permissao' => 0, 'erro' => 'Projeto não configurado'];
    }

    try {
        // #region agent log
        try {
            $logFile = __DIR__ . '/../../../.cursor/debug.log';
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logEntry = json_encode([
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => time() * 1000,
            'location' => 'verifica_permissao.php:85',
            'message' => 'Antes de executar query de permissão',
            'data' => [
                'usuario_id' => $_SESSION['usuario_id'],
                'permissao' => $permissaoNecessaria
            ],
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A,E'
        ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
        } catch (Exception $e) {
            // Silenciar erros de log
        }
        // #endregion
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as tem_permissao 
            FROM cafe_usuarios u
            JOIN cafe_grupos_permissoes gp ON u.grupo_id = gp.grupo_id
            JOIN cafe_permissoes p ON gp.permissao_id = p.id
            WHERE u.id = ? AND p.nome = ? AND u.ativo = 1
        ");
        
        $stmt->execute([$_SESSION['usuario_id'], $permissaoNecessaria]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // #region agent log
        try {
            $logFile = __DIR__ . '/../../../.cursor/debug.log';
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logEntry = json_encode([
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => time() * 1000,
            'location' => 'verifica_permissao.php:105',
            'message' => 'Depois de executar query de permissão',
            'data' => ['resultado' => $resultado],
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A'
        ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
        } catch (Exception $e) {
            // Silenciar erros de log
        }
        // #endregion
        
        if (!$resultado) {
            return ['tem_permissao' => 0];
        }
        
        return $resultado;
    } catch(PDOException $e) {
        // #region agent log
        try {
            $logFile = __DIR__ . '/../../../.cursor/debug.log';
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logEntry = json_encode([
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => time() * 1000,
            'location' => 'verifica_permissao.php:120',
            'message' => 'PDOException em verificarPermissaoApi',
            'data' => [
                'exception_message' => $e->getMessage(),
                'error_info' => $e->errorInfo ?? 'N/A'
            ],
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A,E'
        ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
        } catch (Exception $e) {
            // Silenciar erros de log
        }
        // #endregion
        
        error_log("Erro ao verificar permissão API: " . $e->getMessage());
        // Retornar erro em formato que não quebre o JSON
        return ['tem_permissao' => 0, 'erro' => $e->getMessage()];
    }
}

// Função para verificar se tem permissão (sem redirecionar)
function temPermissao($permissao) {
    global $pdo;
    
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as tem_permissao 
            FROM cafe_usuarios u
            JOIN cafe_grupos_permissoes gp ON u.grupo_id = gp.grupo_id
            JOIN cafe_permissoes p ON gp.permissao_id = p.id
            WHERE u.id = ? AND p.nome = ? AND u.ativo = 1
        ");
        
        $stmt->execute([$_SESSION['usuario_id'], $permissao]);
        $resultado = $stmt->fetch();

        return (bool)$resultado['tem_permissao'];
    } catch(PDOException $e) {
        return false;
    }
}

// Função para verificar se tem permissão (sem redirecionar)
function verificaGrupoPermissao() {
    global $pdo;
    
    // #region agent log
    $logFile = __DIR__ . '/../../../.cursor/debug.log';
    $logEntry = json_encode([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'verifica_permissao.php:115',
        'message' => 'verificaGrupoPermissao entrada',
        'data' => [
            'usuario_id' => $_SESSION['usuario_id'] ?? 'N/A',
            'pdo_exists' => isset($pdo)
        ],
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'B,E'
    ]) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    // #endregion
    
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }

    try {
        // #region agent log
        try {
            $logFile = __DIR__ . '/../../../.cursor/debug.log';
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logEntry = json_encode([
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => time() * 1000,
            'location' => 'verifica_permissao.php:130',
            'message' => 'Antes de executar query de grupo',
            'data' => ['usuario_id' => $_SESSION['usuario_id']],
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'B'
        ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
        } catch (Exception $e) {
            // Silenciar erros de log
        }
        // #endregion
        
        $stmt = $pdo->prepare("
            SELECT g.nome
            FROM cafe_usuarios u
            JOIN cafe_grupos g ON u.grupo_id = g.id
            WHERE u.id = ? AND u.ativo = 1
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['usuario_id']]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // #region agent log
        try {
            $logFile = __DIR__ . '/../../../.cursor/debug.log';
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logEntry = json_encode([
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => time() * 1000,
            'location' => 'verifica_permissao.php:150',
            'message' => 'Depois de executar query de grupo',
            'data' => ['resultado' => $resultado],
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'B'
        ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
        } catch (Exception $e) {
            // Silenciar erros de log
        }
        // #endregion
        
        if ($resultado && isset($resultado['nome'])) {
            return $resultado['nome'];
        }
        
        return false;
    } catch(PDOException $e) {
        // #region agent log
        try {
            $logFile = __DIR__ . '/../../../.cursor/debug.log';
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logEntry = json_encode([
            'id' => 'log_' . time() . '_' . uniqid(),
            'timestamp' => time() * 1000,
            'location' => 'verifica_permissao.php:165',
            'message' => 'PDOException em verificaGrupoPermissao',
            'data' => [
                'exception_message' => $e->getMessage(),
                'error_info' => $e->errorInfo ?? 'N/A'
            ],
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'B,E'
        ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
        } catch (Exception $e) {
            // Silenciar erros de log
        }
        // #endregion
        
        error_log("Erro ao verificar grupo: " . $e->getMessage());
        return false;
    }
}

// Verificar automaticamente a permissão necessária para a página atual
// $pagina_atual = basename($_SERVER['SCRIPT_NAME']);
// if (isset($PERMISSOES_PAGINAS[$pagina_atual])) {
//     verificarPermissao($PERMISSOES_PAGINAS[$pagina_atual]);
// }
