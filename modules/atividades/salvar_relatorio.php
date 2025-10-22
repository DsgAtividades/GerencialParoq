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

// Receber dados do formulário
$titulo_atividade = trim($_POST['titulo_atividade'] ?? '');
$setor = trim($_POST['setor'] ?? '');
$responsavel = trim($_POST['responsavel'] ?? '');
$data_inicio = $_POST['data_inicio'] ?? '';
$data_previsao = $_POST['data_previsao'] ?? '';
$data_termino = $_POST['data_termino'] ?? '';
$status = $_POST['status'] ?? 'a_fazer';
$observacao = trim($_POST['observacao'] ?? '');


// Validação básica
if (empty($titulo_atividade) || empty($setor) || empty($responsavel) || empty($data_inicio) || empty($data_previsao)) {
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios não preenchidos']);
    exit;
}

try {
    $pdo = getConnection();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Erro de conexão com banco de dados']);
        exit;
    }
    
    // Preparar dados para inserção
    $data_termino_sql = !empty($data_termino) ? $data_termino : null;
    $user_id = $_SESSION['module_user_id'] ?? null;
    
    // Inserir relatório no banco
    $sql = "INSERT INTO relatorios_atividades 
            (titulo_atividade, setor, responsavel, data_inicio, data_previsao, data_termino, status, observacao, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $titulo_atividade,
        $setor,
        ucfirst(strtolower($responsavel)), // Primeira letra maiúscula
        $data_inicio,
        $data_previsao,
        $data_termino_sql,
        $status,
        $observacao,
        $user_id
    ]);
    
    if ($result) {
        $relatorio_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Relatório salvo com sucesso!',
            'relatorio_id' => $relatorio_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar relatório']);
    }
    
} catch(PDOException $e) {
    error_log("Erro ao salvar relatório: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
