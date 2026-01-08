<?php
/**
 * Endpoint: Relatório - Membros sem Pastoral
 * Retorna contagem e lista de membros sem pastoral
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (ob_get_level()) {
    ob_clean();
}
ob_start();

try {
    require_once __DIR__ . '/../../../config/database.php';
    require_once __DIR__ . '/../../utils/Response.php';
    
    $db = new MembrosDatabase();
    
    // Contar membros sem pastoral
    $countQuery = "
        SELECT COUNT(*) as total
        FROM membros_membros m
        WHERE m.status != 'bloqueado'
            AND m.status IS NOT NULL
            AND m.id NOT IN (
                SELECT DISTINCT membro_id 
                FROM membros_membros_pastorais 
                WHERE status = 'ativo'
            )
    ";
    
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute();
    $total = $countStmt->fetch()['total'];
    
    // Listar membros sem pastoral (limitado a 20)
    $listQuery = "
        SELECT 
            m.id,
            m.nome_completo,
            m.email,
            m.status,
            m.created_at
        FROM membros_membros m
        WHERE m.status != 'bloqueado'
            AND m.status IS NOT NULL
            AND m.id NOT IN (
                SELECT DISTINCT membro_id 
                FROM membros_membros_pastorais 
                WHERE status = 'ativo'
            )
        ORDER BY m.nome_completo ASC
        LIMIT 20
    ";
    
    $listStmt = $db->prepare($listQuery);
    $listStmt->execute();
    $membros = $listStmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_end_clean();
    Response::success([
        'total' => (int)$total,
        'membros' => $membros,
        'mostrando' => count($membros)
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    error_log("Relatório membros sem pastoral error: " . $e->getMessage());
    Response::error('Erro ao gerar relatório: ' . $e->getMessage(), 500);
}
?>

