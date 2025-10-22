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
$relatorio_id = $_POST['relatorio_id'] ?? '';
$titulo_atividade = trim($_POST['titulo_atividade'] ?? '');
$setor = trim($_POST['setor'] ?? '');
$responsavel = trim($_POST['responsavel'] ?? '');
$data_inicio = $_POST['data_inicio'] ?? '';
$data_previsao = $_POST['data_previsao'] ?? '';
$data_termino = $_POST['data_termino'] ?? '';
$status = $_POST['status'] ?? 'a_fazer';
$observacao = trim($_POST['observacao'] ?? '');


// Validação básica
if (empty($relatorio_id)) {
    echo json_encode(['success' => false, 'message' => 'ID do relatório não informado']);
    exit;
}

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
    
    // Verificar se o relatório existe
    $check_sql = "SELECT id FROM relatorios_atividades WHERE id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$relatorio_id]);
    
    if (!$check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Relatório não encontrado']);
        exit;
    }
    
    // Preparar dados para atualização
    $data_termino_sql = !empty($data_termino) ? $data_termino : null;
    
    // Atualizar relatório no banco (todos os relatórios podem ser atualizados)
    $sql = "UPDATE relatorios_atividades 
            SET titulo_atividade = ?, setor = ?, responsavel = ?, data_inicio = ?, data_previsao = ?, 
                data_termino = ?, status = ?, observacao = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";
    
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
        $relatorio_id
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Relatório atualizado com sucesso!'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar relatório']);
    }
    
} catch(PDOException $e) {
    error_log("Erro ao atualizar relatório: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()]);
} catch(Exception $e) {
    error_log("Erro geral ao atualizar relatório: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>
