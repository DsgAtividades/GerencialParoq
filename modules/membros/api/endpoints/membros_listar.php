<?php
/**
 * Endpoint: Listar Membros - Versão Simplificada
 * Retorna lista paginada de membros
 */

require_once '../config/database.php';

try {
    error_log("membros_listar.php: Iniciando execução");
    $db = new MembrosDatabase();
    error_log("membros_listar.php: Conexão com banco estabelecida");
    
    // Parâmetros de paginação
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = ($page - 1) * $limit;
    error_log("membros_listar.php: Parâmetros - page: $page, limit: $limit, offset: $offset");
    
    // Parâmetros de filtro
    $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $pastoral = isset($_GET['pastoral']) ? trim($_GET['pastoral']) : '';
    $funcao = isset($_GET['funcao']) ? trim($_GET['funcao']) : '';
    
    // Log dos filtros recebidos para debug
    error_log("membros_listar.php: Filtros - busca: '$busca', status: '$status', pastoral: '$pastoral', funcao: '$funcao'");
    
    // Query otimizada com LEFT JOIN para buscar pastorais em uma única query
    $query = "
        SELECT 
            m.id,
            m.nome_completo,
            m.apelido,
            m.email,
            COALESCE(m.celular_whatsapp, m.telefone_fixo) as telefone,
            m.status,
            m.paroquiano,
            m.comunidade_ou_capelania,
            m.foto_url,
            m.data_entrada,
            m.created_at,
            GROUP_CONCAT(DISTINCT p.nome SEPARATOR ', ') as pastorais
        FROM membros_membros m
        LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id
        LEFT JOIN membros_pastorais p ON mp.pastoral_id = p.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Adicionar filtros
    if (!empty($busca)) {
        $query .= " AND (
            m.nome_completo LIKE ? OR 
            m.apelido LIKE ? OR 
            m.email LIKE ? OR 
            m.celular_whatsapp LIKE ? OR 
            m.telefone_fixo LIKE ?
        )";
        $buscaParam = "%{$busca}%";
        $params[] = $buscaParam;
        $params[] = $buscaParam;
        $params[] = $buscaParam;
        $params[] = $buscaParam;
        $params[] = $buscaParam;
    }
    
    if (!empty($status)) {
        $query .= " AND m.status = ?";
        $params[] = $status;
    }
    
    // Filtro de pastoral - Otimizado: usar JOIN em vez de subquery
    if (!empty($pastoral)) {
        // Verificar se JOIN já existe
        $hasJoin = strpos($query, 'LEFT JOIN membros_membros_pastorais mp') !== false;
        
        if (!$hasJoin) {
            // Adicionar JOIN antes do WHERE
            $query = str_replace(
                'WHERE 1=1',
                'LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id WHERE 1=1',
                $query
            );
        }
        $query .= " AND mp.pastoral_id = ?";
        $params[] = $pastoral;
    }
    
    // Filtro de função - Otimizado: usar JOIN em vez de subquery
    if (!empty($funcao)) {
        // Verificar se JOIN já existe
        $hasJoin = strpos($query, 'LEFT JOIN membros_membros_pastorais mp') !== false;
        
        if (!$hasJoin) {
            // Adicionar JOIN antes do WHERE
            $query = str_replace(
                'WHERE 1=1',
                'LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id WHERE 1=1',
                $query
            );
        }
        $query .= " AND mp.funcao_id = ?";
        $params[] = $funcao;
    }
    
    // Agrupar por membro para o GROUP_CONCAT funcionar corretamente
    $query .= " GROUP BY m.id ORDER BY m.nome_completo LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    error_log("membros_listar.php: Executando query principal");
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("membros_listar.php: Query executada - " . count($membros) . " membros encontrados");
    
    // Contar total - query simplificada
    $countQuery = "
        SELECT COUNT(*) as total
        FROM membros_membros m
        WHERE 1=1
    ";
    
    $countParams = [];
    
    if (!empty($busca)) {
        $countQuery .= " AND (
            m.nome_completo LIKE ? OR 
            m.apelido LIKE ? OR 
            m.email LIKE ? OR 
            m.celular_whatsapp LIKE ? OR 
            m.telefone_fixo LIKE ?
        )";
        $buscaParam = "%{$busca}%";
        $countParams[] = $buscaParam;
        $countParams[] = $buscaParam;
        $countParams[] = $buscaParam;
        $countParams[] = $buscaParam;
        $countParams[] = $buscaParam;
    }
    
    if (!empty($status)) {
        $countQuery .= " AND m.status = ?";
        $countParams[] = $status;
    }
    
    // Filtro de pastoral - Otimizado: usar JOIN em vez de subquery
    if (!empty($pastoral)) {
        // Verificar se JOIN já existe
        $hasJoin = strpos($countQuery, 'LEFT JOIN membros_membros_pastorais mp') !== false;
        
        if (!$hasJoin) {
            // Adicionar JOIN antes do WHERE
            $countQuery = str_replace(
                'WHERE 1=1',
                'LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id WHERE 1=1',
                $countQuery
            );
        }
        $countQuery .= " AND mp.pastoral_id = ?";
        $countParams[] = $pastoral;
    }
    
    // Filtro de função - Otimizado: usar JOIN em vez de subquery
    if (!empty($funcao)) {
        // Verificar se JOIN já existe
        $hasJoin = strpos($countQuery, 'LEFT JOIN membros_membros_pastorais mp') !== false;
        
        if (!$hasJoin) {
            // Adicionar JOIN antes do WHERE
            $countQuery = str_replace(
                'WHERE 1=1',
                'LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id WHERE 1=1',
                $countQuery
            );
        }
        $countQuery .= " AND mp.funcao_id = ?";
        $countParams[] = $funcao;
    }
    
    error_log("membros_listar.php: Executando query de contagem");
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($countParams);
    $total = $countStmt->fetch()['total'];
    $totalPages = ceil($total / $limit);
    error_log("membros_listar.php: Contagem - total: $total, pages: $totalPages");
    
    Response::success([
        'data' => $membros,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => $totalPages
        ]
    ]);
    
} catch (Exception $e) {
    Response::error('Erro ao carregar membros: ' . $e->getMessage(), 500);
}
?>
