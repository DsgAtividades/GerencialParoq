<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Pegar dados do POST JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!isset($data['qrcode'])) {
        throw new Exception('QR Code não informado');
    }
    
    $qrcode = $data['qrcode'];
    
    // Buscar cliente e seu saldo
    $stmt = $db->prepare("
        SELECT codigo, id_pessoa, usado 
        FROM cafe_cartoes
        WHERE codigo = ?
    ");
    
    $stmt->execute([$qrcode]);
    $cliente = $stmt->fetch();
    
    if (!$cliente) {
        throw new Exception('Cartão não encontrado');
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'codigo' => $cliente['codigo'],
            'id_pessoa' => $cliente['id_pessoa'],
            'usado' => $cliente['usado']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
