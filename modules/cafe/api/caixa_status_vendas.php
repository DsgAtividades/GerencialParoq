<?php
/**
 * API: Verificar Status do Caixa (para vendas)
 * Versão simplificada que aceita permissão vendas_mobile
 * Retorna apenas se há caixa aberto ou não
 */
header('Content-Type: application/json');
require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';

// Aceitar tanto visualizar_caixa quanto vendas_mobile
$temPermissao = false;
$permissao1 = verificarPermissaoApi('visualizar_caixa');
$permissao2 = verificarPermissaoApi('vendas_mobile');

if (isset($permissao1['tem_permissao']) && $permissao1['tem_permissao'] == 1) {
    $temPermissao = true;
} elseif (isset($permissao2['tem_permissao']) && $permissao2['tem_permissao'] == 1) {
    $temPermissao = true;
}

if (!$temPermissao) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuário sem permissão de acesso'
    ]);
    exit;
}

try {
    // Buscar apenas se há caixa aberto (sem detalhes)
    $stmt = $pdo->query("
        SELECT id, status 
        FROM cafe_caixas 
        WHERE status = 'aberto' 
        ORDER BY data_abertura DESC 
        LIMIT 1
    ");
    
    $caixa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($caixa) {
        echo json_encode([
            'success' => true,
            'caixa_aberto' => true
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





