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
if (!isset($data['id_pessoa']) || !isset($data['valor'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

// Validar valor
$valor = floatval($data['valor']);
if ($valor <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valor inválido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Iniciar transação
    $db->beginTransaction();

    // Atualizar saldo
    $query = "UPDATE saldos_cartao SET saldo = saldo + :valor WHERE id_pessoa = :id_pessoa";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':id_pessoa', $data['id_pessoa']);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao atualizar saldo');
    }

    // Registrar histórico
    $query = "INSERT INTO historico_saldo (id_pessoa, tipo, valor, data_hora) 
              VALUES (:id_pessoa, 'credito', :valor, NOW())";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_pessoa', $data['id_pessoa']);
    $stmt->bindParam(':valor', $valor);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao registrar histórico');
    }

    // Commit da transação
    $db->commit();

    echo json_encode(['success' => true, 'message' => 'Crédito adicionado com sucesso']);

} catch (Exception $e) {
    // Rollback em caso de erro
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar crédito']);
}
