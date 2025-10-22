<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Verificar método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar parâmetros
if (!isset($_GET['qr_code'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'QR Code não informado']);
    exit;
}

$qr_code = $_GET['qr_code'];

try {
    $database = new Database();
    $db = $database->getConnection();

    // Verificar se o QR Code já está em uso
    $query = "SELECT id_pessoa FROM pessoas WHERE qr_code = :qr_code";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':qr_code', $qr_code);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'disponivel' => false]);
    } else {
        echo json_encode(['success' => true, 'disponivel' => true]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao verificar QR Code']);
}
