<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Obras - Paróquia São Pedro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        }
        .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <div class="text-center mb-4">
                    <h4>Sistema de Obras</h4>
                    <small class="text-muted">Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome_completo']); ?></small>
                    <div class="mt-3">
                        <a href="/gerencialParoquia/dashboard.html" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-left"></i> Voltar aos Módulos
                        </a>
                    </div>
                </div>
                <?php $current_page = $_GET['page'] ?? 'dashboard'; ?>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>" href="/gerencialParoquia/projetos-modulos/obras/index.php?page=dashboard">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'relatorios' ? 'active' : ''; ?>" href="/gerencialParoquia/projetos-modulos/obras/index.php?page=relatorios">
                            <i class="bi bi-file-earmark-text"></i> Relatórios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'analytics' ? 'active' : ''; ?>" href="/gerencialParoquia/projetos-modulos/obras/index.php?page=analytics">
                            <i class="bi bi-graph-up"></i> Analytics BI
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'cadastro_obra' ? 'active' : ''; ?>" href="/gerencialParoquia/projetos-modulos/obras/pages/cadastro_obra.php">
                            <i class="bi bi-plus-circle"></i> Nova Obra
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="/gerencialParoquia/projetos-modulos/obras/auth.php?logout=1">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-10 content">
                <!-- Content will be injected here -->
