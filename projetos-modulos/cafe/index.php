<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';
//verificarLogin();
// Buscar algumas estatísticas básicas
$stats = [
    'total_pessoas' => 0,
    'total_produtos' => 0,
    'total_vendas' => 0,
    'saldo_total' => 0
];

try {
    // Total de pessoas
    if (temPermissao('gerenciar_pessoas')) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM cafe_pessoas");
        $stats['total_pessoas'] = $stmt->fetch()['total'];
    }

    // Total de produtos
    if (temPermissao('gerenciar_produtos')) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM cafe_produtos");
        $stats['total_produtos'] = $stmt->fetch()['total'];
    }

    // Total de vendas
    if (temPermissao('gerenciar_vendas')) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM cafe_vendas");
        $stats['total_vendas'] = $stmt->fetch()['total'];
    }

    // Saldo total em cartões
    if (temPermissao('gerenciar_transacoes')) {
        $stmt = $pdo->query("SELECT SUM(saldo) as total FROM cafe_saldos_cartao");
        $stats['saldo_total'] = $stmt->fetch()['total'] ?? 0;
    }
} catch(PDOException $e) {
    // Ignora erros caso as tabelas ainda não existam
}

include 'includes/header.php';
?>

<div class="container py-4">
    <h1 class="col-md-12 col-xl-12 col-sm-12">Dashboard - Festa Junina</h1>
    
    <div class="row g-4">
        <?php if (temPermissao('gerenciar_pessoas')): ?>
        <div class="col-md-6 col-xl-3 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="float-end">
                        <i class="bi bi-people text-primary display-5"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0">Total de Pessoas</h5>
                    <h3 class="mt-3 mb-3"><?= number_format($stats['total_pessoas'], 0, ',', '.') ?></h3>
                    <a href="pessoas.php" class="btn btn-primary btn-sm w-100">Ver Detalhes</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (temPermissao('gerenciar_produtos')): ?>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="float-end">
                        <i class="bi bi-box text-success display-5"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0">Total de Produtos</h5>
                    <h3 class="mt-3 mb-3"><?= number_format($stats['total_produtos'], 0, ',', '.') ?></h3>
                    <a href="produtos.php" class="btn btn-success btn-sm w-100">Ver Detalhes</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (temPermissao('gerenciar_dashboard')): ?>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="float-end">
                        <i class="bi bi-cart text-warning display-5"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0">Total de Vendas</h5>
                    <h3 class="mt-3 mb-3"><?= number_format($stats['total_vendas'], 0, ',', '.') ?></h3>
                    <a href="vendas.php" class="btn btn-warning btn-sm w-100 text-white">Ver Detalhes</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (temPermissao('gerenciar_saldo_total')): ?>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="float-end">
                        <i class="bi bi-wallet2 text-info display-5"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0">Saldo Total</h5>
                    <h3 class="mt-3 mb-3">R$ <?= number_format($stats['saldo_total'], 2, ',', '.') ?></h3>
                    <a href="saldos_lista.php" class="btn btn-info btn-sm w-100 text-white">Ver Detalhes</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Ações Rápidas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php if (temPermissao('gerenciar_pessoas')): ?>
                        <div class="col-md-4">
                            <a href="pessoas_novo.php" class="btn btn-primary w-100">
                                <i class="bi bi-person-plus"></i> Nova Pessoa
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if (temPermissao('gerenciar_produtos')): ?>
                        <div class="col-md-4">
                            <a href="produtos_novo.php" class="btn btn-success w-100">
                                <i class="bi bi-plus-square"></i> Novo Produto
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if (temPermissao('gerenciar_vendas')): ?>
                        <div class="col-md-4">
                            <a href="vendas_mobile.php" class="btn btn-warning w-100 text-white">
                                <i class="bi bi-cart-plus"></i> Nova Venda
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3 mt-2">
                        <?php if (temPermissao('gerenciar_transacoes')): ?>
                        <div class="col-md-4">
                            <a href="saldos_mobile.php" class="btn btn-info w-100 text-white">
                                <i class="bi bi-cash"></i> Adicionar Crédito
                            </a>
                        </div>
                        <?php endif; ?>

                        
                        <?php if (temPermissao('gerenciar_geracao_cartoes')): ?>
                            <div class="col-md-4">
                                <a href="gerar_cartoes.php" class="btn btn-secondary w-100">
                                    <i class="bi bi-upc-scan"></i> Gerar Cartões
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if (temPermissao('gerenciar_cartoes')): ?>
                            <div class="col-md-4">
                                <a href="alocar_cartao_mobile.php" class="btn btn-secondary w-100">
                                    <i class="bi bi-upc-scan"></i> Alocar Cartão
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
