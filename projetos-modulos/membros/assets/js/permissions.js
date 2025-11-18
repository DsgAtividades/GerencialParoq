/**
 * Sistema de Gerenciamento de Permissões - Frontend
 * Verifica permissões do usuário e controla visibilidade de botões
 */

// Detectar automaticamente o caminho base da API
function getApiBaseUrl() {
    // Tentar usar CONFIG se disponível (sem causar erro se não existir)
    // Usar window para evitar erro de referência
    const config = (function() {
        try {
            return typeof window !== 'undefined' && window.CONFIG ? window.CONFIG : null;
        } catch (e) {
            return null;
        }
    })();
    
    if (config && config.apiBaseUrl) {
        return config.apiBaseUrl;
    }
    
    // Fallback: detectar caminho automaticamente
    const path = window.location.pathname;
    // Remover nome do arquivo atual (ex: pastoral_detalhes.php, index.php)
    let basePath = path.replace(/\/[^\/]*\.php$/, '').replace(/\/index\.html$/, '');
    // Garantir que termina com /api/
    if (!basePath.endsWith('/api/')) {
        basePath = basePath + '/api/';
    }
    return basePath;
}

const PermissionsManager = {
    isAdmin: false,
    canModify: false,
    user: null,
    initialized: false,
    
    /**
     * Inicializa o sistema de permissões
     */
    async init() {
        if (this.initialized) {
            return;
        }
        
        try {
            const apiBaseUrl = getApiBaseUrl();
            console.log('[Permissions] API Base URL:', apiBaseUrl);
            
            const url = `${apiBaseUrl}check-permissions`;
            console.log('[Permissions] Fetching:', url);
            
            const response = await fetch(url);
            console.log('[Permissions] Response status:', response.status);
            console.log('[Permissions] Response content-type:', response.headers.get('content-type'));
            
            if (!response.ok) {
                console.error('[Permissions] HTTP error:', response.status);
                // Se não conseguir verificar permissões, assumir sem permissões
                this.isAdmin = false;
                this.canModify = false;
                this.initialized = true;
                return;
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                console.error('[Permissions] Resposta não é JSON:', contentType);
                this.isAdmin = false;
                this.canModify = false;
                this.initialized = true;
                return;
            }
            
            const data = await response.json();
            console.log('[Permissions] Data:', data);
            
            if (data.success) {
                this.isAdmin = data.data.is_admin || false;
                this.canModify = data.data.can_modify || false;
                this.user = data.data.user || null;
                this.initialized = true;
                
                console.log('[Permissions] Inicializado - isAdmin:', this.isAdmin, 'canModify:', this.canModify);
                
                // Aplicar controles de permissão na interface
                this.applyPermissionControls();
                
                // Reaplicar controles após um pequeno delay para garantir que elementos dinâmicos sejam processados
                setTimeout(() => {
                    this.applyPermissionControls();
                }, 500);
                
                // Disparar evento customizado para notificar que permissões foram inicializadas
                if (typeof window.dispatchEvent !== 'undefined') {
                    window.dispatchEvent(new CustomEvent('permissionsInitialized', {
                        detail: {
                            isAdmin: this.isAdmin,
                            canModify: this.canModify
                        }
                    }));
                }
            } else {
                console.warn('[Permissions] Resposta sem sucesso:', data.message);
                this.isAdmin = false;
                this.canModify = false;
                this.initialized = true;
            }
        } catch (error) {
            console.error('[Permissions] Erro ao verificar permissões:', error);
            this.isAdmin = false;
            this.canModify = false;
            this.initialized = true;
        }
    },
    
    /**
     * Aplica controles de permissão na interface
     */
    applyPermissionControls() {
        // Mostrar/esconder botões de criar membro
        const btnNovoMembro = document.getElementById('btn-novo-membro');
        if (btnNovoMembro) {
            btnNovoMembro.style.display = this.canModify ? 'inline-block' : 'none';
        }
        
        // Mostrar/esconder botão de importar membros
        const btnImportarMembros = document.getElementById('btn-importar-membros');
        if (btnImportarMembros) {
            btnImportarMembros.style.display = this.canModify ? 'inline-block' : 'none';
        }
        
        // Mostrar/esconder botões de criar pastoral
        const btnNovaPastoral = document.getElementById('btn-nova-pastoral');
        if (btnNovaPastoral) {
            btnNovaPastoral.style.display = this.canModify ? 'inline-block' : 'none';
        }
        
        // Esconder botões de editar/excluir na tabela de membros (se já renderizados)
        const btnEditarMembros = document.querySelectorAll('.btn-editar-membro');
        const btnExcluirMembros = document.querySelectorAll('.btn-excluir-membro');
        
        btnEditarMembros.forEach(btn => {
            btn.style.display = this.canModify ? 'inline-block' : 'none';
        });
        
        btnExcluirMembros.forEach(btn => {
            btn.style.display = this.canModify ? 'inline-block' : 'none';
        });
        
        // Mostrar/esconder botões de remover membro da pastoral (na página pastoral_detalhes)
        const btnRemoverMembroPastoral = document.querySelectorAll('.btn-remover-membro-pastoral');
        btnRemoverMembroPastoral.forEach(btn => {
            btn.style.display = this.canModify ? 'inline-block' : 'none';
        });
        
        // Mostrar/esconder botões de editar/excluir evento (na página pastoral_detalhes)
        const btnEditarEvento = document.querySelectorAll('.btn-editar-evento');
        btnEditarEvento.forEach(btn => {
            btn.style.display = this.canModify ? 'inline-block' : 'none';
        });
        
        const btnExcluirEvento = document.querySelectorAll('.btn-excluir-evento');
        btnExcluirEvento.forEach(btn => {
            btn.style.display = this.canModify ? 'inline-block' : 'none';
        });
        
        // Esconder botões de editar/excluir na tabela de pastorais
        const btnEditarPastorais = document.querySelectorAll('.btn-editar-pastoral, button[onclick*="editarPastoral"]');
        const btnExcluirPastorais = document.querySelectorAll('.btn-excluir-pastoral, button[onclick*="excluirPastoral"]');
        
        btnEditarPastorais.forEach(btn => {
            btn.style.display = this.canModify ? 'inline-block' : 'none';
        });
        
        btnExcluirPastorais.forEach(btn => {
            btn.style.display = this.canModify ? 'inline-block' : 'none';
        });
    },
    
    /**
     * Verifica se o usuário pode modificar dados
     */
    canModifyData() {
        return this.canModify;
    },
    
    /**
     * Verifica se o usuário é administrador
     */
    isUserAdmin() {
        return this.isAdmin;
    },
    
    /**
     * Mostra mensagem de erro de permissão
     */
    showPermissionError(action = 'realizar esta operação') {
        if (window.mostrarNotificacao) {
            window.mostrarNotificacao(
                `Acesso negado. Apenas o administrador (Madmin) pode ${action}.`,
                'error'
            );
        } else {
            alert(`Acesso negado. Apenas o administrador (Madmin) pode ${action}.`);
        }
    },
    
    /**
     * Verifica permissão antes de executar uma ação
     */
    requirePermission(action, callback) {
        if (!this.canModify) {
            this.showPermissionError(action);
            return false;
        }
        
        if (typeof callback === 'function') {
            callback();
        }
        
        return true;
    }
};

// Exportar para uso global
window.PermissionsManager = PermissionsManager;

// Inicializar quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        PermissionsManager.init();
    });
} else {
    PermissionsManager.init();
}

