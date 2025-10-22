<?php
// Desativa a exibição de erros
error_reporting(0);
ini_set('display_errors', 0);

// Define o tipo de conteúdo como JSON
header('Content-Type: application/json');

try {
    // Inicia a sessão
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Inclui os arquivos de configuração
    require_once __DIR__ . '/../config/database.php';

    // Verifica se é uma requisição POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    // Verifica se o usuário está logado e é administrador
    if (!isset($_SESSION['user_id']) || $_SESSION['tipo_acesso'] !== 'Administrador') {
        throw new Exception('Acesso não autorizado');
    }

    // Recebe e decodifica os dados JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['id'])) {
        throw new Exception('ID do alimento não fornecido');
    }

    $id = filter_var($data['id'], FILTER_VALIDATE_INT);
    if ($id === false) {
        throw new Exception('ID inválido');
    }

    // Verifica se o alimento existe
    $check = $pdo->prepare("SELECT id FROM estoque WHERE id = ?");
    $check->execute([$id]);
    
    if (!$check->fetch()) {
        throw new Exception('Alimento não encontrado');
    }

    // Primeiro, exclui o histórico relacionado ao alimento
    $stmt = $pdo->prepare("DELETE FROM historico_estoque WHERE alimento_id = ?");
    $stmt->execute([$id]);

    // Depois, exclui o alimento
    $stmt = $pdo->prepare("DELETE FROM estoque WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Alimento excluído com sucesso'
        ]);
    } else {
        throw new Exception('Erro ao excluir alimento');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log('Erro no banco de dados: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
} catch (Throwable $e) {
    error_log('Erro inesperado: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
} 