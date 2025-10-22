<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Receber dados do formulário
$produto_id = intval($_POST['produto_id'] ?? 0);
$tipo = trim($_POST['tipo'] ?? '');
$quantidade = intval($_POST['quantidade'] ?? 0);
$motivo = trim($_POST['motivo'] ?? '');
$produto_nome = trim($_POST['produto_nome'] ?? '');

// Validação básica
if (!$produto_id || !$tipo || !$quantidade || !$motivo) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

if (!in_array($tipo, ['entrada', 'saida', 'ajuste'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo de ajuste inválido']);
    exit;
}

try {
    $pdo = getConnection();

    // Obter estoque atual do produto
    $stmt = $pdo->prepare("SELECT estoque_atual, nome FROM lojinha_produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch();

    if (!$produto) {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
        exit;
    }

    $estoque_atual = intval($produto['estoque_atual']);
    $produto_nome = $produto['nome'];

    // Calcular novo estoque baseado no tipo
    switch ($tipo) {
        case 'entrada':
            $novo_estoque = $estoque_atual + $quantidade;
            $tipo_movimentacao = 'entrada';
            break;
        case 'saida':
            if ($estoque_atual < $quantidade) {
                echo json_encode(['success' => false, 'message' => 'Estoque insuficiente para saída']);
                exit;
            }
            $novo_estoque = $estoque_atual - $quantidade;
            $tipo_movimentacao = 'saida';
            break;
        case 'ajuste':
            $novo_estoque = $quantidade; // Para ajuste, a quantidade se torna o estoque final
            $tipo_movimentacao = 'ajuste';
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Tipo de ajuste inválido']);
            exit;
    }

    // Iniciar transação
    $pdo->beginTransaction();

    // Atualizar estoque do produto
    $stmt = $pdo->prepare("UPDATE lojinha_produtos SET estoque_atual = ? WHERE id = ?");
    $stmt->execute([$novo_estoque, $produto_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Erro ao atualizar estoque do produto");
    }

    // Registrar movimentação
    $stmt = $pdo->prepare("
        INSERT INTO lojinha_estoque_movimentacoes
        (produto_id, tipo, quantidade, motivo, usuario_id)
        VALUES (?, ?, ?, ?, 1)
    ");

    $stmt->execute([
        $produto_id,
        $tipo_movimentacao,
        abs($quantidade), // Sempre positivo na movimentação
        $motivo . " (Produto: $produto_nome)"
    ]);

    // Commit da transação
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Ajuste de estoque realizado com sucesso!',
        'produto' => $produto_nome,
        'tipo' => $tipo,
        'quantidade' => $quantidade,
        'estoque_anterior' => $estoque_atual,
        'estoque_novo' => $novo_estoque
    ]);

} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar ajuste de estoque',
        'error' => $e->getMessage()
    ]);
}
?>
