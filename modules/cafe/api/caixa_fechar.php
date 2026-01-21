<?php
/**
 * API: Fechar Caixa
 * Fecha o caixa aberto e registra o troco final
 */
header('Content-Type: application/json');
require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';

$permissao = verificarPermissaoApi('fechar_caixa');

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
    
    $observacao = $data['observacao'] ?? null;
    
    // Buscar caixa aberto
    $stmt = $pdo->query("SELECT id, valor_troco_inicial, total_trocos_dados FROM cafe_caixas WHERE status = 'aberto' LIMIT 1");
    $caixa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$caixa) {
        throw new Exception('Nenhum caixa aberto encontrado');
    }
    
    // CALCULAR AUTOMATICAMENTE O TROCO FINAL
    $valor_troco_final = $caixa['valor_troco_inicial'] - $caixa['total_trocos_dados'];
    
    // Fechar caixa
    $stmt = $pdo->prepare("
        UPDATE cafe_caixas 
        SET 
            data_fechamento = NOW(),
            valor_troco_final = ?,
            usuario_fechamento_id = ?,
            usuario_fechamento_nome = ?,
            observacao_fechamento = ?,
            status = 'fechado'
        WHERE id = ?
    ");
    
    $stmt->execute([
        $valor_troco_final,
        $_SESSION['usuario_id'],
        $_SESSION['usuario_nome'],
        $observacao,
        $caixa['id']
    ]);
    
    // Buscar resumo do caixa fechado
    $stmt = $pdo->prepare("SELECT * FROM vw_cafe_caixas_resumo WHERE id = ?");
    $stmt->execute([$caixa['id']]);
    $resumo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Caixa fechado com sucesso',
        'resumo' => $resumo
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao fechar caixa: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

