<?php
require_once '../includes/db_connection.php';
require_once '../includes/upload_handler.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    if (!isset($_POST['obra_id']) || !is_numeric($_POST['obra_id'])) {
        throw new Exception('ID da obra inválido');
    }

    $obra_id = (int)$_POST['obra_id'];
    $response = ['success' => true, 'messages' => []];

    // Processar comprovante de pagamento
    if (isset($_FILES['comprovante']) && $_FILES['comprovante']['error'] === UPLOAD_ERR_OK) {
        $filepath = handleFileUpload($_FILES['comprovante'], $obra_id, 'comprovante');
        updateFilePathInDatabase($conn, $obra_id, 'comprovante', $filepath);
        $response['messages'][] = 'Comprovante de pagamento enviado com sucesso';
    }

    // Processar nota fiscal
    if (isset($_FILES['nota_fiscal']) && $_FILES['nota_fiscal']['error'] === UPLOAD_ERR_OK) {
        $filepath = handleFileUpload($_FILES['nota_fiscal'], $obra_id, 'nota_fiscal');
        updateFilePathInDatabase($conn, $obra_id, 'nota_fiscal', $filepath);
        $response['messages'][] = 'Nota fiscal enviada com sucesso';
    }

    if (empty($response['messages'])) {
        throw new Exception('Nenhum arquivo foi enviado');
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
