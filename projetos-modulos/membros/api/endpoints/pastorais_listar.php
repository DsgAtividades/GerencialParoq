<?php
/**
 * Endpoint: Listar Pastorais
 * Retorna lista de pastorais
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    
    $query = "
        SELECT 
            p.id,
            p.nome,
            p.tipo,
            p.comunidade_capelania,
            p.dia_semana,
            p.horario,
            p.created_at,
            COUNT(mp.membro_id) as total_membros
        FROM membros_pastorais p
        LEFT JOIN membros_membros_pastorais mp ON p.id = mp.pastoral_id
        GROUP BY p.id
        ORDER BY p.nome
    ";
    
    $stmt = $db->query($query);
    $pastorais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    Response::success([
        'data' => $pastorais
    ]);
    
} catch (Exception $e) {
    Response::error('Erro ao carregar pastorais: ' . $e->getMessage(), 500);
}
?>
