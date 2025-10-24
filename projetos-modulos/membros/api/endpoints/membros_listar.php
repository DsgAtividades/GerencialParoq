<?php
/**
 * Endpoint: Listar Membros
 * Retorna lista paginada de membros
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    
    // Parâmetros de paginação
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = ($page - 1) * $limit;
    
    // Parâmetros de filtro
    $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $pastoral = isset($_GET['pastoral']) ? trim($_GET['pastoral']) : '';
    $funcao = isset($_GET['funcao']) ? trim($_GET['funcao']) : '';
    
    // Construir query base
    $where = ['1=1'];
    $params = [];
    
    if (!empty($busca)) {
        $where[] = "(m.nome_completo LIKE :busca OR m.apelido LIKE :busca OR m.email LIKE :busca)";
        $params[':busca'] = "%{$busca}%";
    }
    
    if (!empty($status)) {
        $where[] = "m.status = :status";
        $params[':status'] = $status;
    }
    
    if (!empty($pastoral)) {
        $where[] = "mp.pastoral_id = :pastoral";
        $params[':pastoral'] = $pastoral;
    }
    
    if (!empty($funcao)) {
        $where[] = "mp.funcao_id = :funcao";
        $params[':funcao'] = $funcao;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Query principal
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
            m.created_at,
            GROUP_CONCAT(p.nome SEPARATOR ', ') as pastorais
        FROM membros_membros m
        LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id
        LEFT JOIN membros_pastorais p ON mp.pastoral_id = p.id
        WHERE {$whereClause}
        GROUP BY m.id
        ORDER BY m.nome_completo
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar total
    $countQuery = "
        SELECT COUNT(DISTINCT m.id) as total
        FROM membros_membros m
        LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id
        WHERE {$whereClause}
    ";
    
    $countStmt = $db->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    
    $total = $countStmt->fetch()['total'];
    $totalPages = ceil($total / $limit);
    
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
