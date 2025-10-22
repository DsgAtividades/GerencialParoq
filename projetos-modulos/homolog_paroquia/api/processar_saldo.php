<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Receber dados da operação
$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados || !isset($dados['id_pessoa']) || !isset($dados['valor']) || !isset($dados['operacao']) || !isset($dados['motivo'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Dados inválidos'
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Iniciar transação
    $db->beginTransaction();
    
    // Verificar se a pessoa existe e pegar saldo atual
    $stmt = $db->prepare("SELECT p.nome, sc.saldo, sc.id_saldo 
                         FROM pessoas p 
                         LEFT JOIN saldos_cartao sc ON p.id_pessoa = sc.id_pessoa 
                         WHERE p.id_pessoa = ? 
                         FOR UPDATE");
    $stmt->execute([$dados['id_pessoa']]);
    $cliente = $stmt->fetch();
    
    if (!$cliente) {
        throw new Exception('Cliente não encontrado');
    }
    
    $valor = (float)$dados['valor'];
    $saldo_atual = (float)$cliente['saldo'];
    
    // Validar operação
    if ($dados['operacao'] === 'debito') {
        if ($valor > $saldo_atual) {
            throw new Exception('Saldo insuficiente para débito');
        }
        $novo_saldo = $saldo_atual - $valor;
    } else {
        $novo_saldo = $saldo_atual + $valor;
    }
    
    // Atualizar saldo
    if ($cliente['id_saldo']) {
        $stmt = $db->prepare("UPDATE saldos_cartao SET saldo = ? WHERE id_saldo = ?");
        $stmt->execute([$novo_saldo, $cliente['id_saldo']]);
    } else {
        $stmt = $db->prepare("INSERT INTO saldos_cartao (id_pessoa, saldo) VALUES (?, ?)");
        $stmt->execute([$dados['id_pessoa'], $novo_saldo]);
    }
    
    // Registrar no histórico
    $stmt = $db->prepare("
        INSERT INTO historico_saldo 
        (id_pessoa, tipo_operacao, valor, saldo_anterior, saldo_novo, motivo, data_operacao) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $dados['id_pessoa'],
        $dados['operacao'],
        $valor,
        $saldo_atual,
        $novo_saldo,
        $dados['motivo']
    ]);
    
    // Commit da transação
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Operação realizada com sucesso',
        'novo_saldo' => $novo_saldo
    ]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
