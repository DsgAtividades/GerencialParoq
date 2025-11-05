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
 * Abre um modal genérico (OTIMIZADO COM SANITIZAÇÃO)
 */
function abrirModal(titulo, conteudo, botoes = [], opcoes = {}) {
    const modalId = opcoes.id || 'modal-' + Date.now();
    const tamanho = opcoes.tamanho || 'md';
    const fechavel = opcoes.fechavel !== false;
    const isHtmlContent = opcoes.isHtmlContent || false; // Flag para indicar se conteúdo é HTML confiável
    
    // Remover modal anterior se existir
    fecharModal();
    
    // Criar modal usando DOM (mais seguro que innerHTML)
    const modalContainer = document.createElement('div');
    modalContainer.id = modalId;
    modalContainer.className = 'modal fade show';
    modalContainer.style.display = 'block';
    
    const modalDialog = document.createElement('div');
    modalDialog.className = `modal-dialog modal-${Sanitizer.escapeHtml(tamanho)}`;
    
    const modalContent = document.createElement('div');
    modalContent.className = 'modal-content';
    
    // Header
    const modalHeader = document.createElement('div');
    modalHeader.className = 'modal-header';
    
    const modalTitle = document.createElement('h5');
    modalTitle.className = 'modal-title';
    Sanitizer.setText(modalTitle, titulo); // Sanitizar título
    
    modalHeader.appendChild(modalTitle);
    
    if (fechavel) {
        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'btn-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.onclick = fecharModal;
        modalHeader.appendChild(closeBtn);
    }
    
    // Body
    const modalBody = document.createElement('div');
    modalBody.className = 'modal-body';
    
    // Se conteúdo for HTML confiável (ex: formulários), permitir
    // Caso contrário, sanitizar
    if (isHtmlContent) {
        modalBody.innerHTML = conteudo; // Conteúdo já vem de fonte confiável
    } else if (typeof conteudo === 'string') {
        Sanitizer.setInnerHTML(modalBody, conteudo, ['b', 'i', 'u', 'strong', 'em', 'p', 'br', 'div', 'span']);
    } else if (conteudo instanceof HTMLElement) {
        modalBody.appendChild(conteudo);
    }
    
    // Footer com botões
    if (botoes.length > 0) {
        const modalFooter = document.createElement('div');
        modalFooter.className = 'modal-footer';
        
        botoes.forEach(botao => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `btn ${Sanitizer.escapeHtml(botao.classe)}`;
            Sanitizer.setText(btn, botao.texto);
            
            // Usar addEventListener em vez de onclick (mais seguro)
            if (typeof botao.onclick === 'function') {
                btn.addEventListener('click', botao.onclick);
            } else if (typeof botao.onclick === 'string') {
                // Se for string, avaliar (não ideal, mas mantido para compatibilidade)
                btn.onclick = new Function(botao.onclick);
            }
            
            modalFooter.appendChild(btn);
        });
        
        modalContent.appendChild(modalHeader);
        modalContent.appendChild(modalBody);
        modalContent.appendChild(modalFooter);
    } else {
        modalContent.appendChild(modalHeader);
        modalContent.appendChild(modalBody);
    }
    
    modalDialog.appendChild(modalContent);
    modalContainer.appendChild(modalDialog);
    
    // Adicionar modal ao body
    document.body.appendChild(modalContainer);
    document.body.classList.add('modal-open');
    
    // Focar no primeiro input do modal
    setTimeout(() => {
        const primeiroInput = modalContainer.querySelector('input, select, textarea');
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
 * Abre modal de confirmação (OTIMIZADO COM SANITIZAÇÃO)
 */
function abrirModalConfirmacao(titulo, mensagem, callbackSim, callbackNao = null) {
    // Criar conteúdo via DOM (mais seguro)
    const container = document.createElement('div');
    container.className = 'text-center';
    
    const icon = document.createElement('i');
    icon.className = 'fas fa-question-circle fa-3x text-warning mb-3';
    
    const paragraph = document.createElement('p');
    paragraph.className = 'mb-0';
    Sanitizer.setText(paragraph, mensagem); // Sanitizar mensagem
    
    container.appendChild(icon);
    container.appendChild(paragraph);
    
    const botoes = [
        {
            texto: 'Cancelar',
            classe: 'btn-secondary',
            onclick: () => {
                fecharModal();
                if (typeof callbackNao === 'function') {
                    callbackNao();
                }
            }
        },
        {
            texto: 'Confirmar',
            classe: 'btn-danger',
            onclick: () => {
                fecharModal();
                if (typeof callbackSim === 'function') {
                    callbackSim();
                }
            }
        }
    ];
    
    abrirModal(titulo, container, botoes);
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
    
    // Garantir que membro é um objeto válido
    const dadosMembro = (membro && typeof membro === 'object') ? membro : {};
    
    // Criar formulário de forma mais eficiente
    const formId = 'form-membro-' + Date.now();
    const conteudo = criarFormularioMembro(dadosMembro, isEdicao, isVisualizacao, formId);
    
    // Se for visualização: sem botões no footer (fechar pelo X do header)
    const botoes = isVisualizacao ? [] : [
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
    
    abrirModal(titulo, conteudo, botoes, { tamanho: 'lg', isHtmlContent: true }); // Formulário é HTML confiável
    
    // Armazenar ID do membro no modal para carregar pastorais
    setTimeout(() => {
        const modal = document.querySelector('.modal.show');
        if (modal && membro && membro.id) {
            modal.dataset.membroId = membro.id;
        }
    }, 10);
    
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
        }, 10);
    }
}

function criarFormularioMembro(dadosMembro, isEdicao, isVisualizacao, formId) {
    // Garantir que dadosMembro é um objeto válido
    if (!dadosMembro || typeof dadosMembro !== 'object') {
        dadosMembro = {};
    }
    
    return `
        <form id="form-membro" class="needs-validation" novalidate>
            ${isEdicao ? `<input type="hidden" id="membro-id" value="${dadosMembro.id || ''}">` : ''}
            
            <!-- Dados Pessoais -->
            <div class="form-section">
                <h6 class="section-title"><i class="fas fa-user"></i> Dados Pessoais</h6>
                
                <!-- Foto do Membro -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-camera"></i> Foto do Membro</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="foto-preview-container" style="width: 150px; height: 150px; border: 2px dashed #ddd; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                    ${dadosMembro.foto_url && !dadosMembro.foto_url.match(/^[a-f0-9\-]{36}$/) ? 
                                        `<img src="${Sanitizer.sanitizeUrl(dadosMembro.foto_url)}" alt="Foto" style="max-width: 100%; max-height: 100%; object-fit: cover;" id="foto-preview" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\\'fas fa-user fa-3x text-muted\\'></i>';"` :
                                        `<i class="fas fa-user fa-3x text-muted"></i>`
                                    }
                                </div>
                                ${!isVisualizacao ? `
                                    <div class="flex-grow-1">
                                        <input type="file" class="form-control" id="foto_membro" name="foto_membro" 
                                               accept="image/jpeg,image/jpg,image/png,image/gif" 
                                               onchange="previewFoto(this)">
                                        <small class="form-text text-muted">Formatos aceitos: JPEG, PNG, GIF. Tamanho máximo: 5MB</small>
                                        ${dadosMembro.foto_url ? `
                                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removerFoto()">
                                                <i class="fas fa-trash"></i> Remover Foto
                                            </button>
                                        ` : ''}
                                    </div>
                                ` : ''}
                            </div>
                            <input type="hidden" id="foto_url" name="foto_url" value="${dadosMembro.foto_url || ''}">
                            <input type="hidden" id="foto_anexo_id" name="foto_anexo_id" value="">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="nome_completo" class="form-label"><i class="fas fa-user-circle"></i> Nome Completo *</label>
                            <input type="text" class="form-control" id="nome_completo" name="nome_completo" 
                                   value="${dadosMembro.nome_completo || ''}" ${isVisualizacao ? 'disabled' : ''} required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="apelido" class="form-label"><i class="fas fa-tag"></i> Apelido</label>
                            <input type="text" class="form-control" id="apelido" name="apelido" 
                                   value="${dadosMembro.apelido || ''}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="data_nascimento" class="form-label"><i class="fas fa-calendar"></i> Data de Nascimento</label>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" 
                                   value="${dadosMembro.data_nascimento || ''}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sexo" class="form-label"><i class="fas fa-venus-mars"></i> Sexo</label>
                            <select class="form-control" id="sexo" name="sexo" ${isVisualizacao ? 'disabled' : ''}>
                                <option value="">Selecione</option>
                                <option value="M" ${dadosMembro.sexo === 'M' ? 'selected' : ''}>Masculino</option>
                                <option value="F" ${dadosMembro.sexo === 'F' ? 'selected' : ''}>Feminino</option>
                                <option value="Outro" ${dadosMembro.sexo === 'Outro' ? 'selected' : ''}>Outro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cpf" class="form-label"><i class="fas fa-id-card"></i> CPF</label>
                            <input type="text" class="form-control" id="cpf" name="cpf" 
                                   value="${dadosMembro.cpf || ''}" placeholder="000.000.000-00">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contato -->
            <div class="form-section">
                <h6 class="section-title"><i class="fas fa-phone"></i> Contato</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="celular_whatsapp" class="form-label"><i class="fab fa-whatsapp"></i> Celular/WhatsApp</label>
                            <input type="tel" class="form-control" id="celular_whatsapp" name="celular_whatsapp" 
                                   value="${dadosMembro.celular_whatsapp || ''}" placeholder="(00) 00000-0000">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-label"><i class="fas fa-envelope"></i> E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="${dadosMembro.email || ''}" placeholder="seu@email.com">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telefone_fixo" class="form-label"><i class="fas fa-phone"></i> Telefone Fixo</label>
                            <input type="tel" class="form-control" id="telefone_fixo" name="telefone_fixo" 
                                   value="${dadosMembro.telefone_fixo || ''}" placeholder="(00) 0000-0000">
                        </div>
                    </div>
                </div>
            </div>

            ${!isVisualizacao ? `
            <!-- Status e Observações -->
            <div class="form-section">
                <h6 class="section-title"><i class="fas fa-info-circle"></i> Status e Observações</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status" class="form-label"><i class="fas fa-check-circle"></i> Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="ativo" ${dadosMembro.status === 'ativo' ? 'selected' : ''}>Ativo</option>
                                <option value="afastado" ${dadosMembro.status === 'afastado' ? 'selected' : ''}>Afastado</option>
                                <option value="em_discernimento" ${dadosMembro.status === 'em_discernimento' ? 'selected' : ''}>Em Discernimento</option>
                                <option value="bloqueado" ${dadosMembro.status === 'bloqueado' ? 'selected' : ''}>Bloqueado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="comunidade_ou_capelania" class="form-label"><i class="fas fa-building"></i> Comunidade/Capelania</label>
                            <input type="text" class="form-control" id="comunidade_ou_capelania" name="comunidade_ou_capelania" 
                                   value="${dadosMembro.comunidade_ou_capelania || ''}" placeholder="Nome da comunidade">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="data_entrada" class="form-label"><i class="fas fa-calendar-plus"></i> Data de Entrada</label>
                            <input type="date" class="form-control" id="data_entrada" name="data_entrada" 
                                   value="${dadosMembro.data_entrada || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="paroquiano" name="paroquiano" ${dadosMembro.paroquiano ? 'checked' : ''}>
                                <label class="form-check-label" for="paroquiano"><i class="fas fa-home"></i> Paroquiano</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="observacoes_pastorais" class="form-label"><i class="fas fa-sticky-note"></i> Observações Pastorais</label>
                    <textarea class="form-control" id="observacoes_pastorais" name="observacoes_pastorais" 
                              rows="3" placeholder="Observações importantes sobre o membro...">${dadosMembro.observacoes_pastorais || ''}</textarea>
                </div>
            </div>
            ` : ''}

            <!-- Endereço -->
            <div class="form-section">
                <h6 class="section-title"><i class="fas fa-map-marker-alt"></i> Endereço</h6>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="rua" class="form-label">Rua</label>
                            <input type="text" class="form-control" id="rua" name="rua" 
                                   value="${dadosMembro.rua || ''}" placeholder="Nome da rua">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero" 
                                   value="${dadosMembro.numero || ''}" placeholder="123">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bairro" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro" 
                                   value="${dadosMembro.bairro || ''}" placeholder="Nome do bairro">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" 
                                   value="${dadosMembro.cidade || ''}" placeholder="Nome da cidade">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="uf" class="form-label">UF</label>
                            <input type="text" class="form-control" id="uf" name="uf" 
                                   value="${dadosMembro.uf || ''}" placeholder="SP" maxlength="2">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep" 
                                   value="${dadosMembro.cep || ''}" placeholder="00000-000">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documentação -->
            <div class="form-section">
                <h6 class="section-title"><i class="fas fa-file-alt"></i> Documentação</h6>
                <div id="documentos-container">
                    ${Array.isArray(dadosMembro.documentos) && dadosMembro.documentos.length > 0 
                        ? dadosMembro.documentos.map((doc, index) => criarHtmlDocumento(doc, index, isVisualizacao)).join('')
                        : !isVisualizacao ? criarHtmlDocumento(null, 0, false) : '<p class="text-muted">Nenhum documento cadastrado</p>'
                    }
                </div>
                ${!isVisualizacao ? `
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" style ="margin-bottom: 10px;" onclick="adicionarDocumento()">
                        <i class="fas fa-plus"></i> Adicionar Documento
                    </button>
                ` : ''}
            </div>

            <!-- Situação Paroquial (Apenas Visualização) -->
            ${isVisualizacao ? `
            <div class="form-section">
                <div class="section-header">
                    <h6 class="section-title"><i class="fas fa-church"></i> Pastorais</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleSecaoPastoral()">
                        <i class="fas fa-chevron-down" id="icone-secao-pastoral"></i> Ver Pastorais
                    </button>
                </div>
                
                <!-- Seção expandível com tabela de pastorais -->
                <div id="detalhes-situacao-pastoral" class="collapse">
                    <div id="tabela-pastorais-membro" class="table-responsive">
                        <table class="table table-sm table-hover" style="width: 100% !important;">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Status</th>
                                    <th>Data Entrada</th>
                                    <th>Função</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i> Carregando pastorais...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            ` : ''}
        </form>
    `;
}

/**
 * Cria HTML de um documento individual
 */
function criarHtmlDocumento(doc, index, isVisualizacao) {
    const docId = doc?.id || `novo-${index}`;
    const tipoDocumento = doc?.tipo_documento || '';
    const numero = doc?.numero || '';
    const orgaoEmissor = doc?.orgao_emissor || '';
    const dataEmissao = doc?.data_emissao || '';
    const dataVencimento = doc?.data_vencimento || '';
    const arquivoUrl = doc?.arquivo_url || '';
    const observacoes = doc?.observacoes || '';
    
    if (isVisualizacao) {
        return `
            <div class="documento-item mb-3 p-3 border rounded" data-doc-id="${docId}">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Tipo:</strong> ${Sanitizer.escapeHtml(tipoDocumento || 'N/A')}
                    </div>
                    <div class="col-md-3">
                        <strong>Número:</strong> ${Sanitizer.escapeHtml(numero || 'N/A')}
                    </div>
                    <div class="col-md-3">
                        <strong>Órgão Emissor:</strong> ${Sanitizer.escapeHtml(orgaoEmissor || 'N/A')}
                    </div>
                    <div class="col-md-3">
                        <strong>Data Emissão:</strong> ${Sanitizer.escapeHtml(dataEmissao || 'N/A')}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3">
                        <strong>Data Vencimento:</strong> ${Sanitizer.escapeHtml(dataVencimento || 'N/A')}
                    </div>
                    <div class="col-md-6">
                        <strong>Arquivo:</strong> ${arquivoUrl ? `<a href="${Sanitizer.sanitizeUrl(arquivoUrl)}" target="_blank"><i class="fas fa-file-pdf"></i> Ver arquivo</a>` : 'N/A'}
                    </div>
                </div>
                ${observacoes ? `<div class="row mt-2"><div class="col-md-12"><strong>Observações:</strong> ${Sanitizer.escapeHtml(observacoes)}</div></div>` : ''}
            </div>
        `;
    }
    
    return `
        <div class="documento-item mb-3 p-3 border rounded" data-doc-id="${docId}" data-doc-index="${index}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="fas fa-file-alt"></i> Documento ${index + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removerDocumento(${index})">
                    <i class="fas fa-times"></i> Remover
                </button>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Tipo de Documento *</label>
                        <select class="form-control documento-tipo" name="documentos[${index}][tipo_documento]" required>
                            <option value="">Selecione</option>
                            <option value="RG" ${tipoDocumento === 'RG' ? 'selected' : ''}>RG</option>
                            <option value="CPF" ${tipoDocumento === 'CPF' ? 'selected' : ''}>CPF</option>
                            <option value="CNH" ${tipoDocumento === 'CNH' ? 'selected' : ''}>CNH</option>
                            <option value="Certidão de Nascimento" ${tipoDocumento === 'Certidão de Nascimento' ? 'selected' : ''}>Certidão de Nascimento</option>
                            <option value="Certidão de Casamento" ${tipoDocumento === 'Certidão de Casamento' ? 'selected' : ''}>Certidão de Casamento</option>
                            <option value="Título de Eleitor" ${tipoDocumento === 'Título de Eleitor' ? 'selected' : ''}>Título de Eleitor</option>
                            <option value="Reservista" ${tipoDocumento === 'Reservista' ? 'selected' : ''}>Reservista</option>
                            <option value="Outro" ${tipoDocumento === 'Outro' ? 'selected' : ''}>Outro</option>
                        </select>
                        <input type="hidden" name="documentos[${index}][id]" value="${docId}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Número *</label>
                        <input type="text" class="form-control documento-numero" name="documentos[${index}][numero]" 
                               value="${Sanitizer.escapeHtml(numero)}" placeholder="Número do documento" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Órgão Emissor</label>
                        <input type="text" class="form-control" name="documentos[${index}][orgao_emissor]" 
                               value="${Sanitizer.escapeHtml(orgaoEmissor)}" placeholder="Ex: SSP-SP">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Data de Emissão</label>
                        <input type="date" class="form-control" name="documentos[${index}][data_emissao]" 
                               value="${dataEmissao}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Data de Vencimento</label>
                        <input type="date" class="form-control" name="documentos[${index}][data_vencimento]" 
                               value="${dataVencimento}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">URL do Arquivo</label>
                        <input type="url" class="form-control" name="documentos[${index}][arquivo_url]" 
                               value="${Sanitizer.escapeHtml(arquivoUrl)}" placeholder="https://...">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" name="documentos[${index}][observacoes]" rows="2" 
                                  placeholder="Observações sobre o documento">${Sanitizer.escapeHtml(observacoes)}</textarea>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Adiciona novo documento ao formulário
 */
function adicionarDocumento() {
    const container = document.getElementById('documentos-container');
    if (!container) return;
    
    const documentos = container.querySelectorAll('.documento-item');
    const novoIndex = documentos.length;
    
    const novoDocHtml = criarHtmlDocumento(null, novoIndex, false);
    container.insertAdjacentHTML('beforeend', novoDocHtml);
    
    // Scroll suave para o novo documento
    const novoDoc = container.querySelector(`[data-doc-index="${novoIndex}"]`);
    if (novoDoc) {
        novoDoc.scrollIntoView({ behavior: 'smooth', block: 'center' });
        novoDoc.querySelector('.documento-tipo')?.focus();
    }
}

/**
 * Remove documento do formulário
 */
function removerDocumento(index) {
    const container = document.getElementById('documentos-container');
    if (!container) return;
    
    const documento = container.querySelector(`[data-doc-index="${index}"]`);
    if (documento) {
        documento.remove();
        // Reindexar documentos restantes
        reindexarDocumentos();
    }
}

/**
 * Reindexa documentos após remoção
 */
function reindexarDocumentos() {
    const container = document.getElementById('documentos-container');
    if (!container) return;
    
    const documentos = container.querySelectorAll('.documento-item');
    documentos.forEach((doc, index) => {
        // Atualizar título
        const titulo = doc.querySelector('h6');
        if (titulo) {
            titulo.innerHTML = `<i class="fas fa-file-alt"></i> Documento ${index + 1}`;
        }
        
        // Atualizar atributo data-doc-index
        doc.setAttribute('data-doc-index', index);
        
        // Atualizar names dos inputs
        doc.querySelectorAll('input, select, textarea').forEach(input => {
            const name = input.getAttribute('name');
            if (name && name.includes('documentos[')) {
                const novoName = name.replace(/documentos\[\d+\]/, `documentos[${index}]`);
                input.setAttribute('name', novoName);
            }
        });
        
        // Atualizar onclick do botão remover
        const btnRemover = doc.querySelector('button[onclick*="removerDocumento"]');
        if (btnRemover) {
            btnRemover.setAttribute('onclick', `removerDocumento(${index})`);
        }
    });
}

/**
 * Preview da foto selecionada
 */
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById('foto-preview');
            const container = input.closest('.form-group').querySelector('.foto-preview-container');
            
            if (preview) {
                preview.src = e.target.result;
            } else {
                container.innerHTML = `<img src="${e.target.result}" alt="Foto" style="max-width: 100%; max-height: 100%; object-fit: cover;" id="foto-preview">`;
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Remove foto selecionada
 */
function removerFoto() {
    const inputFoto = document.getElementById('foto_membro');
    const fotoUrl = document.getElementById('foto_url');
    const fotoAnexoId = document.getElementById('foto_anexo_id');
    const container = document.querySelector('.foto-preview-container');
    
    if (inputFoto) {
        inputFoto.value = '';
    }
    
    if (fotoUrl) {
        fotoUrl.value = '';
    }
    
    if (fotoAnexoId) {
        fotoAnexoId.value = '';
    }
    
    if (container) {
        container.innerHTML = '<i class="fas fa-user fa-3x text-muted"></i>';
    }
    
    // Esconder botão remover
    const btnRemover = document.querySelector('button[onclick="removerFoto()"]');
    if (btnRemover) {
        btnRemover.remove();
    }
}

/**
 * Faz upload da foto antes de salvar o membro
 */
async function uploadFotoMembro(membroId = null) {
    const inputFoto = document.getElementById('foto_membro');
    
    if (!inputFoto || !inputFoto.files || !inputFoto.files[0]) {
        return null; // Sem foto para upload
    }
    
    const formData = new FormData();
    formData.append('foto', inputFoto.files[0]);
    
    if (membroId) {
        formData.append('membro_id', membroId);
    }
    
    try {
        // Usar o mesmo padrão de URL dos outros endpoints
        const url = '/PROJETOS/GerencialParoq/projetos-modulos/membros/api/membros/upload-foto';
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
            // Não definir Content-Type para FormData - o navegador define automaticamente com boundary
        });
        
        // Verificar se a resposta é JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Resposta não é JSON:', text);
            throw new Error('Resposta do servidor não é JSON válido. Verifique o console para mais detalhes.');
        }
        
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Erro ao fazer upload da foto');
        }
        
        const data = await response.json();
        
        if (data.success && data.data) {
            // Atualizar campos hidden
            const fotoUrl = document.getElementById('foto_url');
            const fotoAnexoId = document.getElementById('foto_anexo_id');
            
            if (fotoUrl) {
                // Se tem anexo_id (membro existe), usar anexo_id, senão usar URL (será criado depois)
                fotoUrl.value = data.data.anexo_id || data.data.url;
            }
            
            if (fotoAnexoId && data.data.anexo_id) {
                fotoAnexoId.value = data.data.anexo_id;
            }
            
            return data.data;
        }
        
        return null;
    } catch (error) {
        console.error('Erro ao fazer upload da foto:', error);
        throw error;
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
async function criarMembro() {
    if (!validarFormulario('form-membro')) return;
    
    const formData = new FormData(document.getElementById('form-membro'));
    const dados = Object.fromEntries(formData.entries());
    
    // Validações específicas para campos NOT NULL do banco
    const camposObrigatorios = [
        { id: 'nome_completo', nome: 'Nome Completo', mensagem: 'O nome completo é obrigatório e não pode estar vazio.' }
    ];
    
    for (const campo of camposObrigatorios) {
        const valor = dados[campo.id];
        if (!valor || valor.trim() === '') {
            // Mostrar mensagem pequena abaixo do campo
            destacarCampoErro(campo.id, campo.mensagem);
            
            // Mostrar notificação também
            const mensagemErro = `
                <p><strong>❌ Erro ao criar membro</strong></p>
                <p><strong>Campo obrigatório não preenchido:</strong> ${campo.nome}</p>
                <p><strong>Solução:</strong> Preencha o campo ${campo.nome} antes de salvar.</p>
            `;
            mostrarNotificacao(mensagemErro, 'error');
            return;
        }
    }
    
    // Fazer upload da foto primeiro (se houver)
    try {
        const fotoData = await uploadFotoMembro();
        if (fotoData) {
            // Atualizar dados com foto_url
            dados.foto_url = fotoData.anexo_id || fotoData.url;
        }
    } catch (error) {
        mostrarNotificacao(`Erro ao fazer upload da foto: ${error.message}`, 'error');
        return;
    }
    
    // Processar dados específicos
    const dadosProcessados = processarDadosMembro(dados);
    
    // Validação adicional após processamento
    if (!dadosProcessados.nome_completo || dadosProcessados.nome_completo.trim() === '') {
        // Mostrar mensagem pequena abaixo do campo
        destacarCampoErro('nome_completo', 'Nome completo é obrigatório e não pode estar vazio');
        
        // Mostrar notificação também
        const mensagemErro = `
            <p><strong>❌ Erro ao criar membro</strong></p>
            <p><strong>Campo obrigatório inválido:</strong> Nome Completo</p>
            <p><strong>Solução:</strong> Preencha o campo Nome Completo com um valor válido.</p>
        `;
        mostrarNotificacao(mensagemErro, 'error');
        return;
    }
    
    try {
        // Chamar API real
        const response = await MembrosAPI.criar(dadosProcessados);
        
        if (response && response.success) {
            mostrarNotificacao('Membro criado com sucesso!', 'success');
            fecharModal();
            carregarMembros(); // Recarregar lista
            // Limpar cache para garantir dados atualizados
            if (typeof limparCacheMembros === 'function') {
                limparCacheMembros();
            }
        } else {
            const errorMessage = response?.error || 'Erro desconhecido';
            let mensagemErro = '';
            let campoErro = null;
            
            // Tratar erros específicos com mensagens detalhadas
            if (errorMessage.includes('CPF já cadastrado')) {
                destacarCampoErro('cpf', 'Este CPF já está cadastrado para outro membro');
                mensagemErro = `
                    <p><strong>❌ Erro ao criar membro</strong></p>
                    <p><strong>Campo com problema:</strong> CPF</p>
                    <p><strong>Solução:</strong> Use um CPF diferente ou deixe o campo vazio.</p>
                `;
            } else if (errorMessage.includes('Email já cadastrado')) {
                destacarCampoErro('email', 'Este email já está cadastrado para outro membro');
                mensagemErro = `
                    <p><strong>❌ Erro ao criar membro</strong></p>
                    <p><strong>Campo com problema:</strong> Email</p>
                    <p><strong>Solução:</strong> Use um email diferente ou deixe o campo vazio.</p>
                `;
            } else if (errorMessage.includes('CPF inválido')) {
                destacarCampoErro('cpf', 'CPF inválido - verifique os dígitos verificadores');
                mensagemErro = `
                    <p><strong>❌ Erro ao criar membro</strong></p>
                    <p><strong>Campo com problema:</strong> CPF</p>
                    <p><strong>Solução:</strong> Verifique se digitou corretamente os 11 dígitos ou deixe o campo vazio.</p>
                `;
            } else if (errorMessage.includes('Email inválido')) {
                destacarCampoErro('email', 'Formato de email inválido');
                mensagemErro = `
                    <p><strong>❌ Erro ao criar membro</strong></p>
                    <p><strong>Campo com problema:</strong> Email</p>
                    <p><strong>Solução:</strong> Verifique o endereço digitado (ex: nome@exemplo.com)</p>
                `;
            } else if (errorMessage.includes('Nome completo') || errorMessage.includes('Campo obrigatório') || errorMessage.includes('não pode estar vazio')) {
                // Detectar qual campo obrigatório está faltando
                let campoId = 'nome_completo';
                let campoNome = 'Nome Completo';
                
                if (errorMessage.includes('nome completo') || errorMessage.includes('Nome completo')) {
                    campoId = 'nome_completo';
                    campoNome = 'Nome Completo';
                }
                
                destacarCampoErro(campoId, 'Este campo é obrigatório e não pode estar vazio');
                mensagemErro = `
                    <p><strong>❌ Erro ao criar membro</strong></p>
                    <p><strong>Campo obrigatório não preenchido:</strong> ${campoNome}</p>
                    <p><strong>Solução:</strong> Preencha o campo ${campoNome} antes de salvar.</p>
                `;
            } else {
                mensagemErro = `
                    <p><strong>❌ Erro ao criar membro</strong></p>
                    <p><strong>Detalhes:</strong> ${errorMessage}</p>
                    <p>Verifique os dados informados e tente novamente.</p>
                `;
            }
            
            mostrarNotificacao(mensagemErro, 'error');
        }
    } catch (error) {
        console.error('Erro ao criar membro:', error);
        
        // Extrair mensagem de erro do responseData se disponível
        let errorMessage = error.message || 'Erro desconhecido';
        if (error.responseData && error.responseData.error) {
            errorMessage = error.responseData.error;
        }
        
        console.log('Mensagem de erro extraída:', errorMessage);
        
        // Tratar erros específicos com mensagens detalhadas
        let mensagemErro = '';
        
        if (errorMessage.includes('CPF já cadastrado')) {
            destacarCampoErro('cpf', 'Este CPF já está cadastrado');
            mensagemErro = `
                <p><strong>❌ Erro ao criar membro</strong></p>
                <p><strong>Campo com problema:</strong> CPF</p>
                <p><strong>Solução:</strong> Use um CPF diferente ou deixe o campo vazio.</p>
            `;
        } else if (errorMessage.includes('Email já cadastrado')) {
            destacarCampoErro('email', 'Este email já está cadastrado');
            mensagemErro = `
                <p><strong>❌ Erro ao criar membro</strong></p>
                <p><strong>Campo com problema:</strong> Email</p>
                <p><strong>Solução:</strong> Use um email diferente ou deixe o campo vazio.</p>
            `;
        } else if (errorMessage.includes('CPF inválido')) {
            destacarCampoErro('cpf', 'CPF inválido - verifique os dígitos verificadores');
            mensagemErro = `
                <p><strong>❌ Erro ao criar membro</strong></p>
                <p><strong>Campo com problema:</strong> CPF</p>
                <p><strong>Solução:</strong> Verifique se digitou corretamente os 11 dígitos ou deixe o campo vazio.</p>
            `;
        } else if (errorMessage.includes('Email inválido')) {
            destacarCampoErro('email', 'Formato de email inválido');
            mensagemErro = `
                <p><strong>❌ Erro ao criar membro</strong></p>
                <p><strong>Campo com problema:</strong> Email</p>
                <p><strong>Solução:</strong> Verifique o endereço digitado (ex: nome@exemplo.com)</p>
            `;
        } else if (errorMessage.includes('Nome completo') || errorMessage.includes('Campo obrigatório') || errorMessage.includes('não pode estar vazio')) {
            // Detectar qual campo obrigatório está faltando
            let campoId = 'nome_completo';
            let campoNome = 'Nome Completo';
            
            if (errorMessage.includes('nome completo') || errorMessage.includes('Nome completo')) {
                campoId = 'nome_completo';
                campoNome = 'Nome Completo';
            }
            
            destacarCampoErro(campoId, 'Este campo é obrigatório e não pode estar vazio');
            mensagemErro = `
                <p><strong>❌ Erro ao criar membro</strong></p>
                <p><strong>Campo obrigatório não preenchido:</strong> ${campoNome}</p>
                <p><strong>Solução:</strong> Preencha o campo ${campoNome} antes de salvar.</p>
            `;
        } else {
            mensagemErro = `
                <p><strong>❌ Erro ao criar membro</strong></p>
                <p><strong>Detalhes:</strong> ${errorMessage}</p>
                <p>Verifique os dados informados e tente novamente.</p>
            `;
        }
        
        mostrarNotificacao(mensagemErro, 'error');
    }
}

/**
 * Salva membro (edição)
 */
async function salvarMembro() {
    if (!validarFormulario('form-membro')) return;
    
    const membroIdElement = document.getElementById('membro-id');
    if (!membroIdElement) {
        mostrarNotificacao('ID do membro não encontrado', 'error');
        return;
    }
    
    const membroId = membroIdElement.value;
    if (!membroId) {
        mostrarNotificacao('ID do membro inválido', 'error');
        return;
    }
    
    const formData = new FormData(document.getElementById('form-membro'));
    const dados = Object.fromEntries(formData.entries());
    
    // Fazer upload da foto primeiro (se houver)
    try {
        const fotoData = await uploadFotoMembro(membroId);
        if (fotoData) {
            // Atualizar dados com foto_url
            dados.foto_url = fotoData.anexo_id || fotoData.url;
        }
    } catch (error) {
        mostrarNotificacao(`Erro ao fazer upload da foto: ${error.message}`, 'error');
        return;
    }
    
    // Validações específicas para campos NOT NULL do banco
    const camposObrigatorios = [
        { id: 'nome_completo', nome: 'Nome Completo', mensagem: 'O nome completo é obrigatório e não pode estar vazio.' }
    ];
    
    for (const campo of camposObrigatorios) {
        const valor = dados[campo.id];
        if (!valor || valor.trim() === '') {
            // Mostrar mensagem pequena abaixo do campo
            destacarCampoErro(campo.id, campo.mensagem);
            
            // Mostrar notificação também
            const mensagemErro = `
                <p><strong>❌ Erro ao atualizar membro</strong></p>
                <p><strong>Campo obrigatório não preenchido:</strong> ${campo.nome}</p>
                <p><strong>Solução:</strong> Preencha o campo ${campo.nome} antes de salvar.</p>
            `;
            mostrarNotificacao(mensagemErro, 'error');
        return;
        }
    }
    
    // Processar dados específicos
    const dadosProcessados = processarDadosMembro(dados);
    
    // Validação adicional após processamento (garantir que não foi convertido para null)
    if (!dadosProcessados.nome_completo || dadosProcessados.nome_completo.trim() === '') {
        // Mostrar mensagem pequena abaixo do campo
        destacarCampoErro('nome_completo', 'Nome completo é obrigatório e não pode estar vazio');
        
        // Mostrar notificação também
        const mensagemErro = `
            <p><strong>❌ Erro ao atualizar membro</strong></p>
            <p><strong>Campo obrigatório inválido:</strong> Nome Completo</p>
            <p><strong>Solução:</strong> Preencha o campo Nome Completo com um valor válido.</p>
        `;
        mostrarNotificacao(mensagemErro, 'error');
        return;
    }
    
    try {
        // Log para debug
        console.log('Dados a serem enviados:', dadosProcessados);
        console.log('ID do membro:', membroId);
        console.log('Nome completo:', dadosProcessados.nome_completo);
        
        // Chamar API real
        const response = await MembrosAPI.atualizar(membroId, dadosProcessados);
        
        if (response && response.success) {
            mostrarNotificacao('Membro atualizado com sucesso!', 'success');
            fecharModal();
            carregarMembros(); // Recarregar lista
            // Invalidar cache do membro específico
            if (typeof invalidarCacheMembro === 'function') {
                invalidarCacheMembro(membroId);
            }
        } else {
            const errorMessage = response?.error || 'Erro desconhecido';
            let mensagemErro = '';
            let campoErro = null;
            
            // Tratar erros específicos com mensagens detalhadas
            if (errorMessage.includes('CPF já cadastrado')) {
                destacarCampoErro('cpf', 'Este CPF já está cadastrado para outro membro');
                mensagemErro = `
                    <p><strong>❌ Erro ao atualizar membro</strong></p>
                    <p><strong>Campo com problema:</strong> CPF</p>
                    <p><strong>Solução:</strong> Use um CPF diferente ou deixe o campo vazio.</p>
                `;
            } else if (errorMessage.includes('Email já cadastrado')) {
                destacarCampoErro('email', 'Este email já está cadastrado para outro membro');
                mensagemErro = `
                    <p><strong>❌ Erro ao atualizar membro</strong></p>
                    <p><strong>Campo com problema:</strong> Email</p>
                    <p><strong>Solução:</strong> Use um email diferente ou deixe o campo vazio.</p>
                `;
            } else if (errorMessage.includes('CPF inválido')) {
                destacarCampoErro('cpf', 'CPF inválido - verifique os dígitos verificadores');
                mensagemErro = `
                    <p><strong>❌ Erro ao atualizar membro</strong></p>
                    <p><strong>Campo com problema:</strong> CPF</p>
                    <p><strong>Solução:</strong> Verifique se digitou corretamente os 11 dígitos ou deixe o campo vazio.</p>
                `;
            } else if (errorMessage.includes('Email inválido')) {
                destacarCampoErro('email', 'Formato de email inválido');
                mensagemErro = `
                    <p><strong>❌ Erro ao atualizar membro</strong></p>
                    <p><strong>Campo com problema:</strong> Email</p>
                    <p><strong>Solução:</strong> Verifique o endereço digitado (ex: nome@exemplo.com)</p>
                `;
            } else if (errorMessage.includes('Nome completo') || errorMessage.includes('Campo obrigatório') || errorMessage.includes('não pode estar vazio')) {
                // Detectar qual campo obrigatório está faltando
                let campoId = 'nome_completo';
                let campoNome = 'Nome Completo';
                
                if (errorMessage.includes('nome completo') || errorMessage.includes('Nome completo')) {
                    campoId = 'nome_completo';
                    campoNome = 'Nome Completo';
                }
                
                destacarCampoErro(campoId, 'Este campo é obrigatório e não pode estar vazio');
                mensagemErro = `
                    <p><strong>❌ Erro ao atualizar membro</strong></p>
                    <p><strong>Campo obrigatório não preenchido:</strong> ${campoNome}</p>
                    <p><strong>Solução:</strong> Preencha o campo ${campoNome} antes de salvar.</p>
                `;
            } else {
                mensagemErro = `
                    <p><strong>❌ Erro ao atualizar membro</strong></p>
                    <p><strong>Detalhes:</strong> ${errorMessage}</p>
                    <p>Verifique os dados informados e tente novamente.</p>
                `;
            }
            
            mostrarNotificacao(mensagemErro, 'error');
        }
    } catch (error) {
        console.error('Erro ao atualizar membro:', error);
        
        // Extrair mensagem de erro do responseData se disponível
        let errorMessage = error.message || 'Erro desconhecido';
        if (error.responseData && error.responseData.error) {
            errorMessage = error.responseData.error;
        }
        
        console.log('Mensagem de erro extraída:', errorMessage);
        
        // Tratar erros específicos com mensagens detalhadas
        let mensagemErro = '';
        let campoErro = null;
        
        if (errorMessage.includes('CPF já cadastrado')) {
            destacarCampoErro('cpf', 'Este CPF já está cadastrado para outro membro');
            mensagemErro = `
                <p><strong>❌ Erro ao atualizar membro</strong></p>
                <p><strong>Campo com problema:</strong> CPF</p>
                <p><strong>Solução:</strong> Use um CPF diferente ou deixe o campo vazio.</p>
            `;
        } else if (errorMessage.includes('Email já cadastrado')) {
            destacarCampoErro('email', 'Este email já está cadastrado para outro membro');
            mensagemErro = `
                <p><strong>❌ Erro ao atualizar membro</strong></p>
                <p><strong>Campo com problema:</strong> Email</p>
                <p><strong>Solução:</strong> Use um email diferente ou deixe o campo vazio.</p>
            `;
        } else if (errorMessage.includes('CPF inválido')) {
            destacarCampoErro('cpf', 'CPF inválido - verifique os dígitos verificadores');
            mensagemErro = `
                <p><strong>❌ Erro ao atualizar membro</strong></p>
                <p><strong>Campo com problema:</strong> CPF</p>
                <p><strong>Solução:</strong> Verifique se digitou corretamente os 11 dígitos ou deixe o campo vazio.</p>
            `;
        } else if (errorMessage.includes('Email inválido')) {
            destacarCampoErro('email', 'Formato de email inválido');
            mensagemErro = `
                <p><strong>❌ Erro ao atualizar membro</strong></p>
                <p><strong>Campo com problema:</strong> Email</p>
                <p><strong>Solução:</strong> Verifique o endereço digitado (ex: nome@exemplo.com)</p>
            `;
        } else if (errorMessage.includes('Nome completo') || errorMessage.includes('Campo obrigatório') || errorMessage.includes('não pode estar vazio')) {
            // Detectar qual campo obrigatório está faltando
            let campoId = 'nome_completo';
            let campoNome = 'Nome Completo';
            
            if (errorMessage.includes('nome completo') || errorMessage.includes('Nome completo')) {
                campoId = 'nome_completo';
                campoNome = 'Nome Completo';
            }
            
            destacarCampoErro(campoId, 'Este campo é obrigatório e não pode estar vazio');
            mensagemErro = `
                <p><strong>❌ Erro ao atualizar membro</strong></p>
                <p><strong>Campo obrigatório não preenchido:</strong> ${campoNome}</p>
                <p><strong>Solução:</strong> Preencha o campo ${campoNome} antes de salvar.</p>
            `;
        } else {
            mensagemErro = `
                <p><strong>❌ Erro ao atualizar membro</strong></p>
                <p><strong>Detalhes:</strong> ${errorMessage}</p>
                <p>Verifique os dados informados e tente novamente.</p>
            `;
        }
        
        mostrarNotificacao(mensagemErro, 'error');
    }
}

// =====================================================
// UTILITÁRIOS
// =====================================================

/**
 * Processa dados do formulário de membro (simplificado)
 */
function processarDadosMembro(dados) {
    const dadosProcessados = { ...dados };
    
    // Converter checkbox para boolean ("on" => true) e garantir tipo consistente (0 ou 1)
    dadosProcessados.paroquiano = dadosProcessados.paroquiano === 'on' || dadosProcessados.paroquiano === true || dadosProcessados.paroquiano === '1';
    
    // Processar documentos
    if (dadosProcessados.documentos) {
        // Se documentos vier como objeto (FormData), converter para array
        if (!Array.isArray(dadosProcessados.documentos)) {
            const documentosObj = dadosProcessados.documentos;
            const documentosArray = [];
            
            // Agrupar por índice
            const indices = new Set();
            Object.keys(documentosObj).forEach(key => {
                const match = key.match(/documentos\[(\d+)\]\[(.+)\]/);
                if (match) {
                    indices.add(match[1]);
                }
            });
            
            indices.forEach(index => {
                const documento = {};
                Object.keys(documentosObj).forEach(key => {
                    const match = key.match(/documentos\[(\d+)\]\[(.+)\]/);
                    if (match && match[1] === index) {
                        const campo = match[2];
                        documento[campo] = documentosObj[key];
                    }
                });
                
                // Só adicionar se tiver tipo_documento e numero
                if (documento.tipo_documento && documento.numero) {
                    documentosArray.push(documento);
                }
            });
            
            dadosProcessados.documentos = documentosArray;
        }
        
        // Limpar documentos vazios (sem tipo_documento ou numero)
        dadosProcessados.documentos = dadosProcessados.documentos.filter(doc => 
            doc && doc.tipo_documento && doc.numero && doc.numero.trim() !== ''
        );
        
        // Se não houver documentos válidos, não enviar o campo
        if (dadosProcessados.documentos.length === 0) {
            delete dadosProcessados.documentos;
        }
    }
    
    // Adicionar campos obrigatórios se não existirem
    if (!dadosProcessados.status) {
        dadosProcessados.status = 'ativo';
    }
    
    // Garantir que paroquiano seja boolean
    if (dadosProcessados.paroquiano === undefined) {
        dadosProcessados.paroquiano = false;
    }
    
    // GARANTIR que nome_completo está presente e não é null/undefined
    // É OBRIGATÓRIO e deve ser uma string (mesmo que vazia, será validado antes)
    if (!dadosProcessados.hasOwnProperty('nome_completo') || dadosProcessados.nome_completo === null || dadosProcessados.nome_completo === undefined) {
        dadosProcessados.nome_completo = dados.nome_completo || '';
    }
    
    // Garantir que nome_completo seja string (trim será feito no backend)
    if (typeof dadosProcessados.nome_completo !== 'string') {
        dadosProcessados.nome_completo = String(dadosProcessados.nome_completo || '');
    }
    
    // Apenas converter strings vazias para null para campos opcionais
    // IMPORTANTE: nome_completo NÃO está na lista de opcionais pois é obrigatório
    const camposOpcionais = ['apelido', 'data_nascimento', 'sexo', 'celular_whatsapp', 'email', 'telefone_fixo', 'rua', 'numero', 'bairro', 'cidade', 'uf', 'cep', 'rg', 'comunidade_ou_capelania', 'data_entrada', 'observacoes_pastorais', 'foto_url', 'motivo_bloqueio', 'frequencia', 'periodo'];
    
    camposOpcionais.forEach(campo => {
        if (dadosProcessados[campo] === '') {
            dadosProcessados[campo] = null;
        }
    });
    
    // Tratamento especial para CPF: limpar formatação e verificar se está realmente vazio
    // SEMPRE incluir o campo CPF no objeto (mesmo que seja null), para permitir limpar o campo no banco
    
    // Log do CPF original antes do processamento
    console.log('processarDadosMembro: CPF original (antes do processamento):', dadosProcessados.cpf);
    console.log('processarDadosMembro: tipo do CPF original:', typeof dadosProcessados.cpf);
    
    if (dadosProcessados.cpf !== null && dadosProcessados.cpf !== undefined && dadosProcessados.cpf !== '') {
        // Remover formatação (pontos, hífens, espaços)
        const cpfLimpo = dadosProcessados.cpf.replace(/[^0-9]/g, '');
        
        console.log('processarDadosMembro: CPF após limpar formatação:', cpfLimpo);
        console.log('processarDadosMembro: CPF limpo tem', cpfLimpo.length, 'dígitos');
        
        // Se após limpar estiver vazio, enviar como null
        if (cpfLimpo === '') {
            console.log('processarDadosMembro: CPF vazio após limpar, definindo como null');
            dadosProcessados.cpf = null; // Garantir que o campo existe no objeto
        } else {
            // Se tiver números, usar o CPF limpo
            console.log('processarDadosMembro: CPF limpo será enviado:', cpfLimpo);
            dadosProcessados.cpf = cpfLimpo;
        }
    } else {
        // Se não foi fornecido ou está vazio, definir explicitamente como null
        console.log('processarDadosMembro: CPF não fornecido ou vazio, definindo como null');
        dadosProcessados.cpf = null;
    }
    
    // Garantir que o backend receba o valor de paroquiano como inteiro (tinyint)
    dadosProcessados.paroquiano = dadosProcessados.paroquiano ? 1 : 0;
    
    // Log para debug
    console.log('processarDadosMembro: nome_completo processado:', dadosProcessados.nome_completo);
    console.log('processarDadosMembro: tipo de nome_completo:', typeof dadosProcessados.nome_completo);
    console.log('processarDadosMembro: CPF FINAL que será enviado:', dadosProcessados.cpf);
    console.log('processarDadosMembro: tipo do CPF FINAL:', typeof dadosProcessados.cpf);
    console.log('processarDadosMembro: documentos processados:', dadosProcessados.documentos);
    
    return dadosProcessados;
}

/**
 * Destaca campo com erro no formulário e mostra mensagem abaixo do campo
 */
function destacarCampoErro(campoId, mensagem) {
    console.log(`[destacarCampoErro] Iniciando: campoId="${campoId}", mensagem="${mensagem}"`);
    
    // Função para tentar aplicar o destaque
    const tentarAplicarDestaque = (tentativa = 1) => {
        const campo = document.getElementById(campoId);
        
        if (!campo) {
            if (tentativa < 5) {
                console.log(`[destacarCampoErro] Tentativa ${tentativa}: Campo não encontrado, tentando novamente...`);
                setTimeout(() => tentarAplicarDestaque(tentativa + 1), 100 * tentativa);
            } else {
                console.error(`[destacarCampoErro] Campo '${campoId}' não encontrado após ${tentativa} tentativas`);
            }
            return;
        }
        
        console.log(`[destacarCampoErro] Campo encontrado na tentativa ${tentativa}:`, campo);
        aplicarDestaqueErro(campo, mensagem);
    };
    
    // Iniciar tentativa após pequeno delay para garantir que o modal está renderizado
    setTimeout(() => tentarAplicarDestaque(1), 100);
}

/**
 * Aplica o destaque e mensagem de erro no campo
 */
function aplicarDestaqueErro(campo, mensagem) {
    console.log(`[aplicarDestaqueErro] Campo:`, campo);
    console.log(`[aplicarDestaqueErro] Mensagem:`, mensagem);
    
    // Remover destaque anterior
    campo.classList.remove('is-invalid', 'border-danger');
    
    // Adicionar destaque visual
    campo.classList.add('is-invalid', 'border-danger');
    campo.style.borderWidth = '2px';
    campo.style.borderColor = '#dc3545';
    campo.style.borderStyle = 'solid';
    
    // Encontrar o container do campo (form-group)
    let container = campo.closest('.form-group');
    if (!container) {
        // Se não encontrar form-group, procurar por div pai que contenha o campo
        container = campo.parentElement;
        console.log('[aplicarDestaqueErro] Form-group não encontrado, usando parentElement:', container);
        
        // Tentar encontrar o form-group indo mais acima na hierarquia
        let parent = campo.parentElement;
        while (parent && parent !== document.body) {
            if (parent.classList && parent.classList.contains('form-group')) {
                container = parent;
                console.log('[aplicarDestaqueErro] Form-group encontrado na hierarquia:', container);
                break;
            }
            parent = parent.parentElement;
        }
    }
    
    if (!container) {
        console.error('[aplicarDestaqueErro] Container não encontrado para o campo');
        return;
    }
    
    console.log('[aplicarDestaqueErro] Container encontrado:', container);
    
    // Remover mensagem de erro anterior (procurar em todo o container)
    const feedbackAnterior = container.querySelector('.invalid-feedback[data-field-error="' + campo.id + '"]');
    if (feedbackAnterior) {
        console.log('[aplicarDestaqueErro] Removendo feedback anterior');
        feedbackAnterior.remove();
    }
    
    // Criar mensagem de erro pequena abaixo do campo
    const feedback = document.createElement('div');
    feedback.className = 'invalid-feedback';
    feedback.setAttribute('data-field-error', campo.id);
    feedback.setAttribute('id', 'feedback-' + campo.id);
    
    // Aplicar estilos inline para garantir que apareça
    feedback.style.cssText = `
        display: block !important;
        width: 100%;
        font-size: 0.875rem !important;
        margin-top: 0.25rem !important;
        color: #dc3545 !important;
        font-weight: 400;
        padding: 0;
        background-color: transparent;
        border: none;
        line-height: 1.4;
        opacity: 1 !important;
        visibility: visible !important;
    `;
    
    feedback.textContent = mensagem; // Usar textContent para evitar XSS
    
    console.log('[aplicarDestaqueErro] Mensagem criada:', feedback);
    console.log('[aplicarDestaqueErro] HTML da mensagem:', feedback.outerHTML);
    
    // Adicionar mensagem no final do container (form-group)
    container.appendChild(feedback);
    
    console.log('[aplicarDestaqueErro] Mensagem adicionada ao container');
    console.log('[aplicarDestaqueErro] Parent da mensagem:', feedback.parentElement);
    console.log('[aplicarDestaqueErro] Container completo:', container.outerHTML.substring(0, 500));
    
    // Verificar se a mensagem foi realmente adicionada
    if (!feedback.parentElement) {
        console.error('[aplicarDestaqueErro] ERRO: Mensagem não foi adicionada ao DOM!');
        // Tentar método alternativo
        try {
            campo.insertAdjacentElement('afterend', feedback);
            console.log('[aplicarDestaqueErro] Mensagem adicionada usando insertAdjacentElement');
        } catch (e) {
            console.error('[aplicarDestaqueErro] Erro ao adicionar mensagem:', e);
        }
    }
    
    // Aguardar um pouco e verificar se está visível
    setTimeout(() => {
        const feedbackVerificacao = document.getElementById('feedback-' + campo.id) || 
                                   container.querySelector('.invalid-feedback[data-field-error="' + campo.id + '"]');
        
        if (feedbackVerificacao) {
            const estilos = window.getComputedStyle(feedbackVerificacao);
            console.log('✅ Mensagem de erro está no DOM');
            console.log('Estilos computados:', {
                display: estilos.display,
                visibility: estilos.visibility,
                opacity: estilos.opacity,
                color: estilos.color,
                fontSize: estilos.fontSize,
                marginTop: estilos.marginTop,
                width: estilos.width
            });
            
            // Verificar se está realmente visível
            if (estilos.display === 'none' || estilos.visibility === 'hidden' || estilos.opacity === '0') {
                console.warn('⚠️ Mensagem criada mas não está visível! Forçando display...');
                feedbackVerificacao.style.display = 'block';
                feedbackVerificacao.style.visibility = 'visible';
                feedbackVerificacao.style.opacity = '1';
            }
        } else {
            console.error('❌ Mensagem de erro não encontrada no DOM após inserção');
        }
        
        // Focar no campo e scroll suave
        campo.focus();
        campo.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 150);
    
    // Remover destaque quando o usuário começar a digitar (se o campo for editável)
    if (campo.tagName === 'INPUT' || campo.tagName === 'TEXTAREA' || campo.tagName === 'SELECT') {
        const removerErroAoDigitar = () => {
            campo.classList.remove('is-invalid', 'border-danger');
            campo.style.borderWidth = '';
            campo.style.borderColor = '';
            campo.style.borderStyle = '';
            const feedbackParaRemover = document.getElementById('feedback-' + campo.id) ||
                                       container.querySelector('.invalid-feedback[data-field-error="' + campo.id + '"]');
            if (feedbackParaRemover) {
                feedbackParaRemover.remove();
            }
            campo.removeEventListener('input', removerErroAoDigitar);
            campo.removeEventListener('change', removerErroAoDigitar);
        };
        
        campo.addEventListener('input', removerErroAoDigitar, { once: true });
        campo.addEventListener('change', removerErroAoDigitar, { once: true });
    }
}

/**
 * Remove destaque de erro de um campo
 */
function removerDestaqueErro(campoId) {
    const campo = document.getElementById(campoId);
    if (campo) {
        campo.classList.remove('is-invalid', 'border-danger');
        campo.style.borderWidth = '';
        const feedback = campo.parentElement.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }
}

/**
 * Mostra notificação (aceita HTML)
 */
function mostrarNotificacao(mensagem, tipo = 'info', campoErro = null) {
    const notificacao = document.createElement('div');
    notificacao.className = `alert alert-${tipo} alert-dismissible fade show position-fixed`;
    notificacao.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 350px; max-width: 500px;';
    
    // Formatar mensagem com parágrafos se necessário
    let mensagemFormatada = mensagem;
    if (typeof mensagem === 'string' && !mensagem.includes('<p>') && !mensagem.includes('<br>')) {
        // Se a mensagem não tem HTML, adicionar tags <p> para melhor formatação
        mensagemFormatada = `<p style="margin-bottom: 0;">${mensagem}</p>`;
    }
    
    notificacao.innerHTML = `
        <div style="font-size: 0.95rem;">
            ${mensagemFormatada}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    `;
    
    document.body.appendChild(notificacao);
    
    // Nota: O destaque do campo agora é feito diretamente onde o erro é detectado,
    // não mais através do parâmetro campoErro da notificação
    
    // Remove automaticamente após 8 segundos (aumentado para dar tempo de ler)
    setTimeout(() => {
        if (notificacao.parentNode) {
            notificacao.remove();
        }
    }, 8000);
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

// =====================================================
// FUNÇÕES PARA SITUAÇÃO PAROQUIAL
// =====================================================

/**
 * Alterna a exibição da seção de situação paroquial
 */
function toggleSecaoPastoral() {
    const detalhes = document.getElementById('detalhes-situacao-pastoral');
    const icone = document.getElementById('icone-secao-pastoral');
    
    if (detalhes.classList.contains('show')) {
        detalhes.classList.remove('show');
        icone.classList.remove('fa-chevron-up');
        icone.classList.add('fa-chevron-down');
    } else {
        detalhes.classList.add('show');
        icone.classList.remove('fa-chevron-down');
        icone.classList.add('fa-chevron-up');
        
        // Carregar pastorais do membro se ainda não foram carregadas
        if (!detalhes.dataset.pastoraisCarregadas) {
            carregarPastoraisMembro();
        }
    }
}

/**
 * Carrega as pastorais de um membro específico
 */
async function carregarPastoraisMembro() {
    const detalhes = document.getElementById('detalhes-situacao-pastoral');
    if (!detalhes) return;
    
    // Obter ID do membro do modal
    const modalAtual = document.querySelector('.modal.show');
    if (!modalAtual) return;
    
    const membroId = modalAtual.dataset.membroId;
    if (!membroId) return;
    
    try {
        const response = await fetch(`api/membros/${membroId}/pastorais`);
        const result = await response.json();
        
        let pastorais = [];
        if (result.success && result.data) {
            pastorais = Array.isArray(result.data) ? result.data : [];
        }
        
        console.log('Pastorais do membro:', pastorais);
        atualizarTabelaPastorais(pastorais);
        detalhes.dataset.pastoraisCarregadas = 'true';
    } catch (error) {
        console.error('Erro ao carregar pastorais:', error);
        atualizarTabelaPastorais([]);
    }
}

/**
 * Atualiza a tabela de pastorais
 */
function atualizarTabelaPastorais(pastorais) {
    const tbody = document.querySelector('#tabela-pastorais-membro tbody');
    if (!tbody) return;
    
    if (pastorais.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted">
                    <i class="fas fa-info-circle"></i> Nenhuma pastoral vinculada
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = pastorais.map(p => `
        <tr>
            <td>${p.nome || '-'}</td>
            <td>
                <span class="badge badge-${p.status_vinculo === 'ativo' ? 'success' : 'secondary'}">
                    ${p.status_vinculo === 'ativo' ? 'Ativo' : 'Inativo'}
                </span>
            </td>
            <td>${p.data_inicio ? formatarData(p.data_inicio) : '-'}</td>
            <td>${p.funcao || 'Membro'}</td>
        </tr>
    `).join('');
}

/**
 * Formata data para exibição
 */
function formatarData(data) {
    if (!data) return '-';
    try {
        const date = new Date(data);
        return date.toLocaleDateString('pt-BR');
    } catch (e) {
        return data;
    }
}

/**
 * Retorna a classe do badge de status
 */
function getStatusBadgeClass(status) {
    const classes = {
        'ativo': 'success',
        'afastado': 'warning',
        'em_discernimento': 'info',
        'bloqueado': 'danger'
    };
    return classes[status] || 'secondary';
}

/**
 * Retorna o texto do status
 */
function getStatusText(status) {
    const texts = {
        'ativo': 'Ativo',
        'afastado': 'Afastado',
        'em_discernimento': 'Em Discernimento',
        'bloqueado': 'Bloqueado'
    };
    return texts[status] || status;
}

// Exportar funções para o escopo global
window.toggleSecaoPastoral = toggleSecaoPastoral;
