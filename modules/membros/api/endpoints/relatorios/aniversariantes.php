<?php
/**
 * Endpoint: Relatório - Aniversariantes do Mês
 * Retorna membros que fazem aniversário no mês atual
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
    
    $query = "
        SELECT 
            m.id,
            m.nome_completo,
            m.data_nascimento,
            DAY(m.data_nascimento) as dia,
            TIMESTAMPDIFF(YEAR, m.data_nascimento, CURDATE()) as idade
        FROM membros_membros m
        WHERE m.status != 'bloqueado'
            AND m.data_nascimento IS NOT NULL
            AND MONTH(m.data_nascimento) = MONTH(CURDATE())
        ORDER BY dia ASC, m.nome_completo ASC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $aniversariantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_end_clean();
    Response::success([
        'total' => count($aniversariantes),
        'mes' => date('F', mktime(0, 0, 0, date('m'), 1)),
        'aniversariantes' => $aniversariantes
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    error_log("Relatório aniversariantes error: " . $e->getMessage());
    Response::error('Erro ao gerar relatório: ' . $e->getMessage(), 500);
}
?>

