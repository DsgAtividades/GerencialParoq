/**
 * JavaScript para Página de Detalhes da Pastoral
 * Módulo de Cadastro de Membros
 */

// Estado da página com cache
let PastoralState = {
    pastoral: null,
    membros: [],
    eventos: [],
    // Cache local
    cache: new Map(),
    cacheValidoPor: 5 * 60 * 1000 // 5 minutos
};

// Sistema de cache
function obterDoCache(key) {
    const cached = PastoralState.cache.get(key);
    if (!cached) return null;
    
    const agora = Date.now();
    if (agora - cached.timestamp > PastoralState.cacheValidoPor) {
        PastoralState.cache.delete(key);
        return null;
    }
    
    return cached.data;
}

function salvarNoCache(key, data) {
    PastoralState.cache.set(key, {
        data: data,
        timestamp: Date.now()
    });
}

/**
 * Carrega dados da pastoral com cache e otimização
 */
async function carregarDadosPastoral(pastoralId) {
    try {
        console.log('Carregando dados da pastoral:', pastoralId);
        
        // Verificar cache primeiro
        const cached = obterDoCache('pastoral-' + pastoralId);
        if (cached) {
            console.log('Usando dados do cache');
            aplicarDadosCached(cached);
            return;
        }
        
        // Carregar todos os dados em paralelo para melhor performance
        const [pastoralResponse, membrosResponse, eventosResponse] = await Promise.all([
            fetch(`api/pastorais/${pastoralId}`),
            fetch(`api/pastorais/${pastoralId}/membros`),
            fetch(`api/pastorais/${pastoralId}/eventos`)
        ]);
        
        const pastoralJson = await pastoralResponse.json();
        const membrosJson = await membrosResponse.json();
        const eventosJson = await eventosResponse.json();
        
        if (!pastoralJson.success) {
            mostrarNotificacao('Erro ao carregar dados da pastoral', 'error');
            return;
        }
        
        // Armazenar dados
        PastoralState.pastoral = pastoralJson.data;
        PastoralState.membros = membrosJson.success ? (membrosJson.data || []) : [];
        PastoralState.eventos = eventosJson.success ? (eventosJson.data || []) : [];
        
        // Salvar no cache
        const dadosParaCache = {
            pastoral: PastoralState.pastoral,
            membros: PastoralState.membros,
            eventos: PastoralState.eventos
        };
        salvarNoCache('pastoral-' + pastoralId, dadosParaCache);
        
        // Atualizar interface
        atualizarInterface();
        
        // Garantir que os contadores sejam atualizados após um pequeno delay
        // (para caso os elementos ainda não estejam no DOM)
        setTimeout(() => {
            atualizarTabelaMembros();
            atualizarTabelaEventos();
        }, 200);
        
    } catch (error) {
        console.error('Erro ao carregar dados da pastoral:', error);
        mostrarNotificacao('Erro ao carregar dados da pastoral: ' + error.message, 'error');
        
        // Mesmo em caso de erro, tentar atualizar contadores com valores vazios
        PastoralState.membros = [];
        PastoralState.eventos = [];
        setTimeout(() => {
            atualizarTabelaMembros();
            atualizarTabelaEventos();
        }, 200);
    }
}

/**
 * Aplica dados do cache
 */
function aplicarDadosCached(dados) {
    PastoralState.pastoral = dados.pastoral;
    PastoralState.membros = dados.membros || [];
    PastoralState.eventos = dados.eventos || [];
    atualizarInterface();
    
    // Garantir que os contadores sejam atualizados após aplicar cache
    setTimeout(() => {
        atualizarTabelaMembros();
        atualizarTabelaEventos();
    }, 100);
}

/**
 * Atualiza toda a interface
 */
function atualizarInterface() {
    // Preencher informações básicas
    const nomeEl = document.getElementById('pastoral-nome');
    const descEl = document.getElementById('pastoral-descricao');
    if (nomeEl) nomeEl.textContent = PastoralState.pastoral?.nome || 'Carregando...';
    if (descEl) descEl.textContent = PastoralState.pastoral?.finalidade_descricao || 'Sem descrição';
    
    // Atualizar todas as seções
    atualizarMetricas();
    atualizarInfoPastoral();
    atualizarTabelaMembros(); // Já atualiza o contador internamente
    atualizarTabelaEventos(); // Já atualiza o contador internamente
    atualizarCoordenadores();
}

/**
 * Carrega membros da pastoral
 */
async function carregarMembrosPastoral(pastoralId) {
    try {
        const response = await fetch(`api/pastorais/${pastoralId}/membros`);
        const data = await response.json();
        
        if (data.success) {
            PastoralState.membros = data.data || [];
            atualizarTabelaMembros();
            atualizarMetricas(); // Atualizar métricas também
        } else {
            PastoralState.membros = [];
            atualizarTabelaMembros();
        }
    } catch (error) {
        console.error('Erro ao carregar membros:', error);
        PastoralState.membros = [];
        atualizarTabelaMembros();
    }
}

/**
 * Carrega eventos da pastoral
 */
async function carregarEventosPastoral(pastoralId) {
    try {
        const response = await fetch(`api/pastorais/${pastoralId}/eventos`);
        const data = await response.json();
        
        if (data.success) {
            PastoralState.eventos = data.data || [];
            atualizarTabelaEventos();
            atualizarMetricas(); // Atualizar métricas também
        } else {
            PastoralState.eventos = [];
            atualizarTabelaEventos();
        }
    } catch (error) {
        console.error('Erro ao carregar eventos:', error);
        PastoralState.eventos = [];
        atualizarTabelaEventos();
    }
}

/**
 * Atualiza métricas da página
 */
function atualizarMetricas() {
    const totalMembros = PastoralState.membros.length;
    const membrosAtivos = PastoralState.membros.filter(m => m.status_vinculo === 'ativo').length;
    
    // Contar coordenadores (coordenador + vice-coordenador)
    let totalCoordenadores = 0;
    if (PastoralState.pastoral?.coordenador_id) totalCoordenadores++;
    if (PastoralState.pastoral?.vice_coordenador_id) totalCoordenadores++;
    
    const totalEventos = PastoralState.eventos.length;
    
    document.getElementById('total-membros').textContent = totalMembros;
    document.getElementById('membros-ativos').textContent = membrosAtivos;
    document.getElementById('total-coordenadores').textContent = totalCoordenadores;
    document.getElementById('total-eventos').textContent = totalEventos;
    
    // Atualizar informações da pastoral
    atualizarInfoPastoral();
}

/**
 * Atualiza informações da pastoral
 */
function atualizarInfoPastoral() {
    const pastoral = PastoralState.pastoral;
    if (!pastoral) return;
    
    const infoGrid = document.getElementById('info-pastoral');
    let html = '';
    
    if (pastoral.email) {
        html += `
            <div class="info-item">
                <div class="icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="content">
                    <div class="label">Email</div>
                    <div class="value">${pastoral.email}</div>
                </div>
            </div>
        `;
    }
    
    if (pastoral.whatsapp) {
        html += `
            <div class="info-item">
                <div class="icon" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div class="content">
                    <div class="label">WhatsApp</div>
                    <div class="value">${pastoral.whatsapp}</div>
                </div>
            </div>
        `;
    }
    
    if (pastoral.tipo) {
        html += `
            <div class="info-item">
                <div class="icon" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                    <i class="fas fa-tag"></i>
                </div>
                <div class="content">
                    <div class="label">Tipo</div>
                    <div class="value">${pastoral.tipo}</div>
                </div>
            </div>
        `;
    }
    
    if (pastoral.comunidade) {
        html += `
            <div class="info-item">
                <div class="icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                    <i class="fas fa-building"></i>
                </div>
                <div class="content">
                    <div class="label">Comunidade/Capela</div>
                    <div class="value">${pastoral.comunidade}</div>
                </div>
            </div>
        `;
    }
    
    infoGrid.innerHTML = html || '<p>Nenhuma informação adicional disponível.</p>';
}

/**
 * Atualiza tabela de membros
 */
function atualizarTabelaMembros() {
    const tbody = document.getElementById('tabela-membros');
    if (!tbody) return;
    
    const membros = Array.isArray(PastoralState.membros) ? PastoralState.membros : [];
    
    if (membros.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i class="fas fa-users"></i> Nenhum membro encontrado
                </td>
            </tr>
        `;
        
        // Atualizar contador
        const totalMembros = document.getElementById('total-membros-pastoral');
        if (totalMembros) {
            totalMembros.textContent = '0 membros';
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
                        <strong>${window.Sanitizer ? window.Sanitizer.escapeHtml(membro.nome_completo || '-') : (membro.nome_completo || '-')}</strong>
                        ${membro.apelido ? `<br><small class="text-muted">${window.Sanitizer ? window.Sanitizer.escapeHtml(membro.apelido) : membro.apelido}</small>` : ''}
                    </div>
                </div>
            </td>
            <td style="text-align: left;">${membro.email ? `<i class="fas fa-envelope"></i> ${window.Sanitizer ? window.Sanitizer.escapeHtml(membro.email) : membro.email}` : '-'}</td>
            <td style="text-align: left;">${membro.telefone ? `<i class="fas fa-phone"></i> ${window.Sanitizer ? window.Sanitizer.escapeHtml(membro.telefone) : membro.telefone}` : '-'}</td>
            <td>${membro.funcao ? (window.Sanitizer ? window.Sanitizer.escapeHtml(membro.funcao) : membro.funcao) : '-'}</td>
            <td><span class="badge badge-${getStatusClass(membro.status)}">${getStatusText(membro.status)}</span></td>
            <td>
                <div class="d-flex gap-1">
                <button class="btn btn-sm btn-secondary" onclick="visualizarMembro('${membro.id}')" title="Visualizar">
                    <i class="fas fa-eye"></i>
                </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    // Atualizar contador - sempre atualizar, mesmo se elemento não existir ainda
    const totalMembros = document.getElementById('total-membros-pastoral');
    if (totalMembros) {
        const total = membros.length;
        const texto = total === 1 ? '1 membro' : `${total} membros`;
        totalMembros.textContent = texto;
    } else {
        // Se o elemento não existe ainda, tentar novamente após um pequeno delay
        setTimeout(() => {
            const el = document.getElementById('total-membros-pastoral');
            if (el) {
                const total = membros.length;
                const texto = total === 1 ? '1 membro' : `${total} membros`;
                el.textContent = texto;
            }
        }, 100);
    }
}

/**
 * Atualiza tabela de eventos
 */
function atualizarTabelaEventos() {
    const tbody = document.getElementById('tabela-eventos');
    if (!tbody) return;
    
    const eventos = Array.isArray(PastoralState.eventos) ? PastoralState.eventos : [];
    
    if (eventos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i class="fas fa-calendar"></i> Nenhum evento encontrado
                </td>
            </tr>
        `;
        
        // Atualizar contador
        const totalEventos = document.getElementById('total-eventos-pastoral');
        if (totalEventos) {
            totalEventos.textContent = '0 eventos';
        }
        return;
    }
    
    tbody.innerHTML = eventos.map(evento => `
        <tr>
            <td>${formatarData(evento.data)}</td>
            <td style="text-align: left;"><strong>${window.Sanitizer ? window.Sanitizer.escapeHtml(evento.nome || '-') : (evento.nome || '-')}</strong></td>
            <td style="text-align: left;"><span class="badge badge-secondary">${window.Sanitizer ? window.Sanitizer.escapeHtml(evento.tipo || '-') : (evento.tipo || '-')}</span></td>
            <td style="text-align: left;"><i class="fas fa-clock"></i> ${evento.horario ? evento.horario.substring(0, 5) : '-'}</td>
            <td style="text-align: left;"><i class="fas fa-map-marker-alt"></i> ${evento.local ? (window.Sanitizer ? window.Sanitizer.escapeHtml(evento.local) : evento.local) : '-'}</td>
            <td>
                <div class="d-flex gap-1">
                <button class="btn btn-sm btn-primary" onclick="verDetalhesEvento('${evento.id}')" title="Ver Detalhes">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="editarEvento('${evento.id}')" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="excluirEvento('${evento.id}')" title="Excluir">
                    <i class="fas fa-trash"></i>
                </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    // Atualizar contador - sempre atualizar, mesmo se elemento não existir ainda
    const totalEventos = document.getElementById('total-eventos-pastoral');
    if (totalEventos) {
        const total = eventos.length;
        const texto = total === 1 ? '1 evento' : `${total} eventos`;
        totalEventos.textContent = texto;
    } else {
        // Se o elemento não existe ainda, tentar novamente após um pequeno delay
        setTimeout(() => {
            const el = document.getElementById('total-eventos-pastoral');
            if (el) {
                const total = eventos.length;
                const texto = total === 1 ? '1 evento' : `${total} eventos`;
                el.textContent = texto;
            }
        }, 100);
    }
}

// Variável global para evento em edição
let eventoEditando = null;

/**
 * Abre modal para criar novo evento
 */
function abrirModalEvento(evento = null) {
    eventoEditando = evento;
    const isEdicao = evento !== null;
    
    const modalHTML = `
        <div id="modal-evento" class="modal fade show" style="display: block;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-calendar"></i> ${isEdicao ? 'Editar Evento' : 'Novo Evento'}
                        </h5>
                        <button type="button" class="close" onclick="fecharModalEvento()" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="form-evento">
                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label for="evento-nome">Nome do Evento *</label>
                                    <input type="text" class="form-control" id="evento-nome" name="nome" required 
                                           value="${evento ? (evento.nome || '') : ''}" 
                                           placeholder="Ex: Reunião Mensal">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="evento-tipo">Tipo</label>
                                    <input type="text" class="form-control" id="evento-tipo" name="tipo" 
                                           value="${evento ? (evento.tipo || '') : ''}" 
                                           placeholder="Ex: Reunião, Tarde de Recreação">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="evento-data">Data do Evento *</label>
                                    <input type="date" class="form-control" id="evento-data" name="data_evento" required 
                                           value="${evento ? (evento.data || '') : ''}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="evento-horario">Horário</label>
                                    <input type="time" class="form-control" id="evento-horario" name="horario" 
                                           value="${evento && evento.horario ? evento.horario.substring(0, 5) : ''}">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="evento-local">Local</label>
                                    <input type="text" class="form-control" id="evento-local" name="local" 
                                           value="${evento ? (evento.local || '') : ''}" 
                                           placeholder="Ex: Sala de Reuniões">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="evento-responsavel">Responsável</label>
                                    <div class="autocomplete-wrapper">
                                        <input type="text" class="form-control autocomplete-input" 
                                               id="evento-responsavel" 
                                               name="responsavel_nome" 
                                               autocomplete="off"
                                               value="${evento && evento.responsavel_nome ? (evento.responsavel_nome || '') : ''}" 
                                               placeholder="Digite o nome do responsável..." 
                                               onkeyup="buscarMembrosAutocomplete('evento-responsavel', event)"
                                               onblur="fecharAutocomplete('evento-responsavel')"
                                               onfocus="if(this.value) buscarMembrosAutocomplete('evento-responsavel', event)">
                                        <input type="hidden" id="evento-responsavel-id" name="responsavel_id" 
                                               value="${evento ? (evento.responsavel_id || '') : ''}">
                                        <div class="autocomplete-dropdown" id="evento-responsavel-dropdown"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="evento-descricao">Descrição</label>
                                    <textarea class="form-control" id="evento-descricao" name="descricao" rows="3" 
                                              placeholder="Descrição do evento...">${evento ? (evento.descricao || '') : ''}</textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModalEvento()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" onclick="salvarEvento()">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" onclick="fecharModalEvento()"></div>
    `;
    
    // Remover modal anterior se existir
    const modalAnterior = document.getElementById('modal-evento');
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
        document.getElementById('evento-nome').focus();
    }, 100);
}

/**
 * Fecha modal de evento
 */
function fecharModalEvento() {
    const modal = document.getElementById('modal-evento');
    const backdrop = document.querySelector('.modal-backdrop');
    if (modal) modal.remove();
    if (backdrop) backdrop.remove();
    eventoEditando = null;
}

/**
 * Salva evento (criar ou atualizar)
 */
async function salvarEvento() {
    const form = document.getElementById('form-evento');
    if (!form || !form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    // Pegar o ID do campo hidden
    const responsavelId = document.getElementById('evento-responsavel-id')?.value || null;
    const dados = {
        nome: formData.get('nome'),
        tipo: formData.get('tipo') || null,
        data_evento: formData.get('data_evento'),
        horario: formData.get('horario') || null,
        local: formData.get('local') || null,
        responsavel_id: responsavelId,
        descricao: formData.get('descricao') || null
    };
    
    const pastoralId = PastoralState.pastoral?.id;
    if (!pastoralId) {
        mostrarNotificacao('Erro: Pastoral não identificada', 'error');
        return;
    }
    
    try {
        const url = eventoEditando 
            ? `api/pastorais/${pastoralId}/eventos/${eventoEditando.id}`
            : `api/pastorais/${pastoralId}/eventos`;
        
        const method = eventoEditando ? 'PUT' : 'POST';
        
        const btnSalvar = document.querySelector('#modal-evento .btn-primary');
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
            mostrarNotificacao(eventoEditando ? 'Evento atualizado com sucesso!' : 'Evento criado com sucesso!', 'success');
            fecharModalEvento();
            
            // Recarregar eventos
            await carregarEventosPastoral(pastoralId);
        } else {
            mostrarNotificacao(result.error || 'Erro ao salvar evento', 'error');
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = textoOriginal;
        }
    } catch (error) {
        console.error('Erro ao salvar evento:', error);
        mostrarNotificacao('Erro ao salvar evento: ' + error.message, 'error');
        
        const btnSalvar = document.querySelector('#modal-evento .btn-primary');
        if (btnSalvar) {
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="fas fa-save"></i> Salvar';
        }
    }
}

/**
 * Edita um evento
 */
function editarEvento(eventoId) {
    const evento = PastoralState.eventos.find(e => e.id === eventoId);
    if (!evento) {
        mostrarNotificacao('Evento não encontrado', 'error');
        return;
    }
    
    abrirModalEvento(evento);
}

/**
 * Exclui um evento
 */
async function excluirEvento(eventoId) {
    if (!confirm('Tem certeza que deseja excluir este evento?')) {
        return;
    }
    
    const pastoralId = PastoralState.pastoral?.id;
    if (!pastoralId) {
        mostrarNotificacao('Erro: Pastoral não identificada', 'error');
        return;
    }
    
    try {
        const response = await fetch(`api/pastorais/${pastoralId}/eventos/${eventoId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacao('Evento excluído com sucesso!', 'success');
            await carregarEventosPastoral(pastoralId);
        } else {
            mostrarNotificacao(result.error || 'Erro ao excluir evento', 'error');
        }
    } catch (error) {
        console.error('Erro ao excluir evento:', error);
        mostrarNotificacao('Erro ao excluir evento: ' + error.message, 'error');
    }
}

/**
 * Atualiza coordenadores
 */
function atualizarCoordenadores() {
    const container = document.getElementById('coordenadores');
    if (!container) return;
    
    const pastoral = PastoralState.pastoral;
    if (!pastoral || (!pastoral.coordenador_nome && !pastoral.vice_coordenador_nome)) {
        container.innerHTML = '<p class="text-muted">Nenhum coordenador cadastrado para esta pastoral.</p>';
        return;
    }
    
    let html = '';
    
    // Exibir Coordenador
    if (pastoral.coordenador_nome) {
        const inicial = pastoral.coordenador_nome.substring(0, 1).toUpperCase();
        html += `
        <div class="coordinator-card" style="margin-bottom: 1rem;">
            <div class="avatar">
                ${inicial}
            </div>
            <div class="name">${pastoral.coordenador_nome}</div>
            <div class="role">Coordenador</div>
        </div>
    `;
    }
    
    // Exibir Vice-Coordenador
    if (pastoral.vice_coordenador_nome) {
        const inicial = pastoral.vice_coordenador_nome.substring(0, 1).toUpperCase();
        html += `
        <div class="coordinator-card" style="margin-bottom: 1rem;">
            <div class="avatar">
                ${inicial}
            </div>
            <div class="name">${pastoral.vice_coordenador_nome}</div>
            <div class="role">Vice-Coordenador</div>
        </div>
    `;
    }
    
    container.innerHTML = html;
}

/**
 * Mostra aba específica
 */
function mostrarAba(aba) {
    // Garantir que os contadores sejam atualizados ao mostrar as abas
    if (aba === 'membros') {
        // Pequeno delay para garantir que a aba está visível
        setTimeout(() => {
            atualizarTabelaMembros();
        }, 50);
    } else if (aba === 'eventos') {
        // Pequeno delay para garantir que a aba está visível
        setTimeout(() => {
            atualizarTabelaEventos();
        }, 50);
    }
    // Remover active de todas as abas
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Adicionar active na aba clicada
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach((tab, index) => {
        const tabContent = tab.getAttribute('onclick');
        if (tabContent && tabContent.includes(`mostrarAba('${aba}')`)) {
            tab.classList.add('active');
        }
    });
    
    document.getElementById(`aba-${aba}`).classList.add('active');
    
    // Preencher formulário se for a aba de edição
    if (aba === 'editar') {
        if (!document.getElementById('edit-nome').value) {
            preencherFormularioEdicao();
        }
        preencherListaCoordenadoresEdicao();
    }
}

/**
 * Preenche o formulário de edição com os dados atuais
 */
function preencherFormularioEdicao() {
    if (!PastoralState.pastoral) return;
    
    const pastoral = PastoralState.pastoral;
    
    document.getElementById('edit-nome').value = pastoral.nome || '';
    document.getElementById('edit-tipo').value = pastoral.tipo || '';
    document.getElementById('edit-comunidade').value = pastoral.comunidade_capelania || '';
    document.getElementById('edit-finalidade').value = pastoral.finalidade_descricao || '';
    document.getElementById('edit-whatsapp').value = pastoral.whatsapp_grupo_link || '';
    document.getElementById('edit-email').value = pastoral.email_grupo || '';
    document.getElementById('edit-ativo').value = pastoral.ativo !== undefined ? pastoral.ativo : '1';
}

/**
 * Salva as alterações da pastoral
 */
async function salvarPastoral() {
    const form = document.getElementById('form-editar-pastoral');
    const formData = new FormData(form);
    
    // Converter FormData para objeto
    const dados = {};
    for (const [key, value] of formData.entries()) {
        dados[key] = value;
    }
    
    // Converter ativo para inteiro
    dados.ativo = parseInt(dados.ativo);
    
    // Converter vazios para null
    Object.keys(dados).forEach(key => {
        if (dados[key] === '') {
            dados[key] = null;
        }
    });
    
    // Adicionar coordenadores ao payload
    dados.coordenador_id = coordenadorSelecionado || null;
    dados.vice_coordenador_id = viceCoordenadorSelecionado || null;
    
    console.log('IDs dos coordenadores selecionados:');
    console.log('Coordenador ID:', coordenadorSelecionado);
    console.log('Vice-Coordenador ID:', viceCoordenadorSelecionado);
    
    try {
        console.log('Salvando dados:', dados);
        
        const response = await fetch(`api/pastorais/${PastoralState.pastoral.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacao('Pastoral atualizada com sucesso!', 'success');
            
            // Limpar cache e recarregar dados
            PastoralState.cache.clear();
            await carregarDadosPastoral(PastoralState.pastoral.id);
            
            // Voltar para aba de membros
            mostrarAba('membros');
        } else {
            mostrarNotificacao(result.message || 'Erro ao atualizar pastoral', 'error');
        }
    } catch (error) {
        console.error('Erro ao salvar pastoral:', error);
        mostrarNotificacao('Erro ao salvar: ' + error.message, 'error');
    }
}

/**
 * Cancela a edição e retorna para a aba de membros
 */
function cancelarEdicao() {
    mostrarAba('membros');
}

// Estado para gerenciamento de coordenadores
let membroSelecionadoParaCoordenador = null;
let coordenadorSelecionado = null;
let viceCoordenadorSelecionado = null;

/**
 * Preenche os coordenadores na aba de edição
 */
function preencherListaCoordenadoresEdicao() {
    console.log('=== preencherListaCoordenadoresEdicao ===');
    console.log('Pastoral atual:', PastoralState.pastoral);
    
    if (PastoralState.pastoral && PastoralState.pastoral.coordenador_id) {
        console.log('Coordenador atual:', PastoralState.pastoral.coordenador_id, '- Nome:', PastoralState.pastoral.coordenador_nome);
        document.getElementById('coordenador-atual').innerHTML = `
            <span class="coordinator-name">${PastoralState.pastoral.coordenador_nome || 'Nenhum selecionado'}</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selecionarCoordenador('coordenador')">
                <i class="fas fa-user-edit"></i> Alterar
            </button>
        `;
        coordenadorSelecionado = PastoralState.pastoral.coordenador_id;
        console.log('coordenadorSelecionado definido para:', coordenadorSelecionado);
    } else {
        console.log('Nenhum coordenador cadastrado');
        // Manter HTML padrão se não houver coordenador
        document.getElementById('coordenador-atual').innerHTML = `
            <span class="coordinator-name">Nenhum selecionado</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selecionarCoordenador('coordenador')">
                <i class="fas fa-user-edit"></i> Selecionar
            </button>
        `;
        coordenadorSelecionado = null;
    }
    
    if (PastoralState.pastoral && PastoralState.pastoral.vice_coordenador_id) {
        console.log('Vice-Coordenador atual:', PastoralState.pastoral.vice_coordenador_id, '- Nome:', PastoralState.pastoral.vice_coordenador_nome);
        document.getElementById('vice-coordenador-atual').innerHTML = `
            <span class="coordinator-name">${PastoralState.pastoral.vice_coordenador_nome || 'Nenhum selecionado'}</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selecionarCoordenador('vice_coordenador')">
                <i class="fas fa-user-edit"></i> Alterar
            </button>
        `;
        viceCoordenadorSelecionado = PastoralState.pastoral.vice_coordenador_id;
        console.log('viceCoordenadorSelecionado definido para:', viceCoordenadorSelecionado);
    } else {
        console.log('Nenhum vice-coordenador cadastrado');
        // Manter HTML padrão se não houver vice-coordenador
        document.getElementById('vice-coordenador-atual').innerHTML = `
            <span class="coordinator-name">Nenhum selecionado</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selecionarCoordenador('vice_coordenador')">
                <i class="fas fa-user-edit"></i> Selecionar
            </button>
        `;
        viceCoordenadorSelecionado = null;
    }
}

/**
 * Abre modal para selecionar coordenador
 */
async function selecionarCoordenador(tipo) {
    membroSelecionadoParaCoordenador = null;
    document.getElementById('tipo-coordenador-selecionando').value = tipo;
    
    // Atualizar título do modal
    const titulo = tipo === 'coordenador' ? 'Selecionar Coordenador' : 'Selecionar Vice-Coordenador';
    document.getElementById('modal-titulo').textContent = titulo;
    
    try {
        // Verificar se a pastoral tem membros
        if (!PastoralState.membros || PastoralState.membros.length === 0) {
            mostrarNotificacao('Esta pastoral ainda não tem membros vinculados', 'warning');
            return;
        }
        
        // Usar apenas os membros que já fazem parte da pastoral
        const membros = PastoralState.membros.filter(m => m.status_vinculo === 'ativo');
        
        if (membros.length > 0) {
            preencherModalMembros(membros);
            document.getElementById('modal-selecionar-membro').classList.add('active');
        } else {
            mostrarNotificacao('Nenhum membro ativo disponível', 'warning');
        }
    } catch (error) {
        console.error('Erro ao buscar membros:', error);
        mostrarNotificacao('Erro ao carregar membros', 'error');
    }
}

/**
 * Preenche o modal com a lista de membros
 */
function preencherModalMembros(membros) {
    const container = document.getElementById('lista-membros-selector');
    container.innerHTML = '';
    
    membros.forEach((membro, index) => {
        const item = document.createElement('div');
        item.className = 'member-item-select';
        item.dataset.membroIndex = index;
        item.onclick = () => selecionarMembro(index, membro);
        item.innerHTML = `
            <strong>${membro.nome_completo || membro.apelido || 'Sem nome'}</strong>
            ${membro.email ? `<br><small>${membro.email}</small>` : ''}
        `;
        container.appendChild(item);
    });
    
    // Armazenar lista de membros globalmente
    window.membrosDisponiveis = membros;
}

/**
 * Seleciona um membro
 */
function selecionarMembro(index, membro) {
    console.log('Membro selecionado:', membro);
    
    // Remover seleção anterior
    document.querySelectorAll('.member-item-select').forEach(item => {
        item.classList.remove('selected');
    });
    
    // Adicionar seleção atual
    const clickedItem = document.querySelector(`[data-membro-index="${index}"]`);
    if (clickedItem) {
        clickedItem.classList.add('selected');
    }
    
    membroSelecionadoParaCoordenador = membro;
    console.log('membroSelecionadoParaCoordenador atualizado:', membroSelecionadoParaCoordenador);
}

/**
 * Adiciona o coordenador selecionado
 */
function adicionarCoordenadorSelecionado() {
    console.log('=== adicionarCoordenadorSelecionado ===');
    console.log('membroSelecionadoParaCoordenador:', membroSelecionadoParaCoordenador);
    
    if (!membroSelecionadoParaCoordenador) {
        mostrarNotificacao('Selecione um membro', 'warning');
        return;
    }
    
    const tipo = document.getElementById('tipo-coordenador-selecionando').value;
    console.log('Tipo de coordenador:', tipo);
    
    // Verificar qual ID usar - a API de membros da pastoral retorna 'id' (membro_id)
    const membroId = membroSelecionadoParaCoordenador.id;
    console.log('ID do membro selecionado:', membroId);
    console.log('Objeto completo do membro:', JSON.stringify(membroSelecionadoParaCoordenador));
    
    if (tipo === 'coordenador') {
        // Verificar se não é o vice-coordenador
        if (viceCoordenadorSelecionado === membroId) {
            mostrarNotificacao('Este membro já é vice-coordenador', 'warning');
            return;
        }
        
        coordenadorSelecionado = membroId;
        console.log('Coordenador ID definido:', coordenadorSelecionado);
        
        // Atualizar interface
        document.getElementById('coordenador-atual').innerHTML = `
            <span class="coordinator-name">${membroSelecionadoParaCoordenador.nome_completo || membroSelecionadoParaCoordenador.apelido}</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selecionarCoordenador('coordenador')">
                <i class="fas fa-user-edit"></i> Alterar
            </button>
        `;
    } else if (tipo === 'vice_coordenador') {
        // Verificar se não é o coordenador
        if (coordenadorSelecionado === membroId) {
            mostrarNotificacao('Este membro já é coordenador', 'warning');
            return;
        }
        
        viceCoordenadorSelecionado = membroId;
        console.log('Vice-Coordenador ID definido:', viceCoordenadorSelecionado);
        
        // Atualizar interface
        document.getElementById('vice-coordenador-atual').innerHTML = `
            <span class="coordinator-name">${membroSelecionadoParaCoordenador.nome_completo || membroSelecionadoParaCoordenador.apelido}</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selecionarCoordenador('vice_coordenador')">
                <i class="fas fa-user-edit"></i> Alterar
            </button>
        `;
    }
    
    // Fechar modal
    fecharModalMembro();
}

/**
 * Fecha o modal de seleção de membro
 */
function fecharModalMembro() {
    document.getElementById('modal-selecionar-membro').classList.remove('active');
    membroSelecionadoParaCoordenador = null;
    document.getElementById('busca-membro').value = '';
}

/**
 * Filtra membros na lista
 */
function filtrarMembros() {
    const busca = document.getElementById('busca-membro').value.toLowerCase();
    const itens = document.querySelectorAll('.member-item-select');
    
    itens.forEach(item => {
        const texto = item.textContent.toLowerCase();
        item.style.display = texto.includes(busca) ? 'block' : 'none';
    });
}

// Adicionar listener no formulário
window.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-editar-pastoral');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await salvarPastoral();
        });
    }
});

/**
 * Funções auxiliares
 */
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

function mostrarNotificacao(mensagem, tipo = 'info') {
    console.log(`${tipo.toUpperCase()}: ${mensagem}`);
}

/**
 * Visualiza detalhes de um evento em pop-up
 */
function verDetalhesEvento(eventoId) {
    const evento = PastoralState.eventos.find(e => e.id === eventoId);
    if (!evento) {
        mostrarNotificacao('Evento não encontrado', 'error');
        return;
    }
    
    const horarioFormatado = evento.horario ? evento.horario.substring(0, 5) : 'Não definido';
    const tipoFormatado = evento.tipo || 'Não especificado';
    const localFormatado = evento.local || 'Não definido';
    const descricaoFormatada = evento.descricao || 'Sem descrição';
    const responsavelFormatado = evento.responsavel_nome || evento.responsavel_id || 'Não definido';
    const pastoralNome = PastoralState.pastoral?.nome || 'Pastoral desconhecida';
    
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
                                <div class="detail-value">${formatarData(evento.data)}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-clock"></i> Horário:</div>
                                <div class="detail-value">${horarioFormatado}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-map-marker-alt"></i> Local:</div>
                                <div class="detail-value">${localFormatado}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-church"></i> Pastoral:</div>
                                <div class="detail-value">${pastoralNome}</div>
                            </div>
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
 * Fecha modal de detalhes do evento
 */
function fecharModalDetalhesEvento() {
    const modal = document.getElementById('modal-detalhes-evento');
    const backdrop = document.querySelector('.modal-backdrop');
    if (modal) modal.remove();
    if (backdrop) backdrop.remove();
}

// =====================================================
// CACHE DE DADOS DE MEMBROS (para visualização rápida)
// =====================================================

/**
 * Obtém dados de um membro do cache
 */
function obterDadosDoCache(id) {
    if (!id) return null;
    const cached = PastoralState.cache.get(`membro-${id}`);
    if (!cached) return null;
    
    const agora = Date.now();
    if (agora - cached.timestamp > PastoralState.cacheValidoPor) {
        PastoralState.cache.delete(`membro-${id}`);
        return null;
    }
    
    return cached.data;
}

/**
 * Salva dados de um membro no cache
 */
function salvarDadosNoCache(id, dados) {
    if (!id || !dados) return;
    PastoralState.cache.set(`membro-${id}`, {
        data: dados,
        timestamp: Date.now()
    });
}

/**
 * Mostra indicador de carregamento
 */
function mostrarIndicadorCarregamento(mensagem = 'Carregando...') {
    // Usar a função de notificação se disponível
    if (typeof mostrarNotificacao === 'function') {
        // Não mostrar notificação para carregamento, apenas log
        console.log(mensagem);
    }
}

/**
 * Oculta indicador de carregamento
 */
function ocultarIndicadorCarregamento() {
    // Função vazia por enquanto, pode ser expandida depois
}

// =====================================================
// FUNÇÕES DE VISUALIZAÇÃO DE MEMBROS
// =====================================================

/**
 * Visualiza membro (copiado de membros.js)
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

// Exportar para o escopo global
window.visualizarMembro = visualizarMembro;

// =====================================================
// FUNÇÕES PARA ADICIONAR MEMBROS À PASTORAL
// =====================================================

let membroSelecionadoParaPastoral = null;

/**
 * Abre modal para adicionar membro à pastoral
 */
async function adicionarMembroPastoral() {
    membroSelecionadoParaPastoral = null;
    
    try {
        // Buscar membros ativos que ainda não estão na pastoral
        const response = await fetch('api/membros?limit=1000&status=ativo');
        const result = await response.json();
        
        const todosMembros = result.data?.data || result.data || [];
        
        // Filtrar membros que já estão na pastoral
        const membrosDaPastoral = PastoralState.membros.map(m => m.id);
        const membrosDisponiveis = todosMembros.filter(m => !membrosDaPastoral.includes(m.id));
        
        if (membrosDisponiveis.length > 0) {
            preencherModalMembrosPastoral(membrosDisponiveis);
            document.getElementById('modal-adicionar-membro-pastoral').classList.add('active');
        } else {
            mostrarNotificacao('Todos os membros ativos já estão nesta pastoral', 'info');
        }
    } catch (error) {
        console.error('Erro ao buscar membros:', error);
        mostrarNotificacao('Erro ao carregar membros', 'error');
    }
}

/**
 * Preenche o modal com lista de membros disponíveis
 */
function preencherModalMembrosPastoral(membros) {
    const container = document.getElementById('lista-membros-pastoral');
    if (!container) return;
    
    container.innerHTML = '';
    
    membros.forEach(membro => {
        const item = document.createElement('div');
        item.className = 'member-item-select';
        item.onclick = () => selecionarMembroParaPastoral(membro);
        item.innerHTML = `
            <strong>${membro.nome_completo || membro.apelido || 'Sem nome'}</strong>
            ${membro.email ? `<br><small>${membro.email}</small>` : ''}
        `;
        container.appendChild(item);
    });
}

/**
 * Seleciona um membro para adicionar à pastoral
 */
function selecionarMembroParaPastoral(membro) {
    // Remover seleção anterior
    document.querySelectorAll('#lista-membros-pastoral .member-item-select').forEach(item => {
        item.classList.remove('selected');
    });
    
    // Adicionar seleção atual
    event.target.closest('.member-item-select').classList.add('selected');
    membroSelecionadoParaPastoral = membro;
}

/**
 * Adiciona o membro selecionado à pastoral
 */
async function adicionarMembroSelecionado() {
    if (!membroSelecionadoParaPastoral) {
        mostrarNotificacao('Selecione um membro', 'warning');
        return;
    }
    
    try {
        const response = await fetch('api/pastorais/vincular-membro', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                membro_id: membroSelecionadoParaPastoral.id,
                pastoral_id: PastoralState.pastoral.id
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacao('Membro adicionado com sucesso!', 'success');
            
            // Recarregar dados da pastoral
            PastoralState.cache.clear();
            await carregarDadosPastoral(PastoralState.pastoral.id);
            
            fecharModalAdicionarMembro();
        } else {
            mostrarNotificacao(result.message || 'Erro ao adicionar membro', 'error');
        }
    } catch (error) {
        console.error('Erro ao adicionar membro:', error);
        mostrarNotificacao('Erro ao adicionar membro: ' + error.message, 'error');
    }
}

/**
 * Fecha o modal de adicionar membro
 */
function fecharModalAdicionarMembro() {
    document.getElementById('modal-adicionar-membro-pastoral').classList.remove('active');
    membroSelecionadoParaPastoral = null;
    document.getElementById('busca-membro-pastoral').value = '';
}

/**
 * Filtra membros na lista da pastoral
 */
function filtrarMembrosPastoral() {
    const busca = document.getElementById('busca-membro-pastoral').value.toLowerCase();
    const itens = document.querySelectorAll('#lista-membros-pastoral .member-item-select');
    
    itens.forEach(item => {
        const texto = item.textContent.toLowerCase();
        item.style.display = texto.includes(busca) ? 'block' : 'none';
    });
}

/**
 * Busca membros para autocomplete (mesma lógica do membros.js)
 */
let autocompleteTimeoutPastoral = null;
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
    clearTimeout(autocompleteTimeoutPastoral);
    autocompleteTimeoutPastoral = setTimeout(async () => {
        try {
            const url = `api/membros/buscar?q=${encodeURIComponent(query)}`;
            console.log('Buscando membros (pastoral):', url);
            
            const response = await fetch(url);
            
            if (!response.ok) {
                console.error('Erro HTTP:', response.status, response.statusText);
                dropdown.style.display = 'none';
                return;
            }
            
            const result = await response.json();
            console.log('Resultado da busca (pastoral):', result);
            
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

// Exportar funções para o escopo global
window.mostrarAba = mostrarAba;
window.cancelarEdicao = cancelarEdicao;
window.selecionarCoordenador = selecionarCoordenador;
window.adicionarCoordenadorSelecionado = adicionarCoordenadorSelecionado;
window.fecharModalMembro = fecharModalMembro;
window.filtrarMembros = filtrarMembros;
window.adicionarMembroPastoral = adicionarMembroPastoral;
window.adicionarMembroSelecionado = adicionarMembroSelecionado;
window.fecharModalAdicionarMembro = fecharModalAdicionarMembro;
window.filtrarMembrosPastoral = filtrarMembrosPastoral;
// Funções de eventos
window.abrirModalEvento = abrirModalEvento;
window.fecharModalEvento = fecharModalEvento;
window.salvarEvento = salvarEvento;
window.editarEvento = editarEvento;
window.excluirEvento = excluirEvento;
window.verDetalhesEvento = verDetalhesEvento;
window.fecharModalDetalhesEvento = fecharModalDetalhesEvento;
// Funções de autocomplete
window.buscarMembrosAutocomplete = buscarMembrosAutocomplete;
window.selecionarMembroAutocomplete = selecionarMembroAutocomplete;
window.fecharAutocomplete = fecharAutocomplete;
window.mostrarAutocompleteDropdown = mostrarAutocompleteDropdown;


