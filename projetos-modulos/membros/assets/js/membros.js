/**
 * JavaScript principal do módulo de Membros
 * Sistema de Gestão Paroquial
 */

// =====================================================
// CONFIGURAÇÕES GLOBAIS
// =====================================================
const CONFIG = {
    apiBaseUrl: '/PROJETOS/GerencialParoq/projetos-modulos/membros/api/',
    itemsPerPage: 20,
    currentPage: 1,
    totalPages: 1,
    currentSection: 'dashboard'
};

// Estado global da aplicação
const AppState = {
    membros: [],
    pastorais: [],
    eventos: [],
    escalas: [],
    filtros: {
        busca: '',
        status: '',
        pastoral: '',
        funcao: ''
    },
    paginacao: {
        page: 1,
        limit: 20,
        total: 0,
        pages: 1
    },
    // Instâncias dos gráficos para controle
    charts: {
        pastorais: null,
        adesoes: null
    }
};

// =====================================================
// INICIALIZAÇÃO
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    inicializarAplicacao();
});

function inicializarAplicacao() {
    configurarNavegacao();
    carregarDadosIniciais();
    configurarEventos();
}

function configurarNavegacao() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const section = this.getAttribute('data-section');
            if (section) {
                mostrarSecao(section);
            }
        });
    });
}

function mostrarSecao(sectionId) {
    // Limpar gráficos ao mudar de seção (exceto dashboard)
    if (sectionId !== 'dashboard') {
        limparTodosGraficos();
    }
    
    // Ocultar todas as seções
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    // Remover classe ativa de todos os links
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.parentElement.classList.remove('active');
    });
    
    // Mostrar seção selecionada
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
        CONFIG.currentSection = sectionId;
    }
    
    // Ativar link correspondente
    const activeLink = document.querySelector(`[data-section="${sectionId}"]`);
    if (activeLink) {
        activeLink.parentElement.classList.add('active');
    }
    
    // Carregar dados específicos da seção
    carregarDadosSecao(sectionId);
}

function carregarDadosSecao(sectionId) {
    switch (sectionId) {
        case 'dashboard':
            carregarDashboard();
            break;
        case 'membros':
            carregarMembros();
            break;
        case 'pastorais':
            carregarPastorais();
            break;
        case 'eventos':
            carregarEventos();
            break;
        case 'escalas':
            carregarEscalas();
            break;
        case 'relatorios':
            // Relatórios são carregados sob demanda
            break;
        case 'configuracoes':
            // Configurações são carregadas sob demanda
            break;
    }
}

// =====================================================
// FUNÇÕES AUXILIARES
// =====================================================

/**
 * Destrói um gráfico Chart.js existente
 */
function destruirGrafico(chartInstance) {
    if (chartInstance && typeof chartInstance.destroy === 'function') {
        chartInstance.destroy();
    }
}

/**
 * Cria ou atualiza um gráfico Chart.js
 */
function criarOuAtualizarGrafico(canvasId, chartKey, config) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;
    
    // Destruir gráfico existente se houver
    if (AppState.charts[chartKey]) {
        destruirGrafico(AppState.charts[chartKey]);
    }
    
    // Garantir que o canvas tenha dimensões adequadas
    const container = canvas.parentElement;
    if (container) {
        // Definir altura mínima se não estiver definida
        if (!container.style.height && !container.style.minHeight) {
            container.style.minHeight = '300px';
        }
        
        // Garantir que o canvas tenha altura definida
        if (!canvas.style.height) {
            canvas.style.height = '300px';
        }
        if (!canvas.style.width) {
            canvas.style.width = '100%';
        }
    }
    
    // Criar novo gráfico
    const chart = new Chart(canvas, config);
    AppState.charts[chartKey] = chart;
    
    return chart;
}

/**
 * Limpa todos os gráficos
 */
function limparTodosGraficos() {
    Object.keys(AppState.charts).forEach(key => {
        destruirGrafico(AppState.charts[key]);
        AppState.charts[key] = null;
    });
}

// =====================================================
// CARREGAMENTO DE DADOS
// =====================================================
async function carregarDadosIniciais() {
    try {
        await Promise.all([
            carregarPastorais(),
            carregarDashboard()
        ]);
    } catch (error) {
        console.error('Erro ao carregar dados iniciais:', error);
        mostrarNotificacao('Erro ao carregar dados iniciais', 'error');
    }
}

async function carregarDashboard() {
    try {
        const response = await carregarDashboardAPI();
        
        if (response && response.success) {
            // A API retorna: { success: true, data: {...} }
            const dashboardData = response.data || response;
            atualizarCardsEstatisticas(dashboardData);
            atualizarGraficos(dashboardData);
            atualizarAlertas(dashboardData.alertas || []);
        }
    } catch (error) {
        console.error('Erro ao carregar dashboard:', error);
    }
}

async function carregarMembros() {
    try {
        mostrarLoadingTabela();

        const params = {
            page: AppState.paginacao.page,
            limit: AppState.paginacao.limit,
            ...AppState.filtros
        };

        const response = await carregarMembrosAPI(params);

        if (response && response.success) {
            // A API retorna: { success: true, data: { data: [...], paginacao: {...} } }
            const membrosData = response.data?.data || response.data || [];
            AppState.membros = Array.isArray(membrosData) ? membrosData : [];
            AppState.paginacao = response.data?.paginacao || response.paginacao || AppState.paginacao;
            atualizarTabelaMembros();
            atualizarPaginacao();
        } else {
            AppState.membros = [];
            mostrarErroTabela('Erro ao carregar membros');
        }
    } catch (error) {
        console.error('Erro ao carregar membros:', error);
        AppState.membros = [];
        mostrarErroTabela('Erro de conexão');
    }
}

async function carregarPastorais() {
    try {
        const response = await carregarPastoraisAPI();
        
        if (response && response.success) {
            // A API retorna: { success: true, data: { data: [...] } }
            const pastoraisData = response.data?.data || response.data || [];
            AppState.pastorais = Array.isArray(pastoraisData) ? pastoraisData : [];
            
            atualizarSelectPastorais();
            atualizarCardsPastorais();
        } else {
            // Se não houver dados válidos, definir como array vazio
            AppState.pastorais = [];
            atualizarSelectPastorais();
            atualizarCardsPastorais();
        }
    } catch (error) {
        console.error('Erro ao carregar pastorais:', error);
        // Em caso de erro, definir como array vazio
        AppState.pastorais = [];
        atualizarSelectPastorais();
        atualizarCardsPastorais();
    }
}

async function carregarEventos() {
    try {
        const response = await carregarEventosAPI();
        
        if (response && response.success) {
            // A API retorna: { success: true, data: { data: [...] } }
            const eventosData = response.data?.data || response.data || [];
            AppState.eventos = Array.isArray(eventosData) ? eventosData : [];
            atualizarCalendarioEventos();
        } else {
            AppState.eventos = [];
            atualizarCalendarioEventos();
        }
    } catch (error) {
        console.error('Erro ao carregar eventos:', error);
        AppState.eventos = [];
        atualizarCalendarioEventos();
    }
}

async function carregarEscalas() {
    try {
        // Implementar carregamento de escalas
        console.log('Carregando escalas...');
    } catch (error) {
        console.error('Erro ao carregar escalas:', error);
    }
}

// =====================================================
// DASHBOARD
// =====================================================
function atualizarCardsEstatisticas(dados) {
    document.getElementById('total-membros').textContent = dados.total_membros || 0;
    document.getElementById('membros-ativos').textContent = dados.membros_ativos || 0;
    document.getElementById('total-pastorais').textContent = dados.total_pastorais || 0;
    document.getElementById('eventos-mes').textContent = dados.eventos_mes || 0;
}

function atualizarGraficos(dados) {
    // Gráfico de membros por pastoral
    criarOuAtualizarGrafico('chart-pastorais', 'pastorais', {
        type: 'doughnut',
        data: {
            labels: dados.membros_por_pastoral?.map(p => p.pastoral) || [],
            datasets: [{
                data: dados.membros_por_pastoral?.map(p => p.quantidade) || [],
                backgroundColor: [
                    '#2c5aa0',
                    '#4a7bc8',
                    '#6b9bd2',
                    '#8bb3dc',
                    '#abcddf'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Gráfico de novas adesões
    criarOuAtualizarGrafico('chart-adesoes', 'adesoes', {
        type: 'line',
        data: {
            labels: dados.adesoes_mensais?.map(a => a.mes) || [],
            datasets: [{
                label: 'Novas Adesões',
                data: dados.adesoes_mensais?.map(a => a.quantidade) || [],
                borderColor: '#2c5aa0',
                backgroundColor: 'rgba(44, 90, 160, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Mês'
                    }
                },
                y: {
                    beginAtZero: true,
                    display: true,
                    title: {
                        display: true,
                        text: 'Quantidade'
                    }
                }
            },
            elements: {
                point: {
                    radius: 4,
                    hoverRadius: 6
                },
                line: {
                    borderWidth: 2
                }
            }
        }
    });
}

function atualizarAlertas(alertas) {
    const alertsList = document.getElementById('alerts-list');
    if (!alertsList) return;
    
    if (alertas.length === 0) {
        alertsList.innerHTML = '<div class="alert-item info"><i class="fas fa-info-circle"></i><span>Nenhum alerta no momento</span></div>';
        return;
    }
    
    alertsList.innerHTML = alertas.map(alerta => `
        <div class="alert-item ${alerta.prioridade}">
            <i class="fas fa-exclamation-triangle"></i>
            <span>${alerta.mensagem}</span>
        </div>
    `).join('');
}

function atualizarDashboard() {
    carregarDashboard();
    mostrarNotificacao('Dashboard atualizado', 'success');
}

// =====================================================
// TABELA DE MEMBROS
// =====================================================
function mostrarLoadingTabela() {
    const tbody = document.querySelector('#tabela-membros tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center">
                    <i class="fas fa-spinner fa-spin"></i> Carregando...
                </td>
            </tr>
        `;
    }
}

function mostrarErroTabela(mensagem) {
    const tbody = document.querySelector('#tabela-membros tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle"></i> ${mensagem}
                </td>
            </tr>
        `;
    }
}

function atualizarTabelaMembros() {
    const tbody = document.querySelector('#tabela-membros tbody');
    if (!tbody) return;
    
    const membros = Array.isArray(AppState.membros) ? AppState.membros : [];
    if (membros.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i class="fas fa-users"></i> Nenhum membro encontrado
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = membros.map(membro => `
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <div class="avatar me-2">
                        <i class="fas fa-user-circle fa-2x text-muted"></i>
                    </div>
                    <div>
                        <strong>${membro.nome_completo}</strong>
                        ${membro.apelido ? `<br><small class="text-muted">${membro.apelido}</small>` : ''}
                    </div>
                </div>
            </td>
            <td>
                <div>
                    ${membro.email ? `<div><i class="fas fa-envelope"></i> ${membro.email}</div>` : ''}
                    ${membro.celular_whatsapp ? `<div><i class="fas fa-phone"></i> ${membro.celular_whatsapp}</div>` : ''}
                </div>
            </td>
            <td>
                <div class="d-flex flex-wrap gap-1">
                    ${membro.pastorais?.map(p => `<span class="badge badge-info">${p.nome}</span>`).join('') || '<span class="text-muted">Nenhuma</span>'}
                </div>
            </td>
            <td>
                <span class="badge badge-${getStatusClass(membro.status)}">${getStatusText(membro.status)}</span>
            </td>
            <td>
                ${membro.data_entrada ? formatarData(membro.data_entrada) : '-'}
            </td>
            <td>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-secondary" onclick="visualizarMembro('${membro.id}')" title="Visualizar">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="editarMembro('${membro.id}')" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="excluirMembro('${membro.id}')" title="Excluir">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    // Atualizar contador de registros
    const totalRegistros = document.getElementById('total-registros');
    if (totalRegistros) {
        totalRegistros.textContent = `${AppState.paginacao.total} registros`;
    }
}

function atualizarPaginacao() {
    const infoPaginacao = document.getElementById('info-paginacao');
    const btnAnterior = document.querySelector('[onclick="paginarAnterior()"]');
    const btnProximo = document.querySelector('[onclick="paginarProximo()"]');
    
    if (infoPaginacao) {
        infoPaginacao.textContent = `Página ${AppState.paginacao.page} de ${AppState.paginacao.pages}`;
    }
    
    if (btnAnterior) {
        btnAnterior.disabled = AppState.paginacao.page <= 1;
    }
    
    if (btnProximo) {
        btnProximo.disabled = AppState.paginacao.page >= AppState.paginacao.pages;
    }
}

function paginarAnterior() {
    if (AppState.paginacao.page > 1) {
        AppState.paginacao.page--;
        carregarMembros();
    }
}

function paginarProximo() {
    if (AppState.paginacao.page < AppState.paginacao.pages) {
        AppState.paginacao.page++;
        carregarMembros();
    }
}

// =====================================================
// FILTROS
// =====================================================
function aplicarFiltros() {
    AppState.filtros.busca = document.getElementById('filtro-busca').value;
    AppState.filtros.status = document.getElementById('filtro-status').value;
    AppState.filtros.pastoral = document.getElementById('filtro-pastoral').value;
    AppState.paginacao.page = 1;
    
    carregarMembros();
}

function atualizarSelectPastorais() {
    const select = document.getElementById('filtro-pastoral');
    if (!select) return;
    
    // Garantir que pastorais seja sempre um array
    const pastorais = Array.isArray(AppState.pastorais) ? AppState.pastorais : [];
    
    select.innerHTML = '<option value="">Todas</option>' +
        pastorais.map(pastoral => 
            `<option value="${pastoral.id}">${pastoral.nome}</option>`
        ).join('');
}

// =====================================================
// PASTORAIS
// =====================================================
function atualizarCardsPastorais() {
    const grid = document.getElementById('pastorais-grid');
    if (!grid) return;
    
    // Garantir que pastorais seja sempre um array
    const pastorais = Array.isArray(AppState.pastorais) ? AppState.pastorais : [];
    
    if (pastorais.length === 0) {
        grid.innerHTML = `
            <div class="loading-card">
                <i class="fas fa-church"></i>
                <p>Nenhuma pastoral encontrada</p>
            </div>
        `;
        return;
    }
    
    grid.innerHTML = pastorais.map(pastoral => `
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-church"></i> ${pastoral.nome}</h3>
            </div>
            <div class="card-body">
                <p><strong>Tipo:</strong> ${pastoral.tipo}</p>
                <p><strong>Coordenador:</strong> ${pastoral.coordenador?.nome_completo || 'Não definido'}</p>
                <p><strong>Reunião:</strong> ${pastoral.dia_semana || 'Não definido'} às ${pastoral.horario || 'Não definido'}</p>
                <p><strong>Local:</strong> ${pastoral.local_reuniao || 'Não definido'}</p>
            </div>
            <div class="card-footer">
                <button class="btn btn-sm btn-primary" onclick="editarPastoral('${pastoral.id}')">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button class="btn btn-sm btn-danger" onclick="excluirPastoral('${pastoral.id}')">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            </div>
        </div>
    `).join('');
}

// =====================================================
// EVENTOS
// =====================================================
function atualizarCalendarioEventos() {
    const container = document.getElementById('calendario-eventos');
    if (!container) return;
    
    const eventos = Array.isArray(AppState.eventos) ? AppState.eventos : [];
    if (eventos.length === 0) {
        container.innerHTML = `
            <div class="loading-card">
                <i class="fas fa-calendar-alt"></i>
                <p>Nenhum evento encontrado</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = `
        <div class="events-list">
            ${eventos.map(evento => `
                <div class="event-card">
                    <div class="event-date">
                        <span class="day">${new Date(evento.data_evento).getDate()}</span>
                        <span class="month">${new Date(evento.data_evento).toLocaleDateString('pt-BR', { month: 'short' })}</span>
                    </div>
                    <div class="event-info">
                        <h4>${evento.nome}</h4>
                        <p><i class="fas fa-clock"></i> ${evento.horario || 'Horário não definido'}</p>
                        <p><i class="fas fa-map-marker-alt"></i> ${evento.local || 'Local não definido'}</p>
                    </div>
                    <div class="event-actions">
                        <button class="btn btn-sm btn-primary" onclick="visualizarEvento('${evento.id}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

// =====================================================
// RELATÓRIOS
// =====================================================
function gerarRelatorio(tipo) {
    switch (tipo) {
        case 'membros':
            window.open(`${CONFIG.apiBaseUrl}relatorios/membros?formato=pdf`, '_blank');
            break;
        case 'frequencia':
            window.open(`${CONFIG.apiBaseUrl}relatorios/frequencia?formato=pdf`, '_blank');
            break;
        case 'pastorais':
            // Implementar relatório de pastorais
            break;
        case 'aniversariantes':
            // Implementar relatório de aniversariantes
            break;
    }
}

// =====================================================
// AÇÕES DE MEMBROS
// =====================================================
function abrirModalMembro() {
    // Implementar modal de membro
    console.log('Abrir modal de membro');
}

function visualizarMembro(id) {
    // Implementar visualização de membro
    console.log('Visualizar membro:', id);
}

function editarMembro(id) {
    // Implementar edição de membro
    console.log('Editar membro:', id);
}

function excluirMembro(id) {
    if (confirm('Tem certeza que deseja excluir este membro?')) {
        // Implementar exclusão de membro
        console.log('Excluir membro:', id);
    }
}

// =====================================================
// AÇÕES DE PASTORAIS
// =====================================================
function abrirModalPastoral() {
    // Implementar modal de pastoral
    console.log('Abrir modal de pastoral');
}

function editarPastoral(id) {
    // Implementar edição de pastoral
    console.log('Editar pastoral:', id);
}

function excluirPastoral(id) {
    if (confirm('Tem certeza que deseja excluir esta pastoral?')) {
        // Implementar exclusão de pastoral
        console.log('Excluir pastoral:', id);
    }
}

// =====================================================
// UTILITÁRIOS
// =====================================================
function getStatusClass(status) {
    const classes = {
        'ativo': 'success',
        'afastado': 'warning',
        'em_discernimento': 'info',
        'bloqueado': 'danger'
    };
    return classes[status] || 'secondary';
}

function getStatusText(status) {
    const texts = {
        'ativo': 'Ativo',
        'afastado': 'Afastado',
        'em_discernimento': 'Em Discernimento',
        'bloqueado': 'Bloqueado'
    };
    return texts[status] || status;
}

function formatarData(data) {
    return new Date(data).toLocaleDateString('pt-BR');
}

function mostrarNotificacao(mensagem, tipo = 'info') {
    // Implementar sistema de notificações
    console.log(`${tipo.toUpperCase()}: ${mensagem}`);
}

// =====================================================
// CONFIGURAÇÃO DE EVENTOS
// =====================================================
function configurarEventos() {
    // Eventos de filtros
    const filtroBusca = document.getElementById('filtro-busca');
    if (filtroBusca) {
        filtroBusca.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                aplicarFiltros();
            }
        });
    }
    
    // Eventos de teclado
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModais();
        }
    });
}

function fecharModais() {
    const modais = document.querySelectorAll('.modal-overlay');
    modais.forEach(modal => modal.remove());
}

// =====================================================
// EXPORTAÇÃO
// =====================================================
function exportarMembros() {
    const params = new URLSearchParams({
        formato: 'excel',
        ...AppState.filtros
    });
    
    window.open(`${CONFIG.apiBaseUrl}relatorios/membros?${params}`, '_blank');
}

