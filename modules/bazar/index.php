<?php
session_start();
require_once '../../config/database.php';

// Verificar se o usuário está logado no módulo específico
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    header('Location: ../../module_login.html?module=bazar');
    exit;
}

// Verificar se o usuário tem acesso a este módulo específico
if (!isset($_SESSION['module_access']) || $_SESSION['module_access'] !== 'bazar') {
    header('Location: ../../module_login.html?module=bazar');
    exit;
}

// Verificar timeout da sessão do módulo (2 horas)
if (isset($_SESSION['module_login_time']) && (time() - $_SESSION['module_login_time'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: ../../module_login.html?module=bazar');
    exit;
}

$module_name = 'Bazar';
$module_description = 'Sistema de controle de estoque e vendas do bazar paroquial';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $module_name; ?> - Sistema de Gestão Paroquial</title>
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/module.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="module-container">
        <!-- Header do Módulo -->
        <header class="module-header">
            <div class="header-content">
                <div class="module-info">
                    <h1><?php echo $module_name; ?></h1>
                    <p><?php echo $module_description; ?></p>
                </div>
                <div class="user-info">
                    <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION['module_username'] ?? 'Usuário'); ?>!</span>
                    <a href="../../auth/module_logout.php" class="botao-sair-modulo">
                        <i class="fas fa-sign-out-alt"></i> Sair do Módulo
                    </a>
                </div>
            </div>
        </header>

        <!-- Navegação do Módulo -->
        <nav class="module-nav">
            <ul>
                <li><a href="#dashboard" class="nav-link active" data-section="dashboard">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a></li>
                <li><a href="#estoque" class="nav-link" data-section="estoque">
                    <i class="fas fa-boxes"></i> Estoque
                </a></li>
                <li><a href="#vendas" class="nav-link" data-section="vendas">
                    <i class="fas fa-shopping-cart"></i> Vendas
                </a></li>
                <li><a href="#produtos" class="nav-link" data-section="produtos">
                    <i class="fas fa-tags"></i> Produtos
                </a></li>
                <li><a href="#relatorios" class="nav-link" data-section="relatorios">
                    <i class="fas fa-chart-bar"></i> Relatórios
                </a></li>
            </ul>
        </nav>

        <!-- Conteúdo Principal -->
        <main class="module-content">
            <!-- Dashboard -->
            <section id="dashboard" class="content-section active">
                <div class="content-card">
                    <h2>Dashboard</h2>
                    <p>Visão geral do sistema de bazar</p>
                    
                    <div class="stats-module">
                        <div class="stat-module">
                            <div class="stat-module-icon">📦</div>
                            <div class="stat-module-number">0</div>
                            <div class="stat-module-label">Produtos em Estoque</div>
                        </div>
                        
                        <div class="stat-module">
                            <div class="stat-module-icon">💰</div>
                            <div class="stat-module-number">R$ 0,00</div>
                            <div class="stat-module-label">Vendas do Mês</div>
                        </div>
                        
                        <div class="stat-module">
                            <div class="stat-module-icon">⚠️</div>
                            <div class="stat-module-number">0</div>
                            <div class="stat-module-label">Estoque Baixo</div>
                        </div>
                        
                        <div class="stat-module">
                            <div class="stat-module-icon">📊</div>
                            <div class="stat-module-number">0</div>
                            <div class="stat-module-label">Total de Vendas</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Estoque -->
            <section id="estoque" class="content-section">
                <div class="content-card">
                    <h2>Controle de Estoque</h2>
                    <button class="btn-module btn-module-primary">
                        <i class="fas fa-plus"></i> Adicionar Produto
                    </button>
                </div>
                
                <div class="content-card">
                    <p style="text-align: center; color: #7f8c8d; font-size: 1.1rem; margin: 40px 0;">
                        <i class="fas fa-boxes" style="font-size: 3rem; margin-bottom: 20px; display: block; opacity: 0.5;"></i>
                        Nenhum produto cadastrado ainda.<br>
                        Clique em "Adicionar Produto" para começar.
                    </p>
                </div>
            </section>

            <!-- Vendas -->
            <section id="vendas" class="content-section">
                <div class="content-card">
                    <h2>Controle de Vendas</h2>
                    <button class="btn-module btn-module-success">
                        <i class="fas fa-plus"></i> Nova Venda
                    </button>
                </div>
                
                <div class="content-card">
                    <p style="text-align: center; color: #7f8c8d; font-size: 1.1rem; margin: 40px 0;">
                        <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 20px; display: block; opacity: 0.5;"></i>
                        Nenhuma venda registrada ainda.<br>
                        Clique em "Nova Venda" para começar.
                    </p>
                </div>
            </section>

            <!-- Produtos -->
            <section id="produtos" class="content-section">
                <div class="content-card">
                    <h2>Gestão de Produtos</h2>
                    <button class="btn-module btn-module-primary">
                        <i class="fas fa-plus"></i> Novo Produto
                    </button>
                </div>
                
                <div class="content-card">
                    <p style="text-align: center; color: #7f8c8d; font-size: 1.1rem; margin: 40px 0;">
                        <i class="fas fa-tags" style="font-size: 3rem; margin-bottom: 20px; display: block; opacity: 0.5;"></i>
                        Nenhum produto cadastrado ainda.<br>
                        Clique em "Novo Produto" para começar.
                    </p>
                </div>
            </section>

            <!-- Relatórios -->
            <section id="relatorios" class="content-section">
                <div class="content-card">
                    <h2>Relatórios</h2>
                    <p style="text-align: center; color: #7f8c8d; font-size: 1.1rem; margin: 40px 0;">
                        <i class="fas fa-chart-bar" style="font-size: 3rem; margin-bottom: 20px; display: block; opacity: 0.5;"></i>
                        Nenhum relatório disponível ainda.<br>
                        Adicione dados para gerar relatórios.
                    </p>
                </div>
            </section>
        </main>
    </div>

    <script src="../../assets/js/paginas/modulo.js"></script>
</body>
</html>
