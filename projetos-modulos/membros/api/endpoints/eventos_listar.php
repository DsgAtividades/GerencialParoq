<?php
/**
 * Endpoint: Listar Eventos
 * Retorna lista de eventos
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    
    // Parâmetros de filtro
    $data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : null;
    $data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : null;
    $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
    
    // Construir query
    $where = ['1=1'];
    $params = [];
    
    if ($data_inicio) {
        $where[] = "e.data_evento >= :data_inicio";
        $params[':data_inicio'] = $data_inicio;
    }
    
    if ($data_fim) {
        $where[] = "e.data_evento <= :data_fim";
        $params[':data_fim'] = $data_fim;
    }
    
    if ($tipo) {
        $where[] = "e.tipo = :tipo";
        $params[':tipo'] = $tipo;
    }
    
    $whereClause = implode(' AND ', $where);
    
    $query = "
        SELECT 
            e.id,
            e.nome as titulo,
            e.descricao,
            e.data_evento,
            e.horario as hora_inicio,
            e.local,
            e.tipo,
            e.ativo,
            e.created_at,
            0 as total_inscritos
        FROM membros_eventos e
        WHERE {$whereClause}
        ORDER BY e.data_evento ASC
    ";
    
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    Response::success([
        'data' => $eventos
    ]);
    
} catch (Exception $e) {
    Response::error('Erro ao carregar eventos: ' . $e->getMessage(), 500);
}
?>
