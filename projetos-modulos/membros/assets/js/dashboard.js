/**
 * Dashboard - Módulo de Membros
 * GerencialParoq
 */

// =====================================================
// CONFIGURAÇÕES DO DASHBOARD
// =====================================================

const DashboardConfig = {
    charts: {
        membrosPorStatus: 'chart-membros-status',
        membrosPorPastoral: 'chart-membros-pastoral',
        presencaMensal: 'chart-presenca-mensal',
        atividadesRecentes: 'chart-atividades-recentes'
    },
    refreshInterval: 30000 // 30 segundos
};

// =====================================================
// INICIALIZAÇÃO DO DASHBOARD
// =====================================================

function inicializarDashboard() {
    console.log('Inicializando dashboard...');
    carregarDadosDashboard();
    configurarGraficos();
    configurarAtualizacaoAutomatica();
}

/**
 * Carrega dados do dashboard
 */
async function carregarDadosDashboard() {
    try {
        // Carregar estatísticas gerais
        await carregarEstatisticasGerais();
        
        // Carregar gráficos
        await carregarGraficoMembrosPorStatus();
        await carregarGraficoMembrosPorPastoral();
        await carregarGraficoPresencaMensal();
        
        // Carregar atividades recentes
        await carregarAtividadesRecentes();
        
        console.log('Dashboard carregado com sucesso');
    } catch (error) {
        console.error('Erro ao carregar dashboard:', error);
        mostrarErroDashboard('Erro ao carregar dados do dashboard');
    }
}

// =====================================================
// CARREGAMENTO DE DADOS
// =====================================================

/**
 * Carrega estatísticas gerais
 */
async function carregarEstatisticasGerais() {
    try {
        const response = await fetch(`${CONFIG.apiBaseUrl}dashboard/geral`);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const dados = await response.json();
        atualizarEstatisticasGerais(dados);
    } catch (error) {
        console.error('Erro ao carregar estatísticas gerais:', error);
        // Usar dados mockados em caso de erro
        atualizarEstatisticasGerais(getDadosMockados().estatisticas);
    }
}

/**
 * Atualiza estatísticas gerais na interface
 */
function atualizarEstatisticasGerais(dados) {
    const elementos = {
        totalMembros: document.getElementById('total-membros'),
        membrosAtivos: document.getElementById('membros-ativos'),
        pastoraisAtivas: document.getElementById('pastorais-ativas'),
        eventosHoje: document.getElementById('eventos-hoje')
    };
    
    if (elementos.totalMembros) elementos.totalMembros.textContent = dados.totalMembros || 0;
    if (elementos.membrosAtivos) elementos.membrosAtivos.textContent = dados.membrosAtivos || 0;
    if (elementos.pastoraisAtivas) elementos.pastoraisAtivas.textContent = dados.pastoraisAtivas || 0;
    if (elementos.eventosHoje) elementos.eventosHoje.textContent = dados.eventosHoje || 0;
}

/**
 * Carrega gráfico de membros por status
 */
async function carregarGraficoMembrosPorStatus() {
    try {
        const response = await fetch(`${CONFIG.apiBaseUrl}dashboard/membros-status`);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const dados = await response.json();
        criarGraficoMembrosPorStatus(dados);
    } catch (error) {
        console.error('Erro ao carregar gráfico de status:', error);
        // Usar dados mockados
        criarGraficoMembrosPorStatus(getDadosMockados().membrosPorStatus);
    }
}

/**
 * Carrega gráfico de membros por pastoral
 */
async function carregarGraficoMembrosPorPastoral() {
    try {
        const response = await fetch(`${CONFIG.apiBaseUrl}dashboard/membros-pastoral`);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const dados = await response.json();
        criarGraficoMembrosPorPastoral(dados);
    } catch (error) {
        console.error('Erro ao carregar gráfico de pastoral:', error);
        // Usar dados mockados
        criarGraficoMembrosPorPastoral(getDadosMockados().membrosPorPastoral);
    }
}

/**
 * Carrega gráfico de presença mensal
 */
async function carregarGraficoPresencaMensal() {
    try {
        const response = await fetch(`${CONFIG.apiBaseUrl}dashboard/presenca-mensal`);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const dados = await response.json();
        criarGraficoPresencaMensal(dados);
    } catch (error) {
        console.error('Erro ao carregar gráfico de presença:', error);
        // Usar dados mockados
        criarGraficoPresencaMensal(getDadosMockados().presencaMensal);
    }
}

/**
 * Carrega atividades recentes
 */
async function carregarAtividadesRecentes() {
    try {
        const response = await fetch(`${CONFIG.apiBaseUrl}dashboard/atividades-recentes`);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const dados = await response.json();
        atualizarAtividadesRecentes(dados);
    } catch (error) {
        console.error('Erro ao carregar atividades recentes:', error);
        // Usar dados mockados
        atualizarAtividadesRecentes(getDadosMockados().atividadesRecentes);
    }
}

// =====================================================
// CRIAÇÃO DE GRÁFICOS
// =====================================================

/**
 * Cria gráfico de membros por status
 */
function criarGraficoMembrosPorStatus(dados) {
    const ctx = document.getElementById(DashboardConfig.charts.membrosPorStatus);
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: dados.labels || ['Ativos', 'Inativos', 'Suspensos'],
            datasets: [{
                data: dados.data || [150, 25, 5],
                backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
                borderWidth: 2
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
}

/**
 * Cria gráfico de membros por pastoral
 */
function criarGraficoMembrosPorPastoral(dados) {
    const ctx = document.getElementById(DashboardConfig.charts.membrosPorPastoral);
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dados.labels || ['Catequese', 'Liturgia', 'Pastoral Social', 'Jovens'],
            datasets: [{
                label: 'Membros',
                data: dados.data || [45, 32, 28, 25],
                backgroundColor: '#007bff',
                borderColor: '#0056b3',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/**
 * Cria gráfico de presença mensal
 */
function criarGraficoPresencaMensal(dados) {
    const ctx = document.getElementById(DashboardConfig.charts.presencaMensal);
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dados.labels || ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Presença (%)',
                data: dados.data || [85, 78, 92, 88, 90, 87],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

// =====================================================
// ATUALIZAÇÃO DE INTERFACE
// =====================================================

/**
 * Atualiza atividades recentes
 */
function atualizarAtividadesRecentes(dados) {
    const container = document.getElementById('atividades-recentes');
    if (!container) return;
    
    const atividades = dados.atividades || [];
    
    if (atividades.length === 0) {
        container.innerHTML = '<p class="text-muted">Nenhuma atividade recente</p>';
        return;
    }
    
    const html = atividades.map(atividade => `
        <div class="activity-item">
            <div class="activity-icon">
                <i class="fas ${atividade.icone}"></i>
            </div>
            <div class="activity-content">
                <h6>${atividade.titulo}</h6>
                <p class="text-muted">${atividade.descricao}</p>
                <small class="text-muted">${atividade.data}</small>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

/**
 * Mostra erro no dashboard
 */
function mostrarErroDashboard(mensagem) {
    const container = document.getElementById('dashboard-content');
    if (!container) return;
    
    container.innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            ${mensagem}
        </div>
    `;
}

// =====================================================
// CONFIGURAÇÕES
// =====================================================

/**
 * Configura gráficos
 */
function configurarGraficos() {
    // Configurações globais do Chart.js
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
    Chart.defaults.font.size = 12;
}

/**
 * Configura atualização automática
 */
function configurarAtualizacaoAutomatica() {
    setInterval(() => {
        if (CONFIG.currentSection === 'dashboard') {
            carregarDadosDashboard();
        }
    }, DashboardConfig.refreshInterval);
}

// =====================================================
// DADOS MOCKADOS (FALLBACK)
// =====================================================

function getDadosMockados() {
    return {
        estatisticas: {
            totalMembros: 180,
            membrosAtivos: 150,
            pastoraisAtivas: 8,
            eventosHoje: 3
        },
        membrosPorStatus: {
            labels: ['Ativos', 'Inativos', 'Suspensos'],
            data: [150, 25, 5]
        },
        membrosPorPastoral: {
            labels: ['Catequese', 'Liturgia', 'Pastoral Social', 'Jovens', 'Família'],
            data: [45, 32, 28, 25, 20]
        },
        presencaMensal: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            data: [85, 78, 92, 88, 90, 87]
        },
        atividadesRecentes: {
            atividades: [
                {
                    icone: 'fa-user-plus',
                    titulo: 'Novo membro cadastrado',
                    descricao: 'João Silva foi cadastrado na pastoral de Catequese',
                    data: 'Há 2 horas'
                },
                {
                    icone: 'fa-calendar-check',
                    titulo: 'Evento confirmado',
                    descricao: 'Missa de Páscoa confirmada para domingo',
                    data: 'Há 4 horas'
                },
                {
                    icone: 'fa-users',
                    titulo: 'Reunião de pastoral',
                    descricao: 'Reunião da Pastoral Social realizada',
                    data: 'Ontem'
                }
            ]
        }
    };
}

// =====================================================
// EXPORTAR FUNÇÕES
// =====================================================

// Exportar funções para uso global
window.inicializarDashboard = inicializarDashboard;
window.carregarDadosDashboard = carregarDadosDashboard;
