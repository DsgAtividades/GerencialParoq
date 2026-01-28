<?php
/**
 * API para remover sobras de produtos já registradas
 * Restaura o estoque dos produtos
 */

// Desabilitar exibição de erros na saída
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../includes/conexao.php';
    require_once '../includes/verifica_permissao.php';
    
    $permissao = verificarPermissaoApi('fechar_caixa');
    
    if(!isset($permissao['tem_permissao']) || $permissao['tem_permissao'] == 0){
        http_response_code(403);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Usuário sem permissão de acesso'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Método não permitido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Validar dados recebidos
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['sobra_id'])) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'ID da sobra não informado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $sobra_id = (int)$input['sobra_id'];
    
    // Buscar informações da sobra
    $stmt = $pdo->prepare("
        SELECT 
            s.id,
            s.caixa_id,
            s.produto_id,
            s.quantidade,
            p.nome_produto,
            p.preco,
            c.status as caixa_status
        FROM cafe_caixas_sobras s
        JOIN cafe_produtos p ON s.produto_id = p.id
        JOIN cafe_caixas c ON s.caixa_id = c.id
        WHERE s.id = ?
    ");
    $stmt->execute([$sobra_id]);
    $sobra = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sobra) {
        http_response_code(404);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Sobra não encontrada.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar se o caixa ainda está aberto (só pode remover se estiver aberto)
    if ($sobra['caixa_status'] !== 'aberto') {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Não é possível remover sobras de um caixa já fechado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Restaurar o estoque do produto
    $stmt = $pdo->prepare("
        UPDATE cafe_produtos 
        SET estoque = estoque + ? 
        WHERE id = ?
    ");
    $stmt->execute([$sobra['quantidade'], $sobra['produto_id']]);
    
    // Registrar no histórico de transações do sistema
    $stmt = $pdo->prepare("
        INSERT INTO cafe_historico_transacoes_sistema 
        (nome_usuario, grupo_usuario, tipo, tipo_transacao, valor, id_pessoa, cartao)
        VALUES (?, ?, ?, ?, ?, NULL, NULL)
    ");
    $stmt->execute([
        $_SESSION['usuario_nome'] ?? 'Sistema',
        $_SESSION['usuario_grupo'] ?? 'Sistema',
        'sobra_removida',
        "Sobra removida: {$sobra['nome_produto']} - Qtd: {$sobra['quantidade']}",
        $sobra['quantidade'] * $sobra['preco']
    ]);
    
    // Remover a sobra
    $stmt = $pdo->prepare("DELETE FROM cafe_caixas_sobras WHERE id = ?");
    $stmt->execute([$sobra_id]);
    
    // Verificar se ainda há sobras para este caixa
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cafe_caixas_sobras WHERE caixa_id = ?");
    $stmt->execute([$sobra['caixa_id']]);
    $totalSobras = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Se não houver mais sobras, resetar o flag
    if ($totalSobras == 0) {
        $stmt = $pdo->prepare("UPDATE cafe_caixas SET sobras_registradas = 0 WHERE id = ?");
        $stmt->execute([$sobra['caixa_id']]);
    }
    
    // Commit da transação
    $pdo->commit();
    
    echo json_encode([
        'sucesso' => true,
        'mensagem' => "Sobra removida com sucesso! Estoque restaurado.",
        'dados' => [
            'produto' => $sobra['nome_produto'],
            'quantidade' => $sobra['quantidade'],
            'valor_restaurado' => $sobra['quantidade'] * $sobra['preco']
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Erro ao remover sobra: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao remover sobra: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Erro geral ao remover sobra: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao remover sobra.'
    ], JSON_UNESCAPED_UNICODE);
}

