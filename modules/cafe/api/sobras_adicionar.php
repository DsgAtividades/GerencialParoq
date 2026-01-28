<?php
/**
 * API para adicionar sobras de produtos ao fechar o caixa
 * Diminui o estoque dos produtos sem gerar receita
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
    
    if (!isset($input['caixa_id']) || !isset($input['sobras']) || !is_array($input['sobras'])) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Dados inválidos. É necessário informar caixa_id e array de sobras.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $caixa_id = (int)$input['caixa_id'];
    $sobras = $input['sobras'];
    $observacao_geral = $input['observacao'] ?? null;
    
    if (empty($sobras)) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Nenhuma sobra foi informada.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar se o caixa existe e está aberto
    $stmt = $pdo->prepare("
        SELECT id, status, sobras_registradas 
        FROM cafe_caixas 
        WHERE id = ? AND status = 'aberto'
    ");
    $stmt->execute([$caixa_id]);
    $caixa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$caixa) {
        http_response_code(404);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Caixa não encontrado ou já está fechado.'
        ]);
        exit;
    }
    
    if ($caixa['sobras_registradas']) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'As sobras já foram registradas para este caixa.'
        ]);
        exit;
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    $total_produtos = 0;
    $total_quantidade = 0;
    $valor_total_perdido = 0;
    
    // Processar cada sobra
    foreach ($sobras as $sobra) {
        $produto_id = (int)$sobra['produto_id'];
        $quantidade = (float)$sobra['quantidade'];
        
        if ($quantidade <= 0) {
            continue; // Ignorar quantidades inválidas
        }
        
        // Buscar informações do produto
        $stmt = $pdo->prepare("
            SELECT id, nome_produto, preco, estoque 
            FROM cafe_produtos 
            WHERE id = ?
        ");
        $stmt->execute([$produto_id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$produto) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => "Produto ID {$produto_id} não encontrado."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Verificar se há estoque suficiente
        if ($produto['estoque'] < $quantidade) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => "Estoque insuficiente para o produto '{$produto['nome_produto']}'. Disponível: {$produto['estoque']}, solicitado: {$quantidade}."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Registrar a sobra
        $stmt = $pdo->prepare("
            INSERT INTO cafe_caixas_sobras 
            (caixa_id, produto_id, quantidade, usuario_registro_id, observacao)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $caixa_id,
            $produto_id,
            $quantidade,
            $_SESSION['usuario_id'],
            $observacao_geral
        ]);
        
        // Diminuir o estoque
        $stmt = $pdo->prepare("
            UPDATE cafe_produtos 
            SET estoque = estoque - ? 
            WHERE id = ?
        ");
        $stmt->execute([$quantidade, $produto_id]);
        
        // Registrar no histórico de transações do sistema
        $stmt = $pdo->prepare("
            INSERT INTO cafe_historico_transacoes_sistema 
            (nome_usuario, grupo_usuario, tipo, tipo_transacao, valor, id_pessoa, cartao)
            VALUES (?, ?, ?, ?, ?, NULL, NULL)
        ");
        $stmt->execute([
            $_SESSION['usuario_nome'] ?? 'Sistema',
            $_SESSION['usuario_grupo'] ?? 'Sistema',
            'sobra',
            "Sobra: {$produto['nome_produto']} - Qtd: {$quantidade}",
            $quantidade * $produto['preco'] // Valor total perdido
        ]);
        
        $total_produtos++;
        $total_quantidade += $quantidade;
        $valor_total_perdido += ($quantidade * $produto['preco']);
    }
    
    // Marcar que as sobras foram registradas
    $stmt = $pdo->prepare("
        UPDATE cafe_caixas 
        SET sobras_registradas = 1 
        WHERE id = ?
    ");
    $stmt->execute([$caixa_id]);
    
    // Commit da transação
    $pdo->commit();
    
    echo json_encode([
        'sucesso' => true,
        'mensagem' => "Sobras registradas com sucesso! {$total_produtos} produto(s), {$total_quantidade} unidade(s).",
        'dados' => [
            'total_produtos' => $total_produtos,
            'total_quantidade' => $total_quantidade,
            'valor_total_perdido' => $valor_total_perdido
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Erro ao adicionar sobras: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao processar sobras: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Erro geral ao adicionar sobras: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao processar sobras.'
    ], JSON_UNESCAPED_UNICODE);
}

