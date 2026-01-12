<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    // Pegar dados da requisição
    $dados = json_decode(file_get_contents('php://input'), true);
    
    if (!$dados || !isset($dados['produto_id']) || !isset($dados['tipo']) || !isset($dados['quantidade'])) {
        throw new Exception('Dados inválidos');
    }
    
    $produto_id = (int)$dados['produto_id'];
    $tipo = trim($dados['tipo']);
    $quantidade = (int)$dados['quantidade'];
    $motivo = trim($dados['motivo'] ?? '');
    
    if ($produto_id <= 0) {
        throw new Exception('Produto inválido');
    }
    
    if ($quantidade <= 0) {
        throw new Exception('A quantidade deve ser maior que zero');
    }
    
    if (!in_array($tipo, ['entrada', 'saida', 'ajuste'])) {
        throw new Exception('Tipo de operação inválido');
    }
    
    if (empty($motivo)) {
        throw new Exception('Informe o motivo do ajuste');
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Buscar produto
    $stmt = $db->prepare("SELECT id, nome, estoque FROM cafe_produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$produto) {
        throw new Exception('Produto não encontrado');
    }
    
    // Calcular novo estoque
    $estoque_anterior = $produto['estoque'];
    $novo_estoque = $estoque_anterior;
    
    if ($tipo === 'entrada') {
        $novo_estoque = $estoque_anterior + $quantidade;
    } elseif ($tipo === 'saida') {
        $novo_estoque = $estoque_anterior - $quantidade;
    } elseif ($tipo === 'ajuste') {
        $novo_estoque = $quantidade;
    }
    
    if ($novo_estoque < 0) {
        throw new Exception('O estoque não pode ficar negativo');
    }
    
    // Iniciar transação
    $db->beginTransaction();
    
    try {
        // Atualizar estoque
        $stmt = $db->prepare("UPDATE cafe_produtos SET estoque = ? WHERE id = ?");
        $stmt->execute([$novo_estoque, $produto_id]);
        
        // Registrar histórico
        $stmt = $db->prepare("
            INSERT INTO cafe_historico_estoque 
            (produto_id, tipo, quantidade, estoque_anterior, estoque_atual, motivo, data_movimento) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $produto_id,
            $tipo,
            $quantidade,
            $estoque_anterior,
            $novo_estoque,
            $motivo
        ]);
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Estoque atualizado com sucesso',
            'novo_estoque' => $novo_estoque
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
