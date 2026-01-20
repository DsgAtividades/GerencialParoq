<?php
header('Content-Type: application/json');
require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';
require_once '../includes/funcoes.php';

$permissao = verificarPermissaoApi('api_finalizar_venda');

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
    
    // Verificar se tipo_venda foi enviado
    $tipo_venda = isset($data['tipo_venda']) ? $data['tipo_venda'] : null;
    if (empty($tipo_venda)) {
        throw new Exception('Tipo de pagamento não informado');
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Calcular total da venda e validar estoque
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
    
    // Formatar total da venda
    $total_venda = number_format($total_venda, 2, '.', '');
    if($total_venda >= (float)1000.00){
        $total_venda = str_replace(',', '',$total_venda);
    }
        
    // Registrar venda com tipo de pagamento e nome do atendente
    $nome_atendente = $_SESSION['usuario_nome'] ?? 'Sistema';
    $stmt = $pdo->prepare("
        INSERT INTO cafe_vendas (id_pessoa, valor_total, Tipo_venda, Atendente, data_venda)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$data['pessoa_id'], $total_venda, $tipo_venda, $nome_atendente]);
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
    
    // Preparar registro de venda para o log
    $reg_venda = 'Venda #' . $id_venda . ' (' . ucfirst($tipo_venda) . ')';

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
        'tipo_venda' => $tipo_venda,
        'valor_total' => number_format($total_venda, 2, ',', '.')
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
