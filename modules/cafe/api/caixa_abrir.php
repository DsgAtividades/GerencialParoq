<?php
/**
 * API: Abrir Caixa
 * Abre um novo caixa se não houver nenhum aberto
 */
header('Content-Type: application/json');
require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';

$permissao = verificarPermissaoApi('abrir_caixa');

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
    // Receber dados
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['valor_troco_inicial'])) {
        throw new Exception('Valor de troco inicial é obrigatório');
    }
    
    $valor_troco = floatval($data['valor_troco_inicial']);
    $observacao = $data['observacao'] ?? null;
    
    if ($valor_troco < 0) {
        throw new Exception('Valor de troco não pode ser negativo');
    }
    
    // Verificar se já existe caixa aberto
    $stmt = $pdo->query("SELECT id FROM cafe_caixas WHERE status = 'aberto' LIMIT 1");
    if ($stmt->fetch()) {
        throw new Exception('Já existe um caixa aberto. Feche o caixa atual antes de abrir um novo.');
    }
    
    // Abrir novo caixa
    $stmt = $pdo->prepare("
        INSERT INTO cafe_caixas (
            valor_troco_inicial,
            usuario_abertura_id,
            usuario_abertura_nome,
            observacao_abertura,
            status
        ) VALUES (?, ?, ?, ?, 'aberto')
    ");
    
    $stmt->execute([
        $valor_troco,
        $_SESSION['usuario_id'],
        $_SESSION['usuario_nome'],
        $observacao
    ]);
    
    $caixa_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Caixa aberto com sucesso',
        'caixa_id' => $caixa_id
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao abrir caixa: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

