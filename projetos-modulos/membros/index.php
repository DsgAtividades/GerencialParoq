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

$module_name = 'Módulo de Membros';
$module_description = 'Sistema completo de gestão de membros paroquiais';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $module_name; ?> - Sistema de Gestão Paroquial</title>
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link rel="stylesheet" href="../../assets/css/module.css">
    <link rel="stylesheet" href="assets/css/membros.css">
    <link rel="stylesheet" href="assets/css/calendario_eventos.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="module-container">
        <!-- Header do Módulo -->
        <header class="module-header">
            <div class="header-content">
                <div class="module-info">
                    <h1><i class="fas fa-users"></i> <?php echo $module_name; ?></h1>
                    <p><?php echo $module_description; ?></p>
                </div>
                <div class="user-info">
                    <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION['module_username'] ?? 'Usuário'); ?>!</span>
                    <a href="../../auth/module_logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </div>
            </div>
        </header>

        <!-- Navegação Principal -->
        <nav class="module-nav">
            <ul class="nav-menu">
                <li class="nav-item active">
                    <a href="#dashboard" class="nav-link" data-section="dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#membros" class="nav-link" data-section="membros">
                        <i class="fas fa-users"></i> Membros
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#pastorais" class="nav-link" data-section="pastorais">
                        <i class="fas fa-church"></i> Pastorais
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#eventos" class="nav-link" data-section="eventos">
                        <i class="fas fa-calendar-alt"></i> Eventos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#relatorios" class="nav-link" data-section="relatorios">
                        <i class="fas fa-chart-bar"></i> Relatórios
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#configuracoes" class="nav-link" data-section="configuracoes">
                        <i class="fas fa-cog"></i> Configurações
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Conteúdo Principal -->
        <main class="module-main">
            <!-- Dashboard -->
            <section id="dashboard" class="content-section active">
                <div class="section-header">
                    <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
                    <div class="section-actions">
                        <button class="btn btn-primary" onclick="atualizarDashboard()">
                            <i class="fas fa-sync-alt"></i> Atualizar
                        </button>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <!-- Cards de Estatísticas -->
                    <div class="stats-cards">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3 id="total-membros">-</h3>
                                <p>Total de Membros</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stat-content">
                                <h3 id="membros-ativos">-</h3>
                                <p>Membros Ativos</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-church"></i>
                            </div>
                            <div class="stat-content">
                                <h3 id="total-pastorais">-</h3>
                                <p>Pastorais</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-content">
                                <h3 id="eventos-mes">-</h3>
                                <p>Eventos este Mês</p>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="charts-grid">
                        <div class="chart-card">
                            <h3>Membros por Pastoral</h3>
                            <canvas id="chart-pastorais"></canvas>
                        </div>
                        <div class="chart-card">
                            <h3>Novas Adesões (Últimos 12 meses)</h3>
                            <canvas id="chart-adesoes"></canvas>
                        </div>
                    </div>

                    <!-- Alertas -->
                    <div class="alerts-card">
                        <h3><i class="fas fa-exclamation-triangle"></i> Alertas</h3>
                        <div id="alerts-list" class="alerts-list">
                            <div class="alert-item">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>Carregando alertas...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Membros -->
            <section id="membros" class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-users"></i> Gestão de Membros</h2>
                    <div class="section-actions">
                        <button class="btn btn-info" onclick="importarMembros()" id="btn-importar-membros" style="display: none;">
                            <i class="fas fa-file-upload"></i> Importar
                        </button>
                        <button class="btn btn-secondary" onclick="exportarMembros()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <button class="btn btn-primary" id="btn-novo-membro" onclick="abrirModalMembro()" style="display: none;">
                            <i class="fas fa-plus"></i> Novo Membro
                        </button>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filters-card">
                    <div class="filters-row">
                        <div class="filter-group">
                            <label for="filtro-busca">Buscar:</label>
                            <input type="text" id="filtro-busca" placeholder="Nome, email ou telefone...">
                        </div>
                        <div class="filter-group">
                            <label for="filtro-status">Status:</label>
                            <select id="filtro-status">
                                <option value="">Todos</option>
                                <option value="ativo">Ativo</option>
                                <option value="afastado">Afastado</option>
                                <option value="em_discernimento">Em Discernimento</option>
                                <option value="bloqueado">Bloqueado</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="filtro-pastoral">Pastoral:</label>
                            <select id="filtro-pastoral">
                                <option value="">Todas</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <button class="btn btn-primary" onclick="aplicarFiltros()">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <button class="btn btn-secondary" onclick="limparFiltros()" style="margin-left: 0.5rem;">
                                <i class="fas fa-times"></i> Limpar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Membros -->
                <div class="table-card">
                    <div class="table-header">
                        <h3>Lista de Membros</h3>
                        <div class="table-actions">
                            <span id="total-registros">0 registros</span>
                        </div>
                    </div>
                    <div class="table-container">
                        <table id="tabela-membros" class="data-table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Contato</th>
                                    <th>Pastorais</th>
                                    <th>Status</th>
                                    <th>Data Entrada</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i> Carregando...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-pagination">
                        <button class="btn btn-secondary" onclick="paginarAnterior()" disabled>
                            <i class="fas fa-chevron-left"></i> Anterior
                        </button>
                        <span id="info-paginacao">Página 1 de 1</span>
                        <button class="btn btn-secondary" onclick="paginarProximo()" disabled>
                            Próximo <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Pastorais -->
            <section id="pastorais" class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-church"></i> Gestão de Pastorais</h2>
                    <div class="section-actions">
                        <button class="btn btn-primary" id="btn-nova-pastoral" onclick="abrirModalPastoral()" style="display: none;">
                            <i class="fas fa-plus"></i> Nova Pastoral
                        </button>
                    </div>
                </div>

                <div class="cards-grid" id="pastorais-grid">
                    <div class="loading-card">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Carregando pastorais...</p>
                    </div>
                </div>
            </section>

            <!-- Eventos -->
            <section id="eventos" class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-calendar-alt"></i> Gestão de Eventos</h2>
                    <div class="section-actions">
                        <button class="btn btn-primary" onclick="abrirModalEvento()">
                            <i class="fas fa-plus"></i> Novo Evento
                        </button>
                    </div>
                </div>

                <div class="events-calendar" id="calendario-eventos">
                    <div class="loading-card">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Carregando eventos...</p>
                    </div>
                </div>
            </section>

            <!-- Escalas -->
            <section id="escalas" class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-clipboard-list"></i> Gestão de Escalas</h2>
                    <div class="section-actions">
                        <button class="btn btn-primary" onclick="abrirModalEscala()">
                            <i class="fas fa-plus"></i> Nova Escala
                        </button>
                    </div>
                </div>

                <div class="escalas-container" id="escalas-container">
                    <div class="loading-card">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Carregando escalas...</p>
                    </div>
                </div>
            </section>

            <!-- Relatórios -->
            <section id="relatorios" class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-chart-bar"></i> Relatórios e Análises</h2>
                    <div class="section-actions">
                        <button class="btn btn-secondary" onclick="carregarRelatorios()">
                            <i class="fas fa-sync-alt"></i> Atualizar
                        </button>
                    </div>
                </div>

                <!-- Grid de Relatórios - 2 colunas por linha -->
                <div class="relatorios-dashboard-grid">


                                        <!-- R7: Aniversariantes do Mês -->
                                        <div class="relatorio-card">
                        <div class="relatorio-header">
                            <h3><i class="fas fa-birthday-cake"></i> Aniversariantes do Mês</h3>
                        </div>
                        <div class="relatorio-body">
                            <div class="relatorio-number-card">
                                <div class="number-display" id="numero-aniversariantes">-</div>
                                <div class="number-label">aniversariantes</div>
                            </div>
                            <div class="relatorio-list" id="lista-aniversariantes">
                                <div class="loading-item">
                                    <i class="fas fa-spinner fa-spin"></i> Carregando...
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <!-- R3: Membros por Gênero -->
                    <div class="relatorio-card">
                        <div class="relatorio-header">
                            <h3><i class="fas fa-venus-mars"></i> Membros por Gênero</h3>
                        </div>
                        <div class="relatorio-body">
                            <canvas id="chart-membros-genero"></canvas>
                            <div class="relatorio-footer">
                                <span id="total-genero" class="relatorio-total">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- R4: Faixa Etária -->
                    <div class="relatorio-card">
                        <div class="relatorio-header">
                            <h3><i class="fas fa-birthday-cake"></i> Faixa Etária</h3>
                        </div>
                        <div class="relatorio-body">
                            <canvas id="chart-faixa-etaria"></canvas>
                            <div class="relatorio-footer">
                                <span id="total-faixa-etaria" class="relatorio-total">-</span>
                            </div>
                        </div>
                    </div>
                    
                    
                    <!-- R2: Membros por Status -->
                    <div class="relatorio-card">
                        <div class="relatorio-header">
                            <h3><i class="fas fa-user-check"></i> Membros por Status</h3>
                        </div>
                        <div class="relatorio-body">
                            <canvas id="chart-membros-status"></canvas>
                            <div class="relatorio-footer">
                                <span id="total-status" class="relatorio-total">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- R6: Membros sem Pastoral -->
                    <div class="relatorio-card">
                        <div class="relatorio-header">
                            <h3><i class="fas fa-user-times"></i> Membros sem Pastoral</h3>
                        </div>
                        <div class="relatorio-body">
                            <div class="relatorio-number-card">
                                <div class="number-display" id="numero-sem-pastoral">-</div>
                                <div class="number-label">membros</div>
                            </div>
                            <div class="relatorio-list" id="lista-sem-pastoral">
                                <div class="loading-item">
                                    <i class="fas fa-spinner fa-spin"></i> Carregando...
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </section>

            <!-- Configurações -->
            <section id="configuracoes" class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-cog"></i> Configurações</h2>
                </div>

                <div class="settings-grid">
                    <div class="settings-card">
                        <h3><i class="fas fa-user-tag"></i> Funções</h3>
                        <p>Gerenciar funções e cargos</p>
                        <button class="btn btn-primary" onclick="abrirModalFuncoes()">
                            Gerenciar
                        </button>
                    </div>
                    <div class="settings-card">
                        <h3><i class="fas fa-graduation-cap"></i> Formações</h3>
                        <p>Gerenciar cursos e certificações</p>
                        <button class="btn btn-primary" onclick="abrirModalFormacoes()">
                            Gerenciar
                        </button>
                    </div>
                    <div class="settings-card">
                        <h3><i class="fas fa-shield-alt"></i> LGPD</h3>
                        <p>Configurações de privacidade</p>
                        <button class="btn btn-primary" onclick="abrirModalLGPD()">
                            Configurar
                        </button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modais -->
    <div id="modal-container"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Utilitários devem ser carregados primeiro -->
    <script src="assets/js/sanitizer.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/validator.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/api.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/permissions.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/modals.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/table.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/importar.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/membros.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/relatorios.js?v=<?php echo time(); ?>"></script>
</body>
</html>

