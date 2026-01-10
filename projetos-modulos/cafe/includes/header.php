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
        /* Navbar com ícones integrados */
        .navbar {
            min-height: 60px;
            padding: 0.5rem 1rem;
        }

        .navbar .container-fluid {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Container de ícones */
        .nav-icons-container {
            display: flex;
            align-items: center;
            gap: 4px;
            overflow-x: auto;
            overflow-y: hidden;
            flex: 1;
            padding: 0 0.5rem;
            scrollbar-width: thin;
            max-width: calc(100vw - 600px);
        }

        .nav-icons-container::-webkit-scrollbar {
            height: 4px;
        }

        .nav-icons-container::-webkit-scrollbar-track {
            background: rgba(248, 240, 175, 0.1);
        }

        .nav-icons-container::-webkit-scrollbar-thumb {
            background: rgba(248, 240, 175, 0.3);
            border-radius: 2px;
        }

        .nav-icons-container::-webkit-scrollbar-thumb:hover {
            background: rgba(248, 240, 175, 0.5);
        }

        /* Ícones de navegação */
        .nav-tab-item {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 42px;
            padding: 0 10px;
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
            font-size: 1.25rem;
            transition: transform 0.2s ease;
        }

        .nav-tab-item:hover {
            background: rgba(248, 240, 175, 0.15);
            border-color: rgba(248, 240, 175, 0.3);
            color: #ffffff;
            transform: translateY(-2px);
        }

        .nav-tab-item:hover i {
            transform: scale(1.15);
        }

        .nav-tab-item.active {
            background: rgba(248, 240, 175, 0.2);
            border-color: #f8f0af;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(248, 240, 175, 0.2);
        }

        /* Tooltip */
        .nav-tab-item::after {
            content: attr(data-tooltip);
            position: absolute;
            top: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%) translateY(-5px);
            background: #f8f0af;
            color: #002930;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: all 0.2s ease;
            z-index: 1050;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .nav-tab-item::before {
            content: '';
            position: absolute;
            top: calc(100% + 2px);
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-bottom-color: #f8f0af;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            z-index: 1050;
        }

        .nav-tab-item:hover::after,
        .nav-tab-item:hover::before {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        /* Responsividade */
        @media (max-width: 1200px) {
            .nav-icons-container {
                max-width: calc(100vw - 500px);
            }
            
            .nav-tab-item {
                min-width: 38px;
                height: 38px;
            }
            
            .nav-tab-item i {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                min-height: 56px;
            }
            
            .nav-icons-container {
                max-width: calc(100vw - 200px);
                padding: 0 0.25rem;
                gap: 2px;
            }
            
            .nav-tab-item {
                min-width: 36px;
                height: 36px;
                padding: 0 8px;
            }
            
            .nav-tab-item i {
                font-size: 1rem;
            }
            
            .nav-tab-item::after {
                font-size: 0.7rem;
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
            </a>
            
            <a href="dashboard_vendas.php" class="nav-tab-item" data-tooltip="Dashboard de Vendas">
                <i class="bi bi-graph-up"></i>
            </a>
            
            <a href="vendas.php" class="nav-tab-item" data-tooltip="Relatório Vendas">
                <i class="bi bi-cart"></i>
            </a>
            
            <?php if (temPermissao('visualizar_relatorios')): ?>
            <a href="saldos_historico.php" class="nav-tab-item" data-tooltip="Histórico Vendas">
                <i class="bi bi-clock-history"></i>
            </a>
            <?php endif; ?>
            
            <a href="relatorio_categorias.php" class="nav-tab-item" data-tooltip="Relatório por Categoria">
                <i class="bi bi-pie-chart"></i>
            </a>
            
            <a href="fechamento_caixa.php" class="nav-tab-item" data-tooltip="Fechamento Caixa">
                <i class="bi bi-calculator"></i>
            </a>
            
            <?php if (temPermissao('visualizar_relatorios')): ?>
            <a href="relatorios.php" class="nav-tab-item" data-tooltip="Relatórios">
                <i class="bi bi-file-text"></i>
            </a>
            <?php endif; ?>
            
            <?php if (temPermissao('gerenciar_cartoes')): ?>
            <a href="alocar_cartao_mobile.php" class="nav-tab-item" data-tooltip="Cadastrar Cliente">
                <i class="bi bi-credit-card"></i>
            </a>
            <?php endif; ?>
            
            <?php if (temPermissao('gerenciar_pessoas')): ?>
            <a href="pessoas.php" class="nav-tab-item" data-tooltip="Pessoas">
                <i class="bi bi-people"></i>
            </a>
            <?php endif; ?>
            
            <?php if (temPermissao('gerenciar_vendas_mobile')): ?>
            <a href="vendas_mobile.php" class="nav-tab-item" data-tooltip="Vender">
                <i class="bi bi-phone"></i>
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_produtos')): ?>
            <a href="produtos.php" class="nav-tab-item" data-tooltip="Produtos">
                <i class="bi bi-box"></i>
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_categorias')): ?>
            <a href="categorias.php" class="nav-tab-item" data-tooltip="Categorias">
                <i class="bi bi-tags"></i>
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_transacoes')): ?>
            <a href="consulta_saldo.php" class="nav-tab-item" data-tooltip="Consulta Saldos">
                <i class="bi bi-wallet2"></i>
            </a>
            
            <a href="saldos_mobile.php" class="nav-tab-item" data-tooltip="Incluir Crédito">
                <i class="bi bi-cash-coin"></i>
            </a>
            <?php endif; ?>
            
            <?php if (temPermissao('gerenciar_geracao_cartoes')): ?>
            <a href="gerar_cartoes.php" class="nav-tab-item" data-tooltip="Gerar Cartões">
                <i class="bi bi-upc-scan"></i>
            </a>
            <?php endif; ?>
            
            <?php if (temPermissao('gerenciar_pessoas')): ?>
            <a href="pessoas_troca.php" class="nav-tab-item" data-tooltip="Trocar Cartão">
                <i class="bi bi-arrow-left-right"></i>
            </a>
            <?php endif; ?>
            
            <?php endif; ?>

            <!-- Itens Administrativos -->
            <?php if (temPermissao('gerenciar_usuarios') || temPermissao('gerenciar_grupos') || temPermissao('gerenciar_permissoes')): ?>

            <?php if (temPermissao('gerenciar_usuarios')): ?>
            <a href="usuarios_lista.php" class="nav-tab-item" data-tooltip="Usuários">
                <i class="bi bi-people-fill"></i>
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_grupos')): ?>
            <a href="gerenciar_grupos.php" class="nav-tab-item" data-tooltip="Grupos">
                <i class="bi bi-diagram-3"></i>
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_permissoes')): ?>
            <a href="gerenciar_permissoes.php" class="nav-tab-item" data-tooltip="Permissões">
                <i class="bi bi-shield-lock"></i>
            </a>
            <?php endif; ?>
            <?php endif; ?>
            </div>
            
            <!-- User Menu -->
            <div class="d-flex align-items-center ms-3">
                <a href="/gerencialParoq/dashboard.html" class="btn btn-outline-light btn-sm me-2 d-none d-lg-flex">
                    <i class="bi bi-arrow-left"></i> <span class="ms-1">Voltar</span>
                </a>
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
                    userMenuDropdown.classList.toggle('show');
                });

                // Fechar o menu quando clicar fora
                document.addEventListener('click', function(e) {
                    if (!userMenu.contains(e.target)) {
                        userMenuDropdown.classList.remove('show');
                    }
                });
            }
            
            // Destacar item ativo da navegação
            const currentPath = window.location.pathname;
            const currentPage = currentPath.split('/').pop() || 'index.php';
            const navItems = document.querySelectorAll('.nav-icons-container .nav-tab-item');
            
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
