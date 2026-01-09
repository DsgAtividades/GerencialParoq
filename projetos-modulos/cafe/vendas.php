<?php

require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('gerenciar_vendas');

$dia_atual = date('Y-m-d');
// Filtros
$data_inicio = isset($_POST['data_inicio']) ? $_POST['data_inicio'] : date('Y-m-d', strtotime("0 day", strtotime($dia_atual)));
$data_fim = isset($_POST['data_fim']) ? $_POST['data_fim'] : date('Y-m-d', strtotime("0 day", strtotime($dia_atual)));
$participante = isset($_POST['cpf_tel']) ? $_POST['cpf_tel'] : '';
$filtro = "";
if($participante){
    $filtro = " AND (p.cpf like '%". $participante ."%' OR p.nome like '%".$participante."%') ";
}
// Buscar vendas
$stmt = $pdo->prepare("
    SELECT 
        v.id_venda,
        v.data_venda,
        v.valor_total,
        v.estornada,
        p.nome as cliente_nome,
        p.cpf as cliente_cpf,
        GROUP_CONCAT(pr.nome_produto SEPARATOR ', ') as produtos
    FROM cafe_vendas v
    JOIN cafe_pessoas p ON v.id_pessoa = p.id_pessoa
    JOIN cafe_itens_venda vi ON v.id_venda = vi.id_venda
    JOIN cafe_produtos pr ON vi.id_produto = pr.id
    WHERE v.estornada is null and DATE(v.data_venda) BETWEEN ? AND ? AND pr.bloqueado = 0
    $filtro
    GROUP BY v.id_venda, v.data_venda, v.valor_total, p.nome, p.cpf
    ORDER BY v.data_venda DESC LIMIT 100
");
$stmt->execute([$data_inicio, $data_fim]);
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar vendas incluindo estornada
$stmt = $pdo->prepare("
    SELECT 
        v.id_venda,
        v.data_venda,
        v.valor_total,
        v.estornada,
        p.nome as cliente_nome,
        p.cpf as cliente_cpf,
        GROUP_CONCAT(pr.nome_produto SEPARATOR ', ') as produtos
    FROM cafe_vendas v
    JOIN cafe_pessoas p ON v.id_pessoa = p.id_pessoa
    JOIN cafe_itens_venda vi ON v.id_venda = vi.id_venda
    JOIN cafe_produtos pr ON vi.id_produto = pr.id
    WHERE DATE(v.data_venda) BETWEEN ? AND ? AND pr.bloqueado = 0
    $filtro
    GROUP BY v.id_venda, v.data_venda, v.valor_total, p.nome, p.cpf
    ORDER BY v.data_venda DESC LIMIT 100
");
$stmt->execute([$data_inicio, $data_fim]);
$vendas_geral = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$total_vendas = count($vendas);
$valor_total = array_sum(array_column($vendas, 'valor_total'));
$ticket_medio = $total_vendas > 0 ? $valor_total / $total_vendas : 0;

// Top 10 produtos mais vendidos
$stmt = $pdo->prepare("
    SELECT 
        p.nome_produto,
        SUM(vi.quantidade) as total_vendido,
        SUM(vi.quantidade * vi.valor_unitario) as valor_total
    FROM cafe_itens_venda vi
    JOIN cafe_produtos p ON vi.id_produto = p.id
    JOIN cafe_vendas v ON vi.id_venda = v.id_venda
    WHERE DATE(v.data_venda) BETWEEN ? AND ? AND p.bloqueado = 0 and v.estornada is null
    GROUP BY p.id, p.nome_produto
    ORDER BY total_vendido DESC
    LIMIT 20
");
$stmt->execute([$data_inicio, $data_fim]);
$top_produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vendas por status
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(valor_total) as valor_total
    FROM cafe_vendas
    WHERE estornada is null and DATE(data_venda) BETWEEN ? AND ?
");
$stmt->execute([$data_inicio, $data_fim]);
$vendas_por_status = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<div class="container py-3">
    <!-- <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Vendas</h1>
        <a href="vendas_novo.php" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Nova Venda
        </a>
    </div> -->

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3" method="post">
                <div class="col-md-3">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" 
                           value="<?= htmlspecialchars($data_inicio) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" 
                           value="<?= htmlspecialchars($data_fim) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">CPF ou Nome</label>
                    <input type="text" name="cpf_tel" class="form-control" 
                           value="<?= htmlspecialchars($participante) ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total de Vendas</h6>
                            <h3 class="mb-0"><?= $total_vendas ?></h3>
                        </div>
                        <i class="bi bi-cart-check h1 mb-0"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Valor Total</h6>
                            <h3 class="mb-0">R$ <?= number_format($valor_total, 2, ',', '.') ?></h3>
                        </div>
                        <i class="bi bi-currency-dollar h1 mb-0"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Ticket Médio</h6>
                            <h3 class="mb-0">R$ <?= number_format($ticket_medio, 2, ',', '.') ?></h3>
                        </div>
                        <i class="bi bi-receipt h1 mb-0"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Média por Dia</h6>
                            <h3 class="mb-0">R$ <?= number_format($valor_total / max(1, abs(strtotime($data_fim) - strtotime($data_inicio)) / 86400), 2, ',', '.') ?></h3>
                        </div>
                        <i class="bi bi-graph-up h1 mb-0"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

   

        <!--Vendas por Status
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header py-2">
                    <h5 class="card-title h6 mb-0">Vendas por Status</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                < foreach ($vendas_por_status as $status): ?>
                                    <tr>
                                        <td>
                                            <
                                            $status_class = [
                                                'pendente' => 'warning',
                                                'concluida' => 'success',
                                                'cancelada' => 'danger'
                                            ][$status['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<= $status_class ?>">
                                                < ucfirst($status['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center"><$status['total'] ?></td>
                                        <td class="text-end">
                                            R$ < number_format($status['valor_total'], 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                < endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>-->

    <!-- Lista de Vendas -->
    <div class="col-md-12">
    <div class="card">
        <div class="card-header py-2">
            <h5 class="card-title h6 mb-0">Últimas Vendas</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>CPF</th>
                            <th>Produtos</th>
                            <th class="text-end">Valor</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vendas)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-3 text-muted">
                                    Nenhuma venda registrada
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vendas_geral as $venda): ?>
                                <tr>
                                    <td><?= $venda['id_venda'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($venda['data_venda'])) ?></td>
                                    <td><?= htmlspecialchars($venda['cliente_nome']) ?></td>
                                    <td><?= $venda['cliente_cpf'] ?></td>
                                    <td>
                                        <span class="text-muted" style="font-size: 0.875em;">
                                            <?= htmlspecialchars($venda['produtos']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?>
                                    </td>
                                    <td>
                                       <?php if($venda['estornada'] == 1): ?>
                                        <span class="badge bg-danger">
                                            Estornada
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                        <a href="vendas_detalhes.php?id=<?= $venda['id_venda'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarExstorno(<?= $venda['id_venda'] ?>)" title="Estorno">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
    <br>
    <div class="col-md-12">
        <!-- Top 5 Produtos -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header py-2">
                    <h5 class="card-title h6 mb-0">Top 20 Produtos Mais Vendidos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_produtos as $produto): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($produto['nome_produto']) ?></td>
                                        <td class="text-center"><?= $produto['total_vendido'] ?></td>
                                        <td class="text-end">
                                            R$ <?= number_format($produto['valor_total'], 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
    function confirmarExstorno(id){
        if (confirm('Tem certeza que deseja estornar a venda?')) {
            fetch('api/estornar_venda.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_venda: id
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Venda estornada com sucesso!");
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao estornar Venda');
            });
        }
    }
</script>
<?php include 'includes/footer.php'; ?>
