<?php
/**
 * API para listar produtos disponíveis
 */

// Desabilitar exibição de erros na saída
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../includes/conexao.php';
    require_once '../includes/verifica_permissao.php';
    
    // Verificar permissão (verifica login automaticamente)
    $permissao = verificarPermissaoApi('vendas_mobile');
    
    if(!isset($permissao['tem_permissao']) || $permissao['tem_permissao'] == 0){
        // Se não tiver permissão vendas_mobile, tenta visualizar_caixa
        $permissao = verificarPermissaoApi('visualizar_caixa');
        if(!isset($permissao['tem_permissao']) || $permissao['tem_permissao'] == 0){
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Usuário sem permissão de acesso'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    // $pdo já está disponível via conexao.php
    
    // Buscar produtos ativos com estoque
    $sql = "SELECT 
                id,
                nome_produto as nome,
                preco,
                estoque,
                categoria_id
            FROM cafe_produtos
            WHERE ativo = 1
            ORDER BY nome_produto ASC";
    
    $stmt = $pdo->query($sql);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Converter valores numéricos para float
    foreach ($produtos as &$produto) {
        $produto['preco'] = (float)$produto['preco'];
        $produto['estoque'] = (float)$produto['estoque'];
    }
    
    echo json_encode([
        'success' => true,
        'produtos' => $produtos
    ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    
} catch (PDOException $e) {
    error_log("Erro ao listar produtos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar produtos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("Erro geral ao listar produtos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar produtos'
    ], JSON_UNESCAPED_UNICODE);
}

