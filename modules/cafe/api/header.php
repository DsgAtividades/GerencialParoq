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
    <title>Sistema Festa Junina</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- QR Code library -->
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/dist/html5-qrcode.min.js"></script>
    <!-- Custom CSS -->
    <style>
        :root {
            --sidebar-width: 250px;
        }
        
        body {
            min-height: 100vh;
            padding-top: 60px;
        }

        .navbar-nav .nav-item {
            position: relative;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .user-menu:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .user-menu-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border: 1px solid rgba(0,0,0,.15);
            border-radius: 0.25rem;
            padding: 0.5rem 0;
            min-width: 200px;
            display: none;
            z-index: 1000;
        }

        .user-menu-dropdown.show {
            display: block;
        }

        .user-menu-dropdown a {
            display: block;
            padding: 0.5rem 1rem;
            color: #dc3545;
            text-decoration: none;
        }

        .user-menu-dropdown a:hover {
            background-color: #f8f9fa;
        }

        .dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.255em;
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
        }

        .user-menu {
            cursor: pointer;
        }
        
        @media (min-width: 750px) {
            #sidebar {
                width: var(--sidebar-width);
                position: fixed;
                top: 56px;
                bottom: 0;
                left: -var(--sidebar-width);
                z-index: 1000;
                background-color: #f8f9fa;
                overflow-y: auto;
                box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            }
        

            #sidebar.active {
                left: 0;
            }

            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }
            #btn-menu{
                display:none;
            }

            .navbar-toggler {
                display: none;
            }
            .content {
                margin-left: var(--sidebar-width);
            }
        }

        .overlay.active {
            display: block;
        }

        .content {
            padding: 20px;
            transition: 0.3s;
            left: 250px
            }

        

        @media (max-width: 749px) {
            #sidebar {
                left: 0;
                display:none;
            }
            .content {
                margin-left: 0;
            }
            #sidebar.active {
                display:block;
                width: 100%;
                height: 100%;
                position: absolute;
                top: 56px;
                bottom: 0;
                left: -var(--sidebar-width);
                z-index: 1000;
                background-color: #f8f9fa;
            }

            .container.active {
                display:none;
            }
            
            #btn-menu{
                width: 40px;
                height: 35px;
                border: 1px solid #FFFFFF;
                border-radius: 5px;
                background-color: rgb(13, 110, 253);
                cursor: pointer;
            }

            button.active .linha:nth-child(1){
                transform: translateY(6px) rotate(-45deg);
            }
            button.active .linha:nth-child(2){
                width:0;
            }
            button.active .linha:nth-child(3){
                transform: translateY(-8px) rotate(45deg);
            }

            .linha {
                width: 20px;
                height: 1px;
                background-color: #FFFFFF;
                display: block;
                margin: 6px auto;
                position: relative;
                transform-origin: center;
                transition: 0.2s;
            }
            #back{
                position: fixed;
                top: 0;
                left: 0;
                background-color: rgba(0,0,0,0.5);
                width: 100%;
                height: 100%;
                display: none;
            }
            #back.active{
                position: fixed;
                top: 0;
                left: 0;
                background-color: rgba(0,0,0,0.5);
                width: 100%;
                height: 100%;
                display: block;
            }
            
        }
        .overlay {
            display: none !important;
            position: fixed;
        }

        .nav-divider {
            height: 1px;
            background-color: #dee2e6;
            margin: 0.5rem 1rem;
        }

        .list-group-item {
            border: none;
            padding: 0.5rem 1rem;
        }

        .list-group-item:hover {
            background-color: #e9ecef;
        }

        .list-group-item i {
            width: 24px;
            text-align: center;
            margin-right: 8px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Festa Junina</a>
            <div class="d-flex align-items-center">
                <a href="logout.php" class="btn btn-outline-light btn-sm me-2 d-lg-none">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
                <button id='btn-menu' type="button" onclick="animar()">
                    <span class="linha"></span>
                    <span class="linha"></span>
                    <span class="linha"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <div class="user-menu" id="userMenu">
                            <i class="bi bi-person-circle"></i>
                            <span><?= escapar($_SESSION['usuario_nome'] ?? 'Usuário') ?></span>
                            <i class="bi bi-chevron-down"></i>
                        </div>
                        <div class="user-menu-dropdown" id="userMenuDropdown">
                            <a href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Sair do Sistema
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Overlay for mobile -->
    <div class="overlay" onclick="toggleSidebar()"></div>
    <!-- Sidebar -->
    <div id="sidebar">
        
        <div class="list-group list-group-flush">
        <?php if (temPermissao('gerenciar_dashboard')): ?>
            <a href="index.php" class="list-group-item list-group-item-action">
                <i class="bi bi-house-door"></i> Início
            </a>
            
            <a href="dashboard_vendas.php" class="list-group-item list-group-item-action">
                <i class="bi bi-graph-up"></i> Dashboard de Vendas
            </a>
            <a href="vendas.php" class="list-group-item list-group-item-action">
                <i class="bi bi-cart"></i> Relatório Vendas
            </a>
            <?php endif; ?>
            <?php if (temPermissao('gerenciar_vendas_mobile')): ?>
            <a href="vendas_mobile.php" class="list-group-item list-group-item-action">
                <i class="bi bi-phone"></i> Vendas
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_pessoas')): ?>
            <a href="pessoas.php" class="list-group-item list-group-item-action">
                <i class="bi bi-people"></i> Pessoas
            </a>
            <?php endif; ?>
            <?php if (temPermissao('gerenciar_pessoas')): ?>
            <a href="pessoas_troca.php" class="list-group-item list-group-item-action">
                <i class="bi bi-people"></i> Trocar Cartão
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_produtos')): ?>
            <a href="produtos.php" class="list-group-item list-group-item-action">
                <i class="bi bi-box"></i> Produtos
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_categorias')): ?>
            <a href="categorias.php" class="list-group-item list-group-item-action">
                <i class="bi bi-tags"></i> Categorias
            </a>
            <?php endif; ?>

            <!-- <?php if (temPermissao('gerenciar_produtos')): ?>
            <a href="produtos.php" class="list-group-item list-group-item-action">
                <i class="bi bi-box-seam me-2"></i> Controle de Estoque
            </a>
            <?php endif; ?> -->

            <?php if (temPermissao('visualizar_relatorios')): ?>
            <a href="relatorios.php" class="list-group-item list-group-item-action">
                <i class="bi bi-graph-up"></i> Relatórios
            </a>
            <a href="saldos_historico.php" class="list-group-item list-group-item-action">
                <i class="bi bi-clock-history"></i> Histórico Vendas
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_transacoes')): ?>
            <!-- <a href="saldos.php" class="list-group-item list-group-item-action">
                <i class="bi bi-wallet2"></i> Saldos
            </a> -->
            
            <a href="saldos_mobile.php" class="list-group-item list-group-item-action">
                <i class="bi bi-phone"></i> Incluir Crédito
            </a>
            <a href="consulta_saldo.php" class="list-group-item list-group-item-action">
                <i class="bi bi-wallet2"></i> Consulta Saldos
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_cartoes')): ?>
            <a href="alocar_cartao_mobile.php" class="list-group-item list-group-item-action">
                <i class="bi bi-credit-card"></i> Entrada Festa
            </a>
            <?php endif; ?>
            <?php if (temPermissao('gerenciar_geracao_cartoes')): ?>
            <a href="gerar_cartoes.php" class="list-group-item list-group-item-action">
                <i class="bi bi-upc-scan"></i> Gerar Cartões
            </a>
            <?php endif; ?>

            <!-- Divisor para seção administrativa -->
            <?php if (temPermissao('gerenciar_usuarios') || temPermissao('gerenciar_grupos') || temPermissao('gerenciar_permissoes')): ?>
            <div class="nav-divider"></div>

            <?php if (temPermissao('gerenciar_usuarios')): ?>
            <a href="usuarios_lista.php" class="list-group-item list-group-item-action">
                <i class="bi bi-people-fill"></i> Usuários
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_grupos')): ?>
            <a href="gerenciar_grupos.php" class="list-group-item list-group-item-action">
                <i class="bi bi-diagram-3"></i> Grupos
            </a>
            <?php endif; ?>

            <?php if (temPermissao('gerenciar_permissoes')): ?>
            <a href="gerenciar_permissoes.php" class="list-group-item list-group-item-action">
                <i class="bi bi-shield-lock"></i> Permissões
            </a>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- Main Content -->
    <div class="content">
    <?php mostrarAlerta(); ?>
        
    <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
        function animar(){
            window.scrollTo({
            top: 0,
            behavior: 'smooth'
            });
            document.getElementById('btn-menu').classList.toggle('active');
            document.getElementById('back').classList.toggle('active');
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userMenu = document.getElementById('userMenu');
            const userMenuDropdown = document.getElementById('userMenuDropdown');

            if (userMenu && userMenuDropdown) {
                userMenu.addEventListener('click', function(e) {
                    userMenuDropdown.classList.toggle('show');
                });

                // Fechar o menu quando clicar fora
                document.addEventListener('click', function(e) {
                    if (!userMenu.contains(e.target)) {
                        userMenuDropdown.classList.remove('show');
                    }
                });
            }
        });
        document.querySelector('#btn-menu').addEventListener('click', toggleSidebar);
        </script>
</body>
</html>
