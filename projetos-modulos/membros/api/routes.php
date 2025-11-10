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

// Incluir dependências
require_once 'utils/Response.php';
require_once 'utils/Validation.php';
require_once __DIR__ . '/../config/database.php';

// Obter método e URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extrair o path correto
$path = $uri;

// Remover vários formatos possíveis de base path
$basePaths = [
    '/PROJETOS/GerencialParoq/projetos-modulos/membros/api/',
    '/projetos-modulos/membros/api/',
    '/api/'
];

foreach ($basePaths as $basePath) {
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
        break;
    }
}

// Remover partes específicas que podem aparecer no path
$path = str_replace('/projetos/GerencialParoq/projetos-modulos/membros/api/', '', $path);
$path = str_replace('/projetos/GerencialParoq/projetos-modulos/membros/api/', '', $path);

// Tentar extrair o path depois de "api/"
$lastApiPos = strrpos($path, '/api/');
if ($lastApiPos !== false) {
    $path = substr($path, $lastApiPos + 5); // +5 para pular "/api/"
}

// Se ainda tiver o prefixo errado, remover manualmente
if (strpos($path, '/projetos/GerencialParoq') === 0) {
    $path = substr($path, strlen('/projetos/GerencialParoq'));
}
if (strpos($path, 'projetos-modulos/membros/api/') === 0) {
    $path = substr($path, strlen('projetos-modulos/membros/api/'));
}
if (strpos($path, 'api/') === 0) {
    $path = substr($path, 4);
}

// Limpar barras extras no início
$path = ltrim($path, '/');

// Remover parâmetros da query string do path
$path = strtok($path, '?');

// Debug: log do path para verificar
error_log("API URI: " . $uri);
error_log("API Path: " . $path);

// Debug: verificar se o path contém 'membros/buscar'
if (strpos($path, 'membros/buscar') !== false) {
    error_log("Routes: Detectado path de busca de membros: $path");
}

// Verificar se é rota de exportação (não definir header JSON)
$isExportacao = strpos($path, 'exportar') !== false || isset($_GET['formato']);

// Configuração de resposta JSON (apenas se não for exportação)
if (!$isExportacao) {
    header('Content-Type: application/json; charset=utf-8');
}

// Roteamento
switch ($path) {
    case 'dashboard/agregado':
        if ($method === 'GET') {
            include 'endpoints/dashboard_agregado.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
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
        
    case 'pastorais/vincular-membro':
        if ($method === 'POST') {
            include 'endpoints/pastorais_vincular_membro.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'membros/exportar':
        if ($method === 'GET') {
            error_log("Routes: Rota específica para membros/exportar detectada");
            include 'endpoints/membros_exportar.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'membros/buscar':
        if ($method === 'GET') {
            error_log("Routes: Rota específica para membros/buscar detectada");
            include 'endpoints/membros_buscar.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'membros':
        if ($method === 'GET') {
            error_log("Rota membros: Incluindo endpoints/membros_listar.php");
            include 'endpoints/membros_listar.php';
        } elseif ($method === 'POST') {
            error_log("Rota membros: Incluindo endpoints/membros_criar.php");
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
        
    case 'eventos/calendario':
        if ($method === 'GET') {
            include 'endpoints/eventos_calendario.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;

    // ====== ESCALAS (por pastoral) ======
    case 'escalas/semana':
        // GET /api/escalas/semana?pastoral_id=...&start=YYYY-MM-DD&end=YYYY-MM-DD
        if ($method === 'GET') {
            include 'endpoints/escalas_listar_semana.php';
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
    
    // ====== RELATÓRIOS ======
    case 'relatorios/membros-por-pastoral':
        if ($method === 'GET') {
            include 'endpoints/relatorios/membros_por_pastoral.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'relatorios/membros-por-status':
        if ($method === 'GET') {
            include 'endpoints/relatorios/membros_por_status.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'relatorios/membros-por-genero':
        if ($method === 'GET') {
            include 'endpoints/relatorios/membros_por_genero.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'relatorios/membros-por-faixa-etaria':
        if ($method === 'GET') {
            include 'endpoints/relatorios/membros_por_faixa_etaria.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'relatorios/crescimento-temporal':
        if ($method === 'GET') {
            include 'endpoints/relatorios/crescimento_temporal.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'relatorios/membros-sem-pastoral':
        if ($method === 'GET') {
            include 'endpoints/relatorios/membros_sem_pastoral.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    case 'relatorios/aniversariantes':
        if ($method === 'GET') {
            include 'endpoints/relatorios/aniversariantes.php';
        } else {
            Response::error('Método não permitido', 405);
        }
        break;
        
    default:
        // Verificar rota de evento por ID ANTES de todas as outras
        // Ex: /api/eventos/{id}
        // Aceita UUIDs (36 caracteres) e IDs com prefixo (ex: evt-xxx)
        if (preg_match('/^eventos\/([a-f0-9\-]+|[a-z]+-[a-f0-9]+)$/i', $path, $matches)) {
            $evento_id = $matches[1];
            
            error_log("Routes: Verificando evento com ID: " . $evento_id);
            
            // Verificar se é evento geral (tabela membros_eventos)
            try {
                $testDb = new MembrosDatabase();
                $testQuery = "SELECT COUNT(*) as count FROM membros_eventos WHERE id = ?";
                $result = $testDb->fetchOne($testQuery, [$evento_id]);
                $isEventoGeral = ($result && isset($result['count']) && $result['count'] > 0);
                error_log("Routes: Evento geral encontrado? " . ($isEventoGeral ? 'sim' : 'não'));
            } catch (Exception $e) {
                error_log("Erro ao verificar tipo de evento: " . $e->getMessage());
                $isEventoGeral = false;
            }
            
            if ($isEventoGeral) {
                // Evento geral
                error_log("Routes: Incluindo endpoint de evento geral para ID: " . $evento_id);
                if ($method === 'GET') {
                    include 'endpoints/eventos_visualizar.php';
                } elseif ($method === 'PUT') {
                    include 'endpoints/eventos_atualizar.php';
                } elseif ($method === 'DELETE') {
                    include 'endpoints/eventos_excluir.php';
                } else {
                    Response::error('Método não permitido', 405);
                }
                exit;
            } else {
                // Evento de escala (fallback para rota antiga)
                error_log("Routes: Incluindo endpoint de evento de escala para ID: " . $evento_id);
                if ($method === 'GET') {
                    include 'endpoints/escalas_evento_detalhes.php';
                } elseif ($method === 'DELETE') {
                    include 'endpoints/escalas_eventos_excluir.php';
                } else {
                    Response::error('Método não permitido', 405);
                }
                exit;
            }
        }
        // Verificar se é uma rota de evento específico de uma pastoral (PUT, DELETE)
        // Ex: /api/pastorais/{pastoral_id}/eventos/{evento_id}
        if (preg_match('/^pastorais\/([a-f0-9\-]+|[a-z]+\-\d+|\d+)\/eventos\/([a-f0-9\-]+)$/', $path, $matches)) {
            $pastoral_id = $matches[1];
            $evento_id = $matches[2];
            
            if ($method === 'PUT') {
                include 'endpoints/pastoral_eventos_atualizar.php';
            } elseif ($method === 'DELETE') {
                include 'endpoints/pastoral_eventos_excluir.php';
            } else {
                Response::error('Método não permitido', 405);
            }
        }
        // Verificar se é uma rota de eventos de uma pastoral (GET, POST)
        // Ex: /api/pastorais/{pastoral_id}/eventos
        elseif (preg_match('/^pastorais\/([a-f0-9\-]+|[a-z]+\-\d+|\d+)\/eventos$/', $path, $matches)) {
            $pastoral_id = $matches[1];
            
            if ($method === 'GET') {
                include 'endpoints/pastoral_eventos.php';
            } elseif ($method === 'POST') {
                include 'endpoints/pastoral_eventos_criar.php';
            } else {
                Response::error('Método não permitido', 405);
            }
        }
        // Verificar se é uma rota de detalhes da pastoral com sub-recursos (membros, coordenadores)
        // Aceita UUIDs, IDs numéricos ou IDs com prefixo (ex: pastoral-2)
        elseif (preg_match('/^pastorais\/([a-f0-9\-]+|[a-z]+\-\d+|\d+)\/(membros|coordenadores)$/', $path, $matches)) {
            $pastoral_id = $matches[1];
            $resource = $matches[2];
            
            if ($method === 'GET') {
                include "endpoints/pastoral_{$resource}.php";
            } else {
                Response::error('Método não permitido', 405);
            }
        }
        // Verificar se é uma rota de pastoral específico
        // Aceita UUIDs, IDs numéricos ou IDs com prefixo (ex: pastoral-2)
        elseif (preg_match('/^pastorais\/([a-f0-9\-]+|[a-z]+\-\d+|\d+)$/', $path, $matches)) {
            $pastoral_id = $matches[1];
            if ($method === 'GET') {
                include 'endpoints/pastoral_detalhes.php';
            } elseif ($method === 'PUT') {
                include 'endpoints/pastoral_atualizar.php';
            } elseif ($method === 'DELETE') {
                include 'endpoints/pastoral_excluir.php';
            } else {
                Response::error('Método não permitido', 405);
            }
        }
        // Verificar rotas de escalas por pastoral
        elseif (preg_match('/^pastorais\/([a-f0-9\-]+|[a-z]+\-\d+|\d+)\/escalas\/eventos$/', $path, $matches)) {
            $pastoral_id = $matches[1];
            if ($method === 'POST') {
                include 'endpoints/escalas_eventos_criar.php';
            } else {
                Response::error('Método não permitido', 405);
            }
        }
        // Verificar se é rota de eventos (listar ou criar)
        elseif ($path === 'eventos') {
            if ($method === 'GET') {
                include 'endpoints/eventos_listar.php';
            } elseif ($method === 'POST') {
                include 'endpoints/eventos_criar.php';
            } else {
                Response::error('Método não permitido', 405);
            }
        }
        // Rota: detalhes do evento (GET) - DEVE VIR ANTES da rota de funções
        elseif (preg_match('/^eventos\/([a-f0-9\-]{36})$/', $path, $matches)) {
            $evento_id = $matches[1];
            if ($method === 'GET') {
                include 'endpoints/escalas_evento_detalhes.php';
            } elseif ($method === 'DELETE') {
                include 'endpoints/escalas_evento_excluir.php';
            } else {
                Response::error('Método não permitido', 405);
            }
        }
        // Rota: salvar funcoes/atribuicoes do evento
        elseif (preg_match('/^eventos\/([a-f0-9\-]{36})\/funcoes$/', $path, $matches)) {
            $evento_id = $matches[1];
            if ($method === 'POST') {
                include 'endpoints/escalas_funcoes_salvar.php';
            } else {
                Response::error('Método não permitido', 405);
            }
        }
        // Export TXT
        elseif (preg_match('/^eventos\/([a-f0-9\-]{36})\/export\/txt$/', $path, $matches)) {
            $evento_id = $matches[1];
            if ($method === 'GET') {
                include 'endpoints/escalas_export_txt.php';
            } else {
                Response::error('Método não permitido', 405);
            }
        }
        // Verificar se é uma rota de pastorais de membro específico
        elseif (preg_match('/^membros\/([a-f0-9\-]{36})\/pastorais$/', $path, $matches)) {
            $membro_id = $matches[1];
            if ($method === 'GET') {
                include 'endpoints/membros_pastorais.php';
            } else {
                Response::error('Método não permitido', 405);
            }
        }
        // Rota: upload de foto do membro
        elseif (preg_match('/^membros\/upload-foto$/', $path)) {
            error_log("Rota upload-foto detectada. Método: " . $method);
            if ($method === 'POST') {
                error_log("Incluindo endpoints/membros_upload_foto.php");
                include 'endpoints/membros_upload_foto.php';
            } else {
                error_log("Erro: Método não permitido para upload-foto: " . $method);
                Response::error('Método não permitido', 405);
            }
        }
        // Verificar se é uma rota de membro específico (GET, PUT, DELETE)
        elseif (preg_match('/^membros\/([a-f0-9\-]{36})$/', $path, $matches)) {
            $membro_id = $matches[1];
            if ($method === 'GET') {
                include 'endpoints/membros_visualizar.php';
            } elseif ($method === 'PUT') {
                include 'endpoints/membros_atualizar.php';
            } elseif ($method === 'DELETE') {
                include 'endpoints/membros_excluir.php';
            } else {
                Response::error('Método não permitido', 405);
            }
        } else {
            Response::error('Endpoint não encontrado', 404);
        }
        break;
}
?>
