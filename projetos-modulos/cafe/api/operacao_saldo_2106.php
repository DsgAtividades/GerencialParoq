<?php
header('Content-Type: application/json');

require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';
require_once '../includes/funcoes.php';

$permissao = verificarPermissaoApi('operacao_saldo');

if($permissao == 0){
    echo json_encode([
        'success' => false,
        'message' => 'Usuário sem permissão de acesso'
    ]);
}

// Verificar método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}


// Receber dados da requisição
$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados || !isset($dados['id_pessoa']) || !isset($dados['valor']) || !isset($dados['tipo']) || !isset($dados['motivo'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

try {
    // Iniciar transação
    $pdo->beginTransaction();

    // Buscar saldo atual
    $stmt = $pdo->prepare("SELECT sc.* FROM saldos_cartao sc WHERE id_pessoa = ?");
    $stmt->execute([$dados['id_pessoa']]);
    $saldo_atual = $stmt->fetch();

    $valor = floatval($dados['valor']);
    
    // Se for débito, converter para negativo
    if ($dados['tipo'] === 'debito') {
        $valor = -$valor;
    }

    // Se não existe saldo, criar
    if (!$saldo_atual) {
        if ($valor < 0) {
            throw new Exception('Saldo insuficiente');
        }

        $stmt = $pdo->prepare("INSERT INTO saldos_cartao (id_pessoa, saldo) VALUES (?, ?)");
        $stmt->execute([$dados['id_pessoa'], $valor]);
        $id_saldo = $pdo->lastInsertId();
        $saldo_anterior = 0;
        $novo_saldo = $valor;
    } else {
        $novo_saldo = $saldo_atual['saldo'] + $valor;
        
        // Verificar se há saldo suficiente para débito
        if ($novo_saldo < 0) {
            throw new Exception('Saldo insuficiente');
        }

        $stmt = $pdo->prepare("UPDATE saldos_cartao SET saldo = ? WHERE id_saldo = ?");
        $stmt->execute([$novo_saldo, $saldo_atual['id_saldo']]);
        $id_saldo = $saldo_atual['id_saldo'];
        $saldo_anterior = $saldo_atual['saldo'];
    }

    // Registrar operação no histórico
    $stmt = $pdo->prepare("
        INSERT INTO historico_saldo 
        (id_pessoa, tipo_operacao, valor, saldo_anterior, saldo_novo, motivo, data_operacao) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $dados['id_pessoa'],
        $dados['tipo'],
        abs($valor), // Sempre armazenar valor positivo
        $saldo_anterior,
        $novo_saldo,
        $dados['motivo']
    ]);

    // Commit da transação
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'novo_saldo' => $novo_saldo,
        'message' => 'Operação realizada com sucesso'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
