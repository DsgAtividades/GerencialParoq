<?php
// Iniciar buffer de saída para capturar qualquer output inesperado
ob_start();

// Desabilitar exibição de erros para não quebrar JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Limpar qualquer output anterior
ob_clean();

// Definir header JSON primeiro
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';

try {
    $permissao = verificarPermissaoApi('visualizar_caixa');
    if (!isset($permissao['tem_permissao']) || $permissao['tem_permissao'] == 0) {
        ob_clean();
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Sem permissão para acessar esta funcionalidade'
        ], JSON_UNESCAPED_UNICODE);
        ob_end_flush();
        exit;
    }
} catch (Exception $e) {
    ob_clean();
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao verificar permissão: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    ob_end_flush();
    exit;
}

$caixa_id = $_GET['id'] ?? null;

if (!$caixa_id) {
    ob_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'ID do caixa não fornecido'
    ], JSON_UNESCAPED_UNICODE);
    ob_end_flush();
    exit;
}

try {
    // Buscar informações do caixa
    $stmt = $pdo->prepare("
        SELECT 
            *,
            DATE_FORMAT(data_abertura, '%d/%m/%Y %H:%i') as data_abertura_formatada,
            DATE_FORMAT(data_fechamento, '%d/%m/%Y %H:%i') as data_fechamento_formatada
        FROM vw_cafe_caixas_resumo WHERE id = ?
    ");
    $stmt->execute([$caixa_id]);
    $caixa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$caixa) {
        throw new Exception('Caixa não encontrado');
    }
    
    // Buscar vendas do caixa com detalhes
    $stmt = $pdo->prepare("
        SELECT 
            v.id_venda,
            v.valor_total,
            v.Tipo_venda as tipo_venda,
            COALESCE(v.Atendente, 'Sistema') as atendente,
            v.data_venda,
            DATE_FORMAT(v.data_venda, '%d/%m/%Y %H:%i') as data_venda_formatada,
            FORMAT(v.valor_total, 2, 'pt_BR') as valor_formatado,
            COUNT(iv.id_item) as total_itens
        FROM cafe_vendas v
        LEFT JOIN cafe_itens_venda iv ON v.id_venda = iv.id_venda
        WHERE v.caixa_id = ?
            AND (v.estornada IS NULL OR v.estornada = 0)
        GROUP BY v.id_venda, v.valor_total, v.Tipo_venda, v.Atendente, v.data_venda
        ORDER BY v.data_venda DESC
    ");
    $stmt->execute([$caixa_id]);
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Para cada venda, buscar os itens
    foreach ($vendas as &$venda) {
        $stmt = $pdo->prepare("
            SELECT 
                iv.id_item,
                iv.quantidade,
                iv.valor_unitario,
                p.nome_produto,
                FORMAT(iv.valor_unitario, 2, 'pt_BR') as valor_unitario_formatado,
                FORMAT(iv.quantidade * iv.valor_unitario, 2, 'pt_BR') as subtotal_formatado
            FROM cafe_itens_venda iv
            INNER JOIN cafe_produtos p ON iv.id_produto = p.id
            WHERE iv.id_venda = ?
            ORDER BY iv.id_item
        ");
        $stmt->execute([$venda['id_venda']]);
        $venda['itens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    
    // Calcular resumo de sobras
    $total_sobras_produtos = count($sobras);
    $total_sobras_quantidade = 0;
    $total_sobras_valor_perdido = 0;
    
    foreach ($sobras as $sobra) {
        $total_sobras_quantidade += $sobra['quantidade'];
        $total_sobras_valor_perdido += $sobra['valor_total_perdido'];
    }
    
    // Garantir que total_pix e total_cortesia existam no array
    // Se a view não tiver essas colunas, calcular diretamente do banco
    if (!isset($caixa['total_pix'])) {
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(valor_total), 0) as total_pix
            FROM cafe_vendas 
            WHERE caixa_id = ? 
              AND (estornada IS NULL OR estornada = 0)
              AND LOWER(TRIM(Tipo_venda)) = 'pix'
        ");
        $stmt->execute([$caixa_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $caixa['total_pix'] = $resultado['total_pix'] ?? 0;
    }
    if (!isset($caixa['total_cortesia'])) {
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(valor_total), 0) as total_cortesia
            FROM cafe_vendas 
            WHERE caixa_id = ? 
              AND (estornada IS NULL OR estornada = 0)
              AND LOWER(TRIM(Tipo_venda)) = 'cortesia'
        ");
        $stmt->execute([$caixa_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $caixa['total_cortesia'] = $resultado['total_cortesia'] ?? 0;
    }
    
    // Limpar buffer antes de enviar JSON
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'caixa' => $caixa,
        'vendas' => $vendas,
        'sobras' => $sobras,
        'resumo_sobras' => [
            'total_produtos' => $total_sobras_produtos,
            'total_quantidade' => $total_sobras_quantidade,
            'total_valor_perdido' => $total_sobras_valor_perdido
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    
    // Enviar output e desabilitar buffer
    ob_end_flush();
    
} catch (PDOException $e) {
    ob_clean();
    error_log("Erro PDO ao buscar detalhes do caixa: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar detalhes do caixa: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    ob_end_flush();
} catch (Exception $e) {
    ob_clean();
    error_log("Erro ao buscar detalhes do caixa: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar detalhes: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    ob_end_flush();
}

