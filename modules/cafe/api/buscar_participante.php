<?php
header('Content-Type: application/json');

require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';
require_once '../includes/funcoes.php';

$permissao = verificarPermissaoApi('buscar_participante');

if($permissao == 0){
    echo json_encode([
        'success' => true,
        'message' => 'Usuário sem permissão de acesso'
    ]);
}
// Verificar método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

 
try {
    // Pegar dados do POST JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    // Pegar o código do cartão ou CPF
    $codigo = isset($data['codigo']) ? trim($data['codigo']) : '';

    // Se não tiver código
    if (empty($codigo)) {
        throw new Exception('Código não fornecido');
    }

    // Remover caracteres não numéricos apenas se for CPF
    $cpf = preg_replace('/\D/', '', $codigo);

    // Buscar participante pelo código do cartão ou CPF
    $query = "SELECT p.*, COALESCE(s.saldo, 0.00) as saldo, c.codigo as cartao_codigo
              FROM cafe_pessoas p 
              LEFT JOIN cafe_cartoes c ON p.id_pessoa = c.id_pessoa
              LEFT JOIN cafe_saldos_cartao s ON p.id_pessoa = s.id_pessoa 
              WHERE 1=1
              AND p.cpf = '".$cpf."'
              OR c.codigo = '".$codigo."'";
    
    $stmt = $pdo->query($query);
    $participante = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($participante) {
        //$participante = $stmt->fetch(PDO::FETCH_ASSOC);
        //error_log("Participante encontrado: " . print_r($participante, true));
        echo json_encode([
            'success' => true,
            'participante' => [
                'id' => $participante['id_pessoa'],
                'nome' => $participante['nome'],
                'cpf' => $participante['cpf'],
                'saldo' => number_format($participante['saldo'], 2, '.', ''),
                'cartao_codigo' => $participante['cartao_codigo']
            ]
        ]);
    } else {
        throw new Exception('Participante não encontrado '.$cpf.' - '.$codigo );
    }

} catch (Exception $e) {
    error_log("Erro na busca de participante: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
