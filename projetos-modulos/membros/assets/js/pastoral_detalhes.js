/**
 * JavaScript para Página de Detalhes da Pastoral
 * Módulo de Cadastro de Membros
 */

// Estado da página
let PastoralState = {
    pastoral: null,
    membros: [],
    eventos: [],
    coordenadores: []
};

/**
 * Carrega dados da pastoral
 */
async function carregarDadosPastoral(pastoralId) {
    try {
        // Buscar dados da pastoral
        const pastoralData = await fetch(`api/pastorais/${pastoralId}`);
        const pastoralJson = await pastoralData.json();
        
        if (!pastoralJson.success) {
            mostrarNotificacao('Erro ao carregar dados da pastoral', 'error');
            return;
        }
        
        PastoralState.pastoral = pastoralJson.data;
        
        // Preencher informações básicas
        document.getElementById('pastoral-nome').textContent = PastoralState.pastoral.nome;
        document.getElementById('pastoral-descricao').textContent = PastoralState.pastoral.descricao || 'Sem descrição';
        
        // Carregar membros da pastoral
        await carregarMembrosPastoral(pastoralId);
        
        // Carregar eventos da pastoral
        await carregarEventosPastoral(pastoralId);
        
        // Carregar coordenadores
        await carregarCoordenadores(pastoralId);
        
        // Atualizar métricas
        atualizarMetricas();
        
    } catch (error) {
        console.error('Erro ao carregar dados da pastoral:', error);
        mostrarNotificacao('Erro ao carregar dados da pastoral: ' + error.message, 'error');
    }
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
    
    if (PastoralState.coordenadores.length === 0) {
        container.innerHTML = '<p>Nenhum coordenador cadastrado para esta pastoral.</p>';
        return;
    }
    
    container.innerHTML = PastoralState.coordenadores.map(coord => `
        <div class="coordinator-card">
            <div class="avatar">
                ${coord.iniciais || coord.nome.substring(0, 2).toUpperCase()}
            </div>
            <div class="name">${coord.nome}</div>
            <div class="role">${coord.funcao || 'Coordenador'}</div>
            ${coord.telefone ? `<div style="margin-top: 0.5rem;"><i class="fas fa-phone"></i> ${coord.telefone}</div>` : ''}
        </div>
    `).join('');
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
}

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


