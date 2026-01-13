<?php
// Desabilitar exibição de erros para não quebrar o JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Definir header JSON primeiro para evitar qualquer output HTML
header('Content-Type: application/json');

require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';
require_once '../includes/funcoes.php';
date_default_timezone_set('Etc/GMT+3');

$dados = json_decode(file_get_contents('php://input'), true);

// Verificar permissão
$permissao = verificarPermissaoApi('visualizar_dashboard');
if(!isset($permissao['tem_permissao']) || $permissao['tem_permissao'] == 0){
    echo json_encode([
        'success' => false,
        'message' => 'Usuário sem permissão de acesso'
    ]);
    exit;
}

// Obter parâmetros com valores padrão
//$periodo = $_POST['periodo'] ?? 'hoje';
$data_inicio = $dados['data_inicial'] ?? '';
$data_fim = $dados['data_final'] ?? '';
$data_inicio_anterior = $data_inicio;
$data_fim_anterior = $data_fim;
$categoria = $dados['categoria'] ?? '';
$busca = $dados['busca'] ?? '';
$hoje = date('Y-m-d');

try {
// // Definir datas com base no período
// switch ($periodo) {
//     case 'hoje':
//         $data_inicio = date('Y-m-d 00:00:00', strtotime("0 day", strtotime($hoje)));
//         $data_fim = date('Y-m-d 23:59:59');
//         $data_inicio_anterior = date('Y-m-d 00:00:00', strtotime('-2 day'));
//         $data_fim_anterior = date('Y-m-d 23:59:59', strtotime('-2 day'));
//         break;
//     case 'ontem':
//         $data_inicio = date('Y-m-d 00:00:00', strtotime('-1 day'));
//         $data_fim = date('Y-m-d 23:59:59', strtotime('-1 day'));
//         $data_inicio_anterior = date('Y-m-d 00:00:00', strtotime('-3 day'));
//         $data_fim_anterior = date('Y-m-d 23:59:59', strtotime('-3 day'));
//         break;
//     case '7dias':
//         $data_inicio = date('Y-m-d 00:00:00', strtotime('-7 days'));
//         $data_fim = date('Y-m-d 23:59:59');
//         $data_inicio_anterior = date('Y-m-d 00:00:00', strtotime('-14 days'));
//         $data_fim_anterior = date('Y-m-d 23:59:59', strtotime('-8 days'));
//         break;
//     case '30dias':
//         $data_inicio = date('Y-m-d 00:00:00', strtotime('-30 days'));
//         $data_fim = date('Y-m-d 23:59:59');
//         $data_inicio_anterior = date('Y-m-d 00:00:00', strtotime('-60 days'));
//         $data_fim_anterior = date('Y-m-d 23:59:59', strtotime('-31 days'));
//         break;
//     case 'personalizado':
//         // Implementar lógica para datas personalizadas se necessário
//         break;
// }

if ($data_inicio == ''){
    $data_inicio = $hoje;
    $data_fim = $hoje;
    $data_fim_anterior = $hoje;
    $data_inicio_anterior = $hoje;
}
// Construir query base
$query_base = "
    FROM cafe_itens_venda vi
    JOIN cafe_produtos p ON vi.id_produto = p.id
    JOIN cafe_vendas v ON vi.id_venda = v.id_venda
    LEFT JOIN cafe_categorias c ON p.categoria_id = c.id
    WHERE v.estornada is null and date(v.data_venda) BETWEEN :data_inicio AND :data_fim
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
    $query_base .= " AND p.nome_produto LIKE :busca";
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

// Garantir que resumo_atual tenha valores padrão se NULL
if (!$resumo_atual) {
    $resumo_atual = [
        'total_vendas' => 0,
        'quantidade_vendida' => 0,
        'produtos_diferentes' => 0,
        'ticket_medio' => 0
    ];
}

// Obter resumo do período anterior para comparação
// $params[':data_inicio'] = $data_inicio_anterior;
// $params[':data_fim'] = $data_fim_anterior;
$params[':data_inicio'] = $data_inicio;
$params[':data_fim'] = $data_fim;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$resumo_anterior = $stmt->fetch(PDO::FETCH_ASSOC);

// Garantir que resumo_anterior tenha valores padrão se NULL
if (!$resumo_anterior) {
    $resumo_anterior = [
        'total_vendas' => 0,
        'quantidade_vendida' => 0,
        'ticket_medio' => 0
    ];
}

// Calcular variações
$variacao_vendas = $resumo_anterior['total_vendas'] > 0 ? 
    round((($resumo_atual['total_vendas'] - $resumo_anterior['total_vendas']) / $resumo_anterior['total_vendas']) * 100, 1) : 0;

$variacao_quantidade = $resumo_anterior['quantidade_vendida'] > 0 ? 
    round((($resumo_atual['quantidade_vendida'] - $resumo_anterior['quantidade_vendida']) / $resumo_anterior['quantidade_vendida']) * 100, 1) : 0;

$variacao_ticket = $resumo_anterior['ticket_medio'] > 0 ? 
    round((($resumo_atual['ticket_medio'] - $resumo_anterior['ticket_medio']) / $resumo_anterior['ticket_medio']) * 100, 1) : 0;

// Obter dados dos produtos
// Criar novos parâmetros para evitar conflito com a subquery
$params_produtos = [
    ':data_inicio' => $data_inicio,
    ':data_fim' => $data_fim,
    ':data_inicio_sub' => $data_inicio,
    ':data_fim_sub' => $data_fim
];

if ($categoria) {
    $params_produtos[':categoria_id'] = $categoria;
}

if ($busca) {
    $params_produtos[':busca'] = "%{$busca}%";
}

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
            WHERE v2.estornada is null and date(v2.data_venda) BETWEEN :data_inicio_sub AND :data_fim_sub
        )) * 100, 1) as percentual
    " . $query_base . "
    GROUP BY p.id, p.nome_produto, c.nome, p.estoque
    ORDER BY valor_vendido DESC LIMIT 20
";

$stmt = $pdo->prepare($query);
$stmt->execute($params_produtos);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Garantir que produtos seja um array
if (!is_array($produtos)) {
    $produtos = [];
}

// Calcular tendência para cada produto
foreach ($produtos as $key => $produto) {
    $params_tendencia = [
        ':produto_id' => $produto['id'],
        ':data_inicio' => $data_inicio_anterior,
        ':data_fim' => $data_fim_anterior
    ];
    
    $query = "
        SELECT SUM(vi.quantidade * vi.valor_unitario) as valor_anterior
        FROM cafe_itens_venda vi
        JOIN cafe_vendas v ON vi.id_venda = v.id_venda
        WHERE v.estornada is null and vi.id_produto = :produto_id
        AND date(v.data_venda) BETWEEN :data_inicio AND :data_fim
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params_tendencia);
    $resultado_tendencia = $stmt->fetch(PDO::FETCH_ASSOC);
    $valor_anterior = $resultado_tendencia['valor_anterior'] ?? 0;
    
    $produtos[$key]['tendencia'] = $valor_anterior > 0 ? 
        round((($produto['valor_vendido'] - $valor_anterior) / $valor_anterior) * 100, 1) : 0;
}

// --- INÍCIO: DADOS DE SALDO DOS CARTÕES ---
// Saldo total dos cartões ativos (apenas saldo >= 0)
//$stmt = $pdo->query("SELECT SUM(saldo) as saldo_total, COUNT(*) as qtd_cartoes, AVG(saldo) as saldo_medio FROM cafe_saldos_cartao a join cafe_historico_saldo b on b.id_pessoa = a.id_pessoa WHERE a.id_pessoa IS NOT NULL AND saldo >= 0 AND b.tipo_operacao = 'credito' AND date(b.data_operacao) between '".$data_inicio."' and '".$data_fim."'");
$stmt = $pdo->query("select sum(valor) as saldo_total, AVG(valor) as saldo_medio, count(distinct a.id_pessoa) as qtd_cartoes
        from cafe_historico_saldo a
        join cafe_pessoas b on b.id_pessoa = a.id_pessoa
        where date(data_operacao) between '".$data_inicio."' and '".$data_fim."'
        and tipo_operacao in ('debito')
        and motivo NOT REGEXP 'Estorno' ");
$dados_saldos = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$dados_saldos) {
    $dados_saldos = ['saldo_total' => 0, 'saldo_medio' => 0, 'qtd_cartoes' => 0];
}

// Quantidade de cartões por faixa de saldo (apenas saldo >= 0)
$stmt = $pdo->query("
    SELECT
      SUM(CASE WHEN saldo >= 0 AND saldo < 10 THEN 1 ELSE 0 END) AS faixa_0_10,
      SUM(CASE WHEN saldo >= 10 AND saldo < 50 THEN 1 ELSE 0 END) AS faixa_10_50,
      SUM(CASE WHEN saldo >= 50 AND saldo < 100 THEN 1 ELSE 0 END) AS faixa_50_100,
      SUM(CASE WHEN saldo >= 100 THEN 1 ELSE 0 END) AS faixa_100_acima
    FROM cafe_saldos_cartao WHERE id_pessoa IS NOT NULL AND saldo >= 0
");
$faixas = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$faixas) {
    $faixas = ['faixa_0_10' => 0, 'faixa_10_50' => 0, 'faixa_50_100' => 0, 'faixa_100_acima' => 0];
}

// Top 10 maiores saldos (pode incluir negativos, mas normalmente só positivos aparecem no topo)
$stmt = $pdo->query("
    SELECT p.nome, c.codigo as cartao, s.saldo
    FROM cafe_saldos_cartao s
    JOIN cafe_pessoas p ON s.id_pessoa = p.id_pessoa
    JOIN cafe_cartoes c ON c.id_pessoa = p.id_pessoa
    WHERE s.saldo >= 0
    ORDER BY s.saldo DESC
    LIMIT 10
");
$top_saldos = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!is_array($top_saldos)) {
    $top_saldos = [];
}


// Total de créditos já inseridos nos cartões (apenas créditos positivos)
$stmt = $pdo->query("SELECT SUM(valor) as total_creditos FROM cafe_historico_saldo WHERE tipo_operacao = 'credito' and motivo not like('%Estorno ') AND valor > 0 AND date(data_operacao) between '".$data_inicio."' and '".$data_fim."'");
$total_creditos = $stmt->fetchColumn();

// Total de custo do cartoes
$stmt = $pdo->query("SELECT SUM(valor*-1) as total_cartao, count(id_historico) as qtde FROM cafe_historico_saldo WHERE tipo_operacao = 'custo cartao' AND date(data_operacao) between '".$data_inicio."' and '".$data_fim."'");
$total_cartao = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$total_cartao) {
    $total_cartao = ['total_cartao' => 0, 'qtde' => 0];
}

// Total estornado
$stmt = $pdo->query("SELECT SUM(valor) as total_estorno, count(id_historico) as qtde FROM cafe_historico_saldo WHERE tipo_operacao = 'debito' AND motivo = 'Estorno' AND date(data_operacao) between '".$data_inicio."' and '".$data_fim."'");
$total_estorno = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$total_estorno) {
    $total_estorno = ['total_estorno' => 0, 'qtde' => 0];
}

// Cartoes ativos
$stmt = $pdo->query("select count(distinct a.id_pessoa) as qtd_cartoes
        from cafe_historico_saldo a
        join cafe_pessoas b on b.id_pessoa = a.id_pessoa
        where date(data_operacao) between '".$data_inicio."' and '".$data_fim."'
        and motivo NOT REGEXP 'Estorno' ");
$cartoes_uso = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cartoes_uso) {
    $cartoes_uso = ['qtd_cartoes' => 0];
}

// --- FIM: DADOS DE SALDO DOS CARTÕES ---

// Retornar dados
echo json_encode([
    'success' => true,
    'resumo' => [
        'total_vendas' => (float)($resumo_atual['total_vendas'] ?? 0),
        'quantidade_vendida' => (int)($resumo_atual['quantidade_vendida'] ?? 0),
        'produtos_diferentes' => (int)($resumo_atual['produtos_diferentes'] ?? 0),
        'ticket_medio' => (float)($resumo_atual['ticket_medio'] ?? 0),
        'variacao_vendas' => $variacao_vendas ?? 0,
        'variacao_quantidade' => $variacao_quantidade ?? 0,
        'variacao_ticket' => $variacao_ticket ?? 0,
        'custo_cartao' => (float)($total_cartao['total_cartao'] ?? 0),
        'qtde_cartao' => (int)($total_cartao['qtde'] ?? 0),
        'total_estorno' => (float)($total_estorno['total_estorno'] ?? 0),
        'qtde_estorno' => (int)($total_estorno['qtde'] ?? 0)
    ],
    'produtos' => $produtos ?? [],
    // --- INÍCIO: DADOS DE SALDO DOS CARTÕES ---
    'saldos_cartao' => [
        'saldo_total' => (float)($dados_saldos['saldo_total'] ?? 0),
        'qtd_cartoes' => (int)($cartoes_uso['qtd_cartoes'] ?? 0),
        'saldo_medio' => (float)($dados_saldos['saldo_medio'] ?? 0),
        'faixas' => $faixas ?? [],
        'top_saldos' => $top_saldos ?? [],
        'total_creditos' => (float)($total_creditos ?? 0)
    ]
    // --- FIM: DADOS DE SALDO DOS CARTÕES ---
]);
} catch (Exception $e) {
    // Retornar erro em formato JSON
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar dados: ' . $e->getMessage()
    ]);
    exit;
}