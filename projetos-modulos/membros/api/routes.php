<?php
/**
 * Rotas da API - Módulo de Membros
 * GerencialParoq
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

// Configuração de resposta JSON
header('Content-Type: application/json; charset=utf-8');

// Incluir dependências
require_once 'utils/Response.php';
require_once 'utils/Validation.php';

// Obter método e URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extrair o path correto
$basePath = '/PROJETOS/GerencialParoq/projetos-modulos/membros/api/';
if (strpos($uri, $basePath) === 0) {
    $path = substr($uri, strlen($basePath));
} else {
    // Fallback para outros formatos de URL
    $path = str_replace('/projetos-modulos/membros/api/', '', $uri);
    $path = str_replace('/api/', '', $path);
}

// Remover parâmetros da query string do path
$path = strtok($path, '?');

// Debug: log do path para verificar
error_log("API URI: " . $uri);
error_log("API Path: " . $path);

// Roteamento
switch ($path) {
    case 'dashboard/geral':
        if ($method === 'GET') {
            include 'endpoints/dashboard_geral.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'dashboard/membros-status':
        if ($method === 'GET') {
            include 'endpoints/dashboard_membros_status.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'dashboard/membros-pastoral':
        if ($method === 'GET') {
            include 'endpoints/dashboard_membros_pastoral.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'dashboard/presenca-mensal':
        if ($method === 'GET') {
            include 'endpoints/dashboard_presenca_mensal.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'dashboard/atividades-recentes':
        if ($method === 'GET') {
            include 'endpoints/dashboard_atividades_recentes.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'membros':
        if ($method === 'GET') {
            include 'endpoints/membros_listar.php';
        } elseif ($method === 'POST') {
            include 'endpoints/membros_criar.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'pastorais':
        if ($method === 'GET') {
            include 'endpoints/pastorais_listar.php';
        } elseif ($method === 'POST') {
            include 'endpoints/pastorais_criar.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'eventos':
        if ($method === 'GET') {
            include 'endpoints/eventos_listar.php';
        } elseif ($method === 'POST') {
            include 'endpoints/eventos_criar.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'health':
        if ($method === 'GET') {
            Response::success(['status' => 'ok', 'timestamp' => date('Y-m-d H:i:s')]);
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    default:
        Response::error('Endpoint não encontrado', 404);
        break;
}
?>
