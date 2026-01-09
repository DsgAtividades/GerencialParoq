<?php
header('Content-Type: application/json');
require_once 'config/database.php';

try {
    // Pegar dados da requisição
    $dados = json_decode(file_get_contents('php://input'), true);
    
    if (!$dados || !isset($dados['pessoa_id']) || !isset($dados['itens']) || empty($dados['itens'])) {
        throw new Exception('Dados inválidos');
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $db->beginTransaction();
    
    try {
        // Calcular total da venda
        $total_venda = 0;
        foreach ($dados['itens'] as $item) {
            $total_venda += $item['valor_unitario'] * $item['quantidade'];
        }

        // Verificar saldo do cliente
        $stmt = $db->prepare("SELECT s.saldo FROM saldos_cartao s WHERE s.pessoa_id = ?");
        $stmt->execute([$dados['pessoa_id']]);
        $saldo = $stmt->fetchColumn();

        if ($saldo === false) {
            throw new Exception("Cliente não possui saldo cadastrado");
        }

        if ($saldo < $total_venda) {
            throw new Exception("Saldo insuficiente. Saldo atual: R$ " . number_format($saldo, 2, ',', '.'));
        }

        // Inserir venda
        $stmt = $db->prepare("INSERT INTO vendas (pessoa_id, data_venda, valor_total, status) VALUES (?, NOW(), ?, 'concluida')");
        $stmt->execute([$dados['pessoa_id'], $total_venda]);
        $venda_id = $db->lastInsertId();
        
        // Inserir itens e atualizar estoque
        foreach ($dados['itens'] as $item) {
            // Verificar estoque
            $stmt = $db->prepare("SELECT estoque FROM produtos WHERE id = ?");
            $stmt->execute([$item['produto_id']]);
            $estoque_atual = $stmt->fetchColumn();
            
            if ($estoque_atual === false) {
                throw new Exception("Produto não encontrado");
            }
            
            if ($estoque_atual < $item['quantidade']) {
                throw new Exception("Estoque insuficiente para o produto ID {$item['produto_id']}");
            }
            
            // Inserir item da venda
            $subtotal = $item['valor_unitario'] * $item['quantidade'];
            $stmt = $db->prepare("INSERT INTO vendas_itens (venda_id, produto_id, quantidade, valor_unitario, subtotal) 
                                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $venda_id,
                $item['produto_id'],
                $item['quantidade'],
                $item['valor_unitario'],
                $subtotal
            ]);
            
            // Atualizar estoque
            $novo_estoque = $estoque_atual - $item['quantidade'];
            $stmt = $db->prepare("UPDATE produtos SET estoque = ? WHERE id = ?");
            $stmt->execute([$novo_estoque, $item['produto_id']]);
            
            // Registrar movimento de estoque
            $stmt = $db->prepare("INSERT INTO historico_estoque 
                                (produto_id, tipo, quantidade, estoque_anterior, estoque_atual, motivo) 
                                VALUES (?, 'saida', ?, ?, ?, ?)");
            $stmt->execute([
                $item['produto_id'],
                $item['quantidade'],
                $estoque_atual,
                $novo_estoque,
                "Venda #" . $venda_id
            ]);
        }

        // Debitar saldo do cliente
        $novo_saldo = $saldo - $total_venda;
        $stmt = $db->prepare("UPDATE saldos_cartao SET saldo = ? WHERE pessoa_id = ?");
        $stmt->execute([$novo_saldo, $dados['pessoa_id']]);

        // Registrar movimento no histórico de saldo
        $stmt = $db->prepare("INSERT INTO historico_saldo (pessoa_id, tipo, valor, motivo) 
                            VALUES (?, 'debito', ?, ?)");
        $stmt->execute([
            $dados['pessoa_id'],
            $total_venda,
            "Venda #" . $venda_id
        ]);
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Venda realizada com sucesso',
            'venda_id' => $venda_id,
            'novo_saldo' => $novo_saldo
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
