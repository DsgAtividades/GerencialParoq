document.addEventListener('DOMContentLoaded', function() {
    // Carregar relatórios do banco de dados
    carregarRelatoriosBanco();
    
    // Atualizar dashboard
    atualizarDashboardBanco();
    
    // Adicionar evento de submit ao formulário
    const formRelatorio = document.getElementById('form-relatorio');
    if (formRelatorio) {
        formRelatorio.addEventListener('submit', function(e) {
            e.preventDefault();
            salvarRelatorioBanco();
        });
    }
    
    // Adicionar evento para recarregar relatórios quando a aba for clicada
    const abaRelatorios = document.querySelector('[data-section="relatorios"]');
    if (abaRelatorios) {
        abaRelatorios.addEventListener('click', function() {
            // Pequeno delay para garantir que a aba seja ativada primeiro
            setTimeout(() => {
                carregarRelatoriosBanco();
            }, 100);
        });
    }
});

// Função para salvar relatório no banco de dados
async function salvarRelatorioBanco() {
    const form = document.getElementById('form-relatorio');
    const formData = new FormData(form);
    
    
    // Verificar se está editando (tem relatorio_id)
    const relatorioId = form.getAttribute('data-relatorio-id');
    const isEditing = relatorioId && relatorioId !== '';
    
    // Validar campos obrigatórios
    const tituloAtividade = formData.get('titulo_atividade');
    const tituloAtividadeTrimmed = tituloAtividade ? tituloAtividade.trim() : '';
    const setor = formData.get('setor');
    const setorTrimmed = setor ? setor.trim() : '';
    const responsavel = formData.get('responsavel');
    const dataInicio = formData.get('data_inicio');
    const dataPrevisao = formData.get('data_previsao');
    
    
    if (!tituloAtividadeTrimmed || !setorTrimmed || !responsavel || !dataInicio || !dataPrevisao) {
        mostrarMensagem('Por favor, preencha todos os campos obrigatórios.', 'error');
        return;
    }
    
    try {
        // Se estiver editando, adicionar o ID do relatório
        if (isEditing) {
            formData.append('relatorio_id', relatorioId);
        }
        
        const url = isEditing ? 'atualizar_relatorio.php' : 'salvar_relatorio.php';
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            const mensagem = isEditing ? 'Relatório atualizado com sucesso!' : 'Relatório salvo com sucesso!';
            mostrarMensagem(mensagem, 'success');
            
            // Se estava editando, remover o atributo de edição
            if (isEditing) {
                form.removeAttribute('data-relatorio-id');
            }
            
            form.reset();
            carregarRelatoriosBanco();
            atualizarDashboardBanco();
        } else {
            mostrarMensagem(result.message || 'Erro ao salvar relatório', 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro de conexão. Tente novamente.', 'error');
    }
}

// Função para carregar relatórios do banco de dados
async function carregarRelatoriosBanco() {
    console.log('🔄 Iniciando carregamento de relatórios...');
    
    try {
        console.log('📡 Fazendo requisição para buscar_relatorios.php...');
        const response = await fetch('buscar_relatorios.php');
        
        console.log('📥 Resposta recebida:', response.status, response.statusText);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('📊 Dados recebidos:', result);
        
        if (result.success) {
            console.log('✅ Sucesso! Exibindo', result.relatorios.length, 'relatórios');
            exibirRelatorios(result.relatorios);
        } else {
            console.error('❌ Erro ao carregar relatórios:', result.message);
            alert('Erro ao carregar relatórios: ' + result.message);
        }
    } catch (error) {
        console.error('❌ Erro ao carregar relatórios:', error);
        alert('Erro de conexão: ' + error.message);
    }
}

// Função para exibir relatórios na tabela
function exibirRelatorios(relatorios) {
    const tbody = document.getElementById('tabela-relatorios');
    
    if (!tbody) return;
    
    // Limpar tabela
    tbody.innerHTML = '';
    
    if (relatorios.length === 0) {
        tbody.innerHTML = `
            <tr id="sem-relatorios">
                <td colspan="8" style="text-align: center; color: #7f8c8d; padding: 40px;">
                    <i class="fas fa-file-alt" style="font-size: 2rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                    Nenhum relatório criado ainda.<br>
                    Crie seu primeiro relatório na aba "Criar Relatório de Atividade".
                </td>
            </tr>
        `;
        return;
    }
    
    // Adicionar relatórios à tabela
    relatorios.forEach(relatorio => {
        const linha = criarLinhaRelatorio(relatorio);
        tbody.appendChild(linha);
    });
}

// Função para criar linha de relatório
function criarLinhaRelatorio(relatorio) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>${relatorio.titulo_atividade}</td>
        <td>${relatorio.setor}</td>
        <td>${relatorio.responsavel}</td>
        <td>${formatarData(relatorio.data_inicio)}</td>
        <td>${formatarData(relatorio.data_previsao)}</td>
        <td>${relatorio.data_termino ? formatarData(relatorio.data_termino) : '-'}</td>
        <td><span class="status-badge ${getStatusClass(relatorio.status)}">${getStatusText(relatorio.status)}</span></td>
        <td>
            <div class="action-buttons">
                <button onclick="visualizarRelatorio(${relatorio.id})" class="btn-action btn-view" title="Visualizar">
                    <i class="fas fa-eye"></i>
                </button>
                <button onclick="editarRelatorio(${relatorio.id})" class="btn-action btn-edit" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="excluirRelatorio(${relatorio.id})" class="btn-action btn-delete" title="Excluir">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    `;
    return tr;
}

// Função para obter classe CSS do status
function getStatusClass(status) {
    const classes = {
        'em_andamento': 'status-em-andamento',
        'concluido': 'status-concluido',
        'a_fazer': 'status-pendente',
        'pausado': 'status-pausado',
        'cancelado': 'status-cancelado'
    };
    return classes[status] || 'status-pendente';
}

// Função para obter texto do status
function getStatusText(status) {
    const textos = {
        'em_andamento': 'Em Andamento',
        'concluido': 'Concluído',
        'a_fazer': 'A Fazer',
        'pausado': 'Pausado',
        'cancelado': 'Cancelado'
    };
    return textos[status] || 'A Fazer';
}

// Função para formatar data
function formatarData(data) {
    if (!data) return '-';
    const date = new Date(data);
    return date.toLocaleDateString('pt-BR');
}

// Função para mostrar mensagem
function mostrarMensagem(mensagem, tipo = 'success') {
    // Remover popup existente
    const popupExistente = document.querySelector('.popup-overlay');
    if (popupExistente) {
        popupExistente.remove();
    }
    
    const popup = document.createElement('div');
    popup.className = 'popup-overlay';
    popup.innerHTML = `
        <div class="popup-content">
            <div class="popup-icon">
                ${tipo === 'success' ? '✅' : '❌'}
            </div>
            <div class="popup-text">${mensagem}</div>
            <button class="popup-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    document.body.appendChild(popup);
    
    // Auto-remover após 3 segundos
    setTimeout(() => {
        if (popup.parentNode) {
            popup.remove();
        }
    }, 3000);
}

// Função para atualizar dashboard
async function atualizarDashboardBanco() {
    try {
        console.log('Atualizando dashboard...');
        const response = await fetch('buscar_relatorios.php');
        const result = await response.json();
        
        if (result.success) {
            const relatorios = result.relatorios;
            console.log('Relatórios para dashboard:', relatorios.length);
            
            // Calcular estatísticas
            const totalRelatorios = relatorios.length;
            const relatoriosConcluidos = relatorios.filter(r => r.status === 'concluido').length;
            const relatoriosEmAndamento = relatorios.filter(r => r.status === 'em_andamento').length;
            const relatoriosPendentes = relatorios.filter(r => r.status === 'a_fazer').length;
            
            console.log('Estatísticas:', { totalRelatorios, relatoriosConcluidos, relatoriosEmAndamento, relatoriosPendentes });
            
            // Atualizar elementos do dashboard
            const totalElement = document.querySelector('.stat-card:nth-child(1) .stat-number');
            const concluidosElement = document.querySelector('.stat-card:nth-child(2) .stat-number');
            const andamentoElement = document.querySelector('.stat-card:nth-child(3) .stat-number');
            const pendentesElement = document.querySelector('.stat-card:nth-child(4) .stat-number');
            
            console.log('Elementos encontrados:', { totalElement, concluidosElement, andamentoElement, pendentesElement });
            
            if (totalElement) {
                totalElement.textContent = totalRelatorios;
                console.log('Total atualizado para:', totalRelatorios);
            }
            if (concluidosElement) {
                concluidosElement.textContent = relatoriosConcluidos;
                console.log('Concluídos atualizado para:', relatoriosConcluidos);
            }
            if (andamentoElement) {
                andamentoElement.textContent = relatoriosEmAndamento;
                console.log('Em andamento atualizado para:', relatoriosEmAndamento);
            }
            if (pendentesElement) {
                pendentesElement.textContent = relatoriosPendentes;
                console.log('Pendentes atualizado para:', relatoriosPendentes);
            }
        }
    } catch (error) {
        console.error('Erro ao atualizar dashboard:', error);
    }
}

// Função para visualizar relatório
async function visualizarRelatorio(id) {
    try {
        const response = await fetch('buscar_relatorios.php');
        const result = await response.json();
        
        if (result.success) {
            const relatorio = result.relatorios.find(r => r.id == id);
            
            if (!relatorio) {
                mostrarMensagem('Relatório não encontrado.', 'error');
                return;
            }
            
            // Remover modal existente se houver
            const modalExistente = document.querySelector('.modal-overlay');
            if (modalExistente) {
                modalExistente.remove();
            }
            
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>📋 Visualizar Relatório</h3>
                        <button class="modal-close" onclick="fecharModal()">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Título da Atividade:</label>
                            <p>${relatorio.titulo_atividade}</p>
                        </div>
                        <div class="form-group">
                            <label>Setor:</label>
                            <p>${relatorio.setor}</p>
                        </div>
                        <div class="form-group">
                            <label>Responsável:</label>
                            <p>${relatorio.responsavel}</p>
                        </div>
                        <div class="form-group">
                            <label>Data de Início:</label>
                            <p>${formatarData(relatorio.data_inicio)}</p>
                        </div>
                        <div class="form-group">
                            <label>Previsão de Término:</label>
                            <p>${formatarData(relatorio.data_previsao)}</p>
                        </div>
                        <div class="form-group">
                            <label>Data de Término:</label>
                            <p>${relatorio.data_termino ? formatarData(relatorio.data_termino) : 'Não informado'}</p>
                        </div>
                        <div class="form-group">
                            <label>Status:</label>
                            <p><span class="status-badge ${getStatusClass(relatorio.status)}">${getStatusText(relatorio.status)}</span></p>
                        </div>
                        <div class="form-group">
                            <label>Observação:</label>
                            <p>${relatorio.observacao || 'Nenhuma observação'}</p>
                        </div>
                    </div>
                </div>
            `;
            
            // Adicionar evento para fechar clicando fora do modal
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    fecharModal();
                }
            });
            
            // Adicionar evento para fechar com ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    fecharModal();
                }
            });
            
            document.body.appendChild(modal);
            
            // Focar no modal para acessibilidade
            modal.focus();
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao carregar relatório.', 'error');
    }
}

// Função para fechar modal
function fecharModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) {
        modal.remove();
    }
}

// Função para editar relatório
async function editarRelatorio(id) {
    try {
        const response = await fetch('buscar_relatorios.php');
        const result = await response.json();
        
        if (result.success) {
            const relatorio = result.relatorios.find(r => r.id == id);
            
            if (!relatorio) {
                mostrarMensagem('Relatório não encontrado.', 'error');
                return;
            }
            
                    // Preencher formulário com dados do relatório
                    document.getElementById('titulo_atividade').value = relatorio.titulo_atividade;
                    document.getElementById('setor').value = relatorio.setor;
                    document.getElementById('responsavel').value = relatorio.responsavel;
                    document.getElementById('data_inicio').value = relatorio.data_inicio;
                    document.getElementById('data_previsao').value = relatorio.data_previsao;
                    document.getElementById('data_termino').value = relatorio.data_termino || '';
                    document.getElementById('status').value = relatorio.status;
                    document.getElementById('observacao').value = relatorio.observacao || '';
            
            // Mudar para a aba de criação
            const abaCriar = document.querySelector('[data-section="criar-relatorio"]');
            if (abaCriar) {
                abaCriar.click();
            }
            
            // Adicionar ID do relatório para atualização
            const form = document.getElementById('form-relatorio');
            form.setAttribute('data-relatorio-id', id);
            
            mostrarMensagem('Relatório carregado para edição. Modifique os campos e clique em "Gerar Relatório" para salvar.', 'success');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao carregar relatório.', 'error');
    }
}

// Função para excluir relatório
async function excluirRelatorio(id) {
    if (!confirm('Tem certeza que deseja excluir este relatório?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('relatorio_id', id);
        
        const response = await fetch('excluir_relatorio.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarMensagem('Relatório excluído com sucesso!', 'success');
            carregarRelatoriosBanco();
            atualizarDashboardBanco();
        } else {
            mostrarMensagem(result.message || 'Erro ao excluir relatório', 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro de conexão. Tente novamente.', 'error');
    }
}
