<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Receber dados da venda
$dados = json_decode(file_get_contents('php://input'), true);



if (!$dados || !isset($dados['pessoa_id']) || !isset($dados['itens']) || empty($dados['itens'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Dados inválidos'
    ]);
    exit;
}

// $dados = [
//     'pessoa_id' => 3, 
//     'itens' => [
//         'produto_id' => 1,
//         'quantidade' => 1,
//         'preco' => 5.00
//     ]
// ];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Iniciar transação
    $db->beginTransaction();
    
    // 1. Verificar saldo do cliente
    $stmt = $db->prepare("SELECT saldo FROM saldos_cartao WHERE id_pessoa = ? FOR UPDATE");
    $stmt->execute([$dados['pessoa_id']]);
    $saldo = $stmt->fetch(PDO::FETCH_COLUMN);
    
    if ($saldo === false) {
        throw new Exception('Cliente não possui saldo cadastrado');
    }
    
    // Calcular total da venda
    $total_venda = 0;
    foreach ($dados['itens'] as $item) {
        $total_venda += $item['preco'] * $item['quantidade'];
    }
    
    if ($total_venda > $saldo) {
        throw new Exception('Saldo insuficiente');
    }
    
    // 2. Verificar e atualizar estoque de cada item
    foreach ($dados['itens'] as $item) {
        // Verificar estoque
        $stmt = $db->prepare("SELECT estoque FROM produtos WHERE id = ? FOR UPDATE");
        $stmt->execute([$item['produto_id']]);
        $estoque = $stmt->fetch(PDO::FETCH_COLUMN);
        
        if ($estoque < $item['quantidade']) {
            throw new Exception('Estoque insuficiente para o produto ' . $item['nome_produto']);
        }
        
        // Atualizar estoque
        $stmt = $db->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id= ?");
        $stmt->execute([$item['quantidade'], $item['produto_id']]);
    }
    
    // 3. Registrar venda
    $stmt = $db->prepare("
        INSERT INTO vendas (id_pessoa, valor_total, data_venda) 
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([
        $dados['pessoa_id'],
        $total_venda
    ]);
    $id_venda = $db->lastInsertId();
    
    // 4. Registrar itens da venda
    $stmt = $db->prepare("
        INSERT INTO itens_venda (id_venda, id_produto, quantidade, valor_unitario)
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($dados['itens'] as $item) {
        $stmt->execute([
            $id_venda,
            $item['produto_id'],
            $item['quantidade'],
            $item['preco']
        ]);
    }
    
    // 5. Debitar saldo do cliente
    $stmt = $db->prepare("UPDATE saldos_cartao SET saldo = saldo - ? WHERE id_pessoa = ?");
    $stmt->execute([$total_venda, $dados['pessoa_id']]);
    
    // 6. Registrar no histórico
    $stmt = $db->prepare("
        INSERT INTO historico_saldo 
        (id_pessoa, tipo_operacao, valor, saldo_anterior, saldo_novo, motivo, data_operacao)
        VALUES (?, 'debito', ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $dados['pessoa_id'],
        $total_venda,
        $saldo,
        $saldo - $total_venda,
        'Compra - Venda #' . $id_venda
    ]);
    
    // Commit da transação
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Venda realizada com sucesso',
        'id_venda' => $id_venda,
        'total' => $total_venda
    ]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
