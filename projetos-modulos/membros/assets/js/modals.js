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
    
    // Remover modal anterior se existir
    fecharModal();
    
    // Adicionar modal ao body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    document.body.classList.add('modal-open');
    
    // Focar no primeiro input do modal
    setTimeout(() => {
        const primeiroInput = document.querySelector(`#${modalId} input, #${modalId} select, #${modalId} textarea`);
        if (primeiroInput) {
            primeiroInput.focus();
        }
    }, 100);
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
function abrirModalMembro(membro = null, modo = 'editar') {
    const titulo = membro ? (modo === 'visualizar' ? 'Visualizar Membro' : 'Editar Membro') : 'Novo Membro';
    const isEdicao = !!membro && modo !== 'visualizar';
    const isVisualizacao = modo === 'visualizar';
    
    const conteudo = `
        <form id="form-membro" class="needs-validation" novalidate>
            <!-- Seção de Dados Pessoais -->
            <div class="form-section">
                <h6 class="section-title">
                    <i class="fas fa-user"></i>
                    Dados Pessoais
                </h6>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="nome_completo" class="form-label">
                                <i class="fas fa-user-circle"></i>
                                Nome Completo *
                            </label>
                            <input type="text" class="form-control" id="nome_completo" name="nome_completo" 
                                   value="${membro?.nome_completo || ''}" ${isVisualizacao ? 'disabled' : ''} required
                                   placeholder="Digite o nome completo">
                            <div class="invalid-feedback">Nome é obrigatório</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="apelido" class="form-label">
                                <i class="fas fa-tag"></i>
                                Apelido
                            </label>
                            <input type="text" class="form-control" id="apelido" name="apelido" 
                                   value="${membro?.apelido || ''}" placeholder="Como prefere ser chamado">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="data_nascimento" class="form-label">
                                <i class="fas fa-calendar"></i>
                                Data de Nascimento
                            </label>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" 
                                   value="${membro?.data_nascimento || ''}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sexo" class="form-label">
                                <i class="fas fa-venus-mars"></i>
                                Sexo
                            </label>
                            <select class="form-control" id="sexo" name="sexo" ${isVisualizacao ? 'disabled' : ''}>
                                <option value="">Selecione</option>
                                <option value="M" ${membro?.sexo === 'M' ? 'selected' : ''}>Masculino</option>
                                <option value="F" ${membro?.sexo === 'F' ? 'selected' : ''}>Feminino</option>
                                <option value="Outro" ${membro?.sexo === 'Outro' ? 'selected' : ''}>Outro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cpf" class="form-label">
                                <i class="fas fa-id-card"></i>
                                CPF
                            </label>
                            <input type="text" class="form-control" id="cpf" name="cpf" 
                                   value="${membro?.cpf || ''}" placeholder="000.000.000-00">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção de Contato -->
            <div class="form-section">
                <h6 class="section-title">
                    <i class="fas fa-phone"></i>
                    Contato
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="celular_whatsapp" class="form-label">
                                <i class="fab fa-whatsapp"></i>
                                Celular/WhatsApp
                            </label>
                            <input type="tel" class="form-control" id="celular_whatsapp" name="celular_whatsapp" 
                                   value="${membro?.celular_whatsapp || ''}" placeholder="(00) 00000-0000">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i>
                                E-mail
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="${membro?.email || ''}" placeholder="seu@email.com">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telefone_fixo" class="form-label">
                                <i class="fas fa-phone"></i>
                                Telefone Fixo
                            </label>
                            <input type="tel" class="form-control" id="telefone_fixo" name="telefone_fixo" 
                                   value="${membro?.telefone_fixo || ''}" placeholder="(00) 0000-0000">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção de Endereço -->
            <div class="form-section">
                <h6 class="section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Endereço
                </h6>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="rua" class="form-label">Rua</label>
                            <input type="text" class="form-control" id="rua" name="rua" 
                                   value="${membro?.rua || ''}" placeholder="Nome da rua">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero" 
                                   value="${membro?.numero || ''}" placeholder="123">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bairro" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro" 
                                   value="${membro?.bairro || ''}" placeholder="Nome do bairro">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" 
                                   value="${membro?.cidade || ''}" placeholder="Nome da cidade">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="uf" class="form-label">UF</label>
                            <input type="text" class="form-control" id="uf" name="uf" 
                                   value="${membro?.uf || ''}" placeholder="SP" maxlength="2">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep" 
                                   value="${membro?.cep || ''}" placeholder="00000-000">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção Pastoral -->
            <div class="form-section">
                <h6 class="section-title">
                    <i class="fas fa-church"></i>
                    Situação Pastoral
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status" class="form-label">
                                <i class="fas fa-check-circle"></i>
                                Status
                            </label>
                            <select class="form-control" id="status" name="status" ${isVisualizacao ? 'disabled' : ''}>
                                <option value="ativo" ${membro?.status === 'ativo' ? 'selected' : ''}>Ativo</option>
                                <option value="afastado" ${membro?.status === 'afastado' ? 'selected' : ''}>Afastado</option>
                                <option value="em_discernimento" ${membro?.status === 'em_discernimento' ? 'selected' : ''}>Em Discernimento</option>
                                <option value="bloqueado" ${membro?.status === 'bloqueado' ? 'selected' : ''}>Bloqueado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="comunidade_ou_capelania" class="form-label">
                                <i class="fas fa-building"></i>
                                Comunidade/Capelania
                            </label>
                            <input type="text" class="form-control" id="comunidade_ou_capelania" name="comunidade_ou_capelania" 
                                   value="${membro?.comunidade_ou_capelania || ''}" placeholder="Nome da comunidade">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="data_entrada" class="form-label">
                                <i class="fas fa-calendar-plus"></i>
                                Data de Entrada
                            </label>
                            <input type="date" class="form-control" id="data_entrada" name="data_entrada" 
                                   value="${membro?.data_entrada || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="paroquiano" name="paroquiano" 
                                       ${membro?.paroquiano ? 'checked' : ''} ${isVisualizacao ? 'disabled' : ''}>
                                <label class="form-check-label" for="paroquiano">
                                    <i class="fas fa-home"></i>
                                    Paroquiano
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="observacoes_pastorais" class="form-label">
                        <i class="fas fa-sticky-note"></i>
                        Observações Pastorais
                    </label>
                    <textarea class="form-control" id="observacoes_pastorais" name="observacoes_pastorais" 
                              rows="3" placeholder="Observações importantes sobre o membro...">${membro?.observacoes_pastorais || ''}</textarea>
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
    
    // Se for visualização, desabilitar todos os campos do formulário
    if (isVisualizacao) {
        setTimeout(() => {
            const form = document.getElementById('form-membro');
            if (form) {
                const inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.disabled = true;
                    input.classList.add('bg-light');
                });
            }
        }, 100);
    }
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
