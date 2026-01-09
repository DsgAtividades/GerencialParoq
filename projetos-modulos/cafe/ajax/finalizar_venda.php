<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

require_once '../config/config.php';

try {
    $pdo = getCafeConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cliente = trim($_POST['cliente'] ?? '');
        $forma_pagamento = $_POST['forma_pagamento'] ?? 'dinheiro';
        $desconto = floatval($_POST['desconto'] ?? 0);
        $itens = json_decode($_POST['itens'] ?? '[]', true);
        
        if (empty($itens)) {
            echo json_encode(['success' => false, 'message' => 'Carrinho vazio']);
            exit;
        }
        
        $pdo->beginTransaction();
        
        // Calcular totais
        $subtotal = 0;
        foreach ($itens as $item) {
            $subtotal += floatval($item['preco']) * intval($item['quantidade']);
        }
        $total = $subtotal - $desconto;
        
        // Gerar número da venda
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM cafe_vendas WHERE DATE(data_venda) = CURDATE()");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $numero_venda = 'V' . date('Ymd') . str_pad($result['total'] + 1, 4, '0', STR_PAD_LEFT);
        
        // Inserir venda
        $stmt = $pdo->prepare("
            INSERT INTO cafe_vendas 
            (numero_venda, vendedor_id, cliente_nome, subtotal, desconto, total, forma_pagamento, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'finalizada')
        ");
        $stmt->execute([
            $numero_venda,
            $_SESSION['module_user_id'],
            $cliente ?: null,
            $subtotal,
            $desconto,
            $total,
            $forma_pagamento
        ]);
        $venda_id = $pdo->lastInsertId();
        
        // Inserir itens e atualizar estoque
        foreach ($itens as $item) {
            $produto_id = intval($item['id']);
            $quantidade = intval($item['quantidade']);
            $preco_unitario = floatval($item['preco']);
            $subtotal_item = $preco_unitario * $quantidade;
            
            // Inserir item
            $stmt = $pdo->prepare("
                INSERT INTO cafe_vendas_itens 
                (venda_id, produto_id, quantidade, preco_unitario, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$venda_id, $produto_id, $quantidade, $preco_unitario, $subtotal_item]);
            
            // Atualizar estoque
            $stmt = $pdo->prepare("
                UPDATE cafe_produtos 
                SET estoque_atual = estoque_atual - ?
                WHERE id = ?
            ");
            $stmt->execute([$quantidade, $produto_id]);
            
            // Registrar movimentação
            $stmt = $pdo->prepare("
                INSERT INTO cafe_estoque_movimentacoes 
                (produto_id, tipo, quantidade, motivo, usuario_id, venda_id)
                VALUES (?, 'saida', ?, ?, ?, ?)
            ");
            $stmt->execute([
                $produto_id,
                $quantidade,
                "Venda #{$numero_venda}",
                $_SESSION['module_user_id'],
                $venda_id
            ]);
        }
        
        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Venda finalizada com sucesso',
            'venda_id' => $venda_id,
            'numero_venda' => $numero_venda
        ]);
    }
    
} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro em finalizar_venda.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao finalizar venda']);
}
?>
