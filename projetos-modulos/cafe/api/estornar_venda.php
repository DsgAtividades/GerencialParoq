<?php
require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';
require_once '../includes/funcoes.php';

$permissao = verificarPermissaoApi('estornar_vendas');
header('Content-Type: application/json');

if($permissao == 0){
    echo json_encode([
        'success' => true,
        'message' => 'Usuário sem permissão de acesso'
    ]);
}

// Receber dados JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_venda']) || !is_numeric($data['id_venda'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID da venda inválido'
    ]);
    exit;
}

try {
    // Iniciar transação
    $pdo->beginTransaction();
    
    // 1. Buscar dados da venda
    $stmt = $pdo->prepare("
        SELECT v.*, p.nome as nome_pessoa
        FROM cafe_vendas v
        JOIN cafe_pessoas p ON v.id_pessoa = p.id_pessoa
        WHERE v.id_venda = ?
    ");
    $stmt->execute([$data['id_venda']]);
    $venda = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$venda) {
        throw new Exception('Venda não encontrada');
    }
    
    // 2. Buscar itens da venda
    $stmt = $pdo->prepare("
        SELECT iv.*, p.nome_produto
        FROM cafe_itens_venda iv
        JOIN cafe_produtos p ON iv.id_produto = p.id
        WHERE iv.id_venda = ?
    ");
    $stmt->execute([$data['id_venda']]);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($itens)) {
        throw new Exception('Nenhum item encontrado para esta venda');
    }
    
    // 3. Devolver produtos ao estoque
    $stmt_estoque = $pdo->prepare("
        UPDATE cafe_produtos 
        SET estoque = estoque + ?
        WHERE id = ?
    ");
    
    $stmt_historico = $pdo->prepare("
        INSERT INTO cafe_historico_estoque 
        (id_produto, tipo_operacao, quantidade, quantidade_anterior, motivo, data_operacao)
        SELECT 
            ?, 'entrada', ?, estoque, ?, NOW()
        FROM cafe_produtos 
        WHERE id = ?
    ");
    
    foreach ($itens as $item) {
        // Atualizar estoque
        $stmt_estoque->execute([$item['quantidade'], $item['id_produto']]);
        
        // Registrar no histórico de estoque
        $stmt_historico->execute([
            $item['id_produto'],
            $item['quantidade'],
            'Estorno - Venda #' . $venda['id_venda'],
            $item['id_produto']
        ]);
    }
    
    // 4. Devolver saldo ao cliente
    $stmt = $pdo->prepare("
        UPDATE cafe_saldos_cartao 
        SET saldo = saldo + ? 
        WHERE id_pessoa = ?
    ");
    $stmt->execute([$venda['valor_total'], $venda['id_pessoa']]);
    
    // 5. Registrar no histórico de saldo
    $stmt = $pdo->prepare("
        INSERT INTO cafe_historico_saldo 
        (id_pessoa, tipo_operacao, valor, saldo_anterior, saldo_novo, motivo, data_operacao)
        SELECT 
            ?, 'credito', ?, saldo - ?, ?, ?, NOW()
        FROM cafe_saldos_cartao
        WHERE id_pessoa = ?
    ");
    $stmt->execute([
        $venda['id_pessoa'],
        $venda['valor_total'],
        $venda['valor_total'],
        $venda['valor_total'],
        'Estorno - Venda #' . $venda['id_venda'],
        $venda['id_pessoa']
    ]);
    
    // 6. Excluir a venda (isso vai excluir os itens em cascata)
    $stmt = $pdo->prepare("UPDATE cafe_vendas SET estornada = 1 WHERE id_venda = ?");
    $stmt->execute([$data['id_venda']]);
    
    // Commit da transação
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Venda estornada com sucesso',
        'venda' => [
            'id_venda' => $venda['id_venda'],
            'valor_total' => $venda['valor_total'],
            'cliente' => $venda['nome_pessoa']
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
