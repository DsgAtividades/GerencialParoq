<?php
session_start();
require_once '../../config/database.php';

// Verificar se o usuário está logado no módulo específico
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    header('Location: ../../module_login.html?module=atividades');
    exit;
}

// Verificar se o usuário tem acesso a este módulo específico
if (!isset($_SESSION['module_access']) || $_SESSION['module_access'] !== 'atividades') {
    header('Location: ../../module_login.html?module=atividades');
    exit;
}

// Verificar timeout da sessão do módulo (2 horas)
if (isset($_SESSION['module_login_time']) && (time() - $_SESSION['module_login_time'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: ../../module_login.html?module=atividades');
    exit;
}

$module_name = 'Atividades';
$module_description = 'Sistema de monitoramento e controle de atividades pastorais';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $module_name; ?> - Sistema de Gestão Paroquial</title>
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link rel="stylesheet" href="../../assets/css/module.css">
    <link rel="stylesheet" href="atividades.css">
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
                    <a href="../../auth/module_logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Sair do Módulo
                    </a>
                </div>
            </div>
        </header>

        <!-- Navegação do Módulo -->
        <nav class="module-nav">
            <ul>
                <li><a href="#" class="nav-link active" data-section="dashboard">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a></li>
                <li><a href="#" class="nav-link" data-section="criar-relatorio">
                    <i class="fas fa-plus-circle"></i> Criar Relatório de Atividade
                </a></li>
                <li><a href="#" class="nav-link" data-section="relatorios">
                    <i class="fas fa-file-alt"></i> Relatórios
                </a></li>
            </ul>
        </nav>

        <!-- Conteúdo Principal -->
        <main class="module-main">
            <!-- Dashboard -->
            <section id="dashboard" class="content-section active">
                <div class="section-header">
                    <h2>Dashboard de Atividades</h2>
                    <p>Visão geral das atividades em execução</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">0</div>
                            <div class="stat-label">Total de Relatórios</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">0</div>
                            <div class="stat-label">Concluídos</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">0</div>
                            <div class="stat-label">Em Andamento</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">0</div>
                            <div class="stat-label">Pendentes</div>
                        </div>
                    </div>
                </div>
                
                
            </section>

            <!-- Criar Relatório -->
            <section id="criar-relatorio" class="content-section">
                <div class="section-header">
                    <h2>Criar Relatório de Atividade</h2>
                    <p>Registre uma nova atividade pastoral</p>
                </div>
                
                <div class="content-card">
                    <div class="card-content">
                        <form id="form-relatorio" class="form-module">
                            <div class="form-group">
                                <label for="titulo_atividade">Título da Atividade *</label>
                                <input type="text" id="titulo_atividade" name="titulo_atividade" required placeholder="Ex: Preparação para Primeira Comunhão, Campanha de Arrecadação, etc.">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="setor">Setor *</label>
                                    <input type="text" id="setor" name="setor" required placeholder="Ex: Catequese, Pastoral Social, etc.">
                                </div>
                                
                                <div class="form-group">
                                    <label for="responsavel">Responsável *</label>
                                    <select id="responsavel" name="responsavel" required>
                                        <option value="">Selecione o responsável</option>
                                        <option value="denys">Denys</option>
                                        <option value="rener">Rener</option>
                                        <option value="flavia">Flavia</option>
                                        <option value="aline">Aline</option>
                                        <option value="cristiano">Cristiano</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="data_inicio">Data de Início *</label>
                                    <input type="date" id="data_inicio" name="data_inicio" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="data_previsao">Previsão de Término *</label>
                                    <input type="date" id="data_previsao" name="data_previsao" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="data_termino">Data de Término</label>
                                    <input type="date" id="data_termino" name="data_termino">
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Status *</label>
                                    <select id="status" name="status" required>
                                        <option value="a_fazer">A Fazer</option>
                                        <option value="em_andamento">Em Andamento</option>
                                        <option value="pausado">Pausado</option>
                                        <option value="concluido">Concluído</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="observacao">Observação</label>
                                <textarea id="observacao" name="observacao" rows="4" placeholder="Adicione observações sobre a atividade..."></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-save"></i> Gerar Relatório
                                </button>
                                <button type="reset" class="btn-secondary">
                                    <i class="fas fa-undo"></i> Limpar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <!-- Relatórios -->
            <section id="relatorios" class="content-section">
                <div class="section-header">
                    <h2>Relatórios de Atividades</h2>
                    <p>Visualize e gerencie todos os relatórios criados</p>
                </div>
                
                <div class="content-card-relatorios">
                    <div class="card-content-relatorios">
                        <div class="table-container">
                            <table class="table-module">
                                <thead>
                                    <tr>
                                        <th>Título da Atividade</th>
                                        <th>Setor</th>
                                        <th>Responsável</th>
                                        <th>Início</th>
                                        <th>Previsão</th>
                                        <th>Término</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tabela-relatorios">
                                    <tr id="sem-relatorios">
                                        <td colspan="8" style="text-align: center; color: #7f8c8d; padding: 40px;">
                                            <i class="fas fa-file-alt" style="font-size: 2rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                            Nenhum relatório criado ainda.<br>
                                            Crie seu primeiro relatório na aba "Criar Relatório de Atividade".
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="../../assets/js/paginas/modulo.js"></script>
    <script src="script_atividades.js"></script>
    
</body>
</html>
