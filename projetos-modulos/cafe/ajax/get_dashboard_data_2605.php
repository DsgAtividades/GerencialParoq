<?php
require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';
require_once '../includes/funcoes.php';

$permissao = verificarPermissaoApi('visualizar_dashboard');

header('Content-Type: application/json');

if($permissao == 0){
    echo json_encode([
        'success' => false,
        'message' => 'Usuário sem permissão de acesso'
    ]);
}



// Obter parâmetros
$periodo = $_POST['periodo'] ?? 'hoje';
$categoria = $_POST['categoria'] ?? '';
$busca = $_POST['busca'] ?? '';
$hoje = date('Y-m-d');
// Definir datas com base no período
switch ($periodo) {
    case 'hoje':
        $data_inicio = date('Y-m-d 00:00:00', strtotime("-1 day", strtotime($hoje)));
        $data_fim = date('Y-m-d 23:59:59');
        $data_inicio_anterior = date('Y-m-d 00:00:00', strtotime('-2 day'));
        $data_fim_anterior = date('Y-m-d 23:59:59', strtotime('-2 day'));
        break;
    case 'ontem':
        $data_inicio = date('Y-m-d 00:00:00', strtotime('-2 day'));
        $data_fim = date('Y-m-d 23:59:59', strtotime('-2 day'));
        $data_inicio_anterior = date('Y-m-d 00:00:00', strtotime('-3 day'));
        $data_fim_anterior = date('Y-m-d 23:59:59', strtotime('-3 day'));
        break;
    case '7dias':
        $data_inicio = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $data_fim = date('Y-m-d 23:59:59');
        $data_inicio_anterior = date('Y-m-d 00:00:00', strtotime('-14 days'));
        $data_fim_anterior = date('Y-m-d 23:59:59', strtotime('-8 days'));
        break;
    case '30dias':
        $data_inicio = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $data_fim = date('Y-m-d 23:59:59');
        $data_inicio_anterior = date('Y-m-d 00:00:00', strtotime('-60 days'));
        $data_fim_anterior = date('Y-m-d 23:59:59', strtotime('-31 days'));
        break;
    case 'personalizado':
        // Implementar lógica para datas personalizadas se necessário
        break;
}

// Construir query base
$query_base = "
    FROM cafe_itens_venda vi
    JOIN cafe_produtos p ON vi.id_produto = p.id
    JOIN cafe_vendas v ON vi.id_venda = v.id_venda
    LEFT JOIN cafe_categorias c ON p.categoria_id = c.id
    WHERE v.estornada is null and v.data_venda BETWEEN :data_inicio AND :data_fim
";

$params = [
    ':data_inicio' => $data_inicio,
    ':data_fim' => $data_fim
];

if ($categoria) {
    $query_base .= " AND p.categoria_id = :categoria_id";
    $params[':categoria_id'] = $categoria;
}

if ($busca) {
    $query_base .= " AND p.nome LIKE :busca";
    $params[':busca'] = "%{$busca}%";
}

// Obter resumo do período atual
$query = "
    SELECT 
        SUM(vi.quantidade * vi.valor_unitario) as total_vendas,
        SUM(vi.quantidade) as quantidade_vendida,
        COUNT(DISTINCT p.id) as produtos_diferentes,
        AVG(vi.valor_unitario) as ticket_medio
    " . $query_base;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$resumo_atual = $stmt->fetch(PDO::FETCH_ASSOC);

// Obter resumo do período anterior para comparação
$params[':data_inicio'] = $data_inicio_anterior;
$params[':data_fim'] = $data_fim_anterior;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$resumo_anterior = $stmt->fetch(PDO::FETCH_ASSOC);

// Calcular variações
$variacao_vendas = $resumo_anterior['total_vendas'] > 0 ? 
    round((($resumo_atual['total_vendas'] - $resumo_anterior['total_vendas']) / $resumo_anterior['total_vendas']) * 100, 1) : 0;

$variacao_quantidade = $resumo_anterior['quantidade_vendida'] > 0 ? 
    round((($resumo_atual['quantidade_vendida'] - $resumo_anterior['quantidade_vendida']) / $resumo_anterior['quantidade_vendida']) * 100, 1) : 0;

$variacao_ticket = $resumo_anterior['ticket_medio'] > 0 ? 
    round((($resumo_atual['ticket_medio'] - $resumo_anterior['ticket_medio']) / $resumo_anterior['ticket_medio']) * 100, 1) : 0;

// Obter dados dos produtos
$params[':data_inicio'] = $data_inicio;
$params[':data_fim'] = $data_fim;

$query = "
    SELECT 
        p.id,
        p.nome_produto,
        c.nome as categoria,
        p.estoque,
        SUM(vi.quantidade) as quantidade_vendida,
        SUM(vi.quantidade * vi.valor_unitario) as valor_vendido,
        ROUND((SUM(vi.quantidade * vi.valor_unitario) / (
            SELECT SUM(vi2.quantidade * vi2.valor_unitario)
            FROM cafe_itens_venda vi2
            JOIN cafe_vendas v2 ON vi2.id_venda = v2.id_venda
            WHERE v2.estornada is null and v2.data_venda BETWEEN :data_inicio AND :data_fim
        )) * 100, 1) as percentual
    " . $query_base . "
    GROUP BY p.id, p.nome_produto, c.nome, p.estoque
    ORDER BY valor_vendido DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular tendência para cada produto
foreach ($produtos as &$produto) {
    $params[':produto_id'] = $produto['id'];
    $params[':data_inicio'] = $data_inicio_anterior;
    $params[':data_fim'] = $data_fim_anterior;
    
    $query = "
        SELECT SUM(vi.quantidade * vi.valor_unitario) as valor_anterior
        FROM cafe_itens_venda vi
        JOIN cafe_vendas v ON vi.id_venda = v.id_venda
        WHERE v.estornada is null and vi.id_produto = :produto_id
        AND v.data_venda BETWEEN :data_inicio AND :data_fim
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $valor_anterior = $stmt->fetch(PDO::FETCH_ASSOC)['valor_anterior'] ?? 0;
    
    $produto['tendencia'] = $valor_anterior > 0 ? 
        round((($produto['valor_vendido'] - $valor_anterior) / $valor_anterior) * 100, 1) : 0;
}

// Retornar dados
echo json_encode([
    'resumo' => [
        'total_vendas' => (float)$resumo_atual['total_vendas'],
        'quantidade_vendida' => (int)$resumo_atual['quantidade_vendida'],
        'produtos_diferentes' => (int)$resumo_atual['produtos_diferentes'],
        'ticket_medio' => (float)$resumo_atual['ticket_medio'],
        'variacao_vendas' => $variacao_vendas,
        'variacao_quantidade' => $variacao_quantidade,
        'variacao_ticket' => $variacao_ticket
    ],
    'produtos' => $produtos
]);
