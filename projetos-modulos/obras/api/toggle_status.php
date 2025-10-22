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

    // Inclui os arquivos de configuração usando o caminho correto
    require_once __DIR__ . '/../config/database.php';

    // Verifica se é uma requisição POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    // Verifica se o usuário está logado e é administrador
    if (!isset($_SESSION['tipo_acesso']) || $_SESSION['tipo_acesso'] !== 'Administrador') {
        throw new Exception('Acesso negado');
    }

    // Pega o conteúdo do corpo da requisição
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON inválido: ' . json_last_error_msg());
    }

    if (!isset($data['id']) || !isset($data['status'])) {
        throw new Exception('Dados inválidos: id e status são obrigatórios');
    }

    $id = filter_var($data['id'], FILTER_VALIDATE_INT);
    if ($id === false) {
        throw new Exception('ID inválido');
    }

    $status = $data['status'];

    // Verifica se o status é válido
    $status_validos = ['Ativo', 'Inativo', 'Aguardando Documentação', 'Outros'];
    if (!in_array($status, $status_validos)) {
        throw new Exception('Status inválido');
    }

    // Primeiro verifica se o usuário existe
    $check = $conn->prepare("SELECT id FROM obras_users WHERE id = ?");
    $check->execute([$id]);
    
    if (!$check->fetch()) {
        throw new Exception('Usuário não encontrado');
    }

    // Atualiza o status
    $stmt = $conn->prepare("UPDATE obras_users SET situacao = ? WHERE id = ?");
    $result = $stmt->execute([$status, $id]);
    
    if (!$result) {
        throw new Exception('Erro ao atualizar status: ' . implode(', ', $stmt->errorInfo()));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Status atualizado com sucesso',
        'newStatus' => $status
    ]);

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
