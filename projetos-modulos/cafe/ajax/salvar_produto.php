<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

require_once '../config/config.php';

try {
    $pdo = getCafeConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? null;
        $codigo = trim($_POST['codigo'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $preco_venda = floatval($_POST['preco_venda'] ?? 0);
        $estoque_atual = intval($_POST['estoque_atual'] ?? 0);
        $estoque_minimo = intval($_POST['estoque_minimo'] ?? 0);
        $unidade_medida = $_POST['unidade_medida'] ?? 'unidade';
        $ativo = intval($_POST['ativo'] ?? 1);
        
        if (empty($codigo) || empty($nome) || $preco_venda <= 0) {
            echo json_encode(['success' => false, 'message' => 'Campos obrigatórios não preenchidos']);
            exit;
        }
        
        $pdo->beginTransaction();
        
        if ($id) {
            // Atualizar
            $stmt = $pdo->prepare("
                UPDATE cafe_produtos 
                SET codigo = ?, nome = ?, descricao = ?, categoria = ?, 
                    preco_venda = ?, estoque_atual = ?, estoque_minimo = ?, 
                    unidade_medida = ?, ativo = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $codigo, $nome, $descricao, $categoria, $preco_venda,
                $estoque_atual, $estoque_minimo, $unidade_medida, $ativo, $id
            ]);
        } else {
            // Inserir
            $stmt = $pdo->prepare("
                INSERT INTO cafe_produtos 
                (codigo, nome, descricao, categoria, preco_venda, estoque_atual, 
                 estoque_minimo, unidade_medida, ativo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $codigo, $nome, $descricao, $categoria, $preco_venda,
                $estoque_atual, $estoque_minimo, $unidade_medida, $ativo
            ]);
            $id = $pdo->lastInsertId();
            
            // Registrar movimentação inicial se houver estoque
            if ($estoque_atual > 0) {
                $stmtMov = $pdo->prepare("
                    INSERT INTO cafe_estoque_movimentacoes 
                    (produto_id, tipo, quantidade, motivo, usuario_id)
                    VALUES (?, 'entrada', ?, 'Estoque inicial', ?)
                ");
                $stmtMov->execute([$id, $estoque_atual, $_SESSION['module_user_id']]);
            }
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Produto salvo com sucesso', 'id' => $id]);
    }
    
} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro em salvar_produto.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar produto']);
}
?>
