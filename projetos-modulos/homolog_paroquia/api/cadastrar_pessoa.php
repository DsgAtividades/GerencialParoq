<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Verificar método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter dados do corpo da requisição
$data = json_decode(file_get_contents('php://input'), true);

// Validar dados obrigatórios
if (!isset($data['nome']) || !isset($data['cpf']) || !isset($data['qr_code'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

// Limpar e validar CPF
$cpf = preg_replace('/[^0-9]/', '', $data['cpf']);
if (strlen($cpf) !== 11) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'CPF inválido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Verificar se o CPF já está cadastrado
    $query = "SELECT id_pessoa FROM pessoas WHERE cpf = :cpf";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':cpf', $cpf);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'CPF já cadastrado']);
        exit;
    }

    // Verificar se o QR Code já está em uso
    $query = "SELECT id_pessoa FROM pessoas WHERE qr_code = :qr_code";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':qr_code', $data['qr_code']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'QR Code já em uso']);
        exit;
    }

    // Inserir pessoa
    $query = "INSERT INTO pessoas (nome, cpf, telefone, qr_code) VALUES (:nome, :cpf, :telefone, :qr_code)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':nome', $data['nome']);
    $stmt->bindParam(':cpf', $cpf);
    $stmt->bindParam(':telefone', $data['telefone']);
    $stmt->bindParam(':qr_code', $data['qr_code']);

    if ($stmt->execute()) {
        // Criar registro de saldo inicial
        $id_pessoa = $db->lastInsertId();
        $query = "INSERT INTO saldos_cartao (id_pessoa, saldo) VALUES (:id_pessoa, 0)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_pessoa', $id_pessoa);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Pessoa cadastrada com sucesso']);
    } else {
        throw new Exception('Erro ao cadastrar pessoa');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar pessoa']);
}
