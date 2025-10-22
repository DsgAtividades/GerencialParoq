<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Debug: Log da sessão
error_log("=== DEBUG TESTE PRODUTOS ===");
error_log("Sessão: " . print_r($_SESSION, true));

// Verificar se está logado no módulo
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não está logado no módulo']);
    exit;
}

try {
    // Testar ambas as formas de conexão
    try {
        $db = getDatabase();
        error_log("Conexão via getDatabase(): OK");
    } catch (Exception $e) {
        error_log("Erro getDatabase(): " . $e->getMessage());
        // Tentar conexão direta
        $pdo = getConnection();
        error_log("Conexão via getConnection(): OK");
        $db = null; // Usar PDO direto
    }
    
    // Verificar se as tabelas existem
    $tabelas_existem = true;
    $tabelas = ['lojinha_categorias', 'lojinha_fornecedores', 'lojinha_produtos'];
    
    foreach ($tabelas as $tabela) {
        if ($db) {
            $result = $db->fetchOne("SHOW TABLES LIKE '$tabela'");
        } else {
            $stmt = $pdo->query("SHOW TABLES LIKE '$tabela'");
            $result = $stmt->fetch();
        }
        
        if (!$result) {
            $tabelas_existem = false;
            error_log("Tabela $tabela não existe");
            break;
        }
    }
    
    if (!$tabelas_existem) {
        echo json_encode(['success' => false, 'message' => 'Tabelas não existem. Execute o setup do banco de dados.']);
        exit;
    }
    
    // Testar consulta simples primeiro
    if ($db) {
        $produtos_simples = $db->fetchAll("SELECT * FROM lojinha_produtos LIMIT 5");
        error_log("Consulta simples: " . count($produtos_simples) . " produtos");
        
        // Testar consulta com JOIN
        $produtos = $db->fetchAll("
            SELECT 
                p.*,
                c.nome as categoria_nome,
                f.nome as fornecedor_nome
            FROM lojinha_produtos p
            LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
            LEFT JOIN lojinha_fornecedores f ON p.fornecedor_id = f.id
            ORDER BY p.nome ASC
        ");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM lojinha_produtos LIMIT 5");
        $stmt->execute();
        $produtos_simples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Consulta simples: " . count($produtos_simples) . " produtos");
        
        // Testar consulta com JOIN
        $stmt = $pdo->prepare("
            SELECT 
                p.*,
                c.nome as categoria_nome,
                f.nome as fornecedor_nome
            FROM lojinha_produtos p
            LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
            LEFT JOIN lojinha_fornecedores f ON p.fornecedor_id = f.id
            ORDER BY p.nome ASC
        ");
        $stmt->execute();
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    error_log("Consulta com JOIN: " . count($produtos) . " produtos");
    
    echo json_encode([
        'success' => true,
        'produtos' => $produtos,
        'debug' => [
            'tabelas_existem' => $tabelas_existem,
            'total_produtos' => count($produtos)
        ]
    ]);
    
} catch(Exception $e) {
    error_log("Erro ao carregar produtos: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
