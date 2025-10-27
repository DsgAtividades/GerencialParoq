/**
 * JavaScript para Página de Detalhes da Pastoral
 * Módulo de Cadastro de Membros
 */

// Estado da página com cache
let PastoralState = {
    pastoral: null,
    membros: [],
    eventos: [],
    coordenadores: [],
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
        const [pastoralResponse, membrosResponse, eventosResponse, coordenadoresResponse] = await Promise.all([
            fetch(`api/pastorais/${pastoralId}`),
            fetch(`api/pastorais/${pastoralId}/membros`),
            fetch(`api/pastorais/${pastoralId}/eventos`),
            fetch(`api/pastorais/${pastoralId}/coordenadores`)
        ]);
        
        const pastoralJson = await pastoralResponse.json();
        const membrosJson = await membrosResponse.json();
        const eventosJson = await eventosResponse.json();
        const coordenadoresJson = await coordenadoresResponse.json();
        
        if (!pastoralJson.success) {
            mostrarNotificacao('Erro ao carregar dados da pastoral', 'error');
            return;
        }
        
        // Armazenar dados
        PastoralState.pastoral = pastoralJson.data;
        PastoralState.membros = membrosJson.success ? (membrosJson.data || []) : [];
        PastoralState.eventos = eventosJson.success ? (eventosJson.data || []) : [];
        PastoralState.coordenadores = coordenadoresJson.success ? (coordenadoresJson.data || []) : [];
        
        // Salvar no cache
        const dadosParaCache = {
            pastoral: PastoralState.pastoral,
            membros: PastoralState.membros,
            eventos: PastoralState.eventos,
            coordenadores: PastoralState.coordenadores
        };
        salvarNoCache('pastoral-' + pastoralId, dadosParaCache);
        
        // Atualizar interface
        atualizarInterface();
        
    } catch (error) {
        console.error('Erro ao carregar dados da pastoral:', error);
        mostrarNotificacao('Erro ao carregar dados da pastoral: ' + error.message, 'error');
    }
}

/**
 * Aplica dados do cache
 */
function aplicarDadosCached(dados) {
    PastoralState.pastoral = dados.pastoral;
    PastoralState.membros = dados.membros;
    PastoralState.eventos = dados.eventos;
    PastoralState.coordenadores = dados.coordenadores;
    atualizarInterface();
}

/**
 * Atualiza toda a interface
 */
function atualizarInterface() {
    // Preencher informações básicas
    document.getElementById('pastoral-nome').textContent = PastoralState.pastoral?.nome || 'Carregando...';
    document.getElementById('pastoral-descricao').textContent = PastoralState.pastoral?.descricao || 'Sem descrição';
    
    // Atualizar todas as seções
    atualizarMetricas();
    atualizarInfoPastoral();
    atualizarTabelaMembros();
    atualizarTabelaEventos();
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
        }
    } catch (error) {
        console.error('Erro ao carregar membros:', error);
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
        }
    } catch (error) {
        console.error('Erro ao carregar eventos:', error);
    }
}

/**
 * Carrega coordenadores da pastoral
 */
async function carregarCoordenadores(pastoralId) {
    try {
        const response = await fetch(`api/pastorais/${pastoralId}/coordenadores`);
        const data = await response.json();
        
        if (data.success) {
            PastoralState.coordenadores = data.data || [];
            atualizarCoordenadores();
        }
    } catch (error) {
        console.error('Erro ao carregar coordenadores:', error);
    }
}

/**
 * Atualiza métricas da página
 */
function atualizarMetricas() {
    const totalMembros = PastoralState.membros.length;
    const membrosAtivos = PastoralState.membros.filter(m => m.status === 'ativo').length;
    const totalCoordenadores = PastoralState.coordenadores.length;
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
    
    if (PastoralState.membros.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum membro encontrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = PastoralState.membros.map(membro => `
        <tr>
            <td>${membro.nome_completo}</td>
            <td>${membro.email || '-'}</td>
            <td>${membro.telefone || '-'}</td>
            <td>${membro.funcao || '-'}</td>
            <td><span class="badge badge-${getStatusClass(membro.status)}">${getStatusText(membro.status)}</span></td>
            <td>
                <button class="btn btn-sm btn-info" onclick="visualizarFoto('${membro.id}')" title="Visualizar Foto" ${!membro.foto_url ? 'disabled' : ''}>
                    <i class="fas fa-image"></i>
                </button>
                <button class="btn btn-sm btn-secondary" onclick="visualizarMembro('${membro.id}')" title="Visualizar">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

/**
 * Atualiza tabela de eventos
 */
function atualizarTabelaEventos() {
    const tbody = document.getElementById('tabela-eventos');
    
    if (PastoralState.eventos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum evento encontrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = PastoralState.eventos.map(evento => `
        <tr>
            <td>${formatarData(evento.data)}</td>
            <td>${evento.nome}</td>
            <td>${evento.horario || '-'}</td>
            <td>${evento.local || '-'}</td>
            <td>${evento.total_inscritos || 0}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="verDetalhesEvento('${evento.id}')" title="Ver Detalhes">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

/**
 * Atualiza coordenadores
 */
function atualizarCoordenadores() {
    const container = document.getElementById('coordenadores');
    if (!container) return;
    
    if (PastoralState.coordenadores.length === 0) {
        container.innerHTML = '<p class="text-muted">Nenhum coordenador cadastrado para esta pastoral.</p>';
        return;
    }
    
    container.innerHTML = PastoralState.coordenadores.map(coord => {
        const iniciais = (coord.nome_completo || coord.nome || '').split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
        return `
        <div class="coordinator-card">
            <div class="avatar">
                ${iniciais}
            </div>
            <div class="name">${coord.nome_completo || coord.nome || 'Sem nome'}</div>
            <div class="role">${coord.funcao || 'Coordenador'}</div>
            ${coord.telefone ? `<div style="margin-top: 0.5rem;"><i class="fas fa-phone"></i> ${coord.telefone}</div>` : ''}
        </div>
    `;
    }).join('');
}

/**
 * Mostra aba específica
 */
function mostrarAba(aba) {
    // Remover active de todas as abas
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Adicionar active na aba clicada
    event.target.classList.add('active');
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
    document.getElementById('edit-comunidade').value = pastoral.comunidade || '';
    document.getElementById('edit-finalidade').value = pastoral.finalidade_descricao || '';
    document.getElementById('edit-contato_whatsapp').value = pastoral.contato_whatsapp || '';
    document.getElementById('edit-contato_email').value = pastoral.contato_email || '';
    document.getElementById('edit-responsavel').value = pastoral.responsavel || '';
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
    dados.coordenadores = PastoralState.coordenadores.map(coord => ({
        id: coord.id,
        nome: coord.nome_completo || coord.nome,
        funcao: coord.funcao || 'Coordenador',
        prioridade: coord.prioridade || 10
    }));
    
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
let coordenadoresParaAdicionar = [];
let membroSelecionadoParaCoordenador = null;

/**
 * Preenche a lista de coordenadores na aba de edição
 */
function preencherListaCoordenadoresEdicao() {
    const container = document.getElementById('coordenadores-lista');
    if (!container) return;
    
    container.innerHTML = '';
    
    PastoralState.coordenadores.forEach((coord, index) => {
        const item = document.createElement('div');
        item.className = 'coordinator-item-edit';
        item.innerHTML = `
            <div class="coordinator-info">
                <div>
                    <strong>Nome:</strong>
                    <span>${coord.nome_completo || coord.nome || 'Sem nome'}</span>
                </div>
                <div>
                    <strong>Função:</strong>
                    <span>${coord.funcao || 'Coordenador'}</span>
                </div>
                <div>
                    <strong>Telefone:</strong>
                    <span>${coord.telefone || '-'}</span>
                </div>
            </div>
            <div class="coordinator-actions">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerCoordenadorEdicao(${index})">
                    <i class="fas fa-trash"></i> Remover
                </button>
            </div>
        `;
        container.appendChild(item);
    });
}

/**
 * Abre modal para adicionar coordenador
 */
async function adicionarCoordenador() {
    membroSelecionadoParaCoordenador = null;
    
    try {
        // Buscar membros ativos
        const response = await fetch('api/membros?limit=1000&status=ativo');
        const result = await response.json();
        
        console.log('Resposta da API de membros:', result);
        
        // A API retorna { success: true, data: { data: [...], pagination: {...} } }
        const membros = result.data?.data || result.data || [];
        
        if (Array.isArray(membros) && membros.length > 0) {
            preencherModalMembros(membros);
            document.getElementById('modal-selecionar-membro').classList.add('active');
        } else {
            console.warn('Nenhum membro encontrado. Dados:', membros);
            mostrarNotificacao('Nenhum membro disponível', 'warning');
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
    
    membros.forEach(membro => {
        const item = document.createElement('div');
        item.className = 'member-item-select';
        item.onclick = () => selecionarMembro(membro);
        item.innerHTML = `
            <strong>${membro.nome_completo || membro.apelido || 'Sem nome'}</strong>
            ${membro.email ? `<br><small>${membro.email}</small>` : ''}
        `;
        container.appendChild(item);
    });
}

/**
 * Seleciona um membro
 */
function selecionarMembro(membro) {
    // Remover seleção anterior
    document.querySelectorAll('.member-item-select').forEach(item => {
        item.classList.remove('selected');
    });
    
    // Adicionar seleção atual
    event.target.closest('.member-item-select').classList.add('selected');
    membroSelecionadoParaCoordenador = membro;
}

/**
 * Adiciona o coordenador selecionado à lista
 */
function adicionarCoordenadorSelecionado() {
    if (!membroSelecionadoParaCoordenador) {
        mostrarNotificacao('Selecione um membro', 'warning');
        return;
    }
    
    // Verificar se já não está na lista
    const jaExiste = PastoralState.coordenadores.some(c => 
        c.id === membroSelecionadoParaCoordenador.id
    );
    
    if (jaExiste) {
        mostrarNotificacao('Este membro já é coordenador', 'warning');
        return;
    }
    
    // Adicionar à lista de coordenadores
    PastoralState.coordenadores.push({
        id: membroSelecionadoParaCoordenador.id,
        nome_completo: membroSelecionadoParaCoordenador.nome_completo,
        nome: membroSelecionadoParaCoordenador.nome_completo,
        email: membroSelecionadoParaCoordenador.email,
        telefone: membroSelecionadoParaCoordenador.celular_whatsapp || membroSelecionadoParaCoordenador.telefone_fixo,
        funcao: 'Coordenador',
        prioridade: 10,
        data_inicio: new Date().toISOString().split('T')[0]
    });
    
    // Atualizar a lista visual
    preencherListaCoordenadoresEdicao();
    
    // Fechar modal
    fecharModalMembro();
}

/**
 * Remove coordenador da lista de edição
 */
function removerCoordenadorEdicao(index) {
    if (confirm('Deseja remover este coordenador?')) {
        PastoralState.coordenadores.splice(index, 1);
        preencherListaCoordenadoresEdicao();
    }
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
    return new Date(data).toLocaleDateString('pt-BR');
}

function mostrarNotificacao(mensagem, tipo = 'info') {
    console.log(`${tipo.toUpperCase()}: ${mensagem}`);
}

function verDetalhesEvento(eventoId) {
    // TODO: Implementar visualização de detalhes do evento
    console.log('Ver detalhes do evento:', eventoId);
}

// Exportar para o escopo global
window.visualizarMembro = function(id) {
    // Abrir modal de visualização de membro
    console.log('Visualizar membro:', id);
};

window.visualizarFoto = function(id) {
    // Abrir modal de visualização de foto
    console.log('Visualizar foto:', id);
};


