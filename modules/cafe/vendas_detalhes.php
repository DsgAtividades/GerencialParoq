<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';


verificarPermissao('gerenciar_vendas');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: vendas.php');
    exit;
}

// Buscar dados da venda
$stmt = $pdo->prepare("
    SELECT 
        v.*,
        p.nome as cliente_nome,
        p.cpf as cliente_cpf,
        c.codigo as cartao_codigo,
        cx.total_trocos_dados
    FROM cafe_vendas v
    JOIN cafe_pessoas p ON v.id_pessoa = p.id_pessoa
    LEFT JOIN cafe_cartoes c ON p.id_pessoa = c.id_pessoa
    LEFT JOIN cafe_caixas cx ON v.caixa_id = cx.id
    WHERE v.id_venda = ?
");
$stmt->execute([$id]);
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    header('Location: vendas.php');
    exit;
}

// Buscar itens da venda
$stmt = $pdo->prepare("
    SELECT 
        vi.*,
        p.nome_produto as produto_nome
    FROM cafe_itens_venda vi
    JOIN cafe_produtos p ON vi.id_produto = p.id
    WHERE vi.id_venda = ?
    ORDER BY p.nome_produto
");
$stmt->execute([$id]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = 0;
include 'includes/header.php';
?>

<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Detalhes da Venda #<?= $venda['id_venda'] ?></h1>
        <a href="vendas.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="row g-4">
        <!-- Informações da Venda -->
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-header py-2">
                    <h5 class="card-title h6 mb-0">Informações da Venda</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted">Data/Hora:</td>
                            <td><?= date('d/m/Y H:i', strtotime($venda['data_venda'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tipo de Pagamento:</td>
                            <td>
                                <?php
                                $tipo_venda = strtolower(trim($venda['Tipo_venda'] ?? ''));
                                $tipo_labels = [
                                    'dinheiro' => ['label' => 'Dinheiro', 'badge' => 'success'],
                                    'credito' => ['label' => 'Crédito', 'badge' => 'info'],
                                    'debito' => ['label' => 'Débito', 'badge' => 'warning'],
                                    'pix' => ['label' => 'Pix', 'badge' => 'primary'],
                                    'cortesia' => ['label' => 'Cortesia', 'badge' => 'danger'],
                                    'sobras' => ['label' => 'Sobras', 'badge' => 'secondary']
                                ];
                                $tipo_info = $tipo_labels[$tipo_venda] ?? ['label' => ucfirst($tipo_venda), 'badge' => 'secondary'];
                                ?>
                                <span class="badge bg-<?= $tipo_info['badge'] ?>">
                                    <?= $tipo_info['label'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php if ($tipo_venda === 'dinheiro'): ?>
                            <tr>
                                <td class="text-muted">Troco:</td>
                                <td>
                                    <span class="text-muted">
                                        <i class="bi bi-info-circle"></i> Informação não disponível por venda
                                    </span>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="text-muted">Valor Total:</td>
                            <td class="text-success fw-bold">
                                R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Informações Adicionais -->
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-header py-2">
                    <h5 class="card-title h6 mb-0">Informações Adicionais</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted">Atendente:</td>
                            <td><?= htmlspecialchars($venda['Atendente'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">ID da Venda:</td>
                            <td>#<?= $venda['id_venda'] ?></td>
                        </tr>
                        <?php if ($venda['estornada'] == 1): ?>
                            <tr>
                                <td class="text-muted">Status:</td>
                                <td>
                                    <span class="badge bg-danger">Estornada</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>


        <!-- Itens da Venda -->
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h5 class="card-title h6 mb-0">Itens da Venda</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-end">Valor Unit.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['produto_nome']) ?></td>
                                        <td class="text-center"><?= $item['quantidade'] ?></td>
                                        <td class="text-end">
                                            R$ <?= number_format($item['valor_unitario'], 2, ',', '.') ?>
                                        </td>
                                        <td class="text-end">
                                            R$ <?= number_format(($item['quantidade']* $item['valor_unitario']), 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php 
                                $total += ($item['quantidade'] * $item['valor_unitario']);
                                    endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold text-success">
                                        R$ <?= number_format($total, 2, ',', '.') ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
