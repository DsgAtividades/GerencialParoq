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
    
    // Query base simples - sem JOINs para evitar perda de registros
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
            m.created_at,
            '' as pastorais
        FROM membros_membros m
        WHERE 1=1
    ";
    
    $params = [];
    
    // Adicionar filtros
    if (!empty($busca)) {
        $query .= " AND (m.nome_completo LIKE ? OR m.apelido LIKE ? OR m.email LIKE ?)";
        $params[] = "%{$busca}%";
        $params[] = "%{$busca}%";
        $params[] = "%{$busca}%";
    }
    
    if (!empty($status)) {
        $query .= " AND m.status = ?";
        $params[] = $status;
    }
    
    // Filtros de pastoral e função removidos temporariamente
    // (serão implementados em versão futura com query separada)
    if (!empty($pastoral)) {
        // TODO: Implementar filtro de pastoral com query separada
    }
    
    if (!empty($funcao)) {
        // TODO: Implementar filtro de função com query separada
    }
    
    $query .= " ORDER BY m.nome_completo LIMIT ? OFFSET ?";
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
        $countQuery .= " AND (m.nome_completo LIKE ? OR m.apelido LIKE ? OR m.email LIKE ?)";
        $countParams[] = "%{$busca}%";
        $countParams[] = "%{$busca}%";
        $countParams[] = "%{$busca}%";
    }
    
    if (!empty($status)) {
        $countQuery .= " AND m.status = ?";
        $countParams[] = $status;
    }
    
    // Filtros de pastoral e função removidos temporariamente
    // (serão implementados em versão futura)
    
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
