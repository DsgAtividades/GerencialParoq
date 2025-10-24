/**
 * API Client - Módulo de Membros
 * GerencialParoq
 */

// =====================================================
// CONFIGURAÇÕES DA API
// =====================================================

const APIConfig = {
    baseUrl: '/PROJETOS/GerencialParoq/projetos-modulos/membros/api/',
    timeout: 30000,
    retryAttempts: 3,
    retryDelay: 1000
};

// =====================================================
// CLASSE PRINCIPAL DA API
// =====================================================

class APIClient {
    constructor() {
        this.baseUrl = APIConfig.baseUrl;
        this.timeout = APIConfig.timeout;
    }
    
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            ...options
        };
        
        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            }
            
            return await response.text();
        } catch (error) {
            console.error(`Erro na API ${endpoint}:`, error);
            throw error;
        }
    }
    
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        
        return this.request(url, { method: 'GET' });
    }
    
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
    
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }
    
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
}

// =====================================================
// INSTÂNCIA GLOBAL DA API
// =====================================================

const api = new APIClient();

// =====================================================
// ENDPOINTS ESPECÍFICOS
// =====================================================

/**
 * API de Membros
 */
const MembrosAPI = {
    async listar(params = {}) {
        return api.get('membros', params);
    },
    
    async buscar(id) {
        return api.get(`membros/${id}`);
    },
    
    async criar(dados) {
        return api.post('membros', dados);
    },
    
    async atualizar(id, dados) {
        return api.put(`membros/${id}`, dados);
    },
    
    async excluir(id) {
        return api.delete(`membros/${id}`);
    },
    
    async buscarPorPastoral(pastoralId) {
        return api.get('membros', { pastoral: pastoralId });
    }
};

/**
 * API de Pastorais
 */
const PastoraisAPI = {
    async listar() {
        return api.get('pastorais');
    },
    
    async buscar(id) {
        return api.get(`pastorais/${id}`);
    },
    
    async criar(dados) {
        return api.post('pastorais', dados);
    },
    
    async atualizar(id, dados) {
        return api.put(`pastorais/${id}`, dados);
    },
    
    async excluir(id) {
        return api.delete(`pastorais/${id}`);
    }
};

/**
 * API de Eventos
 */
const EventosAPI = {
    async listar(params = {}) {
        return api.get('eventos', params);
    },
    
    async buscar(id) {
        return api.get(`eventos/${id}`);
    },
    
    async criar(dados) {
        return api.post('eventos', dados);
    },
    
    async atualizar(id, dados) {
        return api.put(`eventos/${id}`, dados);
    },
    
    async excluir(id) {
        return api.delete(`eventos/${id}`);
    }
};

/**
 * API de Dashboard
 */
const DashboardAPI = {
    async estatisticasGerais() {
        return api.get('dashboard/geral');
    },
    
    async membrosPorStatus() {
        return api.get('dashboard/membros-status');
    },
    
    async membrosPorPastoral() {
        return api.get('dashboard/membros-pastoral');
    },
    
    async presencaMensal() {
        return api.get('dashboard/presenca-mensal');
    },
    
    async atividadesRecentes() {
        return api.get('dashboard/atividades-recentes');
    }
};

/**
 * API de Escalas
 */
const EscalasAPI = {
    async listar(params = {}) {
        return api.get('escalas', params);
    },
    
    async buscar(id) {
        return api.get(`escalas/${id}`);
    },
    
    async criar(dados) {
        return api.post('escalas', dados);
    },
    
    async atualizar(id, dados) {
        return api.put(`escalas/${id}`, dados);
    },
    
    async excluir(id) {
        return api.delete(`escalas/${id}`);
    }
};

// =====================================================
// FUNÇÕES DE FALLBACK (DADOS MOCKADOS)
// =====================================================

/**
 * Dados mockados para quando a API não estiver disponível
 */
const MockData = {
    membros: [
        {
            id: '1',
            nome_completo: 'João Silva',
            apelido: 'João',
            email: 'joao@email.com',
            telefone: '(11) 99999-9999',
            status: 'ativo',
            situacao_pastoral: 'membro',
            created_at: '2024-01-15'
        },
        {
            id: '2',
            nome_completo: 'Maria Santos',
            apelido: 'Maria',
            email: 'maria@email.com',
            telefone: '(11) 88888-8888',
            status: 'ativo',
            situacao_pastoral: 'membro',
            created_at: '2024-01-16'
        }
    ],
    
    pastorais: [
        { id: '1', nome: 'Catequese', descricao: 'Formação catequética' },
        { id: '2', nome: 'Liturgia', descricao: 'Serviços litúrgicos' },
        { id: '3', nome: 'Pastoral Social', descricao: 'Ação social' }
    ],
    
    eventos: [
        { id: '1', titulo: 'Missa Dominical', data: '2024-01-21', local: 'Igreja Matriz' },
        { id: '2', titulo: 'Reunião de Catequese', data: '2024-01-22', local: 'Salão Paroquial' }
    ],
    
    dashboard: {
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
            labels: ['Catequese', 'Liturgia', 'Pastoral Social'],
            data: [45, 32, 28]
        }
    }
};

/**
 * Função para usar dados mockados quando a API falha
 */
async function usarDadosMockados(endpoint) {
    console.warn(`Usando dados mockados para: ${endpoint}`);
    
    // Simular delay de rede
    await new Promise(resolve => setTimeout(resolve, 500));
    
    switch (endpoint) {
        case 'membros':
            return { data: MockData.membros, total: MockData.membros.length };
        case 'pastorais':
            return { data: MockData.pastorais };
        case 'eventos':
            return { data: MockData.eventos };
        case 'dashboard/geral':
            return MockData.dashboard.estatisticas;
        case 'dashboard/membros-status':
            return MockData.dashboard.membrosPorStatus;
        case 'dashboard/membros-pastoral':
            return MockData.dashboard.membrosPorPastoral;
        default:
            return { data: [], total: 0 };
    }
}

// =====================================================
// FUNÇÕES GLOBAIS DE API
// =====================================================

/**
 * Carrega membros com fallback
 */
async function carregarMembrosAPI(params = {}) {
    try {
        return await MembrosAPI.listar(params);
    } catch (error) {
        console.error('Erro ao carregar membros:', error);
        return await usarDadosMockados('membros');
    }
}

/**
 * Carrega pastorais com fallback
 */
async function carregarPastoraisAPI() {
    try {
        return await PastoraisAPI.listar();
    } catch (error) {
        console.error('Erro ao carregar pastorais:', error);
        return await usarDadosMockados('pastorais');
    }
}

/**
 * Carrega eventos com fallback
 */
async function carregarEventosAPI(params = {}) {
    try {
        return await EventosAPI.listar(params);
    } catch (error) {
        console.error('Erro ao carregar eventos:', error);
        return await usarDadosMockados('eventos');
    }
}

/**
 * Carrega dados do dashboard com fallback
 */
async function carregarDashboardAPI() {
    try {
        return await DashboardAPI.estatisticasGerais();
    } catch (error) {
        console.error('Erro ao carregar dashboard:', error);
        return await usarDadosMockados('dashboard/geral');
    }
}

// =====================================================
// UTILITÁRIOS
// =====================================================

/**
 * Verifica se a API está disponível
 */
async function verificarDisponibilidadeAPI() {
    try {
        await api.get('health');
        return true;
    } catch (error) {
        return false;
    }
}

/**
 * Mostra status da conexão
 */
function mostrarStatusConexao() {
    verificarDisponibilidadeAPI().then(disponivel => {
        const status = document.getElementById('api-status');
        if (status) {
            status.innerHTML = disponivel ? 
                '<i class="fas fa-check-circle text-success"></i> API Online' :
                '<i class="fas fa-exclamation-triangle text-warning"></i> API Offline (Modo Demo)';
        }
    });
}

// =====================================================
// EXPORTAR FUNÇÕES
// =====================================================

// Exportar APIs para uso global
window.MembrosAPI = MembrosAPI;
window.PastoraisAPI = PastoraisAPI;
window.EventosAPI = EventosAPI;
window.DashboardAPI = DashboardAPI;
window.EscalasAPI = EscalasAPI;

// Exportar funções de fallback
window.carregarMembrosAPI = carregarMembrosAPI;
window.carregarPastoraisAPI = carregarPastoraisAPI;
window.carregarEventosAPI = carregarEventosAPI;
window.carregarDashboardAPI = carregarDashboardAPI;

// Exportar utilitários
window.verificarDisponibilidadeAPI = verificarDisponibilidadeAPI;
window.mostrarStatusConexao = mostrarStatusConexao;
