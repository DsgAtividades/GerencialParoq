<?php
session_start();
require_once '../../config/database.php';

// Verificar se o usuário está logado no módulo de eventos
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true || $_SESSION['module_access'] !== 'eventos') {
    header('Location: ../../module_login.html?module=eventos');
    exit;
}

// Dados do usuário
$username = $_SESSION['module_username'] ?? 'Usuário';
$module_name = 'Eventos';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $module_name; ?> - Sistema de Gestão Paroquial</title>
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link rel="stylesheet" href="../../assets/css/module.css">
    <link rel="stylesheet" href="assets/css/calendario_eventos.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="module-container">
        <!-- Header do Módulo -->
        <header class="module-header">
            <div class="header-content">
                <div class="module-info">
                    <h1><i class="fas fa-calendar-alt"></i> <?php echo $module_name; ?></h1>
                    <p>Sistema completo de gestão de eventos paroquiais</p>
                </div>
                <div class="user-info">
                    <span>Bem-vindo, <?php echo htmlspecialchars($username); ?>!</span>
                    <a href="../../auth/module_logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </div>
            </div>
        </header>

        <!-- Conteúdo Principal -->
        <main class="module-main">
            <!-- Eventos -->
            <section id="eventos" class="content-section active">
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
        </main>
    </div>

    <!-- Modais -->
    <div id="modal-container"></div>

    <!-- Scripts -->
    <script src="assets/js/eventos.js?v=<?php echo time(); ?>"></script>
</body>
</html>
