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
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $pastorais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar dados para incluir informações adicionais
    $pastoraisFormatadas = array_map(function($pastoral) {
        return [
            'id' => $pastoral['id'],
            'nome' => $pastoral['nome'],
            'tipo' => $pastoral['tipo'],
            'comunidade' => $pastoral['comunidade_capelania'],
            'total_membros' => (int)$pastoral['total_membros'],
            'total_coordenadores' => 0, // Será calculado separadamente se necessário
            'dia_semana' => $pastoral['dia_semana'],
            'horario' => $pastoral['horario'],
            'created_at' => $pastoral['created_at']
        ];
    }, $pastorais);
    
    Response::success($pastoraisFormatadas);
    
} catch (Exception $e) {
    Response::error('Erro ao carregar pastorais: ' . $e->getMessage(), 500);
}
?>
