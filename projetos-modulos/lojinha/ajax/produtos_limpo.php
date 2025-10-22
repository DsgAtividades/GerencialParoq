<?php
// Versão completamente limpa do arquivo produtos
error_reporting(0);
ini_set('display_errors', 0);

require_once '../config/database.php';

header('Content-Type: application/json');

// Verificar se está logado no módulo
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não está logado no módulo']);
    exit;
}

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM lojinha_produtos ORDER BY nome ASC");
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'produtos' => $produtos
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
