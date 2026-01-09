<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['cpf']) || !isset($_GET['id_pessoa'])) {
        throw new Exception('Parâmetros inválidos');
    }

    $cpf = preg_replace('/[^0-9]/', '', $_GET['cpf']);
    $id_pessoa = (int)$_GET['id_pessoa'];

    // Validar formato do CPF
    if (strlen($cpf) !== 11) {
        throw new Exception('CPF deve ter 11 dígitos');
    }

    // Elimina CPFs inválidos conhecidos
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        throw new Exception('CPF inválido');
    }

    // Validação do primeiro dígito
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += (int)$cpf[$i] * (10 - $i);
    }
    $resto = 11 - ($soma % 11);
    if ($resto === 10 || $resto === 11) {
        $resto = 0;
    }
    if ($resto !== (int)$cpf[9]) {
        throw new Exception('CPF inválido');
    }

    // Validação do segundo dígito
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += (int)$cpf[$i] * (11 - $i);
    }
    $resto = 11 - ($soma % 11);
    if ($resto === 10 || $resto === 11) {
        $resto = 0;
    }
    if ($resto !== (int)$cpf[10]) {
        throw new Exception('CPF inválido');
    }

    // Verificar se CPF já existe no banco
    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT id_pessoa FROM cafe_pessoas WHERE cpf = ? AND id_pessoa != ?");
    $stmt->execute([
        substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2),
        $id_pessoa
    ]);

    if ($stmt->fetch()) {
        throw new Exception('Este CPF já está cadastrado para outra pessoa');
    }

    echo json_encode([
        'success' => true,
        'message' => 'CPF válido'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
