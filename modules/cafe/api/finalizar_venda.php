<?php
header('Content-Type: application/json');
require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';
require_once '../includes/funcoes.php';

$permissao = verificarPermissaoApi('finalizar_venda');

if(!isset($permissao['tem_permissao']) || $permissao['tem_permissao'] == 0){
    echo json_encode([
        'success' => false,
        'message' => 'Usuário sem permissão de acesso'
    ]);
    exit;
}

// Verificar método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Receber dados da venda
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['pessoa_id']) || !isset($data['itens']) || empty($data['itens'])) {
        throw new Exception('Dados da venda incompletos');
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Verificar saldo do cliente
    $stmt = $pdo->prepare("SELECT saldo FROM cafe_saldos_cartao WHERE id_pessoa = ?");
    $stmt->execute([$data['pessoa_id']]);
    $saldo = $stmt->fetchColumn();
    
    if ($saldo === false) {
        $saldo = 0; // Se não tiver registro, considera saldo zero
    }
    
    // Calcular total da venda
    $total_venda = 0;
    foreach ($data['itens'] as $item) {
        if (!isset($item['id_produto']) || !isset($item['quantidade'])) {
            throw new Exception('Dados do item incompletos');
        }
        
        // Buscar preço atual do produto
        $stmt = $pdo->prepare("SELECT preco, estoque FROM cafe_produtos WHERE id = ?");
        $stmt->execute([$item['id_produto']]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$produto) {
            throw new Exception('Produto não encontrado: ' . $item['id_produto']);
        }
        
        if ($produto['estoque'] < $item['quantidade']) {
            throw new Exception('Estoque insuficiente para o produto: ' . $item['id_produto']);
        }
        
        $total_venda += $item['quantidade'] * $produto['preco'];
    }
    
    if ($total_venda > $saldo) {
        throw new Exception('Saldo insuficiente. Saldo atual: R$ ' . number_format($saldo, 2, ',', '.') . 
                          '. Total da venda: R$ ' . number_format($total_venda, 2, ',', '.'));
    }
    $total_venda = number_format($total_venda, 2, '.', '');
    if($total_venda >= (float)1000.00){
        $total_venda = str_replace(',', '',$total_venda);
    }
        
    
    // Registrar venda
    $stmt = $pdo->prepare("
        INSERT INTO cafe_vendas (id_pessoa, valor_total, data_venda)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$data['pessoa_id'], $total_venda]);
    $id_venda = $pdo->lastInsertId();
    
    // Registrar itens da venda e atualizar estoque
    $stmt_item = $pdo->prepare("
        INSERT INTO cafe_itens_venda (id_venda, id_produto, quantidade, valor_unitario)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt_estoque = $pdo->prepare("
        UPDATE cafe_produtos 
        SET estoque = estoque - ? 
        WHERE id = ?
    ");
    
    foreach ($data['itens'] as $item) {
        // Buscar preço atual do produto
        $stmt = $pdo->prepare("SELECT preco FROM cafe_produtos WHERE id = ?");
        $stmt->execute([$item['id_produto']]);
        $preco = $stmt->fetchColumn();
        
        // Registrar item
        $stmt_item->execute([
            $id_venda,
            $item['id_produto'],
            $item['quantidade'],
            $preco
        ]);
        
        // Atualizar estoque
        $stmt_estoque->execute([
            $item['quantidade'],
            $item['id_produto']
        ]);
    }
    
    $saldoAtual = number_format(($saldo - $total_venda), 2);
    $saldoAtual2 = str_replace(',', '',$saldoAtual);
 
    #echo ' Saldo Atual ' . $saldoAtual . ' Saldo Novo ' . $saldoAtual2;
     // Atualizar saldo do cliente
    $stmt = $pdo->prepare("
        UPDATE cafe_saldos_cartao SET saldo = ? WHERE id_pessoa = ? 
    ");
    $stmt->execute([$saldoAtual2, $data['pessoa_id']]);
    
     // Registrar movimentação no histórico
    $stmt = $pdo->prepare("
        INSERT INTO cafe_historico_saldo 
        (id_pessoa, valor, tipo_operacao, saldo_anterior, saldo_novo, motivo, data_operacao)
        VALUES (?, ?, 'debito', ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $data['pessoa_id'],
        $total_venda,
        $saldo,
        $saldoAtual2,
        'Venda #' . $id_venda
    ]);
    
    // Buscar novo saldo
    $stmt = $pdo->prepare("SELECT saldo FROM cafe_saldos_cartao WHERE id_pessoa = ?");
    $stmt->execute([$data['pessoa_id']]);
    $novo_saldo = $stmt->fetchColumn();
    $reg_venda = 'Venda #' . $id_venda;

    // Buscar codigo_cartao
    $stmt = $pdo->prepare("SELECT codigo FROM cafe_cartoes WHERE id_pessoa = ? and usado = 1");
    $stmt->execute([$data['pessoa_id']]);
    $codigo_cartao = $stmt->fetchColumn();
    

    //Registra os logs do sistema
    $stmt = $pdo->prepare("
        INSERT INTO cafe_historico_transacoes_sistema 
        (nome_usuario, grupo_usuario, tipo, tipo_transacao, valor, id_pessoa, cartao)
        VALUES (?, ?, ?, 'débito', ?, ?, ?)
        ");
    $stmt->execute([$_SESSION['usuario_nome'],$_SESSION['usuario_grupo'],$reg_venda, $total_venda, $data['pessoa_id'], $codigo_cartao]);
    
    // Commit da transação
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Venda finalizada com sucesso',
        'id_venda' => $id_venda,
        'novo_saldo' => number_format($novo_saldo, 2, ',', '.')
    ]);
    
} catch (Exception $e) {
    if (isset($pdo)) {
       // $pdo->rollBack();
    }
    error_log("Erro ao finalizar venda: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
