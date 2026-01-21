<?php
/**
 * API: Verificar Status do Caixa
 * Retorna informações sobre o caixa aberto (se houver)
 */
header('Content-Type: application/json');
require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';

$permissao = verificarPermissaoApi('visualizar_caixa');

if(!isset($permissao['tem_permissao']) || $permissao['tem_permissao'] == 0){
    echo json_encode([
        'success' => false,
        'message' => 'Usuário sem permissão de acesso'
    ]);
    exit;
}

try {
    // Buscar caixa aberto
    $stmt = $pdo->query("
        SELECT * FROM vw_cafe_caixas_resumo 
        WHERE status = 'aberto' 
        ORDER BY data_abertura DESC 
        LIMIT 1
    ");
    
    $caixa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($caixa) {
        // Calcular troco esperado (inicial + vendas em dinheiro - trocos dados)
        // Para isso, precisamos somar o valor recebido menos o troco dado
        // Por enquanto, vamos usar apenas o troco inicial menos as diferenças
        
        echo json_encode([
            'success' => true,
            'caixa_aberto' => true,
            'caixa' => $caixa
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'caixa_aberto' => false,
            'message' => 'Nenhum caixa aberto no momento'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao verificar status do caixa: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao verificar status do caixa'
    ]);
}

