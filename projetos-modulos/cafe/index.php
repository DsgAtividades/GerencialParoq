<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

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

<style>
    .dashboard-hero {
        background: linear-gradient(135deg, #002930 0%, #004d5a 100%);
        border-radius: 16px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        color: #f8f0af !important;
        box-shadow: 0 8px 24px rgba(0, 41, 48, 0.2);
    }
    
    .dashboard-hero h1,
    .dashboard-hero h1 * {
        font-size: 2.5rem !important;
        font-weight: 700 !important;
        margin-bottom: 0.5rem !important;
        color: #f8f0af !important;
    }
    
    .dashboard-hero h1 i {
        color: #f8f0af !important;
    }
    
    .dashboard-hero p {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 0;
        color: #f8f0af !important;
    }
    
    .stat-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 1.75rem;
        height: 100%;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 41, 48, 0.08);
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--card-color-start), var(--card-color-end));
    }
    
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 41, 48, 0.15);
    }
    
    .stat-card-primary {
        --card-color-start: #0d6efd;
        --card-color-end: #0a58ca;
    }
    
    .stat-card-success {
        --card-color-start: #198754;
        --card-color-end: #146c43;
    }
    
    .stat-card-warning {
        --card-color-start: #ffc107;
        --card-color-end: #ffb300;
    }
    
    .stat-card-info {
        --card-color-start: #0dcaf0;
        --card-color-end: #0aa2c0;
    }
    
    .stat-icon {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, var(--card-color-start), var(--card-color-end));
        color: white !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .stat-icon i {
        color: white !important;
        font-size: 2rem !important;
        display: inline-block !important;
    }
    
    .stat-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }
    
    .stat-value {
        font-size: 2.25rem;
        font-weight: 700;
        color: #002930;
        margin-bottom: 1rem;
        line-height: 1.2;
    }
    
    .stat-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        font-size: 0.9rem;
    }
    
    .stat-link-primary {
        background: linear-gradient(135deg, #0d6efd, #0a58ca);
        color: white;
    }
    
    .stat-link-primary:hover {
        background: linear-gradient(135deg, #0a58ca, #084298);
        color: white;
        transform: translateX(4px);
    }
    
    .stat-link-success {
        background: linear-gradient(135deg, #198754, #146c43);
        color: white;
    }
    
    .stat-link-success:hover {
        background: linear-gradient(135deg, #146c43, #0f5132);
        color: white;
        transform: translateX(4px);
    }
    
    .stat-link-warning {
        background: linear-gradient(135deg, #ffc107, #ffb300);
        color: #002930;
    }
    
    .stat-link-warning:hover {
        background: linear-gradient(135deg, #ffb300, #ff9800);
        color: #002930;
        transform: translateX(4px);
    }
    
    .stat-link-info {
        background: linear-gradient(135deg, #0dcaf0, #0aa2c0);
        color: white;
    }
    
    .stat-link-info:hover {
        background: linear-gradient(135deg, #0aa2c0, #087990);
        color: white;
        transform: translateX(4px);
    }
    
    .quick-actions-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 41, 48, 0.08);
        border: none;
    }
    
    .quick-actions-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f5f1e8;
    }
    
    .quick-actions-header i {
        font-size: 1.5rem;
        color: #ac4a00;
    }
    
    .quick-actions-header h5 {
        margin: 0;
        font-weight: 600;
        color: #002930;
        font-size: 1.25rem;
    }
    
    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.5rem 1rem;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        background: #f8f9fa;
        color: #002930;
        min-height: 120px;
    }
    
    .action-btn:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 41, 48, 0.12);
        border-color: #ac4a00;
        color: #002930;
    }
    
    .action-btn i {
        font-size: 2rem;
        margin-bottom: 0.75rem;
        color: #ac4a00;
    }
    
    .action-btn span {
        font-weight: 500;
        font-size: 0.95rem;
        text-align: center;
    }
    
    .action-btn-primary {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(10, 88, 202, 0.1));
        border-color: #0d6efd;
    }
    
    .action-btn-primary:hover {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.15), rgba(10, 88, 202, 0.15));
    }
    
    .action-btn-success {
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(20, 108, 67, 0.1));
        border-color: #198754;
    }
    
    .action-btn-success:hover {
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.15), rgba(20, 108, 67, 0.15));
    }
    
    .action-btn-warning {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 179, 0, 0.1));
        border-color: #ffc107;
    }
    
    .action-btn-warning:hover {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.15), rgba(255, 179, 0, 0.15));
    }
    
    .action-btn-info {
        background: linear-gradient(135deg, rgba(13, 202, 240, 0.1), rgba(10, 162, 192, 0.1));
        border-color: #0dcaf0;
    }
    
    .action-btn-info:hover {
        background: linear-gradient(135deg, rgba(13, 202, 240, 0.15), rgba(10, 162, 192, 0.15));
    }
    
    .action-btn-secondary {
        background: linear-gradient(135deg, rgba(108, 117, 125, 0.1), rgba(73, 80, 87, 0.1));
        border-color: #6c757d;
    }
    
    .action-btn-secondary:hover {
        background: linear-gradient(135deg, rgba(108, 117, 125, 0.15), rgba(73, 80, 87, 0.15));
    }
    
    @media (max-width: 768px) {
        .dashboard-hero {
            padding: 1.5rem;
        }
        
        .dashboard-hero h1 {
            font-size: 1.75rem;
        }
        
        .stat-value {
            font-size: 1.75rem;
        }
        
        .action-btn {
            min-height: 100px;
            padding: 1rem 0.75rem;
        }
        
        .action-btn i {
            font-size: 1.5rem;
        }
    }
</style>

<div class="container py-4">
    <!-- Hero Section -->
    <div class="dashboard-hero">
        <h1><i class="bi bi-house-door-fill"></i> Bem-vindo ao Sistema</h1>
        <p>Gerencie suas vendas, produtos e clientes de forma eficiente</p>
    </div>
    
    <!-- Estatísticas -->
    <div class="row g-4 mb-4">
        <?php if (temPermissao('gerenciar_pessoas')): ?>
        <div class="col-md-6 col-xl-3 col-sm-12">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-label">Total de Pessoas</div>
                <div class="stat-value"><?= number_format($stats['total_pessoas'], 0, ',', '.') ?></div>
                <a href="pessoas.php" class="stat-link stat-link-primary w-100 text-center">
                    <span>Ver Detalhes</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (temPermissao('gerenciar_produtos')): ?>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="bi bi-box-fill"></i>
                </div>
                <div class="stat-label">Total de Produtos</div>
                <div class="stat-value"><?= number_format($stats['total_produtos'], 0, ',', '.') ?></div>
                <a href="produtos.php" class="stat-link stat-link-success w-100 text-center">
                    <span>Ver Detalhes</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (temPermissao('gerenciar_dashboard')): ?>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="bi bi-cart-fill"></i>
                </div>
                <div class="stat-label">Total de Vendas</div>
                <div class="stat-value"><?= number_format($stats['total_vendas'], 0, ',', '.') ?></div>
                <a href="vendas.php" class="stat-link stat-link-warning w-100 text-center">
                    <span>Ver Detalhes</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (temPermissao('gerenciar_saldo_total')): ?>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-label">Saldo Total</div>
                <div class="stat-value">R$ <?= number_format($stats['saldo_total'], 2, ',', '.') ?></div>
                <a href="consulta_saldo.php" class="stat-link stat-link-info w-100 text-center">
                    <span>Ver Detalhes</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Ações Rápidas -->
    <div class="quick-actions-card">
        <div class="quick-actions-header">
            <i class="bi bi-lightning-charge-fill"></i>
            <h5>Ações Rápidas</h5>
        </div>
        <div class="row g-3">
            <?php if (temPermissao('gerenciar_pessoas')): ?>
            <div class="col-md-4 col-sm-6">
                <a href="alocar_cartao_mobile.php" class="action-btn action-btn-primary">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Cadastrar Cliente</span>
                </a>
            </div>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_produtos')): ?>
            <div class="col-md-4 col-sm-6">
                <a href="produtos_novo.php" class="action-btn action-btn-success">
                    <i class="bi bi-plus-square-fill"></i>
                    <span>Novo Produto</span>
                </a>
            </div>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_vendas')): ?>
            <div class="col-md-4 col-sm-6">
                <a href="vendas_mobile.php" class="action-btn action-btn-warning">
                    <i class="bi bi-cart-plus-fill"></i>
                    <span>Nova Venda</span>
                </a>
            </div>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_transacoes')): ?>
            <div class="col-md-4 col-sm-6">
                <a href="saldos_mobile.php" class="action-btn action-btn-info">
                    <i class="bi bi-cash-coin"></i>
                    <span>Adicionar Crédito</span>
                </a>
            </div>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_geracao_cartoes')): ?>
            <div class="col-md-4 col-sm-6">
                <a href="gerar_cartoes.php" class="action-btn action-btn-secondary">
                    <i class="bi bi-upc-scan"></i>
                    <span>Gerar Cartões</span>
                </a>
            </div>
            <?php endif; ?>
            
            <?php if (temPermissao('gerenciar_cartoes')): ?>
            <div class="col-md-4 col-sm-6">
                <a href="alocar_cartao_mobile.php" class="action-btn action-btn-secondary">
                    <i class="bi bi-credit-card-fill"></i>
                    <span>Alocar Cartão</span>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
