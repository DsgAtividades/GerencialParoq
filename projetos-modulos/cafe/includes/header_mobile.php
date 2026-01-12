<?php
require_once __DIR__ . '/verifica_permissao.php';
require_once __DIR__ . '/funcoes.php';
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema Festa Junina - Mobile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery Mask -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- QR Code library -->
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/dist/html5-qrcode.min.js"></script>
    <style>
        /* Ajustes para mobile */
        @media (max-width: 768px) {
            .navbar-nav {
                margin-top: 1rem;
            }
            .nav-item {
                margin-bottom: 0.5rem;
            }
            .nav-link {
                padding: 0.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <a class="navbar-brand" href="index.php">Festa Junina</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm ms-2">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <?php if (temPermissao('gerenciar_vendas')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="vendas_mobile.php">
                            <i class="bi bi-cart"></i> Vendas
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (temPermissao('gerenciar_transacoes')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="saldos_mobile.php">
                            <i class="bi bi-wallet2"></i> Saldos
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (temPermissao('gerenciar_cartoes')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="alocar_cartao_mobile.php">
                            <i class="bi bi-credit-card"></i> Alocar Cartão
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (temPermissao('gerenciar_pessoas')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="pessoas_mobile.php">
                            <i class="bi bi-people"></i> Pessoas
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (temPermissao('gerenciar_produtos')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="produtos_mobile.php">
                            <i class="bi bi-box"></i> Produtos
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (temPermissao('gerenciar_categorias')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="categorias_mobile.php">
                            <i class="bi bi-tags"></i> Categorias
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (temPermissao('gerenciar_produtos')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="produtos_estoque.php">
                            <i class="bi bi-box-seam me-2"></i> Controle de Estoque
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (temPermissao('visualizar_relatorios')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="relatorios_mobile.php">
                            <i class="bi bi-graph-up"></i> Relatórios
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Sair do Sistema
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Espaço para compensar a navbar fixa -->
    <div style="margin-top: 70px;"></div>
</body>
