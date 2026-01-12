<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Receber dados da venda
$dados = json_decode(file_get_contents('php://input'), true);



if (!$dados || !isset($dados['id_produto'])) {
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
    
    // 1. Verificar tem produto 
    $stmt = $db->prepare("SELECT id FROM cafe_produtos WHERE id = ?");
    $stmt->execute([$dados['id_produto']]);
    $produto = $stmt->fetch();

    if ($produto === false) {
        throw new Exception('Produto não encontrado');
    }
    
    // 2. Verifica se existe venda para esse produto 
    $stmt = $db->prepare("SELECT * FROM cafe_itens_venda WHERE id_produto = ?");
    $stmt->execute([$produto['id']]);
    $vendas = $stmt->fetch();
    
    if ($vendas === false) {
        // 3. executa a exclusão
        $stmt = $db->prepare("DELETE from cafe_produtos where id = ?");
        $stmt->execute([$produto['id']]);
        // Commit da transação
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Produto excluído com sucesso'
        ]);
    }else{
        // 4. Não pode excluir pois existe venda no sistema
        echo json_encode([
            'success' => false,
            'message' => 'Este Produto possui vendas, não é possível excluir'
        ]);
    }
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
