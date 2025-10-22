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
$input = json_decode(file_get_contents('php://input'), true);
$saldo_inicial = floatval($input['saldo_inicial'] ?? 0);

if ($saldo_inicial < 0) {
    echo json_encode(['success' => false, 'message' => 'Saldo inicial inválido']);
    exit;
}

try {
    // Conexão direta com PDO
    $pdo = getConnection();
    
    // Verificar se já existe caixa aberto hoje
    $stmt = $pdo->query("
        SELECT id FROM lojinha_caixa 
        WHERE DATE(data_abertura) = CURDATE() AND status = 'aberto'
    ");
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Já existe um caixa aberto hoje']);
        exit;
    }
    
    // Verificar se existe usuário válido
    $stmt_user = $pdo->query("SELECT id FROM users LIMIT 1");
    $usuario = $stmt_user->fetch();
    $usuario_id = $usuario ? $usuario['id'] : null;
    
    // Abrir novo caixa
    $stmt = $pdo->prepare("
        INSERT INTO lojinha_caixa 
        (saldo_inicial, saldo_atual, status, usuario_id, observacoes) 
        VALUES (?, ?, 'aberto', ?, 'Caixa aberto via sistema')
    ");
    
    $stmt->execute([$saldo_inicial, $saldo_inicial, $usuario_id]);
    $caixa_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Caixa aberto com sucesso!',
        'caixa_id' => $caixa_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao abrir caixa',
        'error' => $e->getMessage()
    ]);
}
?>
