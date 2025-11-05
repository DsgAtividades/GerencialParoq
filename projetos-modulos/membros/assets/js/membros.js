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
    eventosCalendario: [], // Eventos para o calendário (gerais + pastorais)
    eventosPorData: {}, // Eventos organizados por data para calendário
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
            carregarEventosCalendario();
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

        // Limpar filtros vazios antes de enviar
        const filtrosLimpos = {};
        if (AppState.filtros.busca && AppState.filtros.busca.trim()) {
            filtrosLimpos.busca = AppState.filtros.busca.trim();
        }
        if (AppState.filtros.status && AppState.filtros.status.trim()) {
            filtrosLimpos.status = AppState.filtros.status.trim();
        }
        if (AppState.filtros.pastoral && AppState.filtros.pastoral.trim()) {
            filtrosLimpos.pastoral = AppState.filtros.pastoral.trim();
        }
        if (AppState.filtros.funcao && AppState.filtros.funcao.trim()) {
            filtrosLimpos.funcao = AppState.filtros.funcao.trim();
        }

        const params = {
            page: AppState.paginacao.page,
            limit: AppState.paginacao.limit,
            ...filtrosLimpos
        };

        // NÃO usar cache quando há filtros ativos
        const temFiltros = Object.keys(filtrosLimpos).length > 0;
        
        let response;
        if (!temFiltros) {
            // Apenas usar cache se não houver filtros
            const cacheKey = 'membros-' + JSON.stringify(params);
            const cached = obterDoCache(cacheKey);
            
            if (cached) {
                console.log('Usando membros do cache');
                response = cached;
            } else {
                response = await carregarMembrosAPI(params);
                salvarNoCache(cacheKey, response);
            }
        } else {
            // Com filtros, sempre buscar da API (sem cache)
            console.log('Buscando membros com filtros:', params);
            response = await carregarMembrosAPI(params);
        }

        if (response && response.success) {
            // A API retorna: { success: true, data: { data: [...], pagination: {...} } }
            const membrosData = response.data?.data || response.data || [];
            AppState.membros = Array.isArray(membrosData) ? membrosData : [];
            
            // Atualizar paginação - API retorna 'pagination' (em inglês)
            const pagination = response.data?.pagination || response.pagination || {};
            AppState.paginacao = {
                page: pagination.page || AppState.paginacao.page || 1,
                limit: pagination.limit || AppState.paginacao.limit || 20,
                total: pagination.total !== undefined ? pagination.total : (AppState.paginacao.total || 0),
                pages: pagination.pages || 1,
                has_next: pagination.has_next !== undefined ? pagination.has_next : false,
                has_prev: pagination.has_prev !== undefined ? pagination.has_prev : false
            };
            
            atualizarTabelaMembros();
            atualizarPaginacao();
        } else {
            AppState.membros = [];
            AppState.paginacao.total = 0;
            atualizarTabelaMembros();
            mostrarErroTabela('Erro ao carregar membros');
        }
    } catch (error) {
        console.error('Erro ao carregar membros:', error);
        AppState.membros = [];
        AppState.paginacao.total = 0;
        atualizarTabelaMembros();
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
            // A API retorna: { success: true, data: [...] }
            console.log('Resposta da API de pastorais:', response);
            const pastoraisData = response.data?.data || response.data || [];
            console.log('Dados de pastorais extraídos:', pastoraisData);
            console.log('Primeira pastoral (se houver):', pastoraisData[0]);
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
        
        // Atualizar contador mesmo quando não houver membros
        const totalRegistros = document.getElementById('total-registros');
        if (totalRegistros) {
            const total = AppState.paginacao?.total || 0;
            const texto = total === 1 ? '1 registro' : `${total} registros`;
            totalRegistros.textContent = texto;
        }
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
        const total = AppState.paginacao?.total || AppState.membros?.length || 0;
        const texto = total === 1 ? '1 registro' : `${total} registros`;
        totalRegistros.textContent = texto;
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
    const filtroBusca = document.getElementById('filtro-busca');
    const filtroStatus = document.getElementById('filtro-status');
    const filtroPastoral = document.getElementById('filtro-pastoral');
    
    // Atualizar filtros no estado
    AppState.filtros.busca = filtroBusca ? filtroBusca.value.trim() : '';
    AppState.filtros.status = filtroStatus ? filtroStatus.value.trim() : '';
    AppState.filtros.pastoral = filtroPastoral ? filtroPastoral.value.trim() : '';
    AppState.paginacao.page = 1;
    
    // Invalidar todo o cache ao aplicar filtros
    limparCacheExpirado();
    AppState.apiCache.clear(); // Limpar cache de API também
    
    console.log('Aplicando filtros:', AppState.filtros);
    
    carregarMembros();
}

// Versão com debounce para busca
const aplicarFiltrosDebounce = debounce(aplicarFiltros, 500);

/**
 * Limpa todos os filtros e recarrega membros
 */
function limparFiltros() {
    const filtroBusca = document.getElementById('filtro-busca');
    const filtroStatus = document.getElementById('filtro-status');
    const filtroPastoral = document.getElementById('filtro-pastoral');
    
    // Limpar campos
    if (filtroBusca) filtroBusca.value = '';
    if (filtroStatus) filtroStatus.value = '';
    if (filtroPastoral) filtroPastoral.value = '';
    
    // Limpar estado
    AppState.filtros = {
        busca: '',
        status: '',
        pastoral: '',
        funcao: ''
    };
    AppState.paginacao.page = 1;
    
    // Limpar cache
    limparCacheExpirado();
    AppState.apiCache.clear();
    
    console.log('Filtros limpos');
    
    // Recarregar membros
    carregarMembros();
}

// Exportar para escopo global
window.limparFiltros = limparFiltros;

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
    
    console.log('atualizarCardsPastorais: Total de pastorais:', pastorais.length);
    if (pastorais.length > 0) {
        console.log('Primeira pastoral no render:', pastorais[0]);
        console.log('Coordenador_nome da primeira:', pastorais[0]?.coordenador_nome);
    }
    
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
                <p><strong>Coordenador:</strong> ${pastoral.coordenador_nome || 'Não definido'}</p>
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
/**
 * Carrega eventos para o calendário
 */
async function carregarEventosCalendario() {
    try {
        const response = await fetch(`${CONFIG.apiBaseUrl}eventos/calendario`);
        const result = await response.json();
        
        if (result.success) {
            AppState.eventosCalendario = result.data.eventos || [];
            AppState.eventosPorData = result.data.eventos_por_data || {};
            atualizarCalendarioEventos();
        } else {
            console.error('Erro ao carregar eventos do calendário:', result.error);
            mostrarCalendarioErro();
        }
    } catch (error) {
        console.error('Erro ao carregar eventos do calendário:', error);
        mostrarCalendarioErro();
    }
}

/**
 * Mostra mensagem de erro no calendário
 */
function mostrarCalendarioErro() {
    const container = document.getElementById('calendario-eventos');
    if (!container) return;
    
    container.innerHTML = `
        <div class="loading-card">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Erro ao carregar eventos</p>
        </div>
    `;
}

/**
 * Variável global para mês atual do calendário
 */
let mesCalendarioAtual = new Date();

/**
 * Atualiza o calendário de eventos
 */
function atualizarCalendarioEventos() {
    const container = document.getElementById('calendario-eventos');
    if (!container) return;
    
    const eventosPorData = AppState.eventosPorData || {};
    const mes = mesCalendarioAtual.getMonth();
    const ano = mesCalendarioAtual.getFullYear();
    
    // Primeiro dia do mês
    const primeiroDia = new Date(ano, mes, 1);
    const ultimoDia = new Date(ano, mes + 1, 0);
    const diasNoMes = ultimoDia.getDate();
    const diaSemanaInicio = primeiroDia.getDay(); // 0 = Domingo, 6 = Sábado
    
    // Nomes dos meses e dias
    const meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                   'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    const diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
    
    let html = `
        <div class="calendar-wrapper">
            <div class="calendar-header">
                <button class="btn btn-sm btn-secondary" onclick="mesAnterior()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h3>${meses[mes]} ${ano}</h3>
                <button class="btn btn-sm btn-secondary" onclick="mesProximo()">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <table class="calendar-table">
                <thead>
                    <tr>
                        ${diasSemana.map(dia => `<th>${dia}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
    `;
    
    let diaAtual = 1;
    
    // Gerar semanas
    for (let semana = 0; semana < 6; semana++) {
        html += '<tr>';
        
        for (let diaSemana = 0; diaSemana < 7; diaSemana++) {
            if (semana === 0 && diaSemana < diaSemanaInicio) {
                // Dias vazios antes do primeiro dia do mês
                html += '<td class="calendar-day empty"></td>';
            } else if (diaAtual > diasNoMes) {
                // Dias vazios depois do último dia do mês
                html += '<td class="calendar-day empty"></td>';
            } else {
                const dataFormatada = `${ano}-${String(mes + 1).padStart(2, '0')}-${String(diaAtual).padStart(2, '0')}`;
                const eventosDoDia = eventosPorData[dataFormatada] || [];
                const totalEventos = eventosDoDia.length;
                
                // Determinar classe do dia
                const hoje = new Date();
                const isHoje = ano === hoje.getFullYear() && mes === hoje.getMonth() && diaAtual === hoje.getDate();
                const classeDia = isHoje ? 'today' : '';
                
                html += `<td class="calendar-day ${classeDia}" onclick="mostrarEventosDoDia('${dataFormatada}', ${totalEventos})">
                    <div class="day-number">${diaAtual}</div>
                    ${totalEventos > 0 ? `<div class="day-events">
                        ${Array.from({length: Math.min(totalEventos, 3)}, (_, i) => 
                            `<span class="event-dot ${eventosDoDia[i]?.origem || 'geral'}" title="${eventosDoDia[i]?.nome || ''}"></span>`
                        ).join('')}
                        ${totalEventos > 3 ? `<span class="event-count">+${totalEventos - 3}</span>` : ''}
                    </div>` : ''}
                </td>`;
                
                diaAtual++;
            }
        }
        
        html += '</tr>';
        
        // Se já renderizou todos os dias, parar
        if (diaAtual > diasNoMes) break;
    }
    
    html += `
                </tbody>
            </table>
            <div class="calendar-legend">
                <div class="legend-item">
                    <span class="event-dot geral"></span>
                    <span>Eventos Gerais</span>
                </div>
                <div class="legend-item">
                    <span class="event-dot pastoral"></span>
                    <span>Eventos de Pastorais</span>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

/**
 * Navega para o mês anterior
 */
function mesAnterior() {
    mesCalendarioAtual.setMonth(mesCalendarioAtual.getMonth() - 1);
    atualizarCalendarioEventos();
}

/**
 * Navega para o próximo mês
 */
function mesProximo() {
    mesCalendarioAtual.setMonth(mesCalendarioAtual.getMonth() + 1);
    atualizarCalendarioEventos();
}

/**
 * Mostra eventos de um dia específico
 */
function mostrarEventosDoDia(dataFormatada, totalEventos) {
    if (totalEventos === 0) return;
    
    const eventosPorData = AppState.eventosPorData || {};
    const eventosDoDia = eventosPorData[dataFormatada] || [];
    
    // Converter data corretamente para evitar problemas de fuso horário
    const [ano, mes, dia] = dataFormatada.split('-').map(Number);
    const dataObj = new Date(ano, mes - 1, dia);
    const dataFormatadaBR = dataObj.toLocaleDateString('pt-BR', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    let html = `
        <div id="modal-eventos-dia" class="modal fade show" style="display: block;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-calendar-day"></i> Eventos de ${dataFormatadaBR}
                        </h5>
                        <button type="button" class="close" onclick="fecharModalEventosDia()" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="eventos-lista-dia">
                            ${eventosDoDia.map(evento => {
                                const horarioFormatado = evento.horario ? evento.horario.substring(0, 5) : 'Não definido';
                                const origem = evento.origem || (evento.pastoral_id ? 'pastoral' : 'geral');
                                const origemBadge = origem === 'pastoral' 
                                    ? `<span class="badge badge-info">Pastoral: ${evento.pastoral_nome || 'N/A'}</span>`
                                    : `<span class="badge badge-secondary">Evento Geral</span>`;
                                
                                return `
                                    <div class="evento-item-dia" onclick="abrirDetalhesEventoCalendario('${evento.id}', '${origem}')">
                                        <div class="evento-item-header">
                                            <h6>${evento.nome || 'Sem nome'}</h6>
                                            ${origemBadge}
                                        </div>
                                        <div class="evento-item-body">
                                            <p><i class="fas fa-clock"></i> ${horarioFormatado}</p>
                                            ${evento.local ? `<p><i class="fas fa-map-marker-alt"></i> ${evento.local}</p>` : ''}
                                            ${evento.tipo ? `<p><i class="fas fa-tag"></i> ${evento.tipo}</p>` : ''}
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModalEventosDia()">
                            <i class="fas fa-times"></i> Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" onclick="fecharModalEventosDia()"></div>
    `;
    
    // Remover modal anterior se existir
    const modalAnterior = document.getElementById('modal-eventos-dia');
    if (modalAnterior) {
        modalAnterior.remove();
        document.querySelector('.modal-backdrop')?.remove();
    }
    
    // Adicionar modal
    const container = document.getElementById('modal-container');
    if (container) {
        container.innerHTML = html;
    } else {
        document.body.insertAdjacentHTML('beforeend', html);
    }
}

/**
 * Fecha modal de eventos do dia
 */
function fecharModalEventosDia() {
    const modal = document.getElementById('modal-eventos-dia');
    const backdrop = document.querySelector('.modal-backdrop');
    if (modal) modal.remove();
    if (backdrop) backdrop.remove();
}

/**
 * Abre detalhes de um evento do calendário
 */
function abrirDetalhesEventoCalendario(eventoId, origem) {
    const eventos = AppState.eventosCalendario || [];
    const evento = eventos.find(e => e.id === eventoId);
    
    if (!evento) {
        mostrarNotificacao('Evento não encontrado', 'error');
        return;
    }
    
    // Determinar origem se não foi passada
    if (!origem) {
        // Verificar se é evento geral ou de pastoral
        origem = evento.origem || (evento.pastoral_id ? 'pastoral' : 'geral');
    }
    
    // Se não tem pastoral_id definido e não é explicitamente pastoral, considerar geral
    const isEventoGeral = origem === 'geral' || (!evento.pastoral_id && origem !== 'pastoral');
    
    fecharModalEventosDia();
    
    const horarioFormatado = evento.horario ? evento.horario.substring(0, 5) : (evento.hora_inicio ? evento.hora_inicio.substring(0, 5) : 'Não definido');
    const tipoFormatado = evento.tipo || 'Não especificado';
    const localFormatado = evento.local || 'Não definido';
    const descricaoFormatada = evento.descricao || 'Sem descrição';
    const responsavelFormatado = evento.responsavel_nome || evento.responsavel_id || 'Não definido';
    const pastoralNome = evento.pastoral_nome || 'N/A';
    
    const modalHTML = `
        <div id="modal-detalhes-evento" class="modal fade show" style="display: block;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-calendar-check"></i> Detalhes do Evento
                        </h5>
                        <button type="button" class="close" onclick="fecharModalDetalhesEvento()" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="evento-detalhes">
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-heading"></i> Nome:</div>
                                <div class="detail-value">${evento.nome || '-'}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-tag"></i> Tipo:</div>
                                <div class="detail-value">${tipoFormatado}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-calendar"></i> Data:</div>
                                <div class="detail-value">${formatarData(evento.data || evento.data_evento)}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-clock"></i> Horário:</div>
                                <div class="detail-value">${horarioFormatado}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-map-marker-alt"></i> Local:</div>
                                <div class="detail-value">${localFormatado}</div>
                            </div>
                            ${origem === 'pastoral' ? `
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-church"></i> Pastoral:</div>
                                <div class="detail-value">${pastoralNome}</div>
                            </div>
                            ` : ''}
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-user"></i> Responsável:</div>
                                <div class="detail-value">${responsavelFormatado}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-align-left"></i> Descrição:</div>
                                <div class="detail-value" style="white-space: pre-wrap;">${descricaoFormatada}</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        ${isEventoGeral ? `
                        <button type="button" class="btn btn-danger" onclick="confirmarExcluirEvento('${evento.id}')" title="Excluir este evento">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                        <button type="button" class="btn btn-primary" onclick="editarEventoGeral('${evento.id}')" title="Editar este evento">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        ` : ''}
                        <button type="button" class="btn btn-secondary" onclick="fecharModalDetalhesEvento()">
                            <i class="fas fa-times"></i> Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" onclick="fecharModalDetalhesEvento()"></div>
    `;
    
    // Remover modal anterior se existir
    const modalAnterior = document.getElementById('modal-detalhes-evento');
    if (modalAnterior) {
        modalAnterior.remove();
        document.querySelector('.modal-backdrop')?.remove();
    }
    
    // Adicionar modal
    const container = document.getElementById('modal-container');
    if (container) {
        container.innerHTML = modalHTML;
    } else {
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
}

/**
 * Fecha modal de detalhes do evento (para calendário)
 */
function fecharModalDetalhesEvento() {
    const modal = document.getElementById('modal-detalhes-evento');
    const backdrop = document.querySelector('.modal-backdrop');
    if (modal) modal.remove();
    if (backdrop) backdrop.remove();
}

// Variável global para evento geral em edição
let eventoGeralEditando = null;

/**
 * Abre modal para criar/editar evento geral
 */
function abrirModalEvento(evento = null) {
    eventoGeralEditando = evento;
    const isEdicao = evento !== null;
    
    const tiposEvento = [
        { value: 'missa', label: 'Missa' },
        { value: 'reuniao', label: 'Reunião' },
        { value: 'formacao', label: 'Formação' },
        { value: 'acao_social', label: 'Ação Social' },
        { value: 'feira', label: 'Feira' },
        { value: 'festa_patronal', label: 'Festa Patronal' },
        { value: 'outro', label: 'Outro' }
    ];
    
    const tipoOptions = tiposEvento.map(t => 
        `<option value="${t.value}" ${evento && evento.tipo === t.value ? 'selected' : ''}>${t.label}</option>`
    ).join('');
    
    const modalHTML = `
        <div id="modal-evento-geral" class="modal fade show" style="display: block;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-calendar"></i> ${isEdicao ? 'Editar Evento Geral' : 'Novo Evento Geral'}
                        </h5>
                        <button type="button" class="close" onclick="fecharModalEventoGeral()" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="form-evento-geral">
                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label for="evento-geral-nome">Nome do Evento *</label>
                                    <input type="text" class="form-control" id="evento-geral-nome" name="nome" required 
                                           value="${evento ? (evento.nome || '') : ''}" 
                                           placeholder="Ex: Missa de Domingo">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="evento-geral-tipo">Tipo *</label>
                                    <select class="form-control" id="evento-geral-tipo" name="tipo" required>
                                        <option value="">Selecione...</option>
                                        ${tipoOptions}
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="evento-geral-data">Data do Evento *</label>
                                    <input type="date" class="form-control" id="evento-geral-data" name="data_evento" required 
                                           value="${evento ? (evento.data_evento || evento.data || '') : ''}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="evento-geral-horario">Horário</label>
                                    <input type="time" class="form-control" id="evento-geral-horario" name="horario" 
                                           value="${evento && evento.horario ? evento.horario.substring(0, 5) : ''}">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="evento-geral-local">Local</label>
                                    <input type="text" class="form-control" id="evento-geral-local" name="local" 
                                           value="${evento ? (evento.local || '') : ''}" 
                                           placeholder="Ex: Igreja Matriz">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="evento-geral-responsavel">Responsável</label>
                                    <div class="autocomplete-wrapper">
                                        <input type="text" class="form-control autocomplete-input" 
                                               id="evento-geral-responsavel" 
                                               name="responsavel_nome" 
                                               autocomplete="off"
                                               value="${evento && evento.responsavel_nome ? (evento.responsavel_nome || '') : ''}" 
                                               placeholder="Digite o nome do responsável..." 
                                               onkeyup="buscarMembrosAutocomplete('evento-geral-responsavel', event)"
                                               onblur="fecharAutocomplete('evento-geral-responsavel')"
                                               onfocus="if(this.value) buscarMembrosAutocomplete('evento-geral-responsavel', event)">
                                        <input type="hidden" id="evento-geral-responsavel-id" name="responsavel_id" 
                                               value="${evento ? (evento.responsavel_id || '') : ''}">
                                        <div class="autocomplete-dropdown" id="evento-geral-responsavel-dropdown"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="evento-geral-descricao">Descrição</label>
                                    <textarea class="form-control" id="evento-geral-descricao" name="descricao" rows="3" 
                                              placeholder="Descrição do evento...">${evento ? (evento.descricao || '') : ''}</textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModalEventoGeral()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" onclick="salvarEventoGeral()">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" onclick="fecharModalEventoGeral()"></div>
    `;
    
    // Remover modal anterior se existir
    const modalAnterior = document.getElementById('modal-evento-geral');
    if (modalAnterior) {
        modalAnterior.remove();
        document.querySelector('.modal-backdrop')?.remove();
    }
    
    // Adicionar modal
    const container = document.getElementById('modal-container');
    if (container) {
        container.innerHTML = modalHTML;
    } else {
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    // Focar no primeiro campo
    setTimeout(() => {
        document.getElementById('evento-geral-nome').focus();
    }, 100);
}

/**
 * Fecha modal de evento geral
 */
function fecharModalEventoGeral() {
    const modal = document.getElementById('modal-evento-geral');
    const backdrop = document.querySelector('.modal-backdrop');
    if (modal) modal.remove();
    if (backdrop) backdrop.remove();
    eventoGeralEditando = null;
}

/**
 * Salva evento geral (criar ou atualizar)
 */
async function salvarEventoGeral() {
    const form = document.getElementById('form-evento-geral');
    if (!form || !form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    // Pegar o ID do campo hidden
    const responsavelId = document.getElementById('evento-geral-responsavel-id')?.value || null;
    const dados = {
        nome: formData.get('nome'),
        tipo: formData.get('tipo'),
        data_evento: formData.get('data_evento'),
        horario: formData.get('horario') || null,
        local: formData.get('local') || null,
        responsavel_id: responsavelId,
        descricao: formData.get('descricao') || null
    };
    
    try {
        const url = eventoGeralEditando 
            ? `${CONFIG.apiBaseUrl}eventos/${eventoGeralEditando.id}`
            : `${CONFIG.apiBaseUrl}eventos`;
        
        const method = eventoGeralEditando ? 'PUT' : 'POST';
        
        const btnSalvar = document.querySelector('#modal-evento-geral .btn-primary');
        const textoOriginal = btnSalvar.innerHTML;
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacao(eventoGeralEditando ? 'Evento atualizado com sucesso!' : 'Evento criado com sucesso!', 'success');
            fecharModalEventoGeral();
            
            // Recarregar eventos no calendário
            await carregarEventosCalendario();
        } else {
            mostrarNotificacao(result.error || 'Erro ao salvar evento', 'error');
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = textoOriginal;
        }
    } catch (error) {
        console.error('Erro ao salvar evento geral:', error);
        mostrarNotificacao('Erro ao salvar evento: ' + error.message, 'error');
        
        const btnSalvar = document.querySelector('#modal-evento-geral .btn-primary');
        if (btnSalvar) {
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="fas fa-save"></i> Salvar';
        }
    }
}

/**
 * Busca membros para autocomplete
 */
let autocompleteTimeout = null;
async function buscarMembrosAutocomplete(campoId, event) {
    const input = document.getElementById(campoId);
    if (!input) return;
    
    // Ignorar teclas de navegação
    if (['ArrowDown', 'ArrowUp', 'Enter', 'Tab', 'Escape'].includes(event.key)) {
        return;
    }
    
    const query = input.value.trim();
    const dropdown = document.getElementById(campoId + '-dropdown');
    
    if (query.length < 2) {
        dropdown.innerHTML = '';
        dropdown.style.display = 'none';
        // Limpar ID oculto
        const hiddenId = document.getElementById(campoId + '-id');
        if (hiddenId) hiddenId.value = '';
        return;
    }
    
    // Debounce: aguardar 300ms após parar de digitar
    clearTimeout(autocompleteTimeout);
    autocompleteTimeout = setTimeout(async () => {
        try {
            const url = `${CONFIG.apiBaseUrl}membros/buscar?q=${encodeURIComponent(query)}`;
            console.log('Buscando membros:', url);
            
            const response = await fetch(url);
            
            if (!response.ok) {
                console.error('Erro HTTP:', response.status, response.statusText);
                dropdown.style.display = 'none';
                return;
            }
            
            const result = await response.json();
            console.log('Resultado da busca:', result);
            
            if (result.success && result.data && result.data.length > 0) {
                mostrarAutocompleteDropdown(campoId, result.data);
            } else {
                dropdown.innerHTML = '<div class="autocomplete-item">Nenhum membro encontrado</div>';
                dropdown.style.display = 'block';
            }
        } catch (error) {
            console.error('Erro ao buscar membros:', error);
            dropdown.style.display = 'none';
        }
    }, 300);
}

/**
 * Mostra dropdown de autocomplete
 */
function mostrarAutocompleteDropdown(campoId, membros) {
    const dropdown = document.getElementById(campoId + '-dropdown');
    if (!dropdown) {
        console.error('Dropdown não encontrado para:', campoId + '-dropdown');
        return;
    }
    
    console.log('Mostrando dropdown com', membros.length, 'membros');
    
    const html = membros.map(membro => {
        // Escape correto para evitar problemas com aspas
        const nomeEscapado = String(membro.nome).replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const idEscapado = String(membro.id).replace(/'/g, "\\'");
        return `
        <div class="autocomplete-item" 
             onclick="selecionarMembroAutocomplete('${campoId}', '${idEscapado}', '${nomeEscapado}')">
            <strong>${membro.nome}</strong>
        </div>
    `;
    }).join('');
    
    dropdown.innerHTML = html;
    dropdown.style.display = 'block';
    console.log('Dropdown exibido com sucesso');
}

/**
 * Seleciona um membro do autocomplete
 */
function selecionarMembroAutocomplete(campoId, membroId, membroNome) {
    const input = document.getElementById(campoId);
    const hiddenId = document.getElementById(campoId + '-id');
    const dropdown = document.getElementById(campoId + '-dropdown');
    
    if (input) {
        input.value = membroNome;
    }
    if (hiddenId) {
        hiddenId.value = membroId;
    }
    if (dropdown) {
        dropdown.style.display = 'none';
    }
    
    // Focar novamente no input
    if (input) input.focus();
}

/**
 * Fecha dropdown de autocomplete
 */
function fecharAutocomplete(campoId) {
    // Aguardar um pouco para permitir clique no item
    setTimeout(() => {
        const dropdown = document.getElementById(campoId + '-dropdown');
        if (dropdown) {
            dropdown.style.display = 'none';
        }
    }, 200);
}

/**
 * Edita um evento geral
 */
async function editarEventoGeral(eventoId) {
    try {
        console.log('Buscando evento para edição:', eventoId);
        
        // Buscar dados do evento
        const response = await EventosAPI.buscar(eventoId);
        console.log('Resposta da API:', response);
        
        // Verificar se a resposta tem sucesso
        if (response && response.success && response.data) {
            const evento = response.data;
            console.log('Evento encontrado:', evento);
            
            // Converter para formato esperado pelo modal
            // Garantir que data_evento existe (usar data como fallback)
            if (!evento.data_evento && evento.data) {
                evento.data_evento = evento.data;
            }
            evento.data = evento.data_evento || evento.data;
            
            // Garantir que horario existe
            evento.horario = evento.horario || evento.hora_inicio || '';
            
            // Fechar modal de detalhes se estiver aberto
            const modalDetalhes = document.getElementById('modal-detalhes-evento');
            if (modalDetalhes) {
                fecharModalDetalhesEvento();
            }
            
            // Aguardar um pouco para fechar modal anterior
            setTimeout(() => {
                abrirModalEvento(evento);
            }, 100);
        } else {
            console.error('Resposta da API inválida:', response);
            mostrarNotificacao('Erro ao carregar evento para edição. Resposta inválida da API.', 'error');
        }
    } catch (error) {
        console.error('Erro ao buscar evento:', error);
        mostrarNotificacao('Erro ao carregar evento: ' + (error.message || 'Erro desconhecido'), 'error');
    }
}

/**
 * Confirma e exclui um evento geral
 */
function confirmarExcluirEvento(eventoId) {
    abrirModalConfirmacao(
        'Confirmar Exclusão',
        'Tem certeza que deseja excluir este evento? Esta ação não pode ser desfeita.',
        () => excluirEventoGeral(eventoId),
        null
    );
}

/**
 * Exclui um evento geral
 */
async function excluirEventoGeral(eventoId) {
    try {
        const btnExcluir = document.querySelector('[onclick*="confirmarExcluirEvento"]');
        if (btnExcluir) {
            btnExcluir.disabled = true;
            btnExcluir.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Excluindo...';
        }
        
        const response = await EventosAPI.excluir(eventoId);
        
        if (response.success) {
            mostrarNotificacao('Evento excluído com sucesso!', 'success');
            fecharModalDetalhesEvento();
            
            // Recarregar eventos no calendário
            await carregarEventosCalendario();
            await carregarEventos(); // Recarregar também na lista de eventos
        } else {
            mostrarNotificacao(response.error || 'Erro ao excluir evento', 'error');
            if (btnExcluir) {
                btnExcluir.disabled = false;
                btnExcluir.innerHTML = '<i class="fas fa-trash"></i> Excluir';
            }
        }
    } catch (error) {
        console.error('Erro ao excluir evento:', error);
        mostrarNotificacao('Erro ao excluir evento: ' + error.message, 'error');
        
        const btnExcluir = document.querySelector('[onclick*="confirmarExcluirEvento"]');
        if (btnExcluir) {
            btnExcluir.disabled = false;
            btnExcluir.innerHTML = '<i class="fas fa-trash"></i> Excluir';
        }
    }
}

// Exportar para o escopo global
window.abrirModalEvento = abrirModalEvento;
window.fecharModalEventoGeral = fecharModalEventoGeral;
window.salvarEventoGeral = salvarEventoGeral;
window.buscarMembrosAutocomplete = buscarMembrosAutocomplete;
window.selecionarMembroAutocomplete = selecionarMembroAutocomplete;
window.fecharAutocomplete = fecharAutocomplete;
window.editarEventoGeral = editarEventoGeral;
window.confirmarExcluirEvento = confirmarExcluirEvento;
window.excluirEventoGeral = excluirEventoGeral;

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
    const modalHTML = `
        <div id="modal-pastoral" class="modal fade show" style="display: block;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-church"></i> Nova Pastoral
                        </h5>
                        <button type="button" class="close" onclick="fecharModalPastoral()" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="form-nova-pastoral">
                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label for="pastoral-nome">Nome da Pastoral *</label>
                                    <input type="text" class="form-control" id="pastoral-nome" name="nome" required placeholder="Ex: Catequese de Adultos">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="pastoral-tipo">Tipo *</label>
                                    <select class="form-control" id="pastoral-tipo" name="tipo" required>
                                        <option value="">Selecione...</option>
                                        <option value="pastoral">Pastoral</option>
                                        <option value="movimento">Movimento</option>
                                        <option value="ministerio_liturgico">Ministério Litúrgico</option>
                                        <option value="servico">Serviço</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="pastoral-comunidade">Comunidade/Capelania</label>
                                    <input type="text" class="form-control" id="pastoral-comunidade" name="comunidade_capelania" placeholder="Ex: Matriz, Capela São José">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="pastoral-descricao">Finalidade / Descrição</label>
                                    <textarea class="form-control" id="pastoral-descricao" name="finalidade_descricao" rows="3" placeholder="Descreva a finalidade e objetivos desta pastoral..."></textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="pastoral-whatsapp">Link do WhatsApp</label>
                                    <input type="text" class="form-control" id="pastoral-whatsapp" name="whatsapp_grupo_link" placeholder="Link do grupo do WhatsApp">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="pastoral-email">E-mail do Grupo</label>
                                    <input type="email" class="form-control" id="pastoral-email" name="email_grupo" placeholder="contato@pastoral.com">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModalPastoral()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" onclick="salvarNovaPastoral()">
                            <i class="fas fa-save"></i> Salvar Pastoral
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" onclick="fecharModalPastoral()"></div>
    `;
    
    // Remover modal anterior se existir
    const modalAnterior = document.getElementById('modal-pastoral');
    if (modalAnterior) {
        modalAnterior.remove();
    }
    
    // Adicionar modal ao container
    const container = document.getElementById('modal-container');
    if (container) {
        container.innerHTML = modalHTML;
    } else {
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    // Focar no primeiro campo
    setTimeout(() => {
        document.getElementById('pastoral-nome').focus();
    }, 100);
}

async function salvarNovaPastoral() {
    const form = document.getElementById('form-nova-pastoral');
    if (!form) return;
    
    // Validar formulário
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Coletar dados do formulário
    const formData = new FormData(form);
    const dados = {
        nome: formData.get('nome'),
        tipo: formData.get('tipo'),
        comunidade_capelania: formData.get('comunidade_capelania') || null,
        finalidade_descricao: formData.get('finalidade_descricao') || null,
        whatsapp_grupo_link: formData.get('whatsapp_grupo_link') || null,
        email_grupo: formData.get('email_grupo') || null,
        ativo: 1  // Sempre ativa por padrão
    };
    
    // Validações adicionais
    if (!dados.nome || dados.nome.trim() === '') {
        mostrarNotificacao('Nome da pastoral é obrigatório', 'error');
        return;
    }
    
    if (!dados.tipo) {
        mostrarNotificacao('Tipo da pastoral é obrigatório', 'error');
        return;
    }
    
    try {
        // Desabilitar botão durante o salvamento
        const btnSalvar = document.querySelector('#modal-pastoral .btn-primary');
        const textoOriginal = btnSalvar.innerHTML;
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
        
        const response = await fetch('api/pastorais', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacao('Pastoral criada com sucesso!', 'success');
            fecharModalPastoral();
            
            // Limpar cache e recarregar pastorais
            AppState.apiCache.delete('pastorais');
            await carregarPastorais();
        } else {
            mostrarNotificacao(result.error || 'Erro ao criar pastoral', 'error');
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = textoOriginal;
        }
    } catch (error) {
        console.error('Erro ao salvar pastoral:', error);
        mostrarNotificacao('Erro ao criar pastoral: ' + error.message, 'error');
        
        // Reabilitar botão
        const btnSalvar = document.querySelector('#modal-pastoral .btn-primary');
        if (btnSalvar) {
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="fas fa-save"></i> Salvar Pastoral';
        }
    }
}

function fecharModalPastoral() {
    const modal = document.getElementById('modal-pastoral');
    const backdrop = document.querySelector('.modal-backdrop');
    
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        setTimeout(() => modal.remove(), 300);
    }
    
    if (backdrop) {
        backdrop.classList.remove('show');
        setTimeout(() => backdrop.remove(), 300);
    }
    
    // Limpar container se estiver vazio
    const container = document.getElementById('modal-container');
    if (container && container.children.length === 0) {
        container.innerHTML = '';
    }
}

// Exportar funções globalmente
window.abrirModalPastoral = abrirModalPastoral;
window.salvarNovaPastoral = salvarNovaPastoral;
window.fecharModalPastoral = fecharModalPastoral;

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
    if (!data) return '-';
    
    // Se a data já está em formato de string YYYY-MM-DD, converter corretamente
    if (typeof data === 'string' && data.match(/^\d{4}-\d{2}-\d{2}/)) {
        const [ano, mes, dia] = data.split('-').map(Number);
        // Criar data no timezone local (mês é 0-indexed no JS)
        const dataObj = new Date(ano, mes - 1, dia);
        return dataObj.toLocaleDateString('pt-BR');
    }
    
    // Para outros formatos, usar Date normal
    const dataObj = new Date(data);
    // Garantir que não está em UTC
    if (!isNaN(dataObj.getTime())) {
        return dataObj.toLocaleDateString('pt-BR');
    }
    
    return data;
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
    
    // Evento para filtro de status
    const filtroStatus = document.getElementById('filtro-status');
    if (filtroStatus) {
        filtroStatus.addEventListener('change', function() {
            aplicarFiltros();
        });
    }
    
    // Evento para filtro de pastoral
    const filtroPastoral = document.getElementById('filtro-pastoral');
    if (filtroPastoral) {
        filtroPastoral.addEventListener('change', function() {
            aplicarFiltros();
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
    // Criar modal para escolher formato
    // Usar HTML string com isHtmlContent: true (código interno, confiável)
    const modalHTML = `
        <div class="export-options" style="text-align: center;">
            <h5 style="margin-bottom: 1.5rem; font-weight: 600;">Escolha o formato de exportação:</h5>
            <div class="export-buttons" style="display: flex; gap: 1rem; margin: 1.5rem 0;">
                <button type="button" class="btn btn-success" id="btn-export-excel" style="flex: 1; padding: 0.75rem 1rem; font-size: 1rem; min-height: 50px;">
                    <i class="fas fa-file-excel"></i> Excel (XLSX)
                </button>
                <button type="button" class="btn btn-danger" id="btn-export-pdf" style="flex: 1; padding: 0.75rem 1rem; font-size: 1rem; min-height: 50px;">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
            </div>
            <p style="margin-top: 1.5rem; font-size: 0.9rem; color: #6c757d;">
                Os filtros atuais serão aplicados na exportação.
            </p>
        </div>
    `;
    
    abrirModal('Exportar Membros', modalHTML, [
        {
            texto: 'Cancelar',
            classe: 'btn-secondary',
            onclick: function() {
                fecharModal();
            }
        }
    ], { isHtmlContent: true });
    
    // Adicionar event listeners após o modal ser criado
    setTimeout(() => {
        const btnExcel = document.getElementById('btn-export-excel');
        const btnPDF = document.getElementById('btn-export-pdf');
        
        if (btnExcel) {
            btnExcel.addEventListener('click', function(e) {
                e.preventDefault();
                fecharModal();
                exportarMembrosFormato('xlsx');
            });
        }
        
        if (btnPDF) {
            btnPDF.addEventListener('click', function(e) {
                e.preventDefault();
                fecharModal();
                exportarMembrosFormato('pdf');
            });
        }
    }, 100);
}

/**
 * Exporta membros no formato especificado
 */
function exportarMembrosFormato(formato) {
    // Limpar filtros vazios
    const filtrosLimpos = {};
    if (AppState.filtros.busca && AppState.filtros.busca.trim()) {
        filtrosLimpos.busca = AppState.filtros.busca.trim();
    }
    if (AppState.filtros.status && AppState.filtros.status.trim()) {
        filtrosLimpos.status = AppState.filtros.status.trim();
    }
    if (AppState.filtros.pastoral && AppState.filtros.pastoral.trim()) {
        filtrosLimpos.pastoral = AppState.filtros.pastoral.trim();
    }
    
    const params = new URLSearchParams({
        formato: formato,
        ...filtrosLimpos
    });
    
    // Mostrar notificação
    mostrarNotificacao(`Exportando membros em formato ${formato.toUpperCase()}...`, 'info');
    
    // Pequeno delay para garantir que o modal foi fechado
    setTimeout(() => {
        // Abrir download em nova aba
        window.open(`${CONFIG.apiBaseUrl}membros/exportar?${params}`, '_blank');
    }, 100);
}

// =====================================================
// EXPORTAÇÃO DE FUNÇÕES GLOBAIS
// =====================================================

// Exportar funções de ação dos membros para uso global
window.visualizarMembro = visualizarMembro;
window.exportarMembros = exportarMembros;
window.exportarMembrosFormato = exportarMembrosFormato;
window.editarMembro = editarMembro;
window.excluirMembro = excluirMembro;
window.carregarMembros = carregarMembros;

