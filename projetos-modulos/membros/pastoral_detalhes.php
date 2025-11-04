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
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="total-membros">0</h3>
                        <p>Total de Membros</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="membros-ativos">0</h3>
                        <p>Membros Ativos</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="total-coordenadores">0</h3>
                        <p>Coordenadores</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="total-eventos">0</h3>
                        <p>Próximos Eventos</p>
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
                <button class="tab" onclick="mostrarAba('escalas')">
                    <i class="fas fa-clipboard-list"></i> Escalas
                </button>
                <button class="tab" onclick="mostrarAba('editar')">
                    <i class="fas fa-edit"></i> Editar Pastoral
                </button>
            </div>

            <!-- Conteúdo das Abas -->
            <!-- Aba Membros -->
            <div id="aba-membros" class="tab-content active">
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" onclick="adicionarMembroPastoral()">
                        <i class="fas fa-user-plus"></i> Adicionar Membro à Pastoral
                    </button>
                </div>
                <div class="table-card">
                    <div class="table-header">
                        <h3>Membros da Pastoral</h3>
                        <div class="table-actions">
                            <span id="total-membros-pastoral">0 membros</span>
                        </div>
                    </div>
                    <div class="table-container">
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
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i> Carregando...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Aba Eventos -->
            <div id="aba-eventos" class="tab-content">
                <div style="margin-bottom: 1rem;">
                    <button class="btn btn-primary" onclick="abrirModalEvento()">
                        <i class="fas fa-plus"></i> Novo Evento
                    </button>
                </div>
                <div class="table-card">
                    <div class="table-header">
                        <h3>Eventos da Pastoral</h3>
                        <div class="table-actions">
                            <span id="total-eventos-pastoral">0 eventos</span>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Horário</th>
                                    <th>Local</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-eventos">
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i> Carregando...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Aba Escalas -->
            <div id="aba-escalas" class="tab-content">
                <div class="escala-header" style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;">Semana Corrente</h3>
                    <button class="btn btn-primary" onclick="escalasAbrirModalEvento()">
                        <i class="fas fa-plus"></i> Adicionar evento
                    </button>
                </div>
                <div id="escala-semana" class="calendar-week">
                    <!-- calendário semanal será preenchido via JS -->
                </div>
            </div>

            <!-- Aba Edição -->
            <div id="aba-editar" class="tab-content">
                <form id="form-editar-pastoral" class="pastoral-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-nome">Nome da Pastoral *</label>
                            <input type="text" id="edit-nome" name="nome" required placeholder="Ex: Catequese de Adultos">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-tipo">Tipo</label>
                            <input type="text" id="edit-tipo" name="tipo" placeholder="Ex: Catequese, Social, Litúrgica">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-comunidade">Comunidade/Capelania</label>
                            <input type="text" id="edit-comunidade" name="comunidade_capelania" placeholder="Ex: Matriz, Capela São José">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="edit-finalidade">Finalidade / Descrição</label>
                            <textarea id="edit-finalidade" name="finalidade_descricao" rows="4" placeholder="Descreva a finalidade e objetivos desta pastoral..."></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-whatsapp">Link do WhatsApp</label>
                            <input type="text" id="edit-whatsapp" name="whatsapp_grupo_link" placeholder="Link do grupo do WhatsApp">
                        </div>
                        <div class="form-group">
                            <label for="edit-email">E-mail do Grupo</label>
                            <input type="email" id="edit-email" name="email_grupo" placeholder="contato@pastoral.com">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-ativo">Status</label>
                            <select id="edit-ativo" name="ativo" class="form-control">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>
                    </div>

                    <!-- Seção de Coordenadores -->
                    <div class="form-row full-width">
                        <div class="form-group">
                            <label>Coordenador</label>
                            <p class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">Selecione um membro da pastoral para ser o coordenador</p>
                            <div class="coordinator-selector">
                                <div id="coordenador-atual" class="coordinator-selected">
                                    <span class="coordinator-name">Nenhum selecionado</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selecionarCoordenador('coordenador')">
                                        <i class="fas fa-user-edit"></i> Selecionar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Vice-Coordenador</label>
                            <p class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">Selecione um membro da pastoral para ser o vice-coordenador</p>
                            <div class="coordinator-selector">
                                <div id="vice-coordenador-atual" class="coordinator-selected">
                                    <span class="coordinator-name">Nenhum selecionado</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selecionarCoordenador('vice_coordenador')">
                                        <i class="fas fa-user-edit"></i> Selecionar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="cancelarEdicao()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
            </div>
        </main>
    </div>

    <!-- Container para Modais -->
    <div id="modal-container"></div>

    <!-- Modal de Seleção de Coordenadores -->
    <div id="modal-selecionar-membro" class="member-selector-modal">
        <div class="member-selector-content">
            <div class="member-selector-header">
                <h3 id="modal-titulo">Selecionar Coordenador</h3>
                <button type="button" class="btn btn-sm btn-secondary" onclick="fecharModalMembro()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="member-selector-body">
                <input type="text" id="busca-membro" class="member-search-input" placeholder="Buscar membro..." onkeyup="filtrarMembros()">
                <div id="lista-membros-selector" class="member-list">
                    <!-- Lista será preenchida via JS -->
                </div>
            </div>
            <div class="member-selector-footer">
                <input type="hidden" id="tipo-coordenador-selecionando" value="">
                <button type="button" class="btn btn-secondary" onclick="fecharModalMembro()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="adicionarCoordenadorSelecionado()">
                    <i class="fas fa-check"></i> Adicionar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Seleção de Membro para Pastoral -->
    <div id="modal-adicionar-membro-pastoral" class="member-selector-modal">
        <div class="member-selector-content">
            <div class="member-selector-header">
                <h3>Adicionar Membro à Pastoral</h3>
                <button type="button" class="btn btn-sm btn-secondary" onclick="fecharModalAdicionarMembro()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="member-selector-body">
                <input type="text" id="busca-membro-pastoral" class="member-search-input" placeholder="Buscar membro..." onkeyup="filtrarMembrosPastoral()">
                <div id="lista-membros-pastoral" class="member-list">
                    <!-- Lista será preenchida via JS -->
                </div>
            </div>
            <div class="member-selector-footer">
                <button type="button" class="btn btn-secondary" onclick="fecharModalAdicionarMembro()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="adicionarMembroSelecionado()">
                    <i class="fas fa-check"></i> Adicionar
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- Utilitários devem ser carregados primeiro -->
    <script src="assets/js/sanitizer.js"></script>
    <script src="assets/js/validator.js"></script>
    <script src="assets/js/api.js"></script>
    <script src="assets/js/modals.js"></script>
    <script src="assets/js/pastoral_detalhes.js"></script>
    <script src="assets/js/escalas.js"></script>
    <script>
        // Inicializar página
        const pastoralId = '<?php echo $pastoral_id; ?>';
        // Disponibilizar no escopo global para outros scripts
        window.pastoralId = pastoralId;
        window.addEventListener('DOMContentLoaded', () => {
            carregarDadosPastoral(pastoralId);
            // carregar semana de escalas de imediato
            escalasCarregarSemana(pastoralId);
        });
    </script>
</body>
</html>


