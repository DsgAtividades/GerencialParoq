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
    },
    // Cache de dados completos dos membros para edição rápida
    cacheMembros: new Map(),
    ultimaAtualizacaoCache: 0,
    // Cache de API para evitar chamadas duplicadas
    apiCache: new Map(),
    cacheValidoPor: 5 * 60 * 1000 // 5 minutos
};

// =====================================================
// INICIALIZAÇÃO
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    inicializarAplicacao();
});

function inicializarAplicacao() {
    configurarNavegacao();
    
    // Limpar cache expirado ao iniciar
    limparCacheExpirado();
    
    // Configurar limpeza automática do cache a cada 10 minutos
    setInterval(limparCacheExpirado, 10 * 60 * 1000);
    
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
// SISTEMA DE CACHE E OTIMIZAÇÕES
// =====================================================

/**
 * Verifica se há dados em cache válidos
 */
function obterDoCache(url) {
    const cached = AppState.apiCache.get(url);
    if (!cached) return null;
    
    const agora = Date.now();
    if (agora - cached.timestamp > AppState.cacheValidoPor) {
        AppState.apiCache.delete(url);
        return null;
    }
    
    return cached.data;
}

/**
 * Salva dados no cache
 */
function salvarNoCache(url, data) {
    AppState.apiCache.set(url, {
        data: data,
        timestamp: Date.now()
    });
}

/**
 * Limpa o cache expirado
 */
function limparCacheExpirado() {
    const agora = Date.now();
    for (const [url, cached] of AppState.apiCache.entries()) {
        if (agora - cached.timestamp > AppState.cacheValidoPor) {
            AppState.apiCache.delete(url);
        }
    }
}

/**
 * Função debounce para otimizar filtros
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Carregar com cache inteligente
 */
async function fetchComCache(url, options = {}) {
    // Verificar cache
    const cached = obterDoCache(url);
    if (cached) {
        return cached;
    }
    
    // Fazer requisição
    const response = await fetch(url, options);
    const data = await response.json();
    
    // Salvar no cache se for GET
    if (!options.method || options.method === 'GET') {
        salvarNoCache(url, data);
    }
    
    return data;
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
        const oldChart = AppState.charts[chartKey];
        destruirGrafico(oldChart);
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
            carregarDashboard(),
            carregarMembros() // Carregar membros na inicialização
        ]);
    } catch (error) {
        console.error('Erro ao carregar dados iniciais:', error);
        mostrarNotificacao('Erro ao carregar dados iniciais', 'error');
    }
}

async function carregarDashboard() {
    try {
        console.log('Carregando dashboard...');
        
        // Verificar cache primeiro
        const cacheKey = 'dashboard-data';
        const cached = obterDoCache(cacheKey);
        
        if (cached) {
            console.log('Usando dados do cache');
            atualizarCardsEstatisticas(cached);
            atualizarGraficos(cached);
            atualizarAlertas(cached.alertas || []);
            return;
        }
        
        // Carregar dados principais em paralelo
        const [statsResponse, pastoralResponse] = await Promise.all([
            carregarDashboardAPI(),
            DashboardAPI.membrosPorPastoral()
        ]);
        
        // Dados de adesões mockados (até criar endpoint específico)
        const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'];
        const quantidades = [2, 5, 3, 7, 4, 6];
        
        // Combinar todos os dados
        const dashboardData = {
            ...(statsResponse?.data || statsResponse || {}),
            membros_por_pastoral: pastoralResponse?.data || pastoralResponse || { labels: [], data: [] },
            adesoes_mensais: { labels: meses, data: quantidades }
        };
        
        // Salvar no cache
        salvarNoCache(cacheKey, dashboardData);
        
        atualizarCardsEstatisticas(dashboardData);
        atualizarGraficos(dashboardData);
        atualizarAlertas(dashboardData.alertas || []);
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

        // Criar chave de cache baseada nos parâmetros
        const cacheKey = 'membros-' + JSON.stringify(params);
        const cached = obterDoCache(cacheKey);
        
        let response;
        if (cached) {
            console.log('Usando membros do cache');
            response = cached;
        } else {
            response = await carregarMembrosAPI(params);
            // Salvar no cache apenas se não houver busca/filtros
            if (!AppState.filtros.busca && !AppState.filtros.status && !AppState.filtros.pastoral) {
                salvarNoCache(cacheKey, response);
            }
        }

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
        // Verificar cache
        const cached = obterDoCache('pastorais');
        let response;
        
        if (cached) {
            console.log('Usando pastorais do cache');
            response = cached;
        } else {
            response = await carregarPastoraisAPI();
            if (response && response.success) {
                salvarNoCache('pastorais', response);
            }
        }
        
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
    console.log('Dados recebidos para cards:', dados);
    
    // A API retorna: totalMembros, membrosAtivos, pastoraisAtivas, eventosHoje
    const totalMembros = dados.totalMembros || dados.total_membros || 0;
    const membrosAtivos = dados.membrosAtivos || dados.membros_ativos || 0;
    const totalPastorais = dados.pastoraisAtivas || dados.total_pastorais || 0;
    const eventosMes = dados.eventosHoje || dados.eventos_mes || 0;
    
    console.log('Valores extraídos:', { totalMembros, membrosAtivos, totalPastorais, eventosMes });
    
    const totalMembrosEl = document.getElementById('total-membros');
    const membrosAtivosEl = document.getElementById('membros-ativos');
    const totalPastoraisEl = document.getElementById('total-pastorais');
    const eventosMesEl = document.getElementById('eventos-mes');
    
    if (totalMembrosEl) {
        totalMembrosEl.textContent = totalMembros;
        console.log('Atualizado total-membros:', totalMembros);
    } else {
        console.error('Elemento total-membros não encontrado');
    }
    
    if (membrosAtivosEl) {
        membrosAtivosEl.textContent = membrosAtivos;
        console.log('Atualizado membros-ativos:', membrosAtivos);
    } else {
        console.error('Elemento membros-ativos não encontrado');
    }
    
    if (totalPastoraisEl) {
        totalPastoraisEl.textContent = totalPastorais;
        console.log('Atualizado total-pastorais:', totalPastorais);
    } else {
        console.error('Elemento total-pastorais não encontrado');
    }
    
    if (eventosMesEl) {
        eventosMesEl.textContent = eventosMes;
        console.log('Atualizado eventos-mes:', eventosMes);
    } else {
        console.error('Elemento eventos-mes não encontrado');
    }
}

function atualizarGraficos(dados) {
    console.log('Atualizando gráficos com dados:', dados);
    
    // A API retorna { labels: [...], data: [...] }
    const pastoralLabels = dados.membros_por_pastoral?.labels || [];
    const pastoralData = dados.membros_por_pastoral?.data || [];
    
    console.log('Dados para gráfico pastoral:', { pastoralLabels, pastoralData });
    
    // Gráfico de membros por pastoral
    const chart = criarOuAtualizarGrafico('chart-pastorais', 'pastorais', {
        type: 'doughnut',
        data: {
            labels: pastoralLabels,
            datasets: [{
                data: pastoralData,
                backgroundColor: [
                    '#2c5aa0',
                    '#4a7bc8',
                    '#6b9bd2',
                    '#8bb3dc',
                    '#abcddf',
                    '#c0d6e8',
                    '#d4e0f0',
                    '#e8ebf3'
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
    
    // Adicionar evento de click no canvas após criar o gráfico (apenas uma vez)
    const canvasElement = document.getElementById('chart-pastorais');
    if (chart && canvasElement && !canvasElement.dataset.clickListenerAdded) {
        if (canvasElement) {
            canvasElement.dataset.clickListenerAdded = 'true';
            canvasElement.addEventListener('click', async (evt) => {
                const points = chart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                
                if (points.length > 0) {
                    const index = points[0].index;
                    const pastoralNome = pastoralLabels[index];
                    
                    console.log('Clicando na pastoral:', pastoralNome);
                    
                    // Buscar ID da pastoral pelo nome
                    try {
                        const response = await fetch('api/pastorais');
                        const data = await response.json();
                        
                        if (data.success) {
                            const pastorais = Array.isArray(data.data) ? data.data : (data.data?.data || []);
                            const pastoral = pastorais.find(p => p.nome === pastoralNome);
                            
                            if (pastoral) {
                                console.log('Redirecionando para pastoral:', pastoral.id);
                                window.location.href = `pastoral_detalhes.php?id=${pastoral.id}`;
                            } else {
                                console.warn('Pastoral não encontrada:', pastoralNome);
                            }
                        }
                    } catch (error) {
                        console.error('Erro ao buscar pastoral:', error);
                    }
                }
            });
        }
    }
    
    // Ajustar dados de adesões - pode vir em formato diferente
    // Verificar se é array (formato antigo) ou objeto com labels/data
    let adesoesMes = [];
    let adesoesQuantidade = [];
    
    if (Array.isArray(dados.adesoes_mensais)) {
        // Formato array: [{ mes: 'Jan', quantidade: 2 }, ...]
        adesoesMes = dados.adesoes_mensais.map(a => a.mes);
        adesoesQuantidade = dados.adesoes_mensais.map(a => a.quantidade);
    } else if (dados.adesoes_mensais && typeof dados.adesoes_mensais === 'object') {
        // Formato objeto: { labels: [...], data: [...] }
        adesoesMes = dados.adesoes_mensais.labels || [];
        adesoesQuantidade = dados.adesoes_mensais.data || [];
    }
    
    console.log('Dados para gráfico de adesões:', { adesoesMes, adesoesQuantidade });
    
    // Gráfico de novas adesões
    criarOuAtualizarGrafico('chart-adesoes', 'adesoes', {
        type: 'line',
        data: {
            labels: adesoesMes,
            datasets: [{
                label: 'Novas Adesões',
                data: adesoesQuantidade,
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
    // Limpar cache antes de atualizar
    limparCacheExpirado();
    
    // Invalidar caches específicos
    AppState.apiCache.delete('dashboard-data');
    AppState.apiCache.delete('pastorais');
    
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
                    ${formatarPastorais(membro.pastorais)}
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
                    <button class="btn btn-sm btn-info" onclick="window.visualizarFoto('${membro.id}')" title="Visualizar Foto" ${!membro.foto_url ? 'disabled' : ''}>
                        <i class="fas fa-image"></i>
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="window.visualizarMembro('${membro.id}')" title="Visualizar">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="window.editarMembro('${membro.id}')" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="window.excluirMembro('${membro.id}')" title="Excluir">
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

// =====================================================
// FUNÇÕES DE AÇÃO DOS MEMBROS
// =====================================================

/**
 * Visualiza membro
 */
async function visualizarMembro(id) {
    try {
        // Verificar cache primeiro (mais rápido)
        const dadosCache = obterDadosDoCache(id);
        if (dadosCache) {
            console.log('Usando dados do cache para visualização rápida');
            abrirModalMembro(dadosCache, 'visualizar');
            return;
        }
        
        // Se não estiver no cache, mostrar indicador de carregamento
        mostrarIndicadorCarregamento('Carregando dados do membro...');
        
        // Buscar da API para garantir dados completos
        console.log('Buscando dados completos da API...');
        const response = await MembrosAPI.buscar(id);
        
        // Ocultar indicador
        ocultarIndicadorCarregamento();
        
        if (response && response.success) {
            // Salvar no cache para próximas visualizações
            salvarDadosNoCache(id, response.data);
            abrirModalMembro(response.data, 'visualizar');
        } else {
            mostrarNotificacao('Erro ao carregar dados do membro: ' + (response?.error || 'Erro desconhecido'), 'error');
        }
    } catch (error) {
        ocultarIndicadorCarregamento();
        console.error('Erro ao visualizar membro:', error);
        mostrarNotificacao('Erro ao carregar dados do membro: ' + error.message, 'error');
    }
}

/**
 * Edita membro
 */
async function editarMembro(id) {
    try {
        // Verificar cache primeiro (mais rápido)
        const dadosCache = obterDadosDoCache(id);
        if (dadosCache) {
            console.log('Usando dados do cache para edição rápida');
            abrirModalMembro(dadosCache, 'editar');
            return;
        }
        
        // Se não estiver no cache, mostrar indicador de carregamento
        mostrarIndicadorCarregamento('Carregando dados do membro...');
        
        // Buscar da API
        console.log('Buscando dados da API...');
        const response = await MembrosAPI.buscar(id);
        
        // Ocultar indicador
        ocultarIndicadorCarregamento();
        
        if (response && response.success) {
            // Salvar no cache para próximas edições
            salvarDadosNoCache(id, response.data);
            abrirModalMembro(response.data, 'editar');
        } else {
            mostrarNotificacao('Erro ao carregar dados do membro: ' + (response?.error || 'Erro desconhecido'), 'error');
        }
    } catch (error) {
        ocultarIndicadorCarregamento();
        console.error('Erro ao editar membro:', error);
        mostrarNotificacao('Erro ao carregar dados do membro: ' + error.message, 'error');
    }
}


/**
 * Exclui membro
 */
function excluirMembro(id) {
    console.log('excluirMembro chamado com ID:', id);
    console.log('Tipo do ID:', typeof id);
    console.log('ID é undefined?', id === undefined);
    console.log('ID é null?', id === null);
    console.log('ID é string vazia?', id === '');
    
    if (!id) {
        console.error('ID inválido para exclusão:', id);
        mostrarNotificacao('ID inválido para exclusão', 'error');
        return;
    }
    
    const membro = AppState.membros.find(m => m.id === id);
    if (membro) {
        if (confirm(`Tem certeza que deseja excluir o membro "${membro.nome_completo}"?`)) {
            excluirMembroAPI(id)
                .then(response => {
                    if (response.success) {
                        mostrarNotificacao('Membro excluído com sucesso', 'success');
                        carregarMembros(); // Recarregar lista
                    } else {
                        mostrarNotificacao('Erro ao excluir membro', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro ao excluir membro:', error);
                    mostrarNotificacao('Erro ao excluir membro', 'error');
                });
        }
    } else {
        console.error('Membro não encontrado no AppState para ID:', id);
        mostrarNotificacao('Membro não encontrado', 'error');
    }
}

// Função abrirModalMembro está definida em modals.js

/**
 * Visualiza foto do membro
 */
function visualizarFoto(id) {
    const membro = AppState.membros.find(m => m.id === id);
    
    if (!membro) {
        mostrarNotificacao('Membro não encontrado', 'error');
        return;
    }
    
    if (!membro.foto_url) {
        mostrarNotificacao('Este membro não possui foto cadastrada', 'info');
        return;
    }
    
    // Criar modal para exibir a foto
    const modalId = 'modal-foto-' + Date.now();
    const modalHtml = `
        <div class="modal" id="${modalId}" style="display: flex;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Foto - ${membro.nome_completo}</h5>
                        <button type="button" class="btn-close" onclick="fecharModalFoto('${modalId}')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${membro.foto_url}" alt="Foto de ${membro.nome_completo}" 
                             style="max-width: 100%; max-height: 70vh; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);" 
                             onerror="this.src='https://via.placeholder.com/400x400?text=Erro+ao+carregar+imagem'">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModalFoto('${modalId}')">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Adicionar modal ao body
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = modalHtml;
    document.body.appendChild(tempDiv.firstElementChild);
    
    // Adicionar overlay de fundo
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040;';
    overlay.onclick = () => fecharModalFoto(modalId);
    document.body.appendChild(overlay);
    
    // Adicionar animação de entrada
    setTimeout(() => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.opacity = '1';
        }
    }, 10);
}

/**
 * Fechar modal de foto
 */
function fecharModalFoto(modalId) {
    const modal = document.getElementById(modalId);
    const overlay = document.querySelector('.modal-overlay');
    
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.remove();
            if (overlay) overlay.remove();
        }, 300);
    }
}

// Exportar para o escopo global
window.visualizarFoto = visualizarFoto;
window.fecharModalFoto = fecharModalFoto;

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
    
    // Invalidar cache ao aplicar filtros
    limparCacheExpirado();
    
    carregarMembros();
}

// Versão com debounce para busca
const aplicarFiltrosDebounce = debounce(aplicarFiltros, 500);

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
                <button class="btn btn-sm btn-success" onclick="visualizarPastoral('${pastoral.id}')" title="Ver Detalhes">
                    <i class="fas fa-eye"></i> Mais
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
// AÇÕES DE MEMBROS (FUNÇÕES DUPLICADAS REMOVIDAS)
// =====================================================
// As funções de ação dos membros estão definidas acima
// nas linhas 572-626 para evitar conflitos

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

function visualizarPastoral(id) {
    // Redirecionar para a página de detalhes da pastoral
    window.location.href = `pastoral_detalhes.php?id=${id}`;
}

// =====================================================
// SISTEMA DE CACHE
// =====================================================

/**
 * Obtém dados do cache se ainda forem válidos
 */
function obterDadosDoCache(id) {
    const agora = Date.now();
    const dadosCache = AppState.cacheMembros.get(id);
    
    if (dadosCache) {
        // Verificar se os dados não estão muito antigos (5 minutos)
        const tempoLimite = 5 * 60 * 1000; // 5 minutos em ms
        if (agora - dadosCache.timestamp < tempoLimite) {
            return dadosCache.dados;
        } else {
            // Remover dados antigos do cache
            AppState.cacheMembros.delete(id);
        }
    }
    
    return null;
}

/**
 * Salva dados no cache
 */
function salvarDadosNoCache(id, dados) {
    AppState.cacheMembros.set(id, {
        dados: dados,
        timestamp: Date.now()
    });
    
    // Limitar tamanho do cache (máximo 50 membros)
    if (AppState.cacheMembros.size > 50) {
        const primeiroId = AppState.cacheMembros.keys().next().value;
        AppState.cacheMembros.delete(primeiroId);
    }
}

/**
 * Limpa o cache de membros
 */
function limparCacheMembros() {
    AppState.cacheMembros.clear();
    AppState.ultimaAtualizacaoCache = 0;
}

/**
 * Invalida dados específicos do cache (após atualização)
 */
function invalidarCacheMembro(id) {
    AppState.cacheMembros.delete(id);
}

/**
 * Mostra indicador de carregamento
 */
function mostrarIndicadorCarregamento(mensagem = 'Carregando...') {
    // Remover indicador anterior se existir
    ocultarIndicadorCarregamento();
    
    const indicador = document.createElement('div');
    indicador.id = 'loading-indicator';
    indicador.innerHTML = `
        <div class="loading-overlay">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>${mensagem}</p>
            </div>
        </div>
    `;
    
    document.body.appendChild(indicador);
}

/**
 * Oculta indicador de carregamento
 */
function ocultarIndicadorCarregamento() {
    const indicador = document.getElementById('loading-indicator');
    if (indicador) {
        indicador.remove();
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

function formatarPastorais(pastorais) {
    // Se pastorais é um array de objetos
    if (Array.isArray(pastorais)) {
        if (pastorais.length === 0) {
            return '<span class="text-muted">Nenhuma</span>';
        }
        return pastorais.map(p => `<span class="badge badge-info">${p.nome || p}</span>`).join('');
    }
    
    // Se pastorais é uma string (separada por vírgulas)
    if (typeof pastorais === 'string' && pastorais.trim() !== '') {
        const lista = pastorais.split(',').map(p => p.trim()).filter(p => p);
        if (lista.length === 0) {
            return '<span class="text-muted">Nenhuma</span>';
        }
        return lista.map(p => `<span class="badge badge-info">${p}</span>`).join('');
    }
    
    // Se pastorais é null, undefined ou string vazia
    return '<span class="text-muted">Nenhuma</span>';
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
        // Busca com debounce enquanto digita
        filtroBusca.addEventListener('input', function() {
            aplicarFiltrosDebounce();
        });
        
        // Enter aplica imediatamente
        filtroBusca.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
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

// =====================================================
// EXPORTAÇÃO DE FUNÇÕES GLOBAIS
// =====================================================

// Exportar funções de ação dos membros para uso global
window.visualizarMembro = visualizarMembro;
window.editarMembro = editarMembro;
window.excluirMembro = excluirMembro;
window.carregarMembros = carregarMembros;

