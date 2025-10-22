<?php

header('Content-Type: application/json');
require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';
require_once '../includes/funcoes.php';

$permissao = verificarPermissaoApi('visualizar_dashboard');

if($permissao == 0){
    echo json_encode([
        'success' => false,
        'message' => 'Usuário sem permissão de acesso'
    ]);
}


if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID do produto não fornecido']);
    exit;
}

$produto_id = (int)$_GET['id'];

// Obter vendas dos últimos 7 dias
$data_inicio = date('Y-m-d 00:00:00', strtotime('-7 days'));
$data_fim = date('Y-m-d 23:59:59');

// Dados para o gráfico
$query = "
    SELECT 
        DATE(v.data_venda) as data,
        SUM(vi.quantidade * vi.valor_unitario) as valor
    FROM itens_venda vi
    JOIN vendas v ON vi.id_venda = v.id_venda
    WHERE vi.id_produto = :produto_id
    AND v.data_venda BETWEEN :data_inicio AND :data_fim
    GROUP BY DATE(v.data_venda)
    ORDER BY data
";

$stmt = $pdo->prepare($query);
$stmt->execute([
    ':produto_id' => $produto_id,
    ':data_inicio' => $data_inicio,
    ':data_fim' => $data_fim
]);

$grafico = [
    'labels' => [],
    'valores' => []
];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $grafico['labels'][] = date('d/m', strtotime($row['data']));
    $grafico['valores'][] = (float)$row['valor'];
}

// Últimas vendas
$query = "
    SELECT 
        v.data_venda,
        vi.quantidade,
        (vi.quantidade * vi.valor_unitario) as valor,
        CONCAT(p.nome, ' (', COALESCE(p.cpf, 'Não identificado'), ')') as cliente
    FROM itens_venda vi
    JOIN vendas v ON vi.id_venda = v.id_venda
    LEFT JOIN pessoas p ON v.id_pessoa = p.id_pessoa
    WHERE vi.id_produto = :produto_id
    ORDER BY v.data_venda DESC
    LIMIT 10
";

$stmt = $pdo->prepare($query);
$stmt->execute([':produto_id' => $produto_id]);
$vendas = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $vendas[] = [
        'data_hora' => date('d/m/Y H:i', strtotime($row['data_venda'])),
        'quantidade' => (int)$row['quantidade'],
        'valor' => (float)$row['valor'],
        'cliente' => $row['cliente']
    ];
}

echo json_encode([
    'grafico' => $grafico,
    'vendas' => $vendas
]);
