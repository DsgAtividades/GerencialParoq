/**
 * Sistema de Modais - Módulo de Membros
 * GerencialParoq
 */

// =====================================================
// CONFIGURAÇÕES DE MODAIS
// =====================================================

const ModalConfig = {
    container: '#modal-container',
    overlay: 'modal-overlay',
    content: 'modal-content',
    header: 'modal-header',
    body: 'modal-body',
    footer: 'modal-footer'
};

// =====================================================
// FUNÇÕES PRINCIPAIS DE MODAL
// =====================================================

/**
 * Abre um modal genérico
 */
function abrirModal(titulo, conteudo, botoes = [], opcoes = {}) {
    const modalId = opcoes.id || 'modal-' + Date.now();
    const tamanho = opcoes.tamanho || 'md';
    const fechavel = opcoes.fechavel !== false;
    
    const modalHTML = `
        <div id="${modalId}" class="modal fade show" style="display: block;">
            <div class="modal-overlay" onclick="${fechavel ? 'fecharModal()' : ''}"></div>
            <div class="modal-dialog modal-${tamanho}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${titulo}</h5>
                        ${fechavel ? '<button type="button" class="btn-close" onclick="fecharModal()">&times;</button>' : ''}
                    </div>
                    <div class="modal-body">
                        ${conteudo}
                    </div>
                    ${botoes.length > 0 ? `
                        <div class="modal-footer">
                            ${botoes.map(botao => `
                                <button type="button" class="btn ${botao.classe}" onclick="${botao.onclick}">
                                    ${botao.texto}
                                </button>
                            `).join('')}
                        </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
    
    document.querySelector(ModalConfig.container).innerHTML = modalHTML;
    document.body.classList.add('modal-open');
}

/**
 * Fecha o modal atual
 */
function fecharModal() {
    const modal = document.querySelector('.modal.show');
    if (modal) {
        modal.remove();
    }
    document.body.classList.remove('modal-open');
}

/**
 * Abre modal de confirmação
 */
function abrirModalConfirmacao(titulo, mensagem, callbackSim, callbackNao = null) {
    const conteudo = `
        <div class="text-center">
            <i class="fas fa-question-circle fa-3x text-warning mb-3"></i>
            <p class="mb-0">${mensagem}</p>
        </div>
    `;
    
    const botoes = [
        {
            texto: 'Cancelar',
            classe: 'btn-secondary',
            onclick: 'fecharModal(); ' + (callbackNao ? callbackNao + '()' : '')
        },
        {
            texto: 'Confirmar',
            classe: 'btn-danger',
            onclick: 'fecharModal(); ' + callbackSim + '()'
        }
    ];
    
    abrirModal(titulo, conteudo, botoes);
}

// =====================================================
// MODAIS ESPECÍFICOS DO SISTEMA
// =====================================================

/**
 * Modal de cadastro/edição de membro
 */
function abrirModalMembro(membro = null) {
    const titulo = membro ? 'Editar Membro' : 'Novo Membro';
    const isEdicao = !!membro;
    
    const conteudo = `
        <form id="form-membro" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nome_completo" class="form-label">Nome Completo *</label>
                        <input type="text" class="form-control" id="nome_completo" name="nome_completo" 
                               value="${membro?.nome_completo || ''}" required>
                        <div class="invalid-feedback">Nome é obrigatório</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="apelido" class="form-label">Apelido</label>
                        <input type="text" class="form-control" id="apelido" name="apelido" 
                               value="${membro?.apelido || ''}">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                        <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" 
                               value="${membro?.data_nascimento || ''}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="cpf" class="form-label">CPF</label>
                        <input type="text" class="form-control" id="cpf" name="cpf" 
                               value="${membro?.cpf || ''}" placeholder="000.000.000-00">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="tel" class="form-control" id="telefone" name="telefone" 
                               value="${membro?.telefone || ''}" placeholder="(00) 00000-0000">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="${membro?.email || ''}">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="endereco" class="form-label">Endereço</label>
                <textarea class="form-control" id="endereco" name="endereco" rows="2">${membro?.endereco || ''}</textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="ativo" ${membro?.status === 'ativo' ? 'selected' : ''}>Ativo</option>
                            <option value="inativo" ${membro?.status === 'inativo' ? 'selected' : ''}>Inativo</option>
                            <option value="suspenso" ${membro?.status === 'suspenso' ? 'selected' : ''}>Suspenso</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="situacao_pastoral" class="form-label">Situação Pastoral</label>
                        <select class="form-select" id="situacao_pastoral" name="situacao_pastoral">
                            <option value="membro" ${membro?.situacao_pastoral === 'membro' ? 'selected' : ''}>Membro</option>
                            <option value="catecumeno" ${membro?.situacao_pastoral === 'catecumeno' ? 'selected' : ''}>Catecúmeno</option>
                            <option value="visitante" ${membro?.situacao_pastoral === 'visitante' ? 'selected' : ''}>Visitante</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    `;
    
    const botoes = [
        {
            texto: 'Cancelar',
            classe: 'btn-secondary',
            onclick: 'fecharModal()'
        },
        {
            texto: isEdicao ? 'Atualizar' : 'Salvar',
            classe: 'btn-primary',
            onclick: isEdicao ? 'salvarMembro()' : 'criarMembro()'
        }
    ];
    
    abrirModal(titulo, conteudo, botoes, { tamanho: 'lg' });
}

/**
 * Modal de funções
 */
function abrirModalFuncoes() {
    const conteudo = `
        <div class="text-center">
            <i class="fas fa-user-tag fa-3x text-primary mb-3"></i>
            <h5>Gerenciar Funções</h5>
            <p>Funcionalidade em desenvolvimento</p>
        </div>
    `;
    
    const botoes = [
        {
            texto: 'Fechar',
            classe: 'btn-secondary',
            onclick: 'fecharModal()'
        }
    ];
    
    abrirModal('Funções', conteudo, botoes);
}

/**
 * Modal de formações
 */
function abrirModalFormacoes() {
    const conteudo = `
        <div class="text-center">
            <i class="fas fa-graduation-cap fa-3x text-success mb-3"></i>
            <h5>Gerenciar Formações</h5>
            <p>Funcionalidade em desenvolvimento</p>
        </div>
    `;
    
    const botoes = [
        {
            texto: 'Fechar',
            classe: 'btn-secondary',
            onclick: 'fecharModal()'
        }
    ];
    
    abrirModal('Formações', conteudo, botoes);
}

/**
 * Modal de LGPD
 */
function abrirModalLGPD() {
    const conteudo = `
        <div class="text-center">
            <i class="fas fa-shield-alt fa-3x text-info mb-3"></i>
            <h5>Configurações LGPD</h5>
            <p>Funcionalidade em desenvolvimento</p>
        </div>
    `;
    
    const botoes = [
        {
            texto: 'Fechar',
            classe: 'btn-secondary',
            onclick: 'fecharModal()'
        }
    ];
    
    abrirModal('LGPD', conteudo, botoes);
}

// =====================================================
// FUNÇÕES DE FORMULÁRIO
// =====================================================

/**
 * Valida formulário
 */
function validarFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    form.classList.add('was-validated');
    return form.checkValidity();
}

/**
 * Salva membro (criação)
 */
function criarMembro() {
    if (!validarFormulario('form-membro')) return;
    
    const formData = new FormData(document.getElementById('form-membro'));
    const dados = Object.fromEntries(formData.entries());
    
    // Simular salvamento
    console.log('Criando membro:', dados);
    mostrarNotificacao('Membro criado com sucesso!', 'success');
    fecharModal();
    carregarMembros(); // Recarregar lista
}

/**
 * Salva membro (edição)
 */
function salvarMembro() {
    if (!validarFormulario('form-membro')) return;
    
    const formData = new FormData(document.getElementById('form-membro'));
    const dados = Object.fromEntries(formData.entries());
    
    // Simular salvamento
    console.log('Atualizando membro:', dados);
    mostrarNotificacao('Membro atualizado com sucesso!', 'success');
    fecharModal();
    carregarMembros(); // Recarregar lista
}

// =====================================================
// UTILITÁRIOS
// =====================================================

/**
 * Mostra notificação
 */
function mostrarNotificacao(mensagem, tipo = 'info') {
    const notificacao = document.createElement('div');
    notificacao.className = `alert alert-${tipo} alert-dismissible fade show position-fixed`;
    notificacao.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notificacao.innerHTML = `
        ${mensagem}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notificacao);
    
    // Remove automaticamente após 5 segundos
    setTimeout(() => {
        if (notificacao.parentNode) {
            notificacao.remove();
        }
    }, 5000);
}

// =====================================================
// EVENTOS
// =====================================================

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fecharModal();
    }
});

// Fechar modal clicando no overlay
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        fecharModal();
    }
});
