<?php
/**
 * Endpoint: Listar Membros - Versão Simplificada
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
    
    // Query base simples
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
            GROUP_CONCAT(DISTINCT p.nome SEPARATOR ', ') as pastorais
        FROM membros_membros m
        LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id
        LEFT JOIN membros_pastorais p ON mp.pastoral_id = p.id
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
    
    if (!empty($pastoral)) {
        $query .= " AND mp.pastoral_id = ?";
        $params[] = $pastoral;
    }
    
    if (!empty($funcao)) {
        $query .= " AND mp.funcao_id = ?";
        $params[] = $funcao;
    }
    
    $query .= " GROUP BY m.id ORDER BY m.nome_completo LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar total
    $countQuery = "
        SELECT COUNT(DISTINCT m.id) as total
        FROM membros_membros m
        LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id
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
    
    if (!empty($pastoral)) {
        $countQuery .= " AND mp.pastoral_id = ?";
        $countParams[] = $pastoral;
    }
    
    if (!empty($funcao)) {
        $countQuery .= " AND mp.funcao_id = ?";
        $countParams[] = $funcao;
    }
    
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($countParams);
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
