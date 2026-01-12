/**
 * JavaScript para o módulo de Eventos
 * Sistema de Gestão Paroquial
 */

// Detectar automaticamente o caminho base da API
function detectApiBasePath() {
    const path = window.location.pathname;
    // Remover /index.php ou qualquer arquivo final
    const basePath = path.replace(/\/[^\/]*\.php$/, '').replace(/\/index\.html$/, '');
    return basePath + '/api/';
}

// Configuração da API
const CONFIG = {
    apiBaseUrl: detectApiBasePath()
};

// Estado da aplicação
const AppState = {
    eventosCalendario: [],
    eventosPorData: {},
    membros: [] // Para select de responsável
};

// Variável global para mês atual do calendário
let mesCalendarioAtual = new Date();

// Variável global para evento em edição
let eventoEditando = null;

// =====================================================
// INICIALIZAÇÃO
// =====================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo de Eventos carregado');
    carregarEventosCalendario();
    carregarMembros();
});

// =====================================================
// CARREGAMENTO DE DADOS
// =====================================================

/**
 * Carrega eventos para o calendário
 */
async function carregarEventosCalendario() {
    try {
        const response = await fetch(`${CONFIG.apiBaseUrl}eventos/calendario`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            AppState.eventosCalendario = result.data.eventos || [];
            AppState.eventosPorData = result.data.eventos_por_data || {};
            atualizarCalendarioEventos();
        } else {
            console.error('Erro ao carregar eventos do calendário:', result.error);
            mostrarCalendarioErro();
        }
    } catch (error) {
        console.error('Erro ao carregar eventos do calendário:', error);
        mostrarCalendarioErro();
    }
}

/**
 * Carrega membros para select de responsável
 */
async function carregarMembros() {
    try {
        const response = await fetch(`${CONFIG.apiBaseUrl}membros`);
        
        if (!response.ok) {
            console.warn(`Erro HTTP ao carregar membros: ${response.status}`);
            return;
        }
        
        const result = await response.json();
        
        if (result.success) {
            AppState.membros = result.data || [];
        } else {
            console.warn('Erro ao carregar membros:', result.error);
        }
    } catch (error) {
        console.error('Erro ao carregar membros:', error);
    }
}

/**
 * Mostra mensagem de erro no calendário
 */
function mostrarCalendarioErro() {
    const container = document.getElementById('calendario-eventos');
    if (!container) return;
    
    container.innerHTML = `
        <div class="loading-card">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Erro ao carregar eventos</p>
        </div>
    `;
}

// =====================================================
// CALENDÁRIO
// =====================================================

/**
 * Atualiza o calendário de eventos
 */
function atualizarCalendarioEventos() {
    const container = document.getElementById('calendario-eventos');
    if (!container) return;
    
    const eventosPorData = AppState.eventosPorData || {};
    const mes = mesCalendarioAtual.getMonth();
    const ano = mesCalendarioAtual.getFullYear();
    
    // Primeiro dia do mês
    const primeiroDia = new Date(ano, mes, 1);
    const ultimoDia = new Date(ano, mes + 1, 0);
    const diasNoMes = ultimoDia.getDate();
    const diaSemanaInicio = primeiroDia.getDay(); // 0 = Domingo, 6 = Sábado
    
    // Nomes dos meses e dias
    const meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                   'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    const diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
    
    let html = `
        <div class="calendar-wrapper">
            <div class="calendar-header">
                <button class="btn btn-sm btn-secondary" onclick="mesAnterior()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h3>${meses[mes]} ${ano}</h3>
                <button class="btn btn-sm btn-secondary" onclick="mesProximo()">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <table class="calendar-table">
                <thead>
                    <tr>
                        ${diasSemana.map(dia => `<th>${dia}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
    `;
    
    let diaAtual = 1;
    
    // Gerar semanas
    for (let semana = 0; semana < 6; semana++) {
        html += '<tr>';
        
        for (let diaSemana = 0; diaSemana < 7; diaSemana++) {
            if (semana === 0 && diaSemana < diaSemanaInicio) {
                // Dias vazios antes do primeiro dia do mês
                html += '<td class="calendar-day empty"></td>';
            } else if (diaAtual > diasNoMes) {
                // Dias vazios depois do último dia do mês
                html += '<td class="calendar-day empty"></td>';
            } else {
                const dataFormatada = `${ano}-${String(mes + 1).padStart(2, '0')}-${String(diaAtual).padStart(2, '0')}`;
                const eventosDoDia = eventosPorData[dataFormatada] || [];
                const totalEventos = eventosDoDia.length;
                
                // Determinar classe do dia
                const hoje = new Date();
                const isHoje = ano === hoje.getFullYear() && mes === hoje.getMonth() && diaAtual === hoje.getDate();
                const classeDia = isHoje ? 'today' : '';
                
                html += `<td class="calendar-day ${classeDia}" onclick="mostrarEventosDoDia('${dataFormatada}', ${totalEventos})">
                    <div class="day-number">${diaAtual}</div>
                    ${totalEventos > 0 ? `<div class="day-events">
                        ${Array.from({length: Math.min(totalEventos, 3)}, (_, i) => 
                            `<span class="event-dot ${eventosDoDia[i]?.origem || 'geral'}" title="${eventosDoDia[i]?.nome || ''}"></span>`
                        ).join('')}
                        ${totalEventos > 3 ? `<span class="event-count">+${totalEventos - 3}</span>` : ''}
                    </div>` : ''}
                </td>`;
                
                diaAtual++;
            }
        }
        
        html += '</tr>';
        
        // Se já renderizou todos os dias, parar
        if (diaAtual > diasNoMes) break;
    }
    
    html += `
                </tbody>
            </table>
            <div class="calendar-legend">
                <div class="legend-item">
                    <span class="event-dot geral"></span>
                    <span>Eventos Gerais</span>
                </div>
                <div class="legend-item">
                    <span class="event-dot pastoral"></span>
                    <span>Eventos de Pastorais</span>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

/**
 * Navega para o mês anterior
 */
function mesAnterior() {
    mesCalendarioAtual.setMonth(mesCalendarioAtual.getMonth() - 1);
    atualizarCalendarioEventos();
}

/**
 * Navega para o próximo mês
 */
function mesProximo() {
    mesCalendarioAtual.setMonth(mesCalendarioAtual.getMonth() + 1);
    atualizarCalendarioEventos();
}

/**
 * Mostra eventos de um dia específico
 */
function mostrarEventosDoDia(dataFormatada, totalEventos) {
    if (totalEventos === 0) return;
    
    const eventosPorData = AppState.eventosPorData || {};
    const eventosDoDia = eventosPorData[dataFormatada] || [];
    
    // Converter data corretamente para evitar problemas de fuso horário
    const [ano, mes, dia] = dataFormatada.split('-').map(Number);
    const dataObj = new Date(ano, mes - 1, dia);
    const dataFormatadaBR = dataObj.toLocaleDateString('pt-BR', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    let html = `
        <div id="modal-eventos-dia" class="modal show">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-calendar-day"></i> Eventos de ${dataFormatadaBR}
                        </h5>
                        <button type="button" class="close" onclick="fecharModalEventosDia()" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="eventos-lista-dia">
                            ${eventosDoDia.map(evento => {
                                const horarioFormatado = evento.horario ? formatarHora(evento.horario) : (evento.hora_inicio ? formatarHora(evento.hora_inicio) : 'Não definido');
                                const origem = evento.origem || (evento.pastoral_id ? 'pastoral' : 'geral');
                                const origemBadge = origem === 'pastoral' 
                                    ? `<span class="badge badge-info">Pastoral: ${evento.pastoral_nome || 'N/A'}</span>`
                                    : `<span class="badge badge-secondary">Evento Geral</span>`;
                                
                                return `
                                    <div class="evento-item-dia" onclick="abrirDetalhesEventoCalendario('${escapeHtml(evento.id)}', '${origem}')">
                                        <div class="evento-item-header">
                                            <h6>${escapeHtml(evento.nome || 'Sem nome')}</h6>
                                            ${origemBadge}
                                        </div>
                                        <div class="evento-item-body">
                                            <p><i class="fas fa-clock"></i> ${escapeHtml(horarioFormatado)}</p>
                                            ${evento.local ? `<p><i class="fas fa-map-marker-alt"></i> ${escapeHtml(evento.local)}</p>` : ''}
                                            ${evento.tipo ? `<p><i class="fas fa-tag"></i> ${escapeHtml(evento.tipo)}</p>` : ''}
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModalEventosDia()">
                            <i class="fas fa-times"></i> Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop" onclick="fecharModalEventosDia()"></div>
    `;
    
    // Remover modal anterior se existir
    const modalAnterior = document.getElementById('modal-eventos-dia');
    if (modalAnterior) {
        modalAnterior.remove();
        document.querySelector('.modal-backdrop')?.remove();
    }
    
    // Adicionar modal
    const container = document.getElementById('modal-container');
    if (container) {
        container.innerHTML = html;
    } else {
        document.body.insertAdjacentHTML('beforeend', html);
    }
}

/**
 * Fecha modal de eventos do dia
 */
function fecharModalEventosDia() {
    const modal = document.getElementById('modal-eventos-dia');
    const backdrop = document.querySelector('.modal-backdrop');
    if (modal) modal.remove();
    if (backdrop) backdrop.remove();
}

/**
 * Abre detalhes de um evento do calendário
 */
function abrirDetalhesEventoCalendario(eventoId, origem) {
    const eventos = AppState.eventosCalendario || [];
    const evento = eventos.find(e => e.id === eventoId);
    
    if (!evento) {
        alert('Evento não encontrado');
        return;
    }
    
    // Determinar origem se não foi passada
    if (!origem) {
        origem = evento.origem || (evento.pastoral_id ? 'pastoral' : 'geral');
    }
    
    // Se não tem pastoral_id definido e não é explicitamente pastoral, considerar geral
    const isEventoGeral = origem === 'geral' || (!evento.pastoral_id && origem !== 'pastoral');
    
    fecharModalEventosDia();
    
    const horarioFormatado = evento.horario ? formatarHora(evento.horario) : (evento.hora_inicio ? formatarHora(evento.hora_inicio) : 'Não definido');
    const tipoFormatado = evento.tipo || 'Não especificado';
    const localFormatado = evento.local || 'Não definido';
    const descricaoFormatada = evento.descricao || 'Sem descrição';
    const responsavelFormatado = evento.responsavel_nome || evento.responsavel_id || 'Não definido';
    const pastoralNome = evento.pastoral_nome || 'N/A';
    
    const modalHTML = `
        <div id="modal-detalhes-evento" class="modal show">
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
                                <div class="detail-value">${escapeHtml(evento.nome || '-')}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-tag"></i> Tipo:</div>
                                <div class="detail-value">${escapeHtml(tipoFormatado)}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-calendar"></i> Data:</div>
                                <div class="detail-value">${formatarData(evento.data || evento.data_evento)}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-clock"></i> Horário:</div>
                                <div class="detail-value">${escapeHtml(horarioFormatado)}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-map-marker-alt"></i> Local:</div>
                                <div class="detail-value">${escapeHtml(localFormatado)}</div>
                            </div>
                            ${origem === 'pastoral' ? `
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-church"></i> Pastoral:</div>
                                <div class="detail-value">${escapeHtml(pastoralNome)}</div>
                            </div>
                            ` : ''}
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-user"></i> Responsável:</div>
                                <div class="detail-value">${escapeHtml(responsavelFormatado)}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-align-left"></i> Descrição:</div>
                                <div class="detail-value" style="white-space: pre-wrap;">${escapeHtml(descricaoFormatada)}</div>
                            </div>
                            ${(evento.eventos_url || evento.Eventos_url) ? `
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-link"></i> Link:</div>
                                <div class="detail-value">
                                    <a href="${escapeHtml(evento.eventos_url || evento.Eventos_url)}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-info">
                                        <i class="fas fa-external-link-alt"></i> Acessar Link do Evento
                                    </a>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    <div class="modal-footer">
                        ${isEventoGeral ? `
                        <button type="button" class="btn btn-danger" onclick="confirmarExcluirEvento('${escapeHtml(evento.id)}')" title="Excluir este evento">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                        <button type="button" class="btn btn-primary" onclick="editarEventoGeral('${escapeHtml(evento.id)}')" title="Editar este evento">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        ` : ''}
                        ${(evento.eventos_url || evento.Eventos_url) ? `
                        <a href="${escapeHtml(evento.eventos_url || evento.Eventos_url)}" target="_blank" rel="noopener noreferrer" class="btn btn-success">
                            <i class="fas fa-external-link-alt"></i> Ir para Link do Evento
                        </a>
                        ` : ''}
                        <button type="button" class="btn btn-secondary" onclick="fecharModalDetalhesEvento()">
                            <i class="fas fa-times"></i> Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop" onclick="fecharModalDetalhesEvento()"></div>
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
// CRUD DE EVENTOS
// =====================================================

/**
 * Abre modal para criar/editar evento geral
 */
function abrirModalEvento(evento = null) {
    eventoEditando = evento;
    const isEdicao = evento !== null;
    
    const tiposEvento = [
        { value: 'missa', label: 'Missa' },
        { value: 'reuniao', label: 'Reunião' },
        { value: 'formacao', label: 'Formação' },
        { value: 'acao_social', label: 'Ação Social' },
        { value: 'feira', label: 'Feira' },
        { value: 'festa_patronal', label: 'Festa Patronal' },
        { value: 'outro', label: 'Outro' }
    ];
    
    const tipoOptions = tiposEvento.map(t => 
        `<option value="${t.value}" ${evento && evento.tipo === t.value ? 'selected' : ''}>${t.label}</option>`
    ).join('');
    
    // Opções de membros para responsável
    const membrosOptions = '<option value="">Selecione um responsável</option>' +
        AppState.membros.map(m => 
            `<option value="${m.id}" ${evento && evento.responsavel_id === m.id ? 'selected' : ''}>${m.nome_completo || m.apelido}</option>`
        ).join('');
    
    const modalHTML = `
        <div id="modal-evento" class="modal show">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-${isEdicao ? 'edit' : 'plus'}"></i> ${isEdicao ? 'Editar' : 'Novo'} Evento
                        </h5>
                        <button type="button" class="close" onclick="fecharModalEvento()" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="form-evento">
                            <div class="form-group">
                                <label for="evento-nome">Nome do Evento *</label>
                                <input type="text" class="form-control" id="evento-nome" required 
                                       value="${evento ? escapeHtml(evento.nome || '') : ''}" placeholder="Ex: Missa de Natal">
                            </div>
                            
                            <div class="form-group">
                                <label for="evento-tipo">Tipo *</label>
                                <select class="form-control" id="evento-tipo" required>
                                    ${tipoOptions}
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="evento-data">Data *</label>
                                <input type="date" class="form-control" id="evento-data" required 
                                       value="${evento ? (evento.data_evento || evento.data || '') : ''}">
                            </div>
                            
                            <div class="form-group">
                                <label for="evento-hora-inicio">Hora de Início</label>
                                <input type="time" class="form-control" id="evento-hora-inicio" 
                                       value="${evento ? (formatarHora(evento.hora_inicio || evento.horario)) : ''}">
                            </div>
                            
                            <div class="form-group">
                                <label for="evento-hora-fim">Hora de Término</label>
                                <input type="time" class="form-control" id="evento-hora-fim" 
                                       value="${evento ? (formatarHora(evento.hora_fim)) : ''}">
                            </div>
                            
                            <div class="form-group">
                                <label for="evento-local">Local</label>
                                <input type="text" class="form-control" id="evento-local" 
                                       value="${evento ? escapeHtml(evento.local || '') : ''}" placeholder="Ex: Igreja Matriz">
                            </div>
                            
                            <div class="form-group">
                                <label for="evento-responsavel">Responsável</label>
                                <select class="form-control" id="evento-responsavel">
                                    ${membrosOptions}
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="evento-descricao">Descrição</label>
                                <textarea class="form-control" id="evento-descricao" rows="4" 
                                          placeholder="Descrição detalhada do evento...">${evento ? escapeHtml(evento.descricao || '') : ''}</textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="evento-url">URL do Evento (opcional)</label>
                                <input type="url" class="form-control" id="evento-url" 
                                       placeholder="https://exemplo.com/evento" 
                                       value="${evento ? escapeHtml(evento.eventos_url || evento.Eventos_url || '') : ''}">
                                <small class="form-text text-muted">Link externo relacionado ao evento (transmissão, inscrições, etc.)</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModalEvento()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" onclick="salvarEventoGeral()">
                            <i class="fas fa-save"></i> ${isEdicao ? 'Atualizar' : 'Salvar'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop" onclick="fecharModalEvento()"></div>
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
 * Salva evento geral (criar ou atualizar)
 */
async function salvarEventoGeral() {
    const form = document.getElementById('form-evento');
    if (!form || !form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // A tabela usa 'horario' (singular), não 'hora_inicio' e 'hora_fim'
    const horaInicio = document.getElementById('evento-hora-inicio').value || null;
    
    const dados = {
        nome: document.getElementById('evento-nome').value.trim(),
        tipo: document.getElementById('evento-tipo').value,
        data_evento: document.getElementById('evento-data').value,
        horario: horaInicio, // Usar horario em vez de hora_inicio
        local: document.getElementById('evento-local').value.trim() || null,
        responsavel_id: document.getElementById('evento-responsavel').value || null,
        descricao: document.getElementById('evento-descricao').value.trim() || null,
        Eventos_url: document.getElementById('evento-url').value.trim() || null
    };
    
    try {
        let response;
        const isEdicao = eventoEditando !== null;
        
        if (isEdicao) {
            // Atualizar
            response = await fetch(`${CONFIG.apiBaseUrl}eventos/atualizar?id=${eventoEditando.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(dados)
            });
        } else {
            // Criar
            response = await fetch(`${CONFIG.apiBaseUrl}eventos/criar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(dados)
            });
        }
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            alert(isEdicao ? 'Evento atualizado com sucesso!' : 'Evento criado com sucesso!');
            fecharModalEvento();
            await carregarEventosCalendario();
        } else {
            const errorMsg = result.error || result.message || 'Erro desconhecido';
            alert('Erro: ' + errorMsg);
        }
    } catch (error) {
        console.error('Erro ao salvar evento:', error);
        alert('Erro ao salvar evento: ' + (error.message || 'Erro desconhecido'));
    }
}

/**
 * Edita evento geral
 */
async function editarEventoGeral(eventoId) {
    try {
        const response = await fetch(`${CONFIG.apiBaseUrl}eventos/visualizar?id=${eventoId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success && result.data) {
            abrirModalEvento(result.data);
        } else {
            const errorMsg = result.error || result.message || 'Erro desconhecido';
            alert('Erro ao carregar evento: ' + errorMsg);
        }
    } catch (error) {
        console.error('Erro ao carregar evento:', error);
        alert('Erro ao carregar evento: ' + (error.message || 'Erro desconhecido'));
    }
}

/**
 * Confirma e exclui evento
 */
function confirmarExcluirEvento(eventoId) {
    if (confirm('Tem certeza que deseja excluir este evento?\n\nEsta ação não pode ser desfeita.')) {
        excluirEventoGeral(eventoId);
    }
}

/**
 * Exclui evento geral
 */
async function excluirEventoGeral(eventoId) {
    try {
        const response = await fetch(`${CONFIG.apiBaseUrl}eventos/excluir?id=${eventoId}`, {
            method: 'DELETE'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            alert('Evento excluído com sucesso!');
            fecharModalDetalhesEvento();
            await carregarEventosCalendario();
        } else {
            const errorMsg = result.error || result.message || 'Erro desconhecido';
            alert('Erro: ' + errorMsg);
        }
    } catch (error) {
        console.error('Erro ao excluir evento:', error);
        alert('Erro ao excluir evento: ' + (error.message || 'Erro desconhecido'));
    }
}

// =====================================================
// UTILITÁRIOS
// =====================================================

/**
 * Escapa HTML para prevenir XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Formata hora para input type="time" (HH:MM)
 */
function formatarHora(hora) {
    if (!hora) return '';
    const horaStr = hora.toString();
    if (horaStr.length >= 5) {
        return horaStr.substring(0, 5);
    }
    return horaStr;
}

/**
 * Formata data para exibição
 */
function formatarData(data) {
    if (!data) return '-';
    const d = new Date(data);
    if (isNaN(d.getTime())) return data; // Se data inválida, retorna original
    return d.toLocaleDateString('pt-BR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Exportar funções para escopo global
window.abrirModalEvento = abrirModalEvento;
window.fecharModalEvento = fecharModalEvento;
window.salvarEventoGeral = salvarEventoGeral;
window.editarEventoGeral = editarEventoGeral;
window.confirmarExcluirEvento = confirmarExcluirEvento;
window.excluirEventoGeral = excluirEventoGeral;
window.mesAnterior = mesAnterior;
window.mesProximo = mesProximo;
window.mostrarEventosDoDia = mostrarEventosDoDia;
window.fecharModalEventosDia = fecharModalEventosDia;
window.abrirDetalhesEventoCalendario = abrirDetalhesEventoCalendario;
window.fecharModalDetalhesEvento = fecharModalDetalhesEvento;

