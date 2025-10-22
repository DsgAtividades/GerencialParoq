<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Receber dados do formulário
$codigo = trim($_POST['codigo'] ?? '');
$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$categoria_id = $_POST['categoria_id'] ?? null;
$fornecedor = trim($_POST['fornecedor'] ?? ''); // Agora é texto, não ID
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
    $pdo = getConnection();
    
    // Verificar se código já existe
    $stmt = $pdo->prepare("SELECT id FROM lojinha_produtos WHERE codigo = ?");
    $stmt->execute([$codigo]);
    $codigo_existe = $stmt->fetch();
    if ($codigo_existe) {
        echo json_encode(['success' => false, 'message' => 'Código já existe']);
        exit;
    }
    
    // Inserir produto
    $stmt = $pdo->prepare("
        INSERT INTO lojinha_produtos 
        (codigo, nome, descricao, categoria_id, fornecedor, preco_compra, preco_venda, 
         estoque_atual, estoque_minimo, tipo_liturgico, ativo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    $result = $stmt->execute([
        $codigo, $nome, $descricao, $categoria_id, 
        $fornecedor ?: null, $preco_compra, $preco_venda, 
        $estoque_atual, $estoque_minimo, $tipo_liturgico ?: null
    ]);
    
    if ($result) {
        $produto_id = $pdo->lastInsertId();
        
        // Registrar movimentação de estoque se houver estoque inicial
        if ($estoque_atual > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO lojinha_estoque_movimentacoes 
                (produto_id, tipo, quantidade, motivo, usuario_id) 
                VALUES (?, 'entrada', ?, 'Estoque inicial', 1)
            ");
            $stmt->execute([$produto_id, $estoque_atual]);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Produto salvo com sucesso!',
            'produto_id' => $produto_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar produto']);
    }
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor',
        'error' => $e->getMessage()
    ]);
}
?>