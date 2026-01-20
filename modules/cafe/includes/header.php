<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once __DIR__ . '/verifica_permissao.php';
require_once __DIR__ . '/funcoes.php';

verificarLogin();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSPA café</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Tema Cafeteria -->
    <link href="css/cafe-theme.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- QR Code library -->
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/dist/html5-qrcode.min.js"></script>
    <!-- Custom CSS -->
    <style>
        /* Reset de espaçamento */
        body {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }
        
        html {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        
        /* Navbar com ícones integrados */
        .navbar {
            min-height: 80px;
            padding: 0.7rem 1rem;
            overflow: visible;
            z-index: 1080;
            position: relative;
            top: 0;
            margin-top: 0;
        }
        
        /* Ajustar main-wrapper para não ter padding-top */
        .main-wrapper {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }

        .navbar .container-fluid {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            position: relative;
            z-index: 1080;
        }
        
        .navbar-brand {
            flex-shrink: 0;
            padding-right: 0.5rem;
            font-size: 1rem;
            display: flex;
            align-items: center;
            line-height: 1.2;
            padding-top: 2px;
        }
        
        .navbar .d-flex.ms-3 {
            flex-shrink: 0;
            gap: 0.5rem;
        }
        
        /* User Menu */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(248, 240, 175, 0.1);
            border: 2px solid transparent;
            border-radius: 8px;
            color: #f8f0af;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .user-menu:hover {
            background: rgba(248, 240, 175, 0.15);
            border-color: rgba(248, 240, 175, 0.3);
        }
        
        .user-menu i {
            font-size: 1.5rem;
        }
        
        .user-menu .bi-chevron-down {
            font-size: 0.8rem;
            transition: transform 0.2s ease;
        }
        
        .user-menu:hover .bi-chevron-down {
            transform: rotate(180deg);
        }
        
        .user-menu-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: #002930;
            border: 2px solid #ac4a00;
            border-radius: 8px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
            z-index: 1090;
        }
        
        .user-menu-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .user-menu-dropdown a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #f8f0af;
            text-decoration: none;
            transition: background 0.2s ease;
        }
        
        .user-menu-dropdown a:hover {
            background: rgba(248, 240, 175, 0.15);
        }
        
        .user-menu-dropdown a i {
            font-size: 1.2rem;
        }

        /* Container de ícones */
        .nav-icons-container {
            display: flex;
            align-items: center;
            gap: 3px;
            overflow-x: auto;
            overflow-y: hidden;
            flex: 1;
            padding: 12px 0.5rem 10px 0.5rem;
            scrollbar-width: thin;
            min-width: 0;
            -webkit-overflow-scrolling: touch; /* Scroll suave no iOS */
            scroll-behavior: smooth; /* Scroll suave */
        }

        .nav-icons-container::-webkit-scrollbar {
            height: 6px;
        }

        .nav-icons-container::-webkit-scrollbar-track {
            background: rgba(248, 240, 175, 0.1);
            border-radius: 3px;
        }

        .nav-icons-container::-webkit-scrollbar-thumb {
            background: rgba(248, 240, 175, 0.4);
            border-radius: 3px;
            transition: background 0.2s ease;
        }

        .nav-icons-container::-webkit-scrollbar-thumb:hover {
            background: rgba(248, 240, 175, 0.6);
        }
        
        /* Para Firefox */
        .nav-icons-container {
            scrollbar-width: thin;
            scrollbar-color: rgba(248, 240, 175, 0.4) rgba(248, 240, 175, 0.1);
        }

        /* Ícones de navegação */
        .nav-tab-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 52px;
            height: auto;
            padding: 4px 5px 6px 5px;
            background: transparent;
            border: 2px solid transparent;
            border-radius: 8px;
            color: #f8f0af;
            text-decoration: none;
            transition: all 0.2s ease;
            position: relative;
            flex-shrink: 0;
        }

        .nav-tab-item i {
            font-size: 1.3rem;
            transition: transform 0.2s ease;
            margin-bottom: 3px;
            line-height: 1.2;
            display: inline-block;
        }
        
        .nav-tab-item .nav-label {
            font-size: 0.7rem;
            font-weight: 500;
            line-height: 1.1;
            text-align: center;
            margin-top: 2px;
            white-space: nowrap;
            opacity: 0.9;
            transition: opacity 0.2s ease;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nav-tab-item:hover {
            background: rgba(248, 240, 175, 0.15);
            border-color: rgba(248, 240, 175, 0.3);
            color: #ffffff;
            transform: none;
        }
        
        .nav-tab-item:hover .nav-label {
            opacity: 1;
        }

        .nav-tab-item:hover i {
            transform: scale(1.1);
        }

        .nav-tab-item.active {
            background: rgba(248, 240, 175, 0.2);
            border-color: #f8f0af;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(248, 240, 175, 0.2);
        }
        
        .nav-tab-item.active .nav-label {
            opacity: 1;
            font-weight: 600;
        }

        /* Tooltip customizado */
        .nav-tooltip {
            position: fixed;
            background: #f8f0af;
            color: #002930;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            white-space: nowrap;
            z-index: 1060;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease;
            border: 2px solid #ac4a00;
        }

        .nav-tooltip.show {
            opacity: 1;
        }

        .nav-tooltip::before {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 7px solid transparent;
            border-bottom-color: #ac4a00;
        }

        .nav-tooltip::after {
            content: '';
            position: absolute;
            bottom: calc(100% - 2px);
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-bottom-color: #f8f0af;
        }
        
        /* Fallback tooltip CSS (caso JS não carregue) */
        .nav-tab-item[title]:hover::after {
            content: attr(title);
            position: absolute;
            top: calc(100% + 10px);
            left: 50%;
            transform: translateX(-50%);
            background: #f8f0af;
            color: #002930;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
            z-index: 1050;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            border: 2px solid #ac4a00;
        }

        /* Animação suave para ícones */
        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .nav-tab-item:hover i {
            animation: iconPulse 0.6s ease-in-out;
        }
        
        /* Tooltip responsivo */
        @media (max-width: 1200px) {
            .nav-icons-container {
                padding: 12px 0.4rem 10px 0.4rem;
                gap: 2px;
            }
            
            .navbar-brand {
                font-size: 0.9rem;
                padding-right: 0.3rem;
                padding-top: 2px;
            }
            
            .nav-tab-item {
                min-width: 48px;
                padding: 4px 4px 5px 4px;
            }
            
            .nav-tab-item i {
                font-size: 1.2rem;
                margin-bottom: 3px;
                line-height: 1.2;
            }
            
            .nav-tab-item .nav-label {
                font-size: 0.65rem;
            }
            
            .nav-tooltip {
                font-size: 0.75rem;
                padding: 6px 12px;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                min-height: 75px;
            }
            
            .nav-icons-container {
                padding: 12px 0.3rem 10px 0.3rem;
                gap: 2px;
            }
            
            .navbar-brand {
                font-size: 0.85rem;
                padding-right: 0.2rem;
                padding-top: 2px;
            }
            
            .nav-tab-item {
                min-width: 44px;
                padding: 4px 3px 4px 3px;
            }
            
            .nav-tab-item i {
                font-size: 1.1rem;
                margin-bottom: 3px;
                line-height: 1.2;
            }
            
            .nav-tab-item .nav-label {
                font-size: 0.6rem;
            }
            
            .nav-tooltip {
                font-size: 0.7rem;
                padding: 5px 10px;
            }
        }
        
        @media (max-width: 480px) {
            .nav-tooltip {
                font-size: 0.65rem;
                padding: 4px 8px;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">PSPA café</a>
            
            <!-- Navigation Icons -->
            <div class="nav-icons-container" id="navIcons">
        <?php if (temPermissao('gerenciar_dashboard')): ?>
            <a href="index.php" class="nav-tab-item" data-tooltip="Início">
                <i class="bi bi-house-door"></i>
                <span class="nav-label">Início</span>
            </a>
            
            <a href="dashboard_vendas.php" class="nav-tab-item" data-tooltip="Dashboard de Vendas">
                <i class="bi bi-graph-up"></i>
                <span class="nav-label">Dashboard</span>
            </a>
            
            <a href="vendas.php" class="nav-tab-item" data-tooltip="Relatório Vendas">
                <i class="bi bi-cart"></i>
                <span class="nav-label">Relatório</span>
            </a>
            
            <?php if (temPermissao('visualizar_relatorios')): ?>
            <a href="saldos_historico.php" class="nav-tab-item" data-tooltip="Histórico Vendas">
                <i class="bi bi-clock-history"></i>
                <span class="nav-label">Histórico</span>
            </a>
            <?php endif; ?>
            
            <a href="relatorio_categorias.php" class="nav-tab-item" data-tooltip="Relatório por Categoria">
                <i class="bi bi-pie-chart"></i>
                <span class="nav-label">Por Categoria</span>
            </a>
            
            <a href="fechamento_caixa.php" class="nav-tab-item" data-tooltip="Fechamento Caixa">
                <i class="bi bi-calculator"></i>
                <span class="nav-label">Fechamento</span>
            </a>
            
            <?php if (temPermissao('visualizar_relatorios')): ?>
            <a href="relatorios.php" class="nav-tab-item" data-tooltip="Relatórios">
                <i class="bi bi-file-text"></i>
                <span class="nav-label">Relatórios</span>
            </a>
            <?php endif; ?>
            
            <?php if (temPermissao('gerenciar_cartoes')): ?>
            <a href="alocar_cartao_mobile.php" class="nav-tab-item" data-tooltip="Cadastrar Cliente">
                <i class="bi bi-credit-card"></i>
                <span class="nav-label">Cadastrar</span>
            </a>
            <?php endif; ?>
            
            <?php if (temPermissao('gerenciar_pessoas')): ?>
            <a href="pessoas.php" class="nav-tab-item" data-tooltip="Pessoas">
                <i class="bi bi-people"></i>
                <span class="nav-label">Pessoas</span>
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_produtos')): ?>
            <a href="produtos.php" class="nav-tab-item" data-tooltip="Produtos">
                <i class="bi bi-box"></i>
                <span class="nav-label">Produtos</span>
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_categorias')): ?>
            <a href="categorias.php" class="nav-tab-item" data-tooltip="Categorias">
                <i class="bi bi-tags"></i>
                <span class="nav-label">Categorias</span>
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_transacoes')): ?>
            <a href="consulta_saldo.php" class="nav-tab-item" data-tooltip="Consulta Saldos">
                <i class="bi bi-wallet2"></i>
                <span class="nav-label">Consulta</span>
            </a>
            
            <a href="saldos_mobile.php" class="nav-tab-item" data-tooltip="Incluir Crédito">
                <i class="bi bi-cash-coin"></i>
                <span class="nav-label">Incluir</span>
            </a>
            <?php endif; ?>
            
            <?php if (temPermissao('gerenciar_geracao_cartoes')): ?>
            <a href="gerar_cartoes.php" class="nav-tab-item" data-tooltip="Gerar Cartões">
                <i class="bi bi-upc-scan"></i>
                <span class="nav-label">Gerar</span>
            </a>
            <?php endif; ?>
            
            <?php if (temPermissao('gerenciar_pessoas')): ?>
            <a href="pessoas_troca.php" class="nav-tab-item" data-tooltip="Trocar Cartão">
                <i class="bi bi-arrow-left-right"></i>
                <span class="nav-label">Trocar</span>
            </a>
            <?php endif; ?>
            
            <?php endif; ?>
            
            <!-- Link de Vendas Mobile (para atendentes) -->
            <?php if (temPermissao('vendas_mobile')): ?>
            <a href="vendas_mobile.php" class="nav-tab-item" data-tooltip="Vender">
                <i class="bi bi-phone"></i>
                <span class="nav-label">Vender</span>
            </a>
            <?php endif; ?>

            <!-- Itens Administrativos -->
            <?php if (temPermissao('gerenciar_usuarios') || temPermissao('gerenciar_grupos') || temPermissao('gerenciar_permissoes')): ?>

            <?php if (temPermissao('gerenciar_usuarios')): ?>
            <a href="usuarios_lista.php" class="nav-tab-item" data-tooltip="Usuários">
                <i class="bi bi-people-fill"></i>
                <span class="nav-label">Usuários</span>
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_grupos')): ?>
            <a href="gerenciar_grupos.php" class="nav-tab-item" data-tooltip="Grupos">
                <i class="bi bi-diagram-3"></i>
                <span class="nav-label">Grupos</span>
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_permissoes')): ?>
            <a href="gerenciar_permissoes.php" class="nav-tab-item" data-tooltip="Permissões">
                <i class="bi bi-shield-lock"></i>
                <span class="nav-label">Permissões</span>
            </a>
            <?php endif; ?>
            <?php endif; ?>
            </div>
            
            <!-- User Menu -->
            <div class="d-flex align-items-center ms-3" style="position: relative;">
                <div class="user-menu" id="userMenu">
                    <i class="bi bi-person-circle"></i>
                    <span class="d-none d-xl-inline"><?= escapar($_SESSION['usuario_nome'] ?? 'Usuário') ?></span>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="user-menu-dropdown" id="userMenuDropdown">
                    <a href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Sair do Sistema
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Wrapper -->
    <main class="main-wrapper">
        <?php mostrarAlerta(); ?>
        
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // User menu dropdown
            const userMenu = document.getElementById('userMenu');
            const userMenuDropdown = document.getElementById('userMenuDropdown');

            if (userMenu && userMenuDropdown) {
                userMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    userMenuDropdown.classList.toggle('show');
                });

                // Fechar o menu quando clicar fora
                document.addEventListener('click', function(e) {
                    if (!userMenu.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                        userMenuDropdown.classList.remove('show');
                    }
                });
                
                // Prevenir fechamento ao clicar no dropdown
                userMenuDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
            
            // Sistema de Tooltips customizado
            let tooltipElement = null;
            
            function createTooltip() {
                if (!tooltipElement) {
                    tooltipElement = document.createElement('div');
                    tooltipElement.className = 'nav-tooltip';
                    document.body.appendChild(tooltipElement);
                }
                return tooltipElement;
            }
            
            function showTooltip(element, text) {
                const tooltip = createTooltip();
                tooltip.textContent = text;
                tooltip.classList.add('show');
                
                // Posicionar o tooltip
                const rect = element.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();
                
                const left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
                const top = rect.bottom + 10;
                
                tooltip.style.left = left + 'px';
                tooltip.style.top = top + 'px';
            }
            
            function hideTooltip() {
                if (tooltipElement) {
                    tooltipElement.classList.remove('show');
                }
            }
            
            // Adicionar tooltips a todos os itens de navegação
            const navItems = document.querySelectorAll('.nav-icons-container .nav-tab-item');
            
            navItems.forEach(function(item) {
                const tooltipText = item.getAttribute('data-tooltip');
                
                if (tooltipText) {
                    // Adicionar atributo title como fallback
                    item.setAttribute('title', tooltipText);
                    
                    // Mouseenter - mostrar tooltip
                    item.addEventListener('mouseenter', function() {
                        showTooltip(item, tooltipText);
                    });
                    
                    // Mouseleave - esconder tooltip
                    item.addEventListener('mouseleave', function() {
                        hideTooltip();
                    });
                    
                    // Click - esconder tooltip
                    item.addEventListener('click', function() {
                        hideTooltip();
                    });
                }
            });
            
            // Esconder tooltip ao rolar a página
            window.addEventListener('scroll', hideTooltip);
            
            // Destacar item ativo da navegação
            const currentPath = window.location.pathname;
            const currentPage = currentPath.split('/').pop() || 'index.php';
            
            navItems.forEach(function(item) {
                const itemHref = item.getAttribute('href');
                if (itemHref) {
                    // Remove parâmetros de query e hash
                    const itemPage = itemHref.split('/').pop().split('?')[0].split('#')[0];
                    const pageToCompare = currentPage.split('?')[0].split('#')[0];
                    
                    // Comparar página atual com o link
                    if (itemPage === pageToCompare || 
                        (pageToCompare === '' && itemPage === 'index.php') ||
                        (pageToCompare === 'index.php' && itemPage === 'index.php')) {
                        item.classList.add('active');
                        
                        // Scroll para o item ativo (centralizar)
                        setTimeout(() => {
                            item.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                        }, 100);
                    }
                }
            });
        });
        </script>
</body>
</html>
