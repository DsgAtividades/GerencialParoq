<?php
/**
 * Router simples para endpoints da API de Eventos
 */

// Configuração de CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Responder a requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir dependências
require_once 'utils/Response.php';
require_once __DIR__ . '/../config/database.php';

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar método e rota
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extrair o path correto
$path = $uri;

// Remover vários formatos possíveis de base path
$basePaths = [
    '/PROJETOS/GerencialParoq/modules/eventos/api/',
    '/projetos/GerencialParoq/modules/eventos/api/',
    '/modules/eventos/api/',
    '/api/'
];

foreach ($basePaths as $basePath) {
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
        break;
    }
}

// Tentar extrair o path depois de "api/"
$lastApiPos = strrpos($path, '/api/');
if ($lastApiPos !== false) {
    $path = substr($path, $lastApiPos + 5); // +5 para pular "/api/"
}

// Limpar barras extras no início
$path = ltrim($path, '/');

// Remover parâmetros da query string do path
$path = strtok($path, '?');

// Debug: log do path para verificar
error_log("Eventos API URI: " . $uri);
error_log("Eventos API Path: " . $path);

// Configurar resposta JSON
header('Content-Type: application/json; charset=utf-8');

// Converter path em array
$pathParts = explode('/', trim($path, '/'));
$route = $pathParts;

// Determinar endpoint
$endpoint = null;
$id = null;

// Roteamento baseado no path
if (count($route) >= 2 && $route[0] === 'eventos') {
    if ($route[1] === 'calendario') {
        $endpoint = 'eventos_calendario.php';
    } elseif ($route[1] === 'criar') {
        $endpoint = 'eventos_criar.php';
    } elseif ($route[1] === 'visualizar') {
        // /api/eventos/visualizar?id=...
        $endpoint = 'eventos_visualizar.php';
    } elseif ($route[1] === 'atualizar') {
        // /api/eventos/atualizar?id=...
        $endpoint = 'eventos_atualizar.php';
    } elseif ($route[1] === 'excluir') {
        // /api/eventos/excluir?id=...
        $endpoint = 'eventos_excluir.php';
    } elseif ($route[1] === 'listar' || $route[1] === '') {
        $endpoint = 'eventos_listar.php';
    } elseif (isset($route[2])) {
        // /api/eventos/{id}/{acao}
        $id = $route[1];
        $acao = $route[2];
        if ($acao === 'visualizar') {
            $endpoint = 'eventos_visualizar.php';
        } elseif ($acao === 'atualizar') {
            $endpoint = 'eventos_atualizar.php';
        } elseif ($acao === 'excluir') {
            $endpoint = 'eventos_excluir.php';
        }
    } else {
        // /api/eventos/{id}
        $id = $route[1];
        if ($method === 'GET') {
            $endpoint = 'eventos_visualizar.php';
        } elseif ($method === 'PUT' || $method === 'POST') {
            $endpoint = 'eventos_atualizar.php';
        } elseif ($method === 'DELETE') {
            $endpoint = 'eventos_excluir.php';
        }
    }
} elseif (count($route) >= 1 && $route[0] === 'eventos') {
    if ($method === 'POST') {
        $endpoint = 'eventos_criar.php';
    } elseif ($method === 'GET') {
        // Se for GET sem parâmetros, retornar calendário
        $endpoint = 'eventos_calendario.php';
    } else {
        $endpoint = 'eventos_listar.php';
    }
} elseif (count($route) >= 1 && $route[0] === 'membros') {
    $endpoint = 'membros_listar.php';
} elseif (count($route) === 0 || empty($route[0])) {
    // Se não houver rota, retornar calendário por padrão
    if ($method === 'GET') {
        $endpoint = 'eventos_calendario.php';
    } elseif ($method === 'POST') {
        $endpoint = 'eventos_criar.php';
    }
}

// Se não encontrou endpoint, tentar pelo método e query string
if (!$endpoint) {
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        if ($action === 'calendario') {
            $endpoint = 'eventos_calendario.php';
        } elseif ($action === 'listar') {
            $endpoint = 'eventos_listar.php';
        } elseif ($action === 'criar' && $method === 'POST') {
            $endpoint = 'eventos_criar.php';
        } elseif ($action === 'visualizar' && isset($_GET['id'])) {
            $endpoint = 'eventos_visualizar.php';
        } elseif ($action === 'atualizar' && isset($_GET['id'])) {
            $endpoint = 'eventos_atualizar.php';
        } elseif ($action === 'excluir' && isset($_GET['id'])) {
            $endpoint = 'eventos_excluir.php';
        }
    }
    
    // Se ainda não encontrou, usar método POST para criar
    if (!$endpoint && $method === 'POST' && !isset($_GET['id'])) {
        $endpoint = 'eventos_criar.php';
    }
    
    // Se ainda não encontrou, listar eventos
    if (!$endpoint && $method === 'GET') {
        $endpoint = 'eventos_calendario.php'; // Por padrão, retorna calendário
    }
}

// Se encontrou ID na rota, adicionar ao GET
if ($id) {
    $_GET['id'] = $id;
}

// Incluir endpoint
if ($endpoint && file_exists(__DIR__ . '/endpoints/' . $endpoint)) {
    require_once __DIR__ . '/endpoints/' . $endpoint;
} else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Endpoint não encontrado',
        'path' => $path,
        'route' => $route
    ]);
}

