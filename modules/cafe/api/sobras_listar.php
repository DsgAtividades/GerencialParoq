<?php
/**
 * API para listar sobras de um caixa específico
 */

// Desabilitar exibição de erros na saída
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../includes/conexao.php';
    require_once '../includes/verifica_permissao.php';
    
    $permissao = verificarPermissaoApi('visualizar_caixa');
    
    if(!isset($permissao['tem_permissao']) || $permissao['tem_permissao'] == 0){
        http_response_code(403);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Usuário sem permissão de acesso'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $caixa_id = isset($_GET['caixa_id']) ? (int)$_GET['caixa_id'] : 0;
    
    if (!$caixa_id) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'ID do caixa não informado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Buscar sobras do caixa
    $stmt = $pdo->prepare("
        SELECT 
            id,
            produto_nome,
            produto_valor_unitario,
            quantidade,
            valor_total_perdido,
            DATE_FORMAT(data_registro, '%d/%m/%Y %H:%i') as data_registro_formatada,
            usuario_nome,
            observacao
        FROM vw_cafe_caixas_sobras
        WHERE caixa_id = ?
        ORDER BY data_registro ASC
    ");
    $stmt->execute([$caixa_id]);
    $sobras = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular totais
    $total_produtos = count($sobras);
    $total_quantidade = 0;
    $total_valor_perdido = 0;
    
    foreach ($sobras as $sobra) {
        $total_quantidade += $sobra['quantidade'];
        $total_valor_perdido += $sobra['valor_total_perdido'];
    }
    
    echo json_encode([
        'sucesso' => true,
        'sobras' => $sobras,
        'resumo' => [
            'total_produtos' => $total_produtos,
            'total_quantidade' => $total_quantidade,
            'total_valor_perdido' => $total_valor_perdido
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    
} catch (PDOException $e) {
    error_log("Erro ao listar sobras: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao buscar sobras.'
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("Erro geral ao listar sobras: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao buscar sobras.'
    ], JSON_UNESCAPED_UNICODE);
}

