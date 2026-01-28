<?php
require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';

header('Content-Type: application/json');

verificarPermissaoApi('visualizar_caixa');

$caixa_id = $_GET['caixa_id'] ?? null;

if (!$caixa_id) {
    echo json_encode(['success' => false, 'message' => 'ID do caixa não fornecido']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            v.id_venda,
            v.valor_total,
            v.Tipo_venda as tipo_venda,
            v.data_venda,
            DATE_FORMAT(v.data_venda, '%d/%m/%Y %H:%i') as data_venda_formatada,
            COALESCE(v.Atendente, 'Sistema') as atendente,
            FORMAT(v.valor_total, 2, 'pt_BR') as valor_formatado
        FROM cafe_vendas v
        WHERE v.caixa_id = ?
            AND (v.estornada IS NULL OR v.estornada = 0)
        ORDER BY v.data_venda DESC
    ");
    $stmt->execute([$caixa_id]);
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'vendas' => $vendas
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao buscar vendas do caixa: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar vendas: ' . $e->getMessage()
    ]);
}



