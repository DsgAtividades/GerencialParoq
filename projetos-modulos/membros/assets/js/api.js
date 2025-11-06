/**
 * API Client - Módulo de Membros
 * GerencialParoq
 */

// =====================================================
// CONFIGURAÇÕES DA API
// =====================================================

// Detectar automaticamente o caminho base
function detectBasePath() {
    const path = window.location.pathname;
    // Remover /index.php ou qualquer arquivo final
    const basePath = path.replace(/\/[^\/]*\.php$/, '').replace(/\/index\.html$/, '');
    return basePath + '/api/';
}

const APIConfig = {
    baseUrl: detectBasePath(),
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
        // Garantir que não há barras duplicadas
        const cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
        const url = `${this.baseUrl}${cleanEndpoint}`;
        
        const config = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            ...options
        };
        
        // Garantir que o Content-Type seja definido para métodos que enviam dados
        if (options.body && !config.headers['Content-Type']) {
            config.headers['Content-Type'] = 'application/json';
        }
        
        try {
            const response = await fetch(url, config);
            
            // Tentar ler o corpo da resposta mesmo em caso de erro
            const contentType = response.headers.get('content-type');
            let responseData;
            
            console.log(`[API] Status: ${response.status}, Content-Type: ${contentType}`);
            
            if (contentType && contentType.includes('application/json')) {
                responseData = await response.json();
                console.log('[API] Response JSON:', responseData);
            } else {
                responseData = await response.text();
                console.log('[API] Response Text:', responseData);
            }
            
            // Se a resposta não foi OK, verificar se tem mensagem de erro no JSON
            if (!response.ok) {
                console.log('[API] Resposta não OK. responseData tipo:', typeof responseData);
                console.log('[API] responseData.error:', responseData?.error);
                console.log('[API] responseData completo:', responseData);
                
                // Se for JSON e tiver mensagem de erro, usar ela
                if (typeof responseData === 'object' && responseData && responseData.error) {
                    console.log('[API] Criando erro com mensagem do backend:', responseData.error);
                    const error = new Error(responseData.error);
                    error.status = response.status;
                    error.statusText = response.statusText;
                    error.responseData = responseData;
                    throw error;
                } else {
                    // Caso contrário, usar mensagem genérica
                    console.log('[API] Criando erro genérico');
                    const error = new Error(`HTTP ${response.status}: ${response.statusText}`);
                    error.status = response.status;
                    error.statusText = response.statusText;
                    error.responseData = responseData;
                    throw error;
                }
            }
            
            return responseData;
        } catch (error) {
            console.error(`Erro na API ${endpoint}:`, error);
            console.log('[API] error.responseData:', error.responseData);
            // Se o erro já tem responseData, preservar
            if (!error.responseData && error.message) {
                error.responseData = { error: error.message };
            }
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
    async listar(params = {}) {
        return api.get('pastorais', params);
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
    /**
     * OTIMIZADO: Busca todas as estatísticas em uma única requisição
     */
    async agregado() {
        return api.get('dashboard/agregado');
    },
    
    // Endpoints individuais (mantidos para compatibilidade)
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
        // Desabilitar fallback temporariamente para debug
        throw error;
        // return await usarDadosMockados('membros');
    }
}

/**
 * Carrega pastorais com fallback
 */
async function carregarPastoraisAPI(forceRefresh = false) {
    try {
        const params = forceRefresh ? { _nocache: Date.now() } : {};
        return await PastoraisAPI.listar(params);
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

async function carregarAdesoesAPI() {
    try {
        // Retornar dados mockados temporariamente até criar endpoint específico
        const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'];
        const quantidades = [2, 5, 3, 7, 4, 6];
        
        return { 
            success: true, 
            data: {
                labels: meses,
                data: quantidades
            }
        };
    } catch (error) {
        console.error('Erro ao carregar adesões:', error);
        return { success: true, data: { labels: [], data: [] } };
    }
}

/**
 * Exclui membro
 */
async function excluirMembroAPI(id) {
    try {
        return await MembrosAPI.excluir(id);
    } catch (error) {
        console.error('Erro ao excluir membro:', error);
        throw error;
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
window.carregarAdesoesAPI = carregarAdesoesAPI;

// Exportar utilitários
window.verificarDisponibilidadeAPI = verificarDisponibilidadeAPI;
window.mostrarStatusConexao = mostrarStatusConexao;
