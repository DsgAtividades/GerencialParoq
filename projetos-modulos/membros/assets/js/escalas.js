// JS de Escalas - semana corrente, eventos e modais

async function escalasCarregarSemana(pastoralId) {
    const { start, end } = escalasObterSemanaCorrente();
    const url = `api/escalas/semana?pastoral_id=${encodeURIComponent(pastoralId)}&start=${start}&end=${end}`;
    try {
        const resp = await fetch(url);
        const data = await resp.json();
        if (data.success) {
            escalasRenderSemana(data.data || [], start);
        }
    } catch (e) {
        console.error('Erro ao carregar escalas:', e);
    }
}

function escalasObterSemanaCorrente() {
    const hoje = new Date();
    const diaSemana = (hoje.getDay() + 6) % 7; // segunda=0
    const segunda = new Date(hoje);
    segunda.setDate(hoje.getDate() - diaSemana);
    const domingo = new Date(segunda);
    domingo.setDate(segunda.getDate() + 6);
    const toYMD = (d) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    return { start: toYMD(segunda), end: toYMD(domingo) };
}

function escalasRenderSemana(eventos, start) {
    const container = document.getElementById('escala-semana');
    if (!container) return;
    const base = new Date(start + 'T00:00:00');
    const diasSemana = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];

    // Cabeçalho com nomes dos dias
    const head = diasSemana.map(d => `<th>${d}</th>`).join('');

    // Células do corpo (uma linha com 7 colunas)
    const tds = Array.from({ length: 7 }).map((_, i) => {
        const d = new Date(base);
        d.setDate(base.getDate() + i);
        const iso = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        const doDia = (eventos || []).filter(ev => ev.data === iso);
        const chips = doDia.map(ev => `
            <div class="esc-chip" onclick="escalasAbrirModalEventoDetalhe('${ev.id}')">
                ${ev.hora ? ev.hora.substring(0,5) : ''} ${ev.titulo || ''}
            </div>
        `).join('');
        return `
            <td class="esc-td">
                <div class="esc-day-number">${String(d.getDate()).padStart(2, '0')}</div>
                <div class="esc-day-events">${chips || '<span class="esc-empty">—</span>'}</div>
            </td>
        `;
    }).join('');

    container.innerHTML = `
        <table class="esc-week-table">
            <thead><tr>${head}</tr></thead>
            <tbody><tr>${tds}</tr></tbody>
        </table>
    `;
}

function escalasAbrirModalEvento() {
    const html = `
    <div id="modal-esc-evento" class="modal fade show" style="display:block;">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-calendar-plus"></i> Novo Evento</h5>
            <button type="button" class="close" onclick="escalasFecharModalEvento()"><span>&times;</span></button>
          </div>
          <div class="modal-body">
            <form id="form-esc-evento">
              <div class="form-group"><label>Título*</label><input class="form-control" name="titulo" required></div>
              <div class="form-row">
                <div class="form-group col-md-6"><label>Data*</label><input type="date" class="form-control" name="data" required></div>
                <div class="form-group col-md-6"><label>Horário*</label><input type="time" class="form-control" name="hora" required></div>
              </div>
              <div class="form-group"><label>Descrição</label><textarea class="form-control" name="descricao" rows="3"></textarea></div>
            </form>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" onclick="escalasFecharModalEvento()">Cancelar</button>
            <button class="btn btn-primary" onclick="escalasSalvarEvento()"><i class="fas fa-save"></i> Salvar</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" onclick="escalasFecharModalEvento()"></div>`;
    const container = document.getElementById('modal-container') || document.body;
    container.insertAdjacentHTML('beforeend', html);
}

function escalasFecharModalEvento() {
    document.getElementById('modal-esc-evento')?.remove();
    document.querySelector('.modal-backdrop')?.remove();
}

async function escalasSalvarEvento() {
    const f = document.getElementById('form-esc-evento');
    if (!f || !f.checkValidity()) { f?.reportValidity(); return; }
    const fd = new FormData(f);
    const dados = {
        titulo: fd.get('titulo'),
        data: fd.get('data'),
        hora: fd.get('hora'),
        descricao: fd.get('descricao') || null
    };
    const pastoralId = window.PastoralState?.pastoral?.id || window.pastoralId || window.pastoral_id;
    if (!pastoralId) {
        console.error('PastoralId não definido ao salvar evento de escala');
        return;
    }
    try {
        const resp = await fetch(`api/pastorais/${pastoralId}/escalas/eventos`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(dados)});
        const j = await resp.json();
        if (j.success) {
            escalasFecharModalEvento();
            await escalasCarregarSemana(pastoralId);
        }
    } catch(e){ console.error('Erro salvar evento:', e); }
}

function escalasAbrirModalEventoDetalhe(eventoId) {
    escalasGarantirMembros(window.pastoralId || (window.PastoralState?.pastoral?.id)).then(()=>
    escalasCarregarEventoDetalhe(eventoId)).then((detalhe)=>{
        const funcoes = detalhe.funcoes || [];
        const membros = (window.PastoralState?.membros || []).map(m=>({id:m.id, nome: m.nome_completo || m.apelido || '-'}));
        const listaMembros = membros.map(m=>`<div class="membro-item" draggable="true" ondragstart="escDrag(event,'${m.id}','${m.nome.replace(/'/g,"\\'")}')">${m.nome}</div>`).join('');
        const colFuncoes = funcoes.map(f=>`
            <div class="funcao-bloco">
                <div class="funcao-titulo">${f.nome}</div>
                <div class="drop-area" ondragover="escAllow(event)" ondrop="escDrop(event,'${f.id}')">
                    ${(f.membros||[]).map(mm=>`<span class="tag-membro" data-id="${mm.id}">${mm.nome}<i onclick=\"escRemoverMembro('${f.id}','${mm.id}')\">×</i></span>`).join('')}
                </div>
            </div>
        `).join('');
        const html = `
        <div id="modal-esc-detalhe" class="modal fade show" style="display:block;">
          <div class="modal-dialog modal-xl">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-list"></i> Escala do Evento</h5>
                <button type="button" class="close" onclick="escFecharDetalhe()"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <div class="esc-flex">
                  <div class="esc-col membros-col">
                    <div class="col-title">Membros</div>
                    <div class="membros-lista">${listaMembros || '<div class="text-muted">Sem membros</div>'}</div>
                  </div>
                  <div class="esc-col funcoes-col">
                    <div class="col-title">Funções <button class="btn btn-sm btn-outline-primary" onclick="escAdicionarFuncao('${eventoId}')">Adicionar função</button></div>
                    <div id="funcoes-container">${colFuncoes || '<div class="text-muted">Nenhuma função</div>'}</div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <a class="btn btn-secondary" href="api/eventos/${eventoId}/export/txt" target="_blank"><i class="fas fa-file-alt"></i> Exportar TXT</a>
                <button class="btn btn-primary" onclick="escSalvar('${eventoId}')"><i class="fas fa-save"></i> Salvar Escala</button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-backdrop fade show" onclick="escFecharDetalhe()"></div>`;
        const container = document.getElementById('modal-container') || document.body;
        container.insertAdjacentHTML('beforeend', html);
        // renderizar funcoes com IDs reais
        escRenderFuncoes(eventoId, funcoes);
    });
}

async function escalasCarregarEventoDetalhe(eventoId){
    const resp = await fetch(`api/eventos/${eventoId}`);
    const j = await resp.json();
    return j.data || { funcoes: [] };
}

function escRenderFuncoes(eventoId, funcoes){
    const fc = document.getElementById('funcoes-container');
    if (!fc) return;
    if (!funcoes || funcoes.length === 0){
        fc.innerHTML = '<div class="text-muted">Nenhuma função</div>';
        return;
    }
    fc.innerHTML = funcoes.map(f=>{
        const membrosTags = (f.membros||[]).map(mm=>`<span class=\"tag-membro\" data-id=\"${mm.id}\">${mm.nome}<i onclick=\\"escRemoverMembro('${f.id}','${mm.id}')\\">×</i></span>`).join('');
        return `<div class=\"funcao-bloco\">\n            <div class=\"funcao-titulo\">${f.nome}</div>\n            <div class=\"drop-area\" data-funcao-id=\"${f.id}\" ondragover=\"escAllow(event)\" ondrop=\"escDrop(event,'${f.id}')\">${membrosTags}</div>\n        </div>`;
    }).join('');
}

function escAllow(e){ e.preventDefault(); }
function escDrag(e,id,nome){ e.dataTransfer.setData('text/plain', JSON.stringify({id,nome})); }
function escDrop(e, funcaoId){
    e.preventDefault();
    const data = JSON.parse(e.dataTransfer.getData('text/plain'));
    const area = e.currentTarget;
    // evitar duplicados visuais simples
    const exists = Array.from(area.querySelectorAll('.tag-membro')).some(t=>t.getAttribute('data-id')===data.id);
    if (!exists) {
        const span = document.createElement('span');
        span.className = 'tag-membro';
        span.setAttribute('data-id', data.id);
        span.innerHTML = `${data.nome}<i onclick=\"escRemoverMembro('${funcaoId}','${data.id}')\">×</i>`;
        area.appendChild(span);
    }
}
function escRemoverMembro(funcaoId, membroId){
    document.querySelectorAll(`.drop-area`).forEach(area=>{
        area.querySelectorAll('.tag-membro').forEach(tag=>{
            if (tag.getAttribute('data-id')===membroId) tag.remove();
        });
    });
}
function escFecharDetalhe(){
    document.getElementById('modal-esc-detalhe')?.remove();
    document.querySelector('.modal-backdrop')?.remove();
}
async function escAdicionarFuncao(eventoId){
    const nome = prompt('Nome da função:');
    if (!nome) return;
    const box = document.createElement('div');
    box.className = 'funcao-bloco';
    box.innerHTML = `<div class="funcao-titulo">${nome}</div><div class="drop-area" ondragover="escAllow(event)" ondrop="escDrop(event,'tmp-${Date.now()}')"></div>`;
    document.getElementById('funcoes-container')?.appendChild(box);
}
async function escSalvar(eventoId){
    const funcoes = [];
    document.querySelectorAll('#funcoes-container .funcao-bloco').forEach((bloco, idx)=>{
        const nome = bloco.querySelector('.funcao-titulo')?.textContent?.trim() || '';
        const drop = bloco.querySelector('.drop-area');
        const membros = Array.from(drop.querySelectorAll('.tag-membro')).map(t=>t.getAttribute('data-id'));
        // id não é conhecido nos novos; backend fará upsert
        funcoes.push({ id: drop.getAttribute('data-funcao-id') || null, nome, membros });
    });
    try {
        const resp = await fetch(`api/eventos/${eventoId}/funcoes`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ funcoes }) });
        const j = await resp.json();
        if (j.success) {
            // Recarregar detalhes para obter IDs reais e atualizar a UI sem fechar
            const detalhe = await escalasCarregarEventoDetalhe(eventoId);
            escRenderFuncoes(eventoId, detalhe.funcoes || []);
        }
    } catch(e){ console.error('Erro ao salvar escala:', e); }
}

async function escalasGarantirMembros(pastoralId){
    try {
        if (!window.PastoralState) window.PastoralState = { membros: [] };
        if (Array.isArray(window.PastoralState.membros) && window.PastoralState.membros.length > 0) return;
        if (!pastoralId) return;
        const resp = await fetch(`api/pastorais/${pastoralId}/membros`);
        const j = await resp.json();
        if (j.success && Array.isArray(j.data)) {
            window.PastoralState.membros = j.data;
        }
    } catch(e){ console.error('Erro ao carregar membros da pastoral para escalas:', e); }
}

// Exportar globais usadas em pastoral_detalhes
window.escalasAbrirModalEvento = escalasAbrirModalEvento;
window.escalasSalvarEvento = escalasSalvarEvento;
window.escalasAbrirModalEventoDetalhe = escalasAbrirModalEventoDetalhe;
window.escalasCarregarSemana = escalasCarregarSemana;

