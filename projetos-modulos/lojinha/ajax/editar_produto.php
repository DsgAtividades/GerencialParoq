<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$id = intval($_GET['id']);

// Receber dados do formulário
$codigo = trim($_POST['codigo'] ?? '');
$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$categoria_id = $_POST['categoria_id'] ?? null;
$fornecedor = trim($_POST['fornecedor'] ?? '');
$preco_compra = floatval($_POST['preco_compra'] ?? 0);
$preco_venda = floatval($_POST['preco_venda'] ?? 0);
$estoque_atual = intval($_POST['estoque_atual'] ?? 0);
$estoque_minimo = intval($_POST['estoque_minimo'] ?? 0);
$tipo_liturgico = trim($_POST['tipo_liturgico'] ?? '');

// Validação básica
if (empty($codigo) || empty($nome) || empty($categoria_id) || $preco_compra < 0 || $preco_venda < 0) {
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios não preenchidos']);
    exit;
}

try {
    // Conexão direta com PDO
    $pdo = getConnection();
    
    // Verificar se código já existe em outro produto
    $stmt = $pdo->prepare("SELECT id FROM lojinha_produtos WHERE codigo = ? AND id != ?");
    $stmt->execute([$codigo, $id]);
    $codigo_existe = $stmt->fetch();
    if ($codigo_existe) {
        echo json_encode(['success' => false, 'message' => 'Código já existe em outro produto']);
        exit;
    }
    
    // Buscar estoque anterior para registrar movimentação se necessário
    $stmt = $pdo->prepare("SELECT estoque_atual FROM lojinha_produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produto_anterior = $stmt->fetch();
    $estoque_anterior = $produto_anterior['estoque_atual'] ?? 0;
    
    // Atualizar produto
    $stmt = $pdo->prepare("
        UPDATE lojinha_produtos SET
            codigo = ?,
            nome = ?,
            descricao = ?,
            categoria_id = ?,
            fornecedor = ?,
            preco_compra = ?,
            preco_venda = ?,
            estoque_atual = ?,
            estoque_minimo = ?,
            tipo_liturgico = ?
        WHERE id = ?
    ");
    
    $result = $stmt->execute([
        $codigo, $nome, $descricao, $categoria_id, 
        $fornecedor ?: null, $preco_compra, $preco_venda, 
        $estoque_atual, $estoque_minimo, $tipo_liturgico ?: null,
        $id
    ]);
    
    if ($result) {
        // Registrar movimentação de estoque se houver alteração
        $diferenca = $estoque_atual - $estoque_anterior;
        if ($diferenca != 0) {
            $tipo = $diferenca > 0 ? 'entrada' : 'saida';
            $quantidade = abs($diferenca);
            
            $stmt = $pdo->prepare("
                INSERT INTO lojinha_estoque_movimentacoes 
                (produto_id, tipo, quantidade, motivo, usuario_id) 
                VALUES (?, ?, ?, 'Ajuste manual via edição', 1)
            ");
            $stmt->execute([$id, $tipo, $quantidade]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Produto atualizado com sucesso!'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar produto']);
    }
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor',
        'error' => $e->getMessage()
    ]);
}
?>
