<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar se está logado no módulo
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não está logado no módulo']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Receber ID do relatório
$relatorio_id = $_POST['relatorio_id'] ?? '';

if (empty($relatorio_id)) {
    echo json_encode(['success' => false, 'message' => 'ID do relatório não informado']);
    exit;
}

try {
    $pdo = getConnection();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Erro de conexão com banco de dados']);
        exit;
    }
    
    // Verificar se o relatório existe
    $check_sql = "SELECT id FROM relatorios_atividades WHERE id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$relatorio_id]);
    
    if (!$check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Relatório não encontrado']);
        exit;
    }
    
    // Excluir relatório do banco (todos os relatórios podem ser excluídos)
    $sql = "DELETE FROM relatorios_atividades WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$relatorio_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Relatório excluído com sucesso!'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir relatório']);
    }
    
} catch(PDOException $e) {
    error_log("Erro ao excluir relatório: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
