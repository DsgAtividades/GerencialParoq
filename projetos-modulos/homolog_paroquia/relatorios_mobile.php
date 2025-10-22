<?php
require_once 'config/database.php';
require_once 'includes/header_mobile.php';

$conn = getConnection();

// Definir período padrão (hoje)
$data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

// Resumo do dia
$sql = "SELECT 
            COUNT(DISTINCT v.id) as total_vendas,
            SUM(v.valor_total) as valor_total,
            COUNT(DISTINCT v.pessoa_id) as total_clientes
        FROM vendas v 
        WHERE DATE(v.data_venda) = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$data]);
$resumo = $stmt->fetch(PDO::FETCH_ASSOC);

// Vendas por produto
$sql = "SELECT 
            p.id as id_produto,
            p.nome as nome_produto,
            COUNT(DISTINCT v.id) as total_vendas,
            SUM(i.quantidade) as quantidade_total,
            SUM(i.valor_total) as valor_total
        FROM produtos p
        LEFT JOIN itens_venda i ON p.id = i.produto_id
        LEFT JOIN vendas v ON i.venda_id = v.id 
        WHERE DATE(v.data_venda) = ?
        GROUP BY p.id, p.nome
        ORDER BY valor_total DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$data]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vendas por pessoa
$sql = "SELECT 
            p.id_pessoa,
            p.nome,
            COUNT(DISTINCT v.id_venda) as total_vendas,
            SUM(v.valor_total) as valor_total
        FROM pessoas p
        LEFT JOIN vendas v ON p.id_pessoa = v.id_pessoa 
            AND DATE(v.data_venda) = ?
        GROUP BY p.id_pessoa
        ORDER BY COALESCE(SUM(v.valor_total), 0) DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$data]);
$pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">Relatórios</h2>
                <a href="index.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- Seletor de Data -->
    <div class="row mb-3">
        <div class="col-12">
            <form method="get" class="d-flex gap-2">
                <input type="date" name="data" value="<?= $data ?>" class="form-control">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>
    </div>

    <!-- Resumo -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Resumo do Dia</h5>
                    <div class="row">
                        <div class="col-4 text-center">
                            <div class="h3"><?= $resumo['total_vendas'] ?? 0 ?></div>
                            <div class="text-muted">Vendas</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="h3">R$ <?= number_format($resumo['valor_total'] ?? 0, 2, ',', '.') ?></div>
                            <div class="text-muted">Total</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="h3"><?= $resumo['total_clientes'] ?? 0 ?></div>
                            <div class="text-muted">Clientes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Produtos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Vendas por Produto</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-end">Qtd</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos as $produto): ?>
                                <tr>
                                    <td><?= $produto['nome_produto'] ?></td>
                                    <td class="text-end"><?= $produto['quantidade_total'] ?? 0 ?></td>
                                    <td class="text-end">R$ <?= number_format($produto['valor_total'] ?? 0, 2, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Relatório de Vendas por Pessoa -->
    <div class="row">
        <div class="col-12">
            <h3 class="h5">Vendas por Pessoa</h3>
            <?php if (empty($pessoas)): ?>
                <div class="alert alert-info">
                    Nenhuma venda registrada nesta data.
                </div>
            <?php else: ?>
                <?php foreach ($pessoas as $pessoa): ?>
                    <?php if ($pessoa['total_vendas'] > 0): ?>
                        <div class="card mb-2">
                            <div class="card-body p-2">
                                <h5 class="card-title h6 mb-1"><?= htmlspecialchars($pessoa['nome']) ?></h5>
                                <p class="card-text small mb-1">
                                    <strong>Vendas:</strong> <?= $pessoa['total_vendas'] ?? 0 ?><br>
                                    <strong>Total:</strong> R$ <?= number_format($pessoa['valor_total'] ?? 0, 2, ',', '.') ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer_mobile.php';
?>
