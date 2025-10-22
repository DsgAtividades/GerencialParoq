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
if (!isset($_GET['id_pessoa'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da pessoa não informado']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Buscar histórico de saldo
    $query = "SELECT tipo, valor, data_hora 
              FROM historico_saldo 
              WHERE id_pessoa = :id_pessoa 
              ORDER BY data_hora DESC 
              LIMIT 50";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_pessoa', $_GET['id_pessoa']);
    $stmt->execute();

    $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true, 
        'historico' => array_map(function($item) {
            return [
                'tipo' => $item['tipo'],
                'valor' => number_format($item['valor'], 2, '.', ''),
                'data_hora' => $item['data_hora']
            ];
        }, $historico)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar histórico']);
}
