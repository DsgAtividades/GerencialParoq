<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Receber dados
$tipo = trim($_POST['tipo'] ?? '');
$valor = floatval($_POST['valor'] ?? 0);
$descricao = trim($_POST['descricao'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');

// Validação básica
if (empty($tipo) || $valor <= 0 || empty($descricao)) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

if (!in_array($tipo, ['entrada', 'saida'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo de movimentação inválido']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Verificar se existe caixa aberto
    $stmt = $pdo->query("
        SELECT id, saldo_atual 
        FROM lojinha_caixa 
        WHERE DATE(data_abertura) = CURDATE() AND status = 'aberto'
        ORDER BY data_abertura DESC
        LIMIT 1
    ");
    
    $caixa = $stmt->fetch();
    if (!$caixa) {
        echo json_encode(['success' => false, 'message' => 'Nenhum caixa aberto encontrado']);
        exit;
    }
    
    // Calcular novo saldo
    $novo_saldo = $caixa['saldo_atual'];
    if ($tipo === 'entrada') {
        $novo_saldo += $valor;
    } else {
        $novo_saldo -= $valor;
    }
    
    // Verificar se não ficará negativo
    if ($novo_saldo < 0) {
        echo json_encode(['success' => false, 'message' => 'Saldo insuficiente para esta operação']);
        exit;
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    try {
        // Atualizar saldo do caixa
        $stmt = $pdo->prepare("
            UPDATE lojinha_caixa 
            SET saldo_atual = ? 
            WHERE id = ?
        ");
        $stmt->execute([$novo_saldo, $caixa['id']]);
        
        // Inserir movimentação
        $stmt = $pdo->prepare("
            INSERT INTO lojinha_caixa_movimentacoes 
            (caixa_id, tipo, valor, descricao, categoria, usuario_id) 
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$caixa['id'], $tipo, $valor, $descricao, $categoria]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Movimentação registrada com sucesso!',
            'novo_saldo' => $novo_saldo
        ]);
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar movimentação',
        'error' => $e->getMessage()
    ]);
}
?>

