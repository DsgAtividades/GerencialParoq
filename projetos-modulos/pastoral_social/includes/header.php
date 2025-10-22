<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastoral Social - Sistema de Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .content {
            padding: 20px;
        }
        .nav-link {
            color: #333;
        }
        .nav-link:hover {
            background-color: #e9ecef;
            border-radius: 10px;
        }
        .nav-link.active {
            background-color: #0d6efd;
            color: white;
            border-radius: 10px;
        }
        

    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <div class="text-center mb-4">
                    <h4>Pastoral Social</h4>
                    <small class="text-muted">Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome_completo']); ?></small>
                </div>
                <?php $current_page = $_GET['page'] ?? 'dashboard'; ?>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>" href="index.php?page=dashboard">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'usuarios' ? 'active' : ''; ?>" href="index.php?page=usuarios">
                            <i class="bi bi-people"></i> Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'usuarios_novo' ? 'active' : ''; ?>" href="index.php?page=usuarios_novo">
                            <i class="bi bi-person-plus"></i> Novo Usuário
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'equipe' ? 'active' : ''; ?>" href="index.php?page=equipe">
                            <i class="bi bi-person-badge"></i> Equipe Pastoral
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'estoque' ? 'active' : ''; ?>" href="index.php?page=estoque">
                            <i class="bi bi-box-seam"></i> Estoque
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'calendario' ? 'active' : ''; ?>" href="index.php?page=calendario">
                            <i class="bi bi-calendar-event"></i> Calendário
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'relatorios' ? 'active' : ''; ?>" href="index.php?page=relatorios">
                            <i class="bi bi-file-earmark-text"></i> Relatórios
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="auth.php?logout=1">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-info" href="../../dashboard.php">
                            <i class="bi bi-grid"></i> Voltar aos Módulos
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-10 content">
                <!-- Content will be injected here -->
