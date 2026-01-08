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
    // Propriedades básicas (mantidas para compatibilidade)
    isAdmin: false,
    canModify: false,
    user: null,
    userRole: null,
    initialized: false,
    
    // Permissões granulares
    permissions: {
        membros: {
            view: false,
            create: false,
            edit: false,
            delete: false,
            export: false,
            import: false
        },
        pastorais: {
            view: false,
            create: false,
            edit: false,
            delete: false,
            manage_membros: false,
            manage_eventos: false,
            manage_escalas: false
        },
        eventos: {
            view: false,
            create: false,
            edit: false,
            delete: false
        },
        relatorios: {
            view: false
        }
    },
    
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
                this.resetPermissions();
                this.initialized = true;
                return;
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                console.error('[Permissions] Resposta não é JSON:', contentType);
                this.resetPermissions();
                this.initialized = true;
                return;
            }
            
            const data = await response.json();
            console.log('[Permissions] Data:', data);
            
            if (data.success) {
                // Propriedades básicas
                this.isAdmin = data.data.is_admin || false;
                this.canModify = data.data.can_modify || false;
                this.user = data.data.user || null;
                this.userRole = data.data.user_role || null;
                
                // Permissões granulares
                if (data.data.membros) {
                    this.permissions.membros = { ...data.data.membros };
                }
                if (data.data.pastorais) {
                    this.permissions.pastorais = { ...data.data.pastorais };
                }
                if (data.data.eventos) {
                    this.permissions.eventos = { ...data.data.eventos };
                }
                if (data.data.relatorios) {
                    this.permissions.relatorios = { ...data.data.relatorios };
                }
                
                this.initialized = true;
                
                console.log('[Permissions] Inicializado - Role:', this.userRole);
                console.log('[Permissions] Permissões:', this.permissions);
                
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
                            canModify: this.canModify,
                            userRole: this.userRole,
                            permissions: this.permissions
                        }
                    }));
                }
            } else {
                console.warn('[Permissions] Resposta sem sucesso:', data.message);
                this.resetPermissions();
                this.initialized = true;
            }
        } catch (error) {
            console.error('[Permissions] Erro ao verificar permissões:', error);
            this.resetPermissions();
            this.initialized = true;
        }
    },
    
    /**
     * Reseta todas as permissões para false
     */
    resetPermissions() {
        this.isAdmin = false;
        this.canModify = false;
        this.user = null;
        this.userRole = null;
        
        this.permissions.membros = {
            view: false,
            create: false,
            edit: false,
            delete: false,
            export: false,
            import: false
        };
        this.permissions.pastorais = {
            view: false,
            create: false,
            edit: false,
            delete: false,
            manage_membros: false,
            manage_eventos: false,
            manage_escalas: false
        };
        this.permissions.eventos = {
            view: false,
            create: false,
            edit: false,
            delete: false
        };
        this.permissions.relatorios = {
            view: false
        };
    },
    
    /**
     * Aplica controles de permissão na interface
     * Usa permissões granulares para controlar visibilidade dos elementos
     */
    applyPermissionControls() {
        // ===== MEMBROS =====
        
        // Botão de criar membro
        const btnNovoMembro = document.getElementById('btn-novo-membro');
        if (btnNovoMembro) {
            btnNovoMembro.style.display = this.permissions.membros.create ? 'inline-block' : 'none';
        }
        
        // Botão de importar membros
        const btnImportarMembros = document.getElementById('btn-importar-membros');
        if (btnImportarMembros) {
            btnImportarMembros.style.display = this.permissions.membros.import ? 'inline-block' : 'none';
        }
        
        // Botão de exportar membros - sempre visível se pode visualizar
        const btnExportarMembros = document.getElementById('btn-exportar-membros');
        if (btnExportarMembros) {
            btnExportarMembros.style.display = this.permissions.membros.export ? 'inline-block' : 'none';
        }
        
        // Botões de editar membros na tabela
        const btnEditarMembros = document.querySelectorAll('.btn-editar-membro');
        btnEditarMembros.forEach(btn => {
            btn.style.display = this.permissions.membros.edit ? 'inline-block' : 'none';
        });
        
        // Botões de excluir membros na tabela
        const btnExcluirMembros = document.querySelectorAll('.btn-excluir-membro');
        btnExcluirMembros.forEach(btn => {
            btn.style.display = this.permissions.membros.delete ? 'inline-block' : 'none';
        });
        
        // ===== PASTORAIS =====
        
        // Botão de criar pastoral
        const btnNovaPastoral = document.getElementById('btn-nova-pastoral');
        if (btnNovaPastoral) {
            btnNovaPastoral.style.display = this.permissions.pastorais.create ? 'inline-block' : 'none';
        }
        
        // Botões de editar pastorais (NÃO devem aparecer para 'membros')
        const btnEditarPastorais = document.querySelectorAll('.btn-editar-pastoral, button[onclick*="editarPastoral"]');
        btnEditarPastorais.forEach(btn => {
            btn.style.display = this.permissions.pastorais.edit ? 'inline-block' : 'none';
        });
        
        // Aba de editar pastoral (NÃO deve aparecer para 'membros')
        const btnEditarPastoralTab = document.querySelectorAll('.btn-editar-pastoral-tab');
        btnEditarPastoralTab.forEach(btn => {
            btn.style.display = this.permissions.pastorais.edit ? 'inline-block' : 'none';
        });
        
        // Botões de excluir pastorais (NÃO devem aparecer para 'membros')
        const btnExcluirPastorais = document.querySelectorAll('.btn-excluir-pastoral, button[onclick*="excluirPastoral"]');
        btnExcluirPastorais.forEach(btn => {
            btn.style.display = this.permissions.pastorais.delete ? 'inline-block' : 'none';
        });
        
        // Botões de remover membro da pastoral (DEVE aparecer para 'membros')
        const btnRemoverMembroPastoral = document.querySelectorAll('.btn-remover-membro-pastoral');
        btnRemoverMembroPastoral.forEach(btn => {
            btn.style.display = this.permissions.pastorais.manage_membros ? 'inline-block' : 'none';
        });
        
        // Botões de adicionar membro à pastoral
        const btnAdicionarMembroPastoral = document.querySelectorAll('.btn-adicionar-membro-pastoral');
        btnAdicionarMembroPastoral.forEach(btn => {
            btn.style.display = this.permissions.pastorais.manage_membros ? 'inline-block' : 'none';
        });
        
        // ===== EVENTOS DE PASTORAIS =====
        
        // Botões de criar evento de pastoral (DEVE aparecer para 'membros')
        const btnNovoEventoPastoral = document.querySelectorAll('.btn-novo-evento-pastoral, button[onclick*="abrirModalNovoEvento"]');
        btnNovoEventoPastoral.forEach(btn => {
            btn.style.display = this.permissions.pastorais.manage_eventos ? 'inline-block' : 'none';
        });
        
        // Botões de editar evento de pastoral (DEVE aparecer para 'membros')
        const btnEditarEvento = document.querySelectorAll('.btn-editar-evento');
        btnEditarEvento.forEach(btn => {
            btn.style.display = this.permissions.pastorais.manage_eventos ? 'inline-block' : 'none';
        });
        
        // Botões de excluir evento de pastoral (DEVE aparecer para 'membros')
        const btnExcluirEvento = document.querySelectorAll('.btn-excluir-evento');
        btnExcluirEvento.forEach(btn => {
            btn.style.display = this.permissions.pastorais.manage_eventos ? 'inline-block' : 'none';
        });
        
        // ===== ESCALAS DE PASTORAIS =====
        
        // Botões relacionados a escalas (DEVE aparecer para 'membros')
        const btnEscalas = document.querySelectorAll('.btn-nova-escala, .btn-editar-escala, .btn-excluir-escala');
        btnEscalas.forEach(btn => {
            btn.style.display = this.permissions.pastorais.manage_escalas ? 'inline-block' : 'none';
        });
        
        // ===== EVENTOS (ABA PRINCIPAL) =====
        
        // Botão de criar evento na aba principal (NÃO deve aparecer para 'membros')
        const btnNovoEvento = document.getElementById('btn-novo-evento');
        if (btnNovoEvento) {
            btnNovoEvento.style.display = this.permissions.eventos.create ? 'inline-block' : 'none';
        }
        
        // Botões de editar evento na aba principal (NÃO deve aparecer para 'membros')
        const btnEditarEventoGeral = document.querySelectorAll('.btn-editar-evento-geral');
        btnEditarEventoGeral.forEach(btn => {
            btn.style.display = this.permissions.eventos.edit ? 'inline-block' : 'none';
        });
        
        // Botões de excluir evento na aba principal (NÃO deve aparecer para 'membros')
        const btnExcluirEventoGeral = document.querySelectorAll('.btn-excluir-evento-geral');
        btnExcluirEventoGeral.forEach(btn => {
            btn.style.display = this.permissions.eventos.delete ? 'inline-block' : 'none';
        });
    },
    
    /**
     * Verifica se o usuário pode modificar dados (método legado)
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
     * Obtém role do usuário
     */
    getUserRole() {
        return this.userRole;
    },
    
    /**
     * Mostra mensagem de erro de permissão
     */
    showPermissionError(action = 'realizar esta operação') {
        const roleMsg = this.userRole === 'membros' 
            ? 'Apenas o administrador pode' 
            : 'Você não tem permissão para';
            
        if (window.mostrarNotificacao) {
            window.mostrarNotificacao(
                `Acesso negado. ${roleMsg} ${action}.`,
                'error'
            );
        } else {
            alert(`Acesso negado. ${roleMsg} ${action}.`);
        }
    },
    
    /**
     * Verifica permissão antes de executar uma ação (método legado)
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
    },
    
    // =====================================================
    // MÉTODOS AUXILIARES PARA VERIFICAÇÕES ESPECÍFICAS
    // =====================================================
    
    /**
     * Verifica se pode criar membros
     */
    canCreateMembro() {
        return this.permissions.membros.create;
    },
    
    /**
     * Verifica se pode editar membros
     */
    canEditMembro() {
        return this.permissions.membros.edit;
    },
    
    /**
     * Verifica se pode excluir membros
     */
    canDeleteMembro() {
        return this.permissions.membros.delete;
    },
    
    /**
     * Verifica se pode exportar membros
     */
    canExportMembro() {
        return this.permissions.membros.export;
    },
    
    /**
     * Verifica se pode criar pastoral
     */
    canCreatePastoral() {
        return this.permissions.pastorais.create;
    },
    
    /**
     * Verifica se pode editar pastoral
     */
    canEditPastoral() {
        return this.permissions.pastorais.edit;
    },
    
    /**
     * Verifica se pode excluir pastoral
     */
    canDeletePastoral() {
        return this.permissions.pastorais.delete;
    },
    
    /**
     * Verifica se pode gerenciar membros de pastoral
     */
    canManagePastoralMembros() {
        return this.permissions.pastorais.manage_membros;
    },
    
    /**
     * Verifica se pode gerenciar eventos de pastoral
     */
    canManagePastoralEventos() {
        return this.permissions.pastorais.manage_eventos;
    },
    
    /**
     * Verifica se pode gerenciar escalas de pastoral
     */
    canManagePastoralEscalas() {
        return this.permissions.pastorais.manage_escalas;
    },
    
    /**
     * Verifica se pode criar eventos (aba principal)
     */
    canCreateEvento() {
        return this.permissions.eventos.create;
    },
    
    /**
     * Verifica se pode editar eventos (aba principal)
     */
    canEditEvento() {
        return this.permissions.eventos.edit;
    },
    
    /**
     * Verifica se pode excluir eventos (aba principal)
     */
    canDeleteEvento() {
        return this.permissions.eventos.delete;
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

