<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!isset($_GET['qrcode'])) {
        throw new Exception('QR Code não informado');
    }
    
    $qrcode = $_GET['qrcode'];
    
    // Buscar cliente e seu saldo
    $stmt = $db->prepare("
        SELECT p.*, sc.saldo 
        FROM pessoas p 
        LEFT JOIN saldos_cartao sc ON p.id_pessoa = sc.id_pessoa 
        WHERE p.qrcode = ?
    ");
    
    $stmt->execute([$qrcode]);
    $cliente = $stmt->fetch();
    
    if (!$cliente) {
        throw new Exception('Cliente não encontrado');
    }
    
    echo json_encode([
        'success' => true,
        'cliente' => [
            'id_pessoa' => $cliente['id_pessoa'],
            'nome' => $cliente['nome'],
            'cpf' => $cliente['cpf'],
            'saldo' => $cliente['saldo'] ?? 0
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
