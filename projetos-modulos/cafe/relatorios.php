<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('gerenciar_relatorios');

// Período do relatório (padrão: últimos 30 dias)
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');

// Inicializar variáveis
$stats_vendas = [
    'total_vendas' => 0,
    'valor_total_vendas' => 0,
    'total_clientes' => 0
];
$produtos_mais_vendidos = [];
$melhores_clientes = [];
$estoque_atual = [];

// Buscar estatísticas
try {
    // Total de vendas no período
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_vendas,
               SUM(valor_total) as valor_total_vendas,
               COUNT(DISTINCT id_pessoa) as total_clientes
        FROM cafe_vendas
        WHERE DATE(data_venda) BETWEEN ? AND ?
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $stats_vendas = $stmt->fetch();

    // Produtos mais vendidos
    $stmt = $pdo->prepare("
        SELECT 
            p.nome_produto,
            SUM(iv.quantidade) as total_vendido,
            SUM(iv.quantidade * iv.valor_unitario) as valor_total,
            COUNT(DISTINCT v.id_pessoa) as total_clientes
        FROM cafe_itens_venda iv
        JOIN cafe_vendas v ON iv.id_venda = v.id_venda
        JOIN cafe_produtos p ON iv.id_produto = p.id
        WHERE DATE(v.data_venda) BETWEEN ? AND ?
        GROUP BY p.id, p.nome_produto
        ORDER BY total_vendido DESC
        LIMIT 10
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $produtos_mais_vendidos = $stmt->fetchAll();

    // Clientes que mais compraram
    $stmt = $pdo->prepare("
        SELECT 
            p.nome,
            COUNT(DISTINCT v.id_venda) as total_compras,
            SUM(v.valor_total) as valor_total
        FROM cafe_vendas v
        JOIN cafe_pessoas p ON v.id_pessoa = p.id_pessoa
        WHERE DATE(v.data_venda) BETWEEN ? AND ?
        GROUP BY p.id_pessoa, p.nome
        ORDER BY valor_total DESC
        LIMIT 10
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $melhores_clientes = $stmt->fetchAll();

    // Estoque atual
    $stmt = $pdo->query("
        SELECT 
            p.nome_produto,
            p.estoque,
            p.preco,
            (p.estoque * p.preco) as valor_total,
            c.nome as categoria_nome,
            c.icone as categoria_icone
        FROM cafe_produtos p
        LEFT JOIN cafe_categorias c ON p.categoria_id = c.id
        WHERE p.estoque > 0
        ORDER BY p.estoque DESC
    ");
    $estoque_atual = $stmt->fetchAll();

} catch (Exception $e) {
    $erro = "Erro ao gerar relatório: " . $e->getMessage();
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Relatórios</h1>
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>

    <?php if (isset($erro)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i> <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <!-- Filtro de período -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                           value="<?= htmlspecialchars($data_inicio) ?>">
                </div>
                <div class="col-md-4">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" 
                           value="<?= htmlspecialchars($data_fim) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="card-title">Total de Vendas</h6>
                    <h2 class="mb-0">R$ <?= number_format($stats_vendas['valor_total_vendas'] ?? 0, 2, ',', '.') ?></h2>
                    <small><?= number_format($stats_vendas['total_vendas'] ?? 0) ?> vendas no período</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="card-title">Clientes Atendidos</h6>
                    <h2 class="mb-0"><?= number_format($stats_vendas['total_clientes'] ?? 0) ?></h2>
                    <small>clientes únicos no período</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <h6 class="card-title">Ticket Médio</h6>
                    <h2 class="mb-0">R$ <?= number_format(
                        ($stats_vendas['valor_total_vendas'] ?? 0) / ($stats_vendas['total_vendas'] ?: 1),
                        2, ',', '.'
                    ) ?></h2>
                    <small>valor médio por venda</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Produtos Mais Vendidos -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Produtos Mais Vendidos</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="exportarTabela('tabelaProdutos', 'Produtos Mais Vendidos')">
                        <i class="bi bi-download"></i> Exportar
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm" id="tabelaProdutos">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-end">Qtd.</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Clientes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($produtos_mais_vendidos)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            Nenhuma venda registrada no período
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($produtos_mais_vendidos as $produto): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($produto['nome_produto']) ?></td>
                                            <td class="text-end"><?= number_format($produto['total_vendido']) ?></td>
                                            <td class="text-end">R$ <?= number_format($produto['valor_total'], 2, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($produto['total_clientes']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Melhores Clientes -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Melhores Clientes</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="exportarTabela('tabelaClientes', 'Melhores Clientes')">
                        <i class="bi bi-download"></i> Exportar
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm" id="tabelaClientes">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th class="text-end">Compras</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($melhores_clientes)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">
                                            Nenhuma venda registrada no período
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($melhores_clientes as $cliente): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($cliente['nome']) ?></td>
                                            <td class="text-end"><?= number_format($cliente['total_compras']) ?></td>
                                            <td class="text-end">R$ <?= number_format($cliente['valor_total'], 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Situação do Estoque -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Situação do Estoque</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="exportarTabela('tabelaEstoque', 'Situação do Estoque')">
                        <i class="bi bi-download"></i> Exportar
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm" id="tabelaEstoque">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Categoria</th>
                                    <th class="text-end">Estoque</th>
                                    <th class="text-end">Preço Un.</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($estoque_atual)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            Nenhum produto em estoque
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($estoque_atual as $produto): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($produto['nome_produto']) ?></td>
                                            <td>
                                                <?php if ($produto['categoria_icone']): ?>
                                                    <i class="bi bi-<?= htmlspecialchars($produto['categoria_icone']) ?>"></i>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($produto['categoria_nome']) ?>
                                            </td>
                                            <td class="text-end"><?= number_format($produto['estoque']) ?></td>
                                            <td class="text-end">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                                            <td class="text-end">R$ <?= number_format($produto['valor_total'], 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="4">Total em Estoque</th>
                                    <th class="text-end">R$ <?= number_format(
                                        array_sum(array_column($estoque_atual, 'valor_total')),
                                        2, ',', '.'
                                    ) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Função para exportar tabela para Excel
function exportarTabela(tableId, filename) {
    const table = document.getElementById(tableId);
    const ws = XLSX.utils.table_to_sheet(table);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
    XLSX.writeFile(wb, filename + '.xlsx');
}

// Validação do período
document.querySelector('form').addEventListener('submit', function(e) {
    const inicio = new Date(document.getElementById('data_inicio').value);
    const fim = new Date(document.getElementById('data_fim').value);
    
    if (inicio > fim) {
        e.preventDefault();
        alert('A data inicial não pode ser maior que a data final');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
