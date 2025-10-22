<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log para debug
$log_file = __DIR__ . '/_venda_debug.log';
file_put_contents($log_file, "--- REQUISIÇÃO INICIADA EM " . date('Y-m-d H:i:s') . " ---\n", FILE_APPEND);
file_put_contents($log_file, "DADOS RECEBIDOS: " . print_r($_POST, true) . "\n", FILE_APPEND);

header('Content-Type: application/json');

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Receber dados do formulário
$cliente_nome = trim($_POST['cliente_nome'] ?? '');
$cliente_telefone = trim($_POST['cliente_telefone'] ?? '');
$forma_pagamento = trim($_POST['forma_pagamento'] ?? '');
$desconto = floatval($_POST['desconto'] ?? 0);
$observacoes = trim($_POST['observacoes'] ?? '');
$itens = json_decode($_POST['itens'] ?? '[]', true);

// Validação básica
if (empty($forma_pagamento) || empty($itens)) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    file_put_contents($log_file, "1. Tentando conectar ao banco...\n", FILE_APPEND);
    // Conexão direta com PDO
    $pdo = getConnection();
    file_put_contents($log_file, "2. Conexão com banco OK.\n", FILE_APPEND);
    
    // Iniciar transação
    file_put_contents($log_file, "3. Iniciando transação...\n", FILE_APPEND);
    $pdo->beginTransaction();
    file_put_contents($log_file, "4. Transação iniciada.\n", FILE_APPEND);
    
    // Calcular totais
    $subtotal = 0;
    foreach ($itens as $item) {
        $subtotal += $item['preco'] * $item['quantidade'];
    }
    $total = $subtotal - $desconto;
    file_put_contents($log_file, "5. Totais calculados: Subtotal=$subtotal, Total=$total\n", FILE_APPEND);
    
    // Gerar número da venda
    $stmt = $pdo->query("SELECT MAX(id) as ultimo_id FROM lojinha_vendas");
    $ultimo_id = $stmt->fetch()['ultimo_id'] ?? 0;
    $numero_venda = 'V' . str_pad($ultimo_id + 1, 6, '0', STR_PAD_LEFT);
    file_put_contents($log_file, "6. Número da venda gerado: $numero_venda\n", FILE_APPEND);
    
    // Inserir venda
    file_put_contents($log_file, "7. Inserindo na tabela 'lojinha_vendas'...\n", FILE_APPEND);
    $stmt = $pdo->prepare("
        INSERT INTO lojinha_vendas 
        (numero_venda, cliente_nome, cliente_telefone, forma_pagamento, 
         subtotal, desconto, total, observacoes, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'finalizada')
    ");
    $stmt->execute([
        $numero_venda, 
        $cliente_nome ?: null, 
        $cliente_telefone ?: null, 
        $forma_pagamento, 
        $subtotal, 
        $desconto, 
        $total, 
        $observacoes ?: null
    ]);
    
    $venda_id = $pdo->lastInsertId();
    file_put_contents($log_file, "8. Venda inserida com ID: $venda_id\n", FILE_APPEND);
    
    // Inserir itens da venda e atualizar estoque
    $stmt_item = $pdo->prepare("
        INSERT INTO lojinha_vendas_itens 
        (venda_id, produto_id, quantidade, preco_unitario, subtotal) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt_estoque = $pdo->prepare("
        UPDATE lojinha_produtos 
        SET estoque_atual = estoque_atual - ? 
        WHERE id = ? AND estoque_atual >= ?
    ");
    
    $stmt_movimentacao = $pdo->prepare("
        INSERT INTO lojinha_estoque_movimentacoes 
        (produto_id, tipo, quantidade, motivo) 
        VALUES (?, 'saida', ?, ?)
    ");
    
    file_put_contents($log_file, "9. Processando itens da venda...\n", FILE_APPEND);
    foreach ($itens as $item) {
        // Inserir item da venda
        $item_subtotal = $item['preco'] * $item['quantidade'];
        $stmt_item->execute([
            $venda_id,
            $item['id'],
            $item['quantidade'],
            $item['preco'],
            $item_subtotal
        ]);
        
        // Atualizar estoque
        $stmt_estoque->execute([
            $item['quantidade'],
            $item['id'],
            $item['quantidade']
        ]);
        
        // Verificar se o estoque foi atualizado
        if ($stmt_estoque->rowCount() === 0) {
            throw new Exception("Estoque insuficiente para o produto: " . $item['nome']);
        }
        
        // Registrar movimentação de estoque
        $stmt_movimentacao->execute([
            $item['id'],
            $item['quantidade'],
            "Venda #{$numero_venda}"
        ]);
    }
    file_put_contents($log_file, "10. Itens processados com sucesso.\n", FILE_APPEND);
    
    // Commit da transação
    file_put_contents($log_file, "11. Comitando a transação...\n", FILE_APPEND);
    $pdo->commit();
    file_put_contents($log_file, "12. Transação comitada com sucesso.\n", FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'message' => 'Venda finalizada com sucesso!',
        'venda_id' => $venda_id,
        'numero_venda' => $numero_venda,
        'total' => $total
    ]);
    
} catch(Exception $e) {
    file_put_contents($log_file, "!!! ERRO CAPTURADO: " . $e->getMessage() . " na linha " . $e->getLine() . " !!!\n", FILE_APPEND);
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
        file_put_contents($log_file, "Transação revertida (rollback).\n", FILE_APPEND);
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao finalizar venda',
        'error' => $e->getMessage()
    ]);
}
file_put_contents($log_file, "--- REQUISIÇÃO FINALIZADA ---\n\n", FILE_APPEND);
?>
