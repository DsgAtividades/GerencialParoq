<?php
session_start();
require_once 'config/database.php';

// Verificar se o usuário está logado no módulo específico
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    header('Location: ../../module_login.html?module=membros');
    exit;
}

// Verificar se o usuário tem acesso a este módulo específico
if (!isset($_SESSION['module_access']) || $_SESSION['module_access'] !== 'membros') {
    header('Location: ../../module_login.html?module=membros');
    exit;
}

// Verificar timeout da sessão do módulo (2 horas)
if (isset($_SESSION['module_login_time']) && (time() - $_SESSION['module_login_time'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: ../../module_login.html?module=membros');
    exit;
}

// Obter ID da pastoral da URL
$pastoral_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$pastoral_id) {
    header('Location: index.php');
    exit;
}

$module_name = 'Detalhes da Pastoral';
$module_description = 'Informações completas da pastoral';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $module_name; ?> - Sistema de Gestão Paroquial</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link rel="stylesheet" href="../../assets/css/module.css">
    <link rel="stylesheet" href="assets/css/membros.css">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="module-container">
        <!-- Header do Módulo -->
        <header class="module-header">
            <div class="header-content">
                <div class="module-info">
                    <a href="index.php" class="back-button">
                        <i class="fas fa-arrow-left"></i> Voltar para Dashboard
                    </a>
                    <h1 id="pastoral-nome">Carregando...</h1>
                    <p id="pastoral-descricao">Carregando informações da pastoral</p>
                </div>
                <div class="user-info">
                    <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION['module_username'] ?? 'Usuário'); ?>!</span>
                    <a href="../../auth/module_logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </div>
            </div>
        </header>

        <!-- Conteúdo Principal -->
        <main class="module-main">
            <div class="container">
            <!-- Métricas -->
            <div class="row mb-4 metrics-grid">
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="value" id="total-membros">0</div>
                        <div class="label">Total de Membros</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="value" id="membros-ativos">0</div>
                        <div class="label">Membros Ativos</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="value" id="total-coordenadores">0</div>
                        <div class="label">Coordenadores</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="icon" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="value" id="total-eventos">0</div>
                        <div class="label">Próximos Eventos</div>
                    </div>
                </div>
            </div>

            <!-- Informações da Pastoral -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="info-grid" id="info-pastoral">
                        <!-- Informações serão preenchidas via JS -->
                    </div>
                </div>
            </div>

            <!-- Coordenadores -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h3>Coordenadores</h3>
                    <div class="coordinators-list" id="coordenadores">
                        <!-- Coordenadores serão preenchidos via JS -->
                    </div>
                </div>
            </div>

            <!-- Abas -->
            <div class="tabs">
                <button class="tab active" onclick="mostrarAba('membros')">
                    <i class="fas fa-users"></i> Membros
                </button>
                <button class="tab" onclick="mostrarAba('eventos')">
                    <i class="fas fa-calendar"></i> Eventos
                </button>
            </div>

            <!-- Conteúdo das Abas -->
            <!-- Aba Membros -->
            <div id="aba-membros" class="tab-content active">
                <div class="data-table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Função</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-membros">
                            <!-- Membros serão preenchidos via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Aba Eventos -->
            <div id="aba-eventos" class="tab-content">
                <div class="data-table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Nome</th>
                                <th>Horário</th>
                                <th>Local</th>
                                <th>Inscritos</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-eventos">
                            <!-- Eventos serão preenchidos via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="assets/js/api.js"></script>
    <script src="assets/js/pastoral_detalhes.js"></script>
    <script>
        // Inicializar página
        const pastoralId = '<?php echo $pastoral_id; ?>';
        window.addEventListener('DOMContentLoaded', () => {
            carregarDadosPastoral(pastoralId);
        });
    </script>
</body>
</html>


