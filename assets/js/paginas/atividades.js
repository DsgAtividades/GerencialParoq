// JavaScript específico para o módulo de Atividades

// Array global para armazenar os relatórios (simulando banco de dados)
let relatorios = JSON.parse(localStorage.getItem('relatorios_atividades')) || [];

// Função de teste para verificar se o salvamento está funcionando
window.testarSalvamento = function() {
    console.log('=== TESTE DE SALVAMENTO ===');
    
    // Preencher formulário com dados de teste
    document.getElementById('setor').value = 'Teste Setor';
    document.getElementById('responsavel').value = 'denys';
    document.getElementById('inicio').value = '2024-01-01';
    document.getElementById('previsao').value = '2024-01-31';
    document.getElementById('status').value = 'em-andamento';
    document.getElementById('observacao').value = 'Teste de salvamento';
    
    console.log('Formulário preenchido com dados de teste');
    
    // Simular submissão
    const form = document.querySelector('#form-relatorio');
    if (form) {
        console.log('Formulário encontrado, simulando submissão...');
        const event = new Event('submit', { bubbles: true, cancelable: true });
        form.dispatchEvent(event);
    } else {
        console.error('Formulário não encontrado!');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('Script de atividades carregado!');
    console.log('Relatórios carregados do localStorage:', relatorios.length);
    
    // Inicializar a página
    initAtividades();
    
    function initAtividades() {
        console.log('Inicializando atividades...');
        
        // Configurar formulário de relatório
        setupFormularioRelatorio();
        
        // Atualizar dashboard
        atualizarDashboard();
        
        // Carregar relatórios na tabela
        carregarRelatorios();
        
        console.log('Atividades inicializadas com sucesso!');
    }
    
    function setupFormularioRelatorio() {
        const form = document.querySelector('#form-relatorio');
        console.log('Formulário encontrado:', form);
        
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Formulário submetido!');
                salvarRelatorio();
            });
        } else {
            console.error('Formulário não encontrado!');
        }
    }
    
    function salvarRelatorio() {
        const form = document.querySelector('#form-relatorio');
        const formData = new FormData(form);
        console.log('Salvando relatório...');
        
        // Validar campos obrigatórios
        const setor = formData.get('setor');
        const responsavel = formData.get('responsavel');
        const inicio = formData.get('inicio');
        const previsao = formData.get('previsao');
        const status = formData.get('status');
        
        console.log('Dados do formulário:', {
            setor, responsavel, inicio, previsao, status
        });
        
        if (!setor || !responsavel || !inicio || !previsao || !status) {
            console.log('Campos obrigatórios não preenchidos');
            mostrarMensagem('Por favor, preencha todos os campos obrigatórios.', 'error');
            return;
        }
        
        // Criar novo relatório
        const novoRelatorio = {
            id: gerarIdRelatorio(),
            setor: setor,
            responsavel: formData.get('responsavel'),
            inicio: formData.get('inicio'),
            previsao: formData.get('previsao'),
            termino: formData.get('termino') || '',
            status: formData.get('status'),
            observacao: formData.get('observacao') || '',
            dataCriacao: new Date().toISOString()
        };
        
        // Adicionar ao array de relatórios
        relatorios.push(novoRelatorio);
        console.log('Relatório adicionado:', novoRelatorio);
        console.log('Total de relatórios:', relatorios.length);
        
        // Salvar no localStorage
        localStorage.setItem('relatorios_atividades', JSON.stringify(relatorios));
        console.log('Relatórios salvos no localStorage');
        
        // Limpar formulário
        form.reset();
        console.log('Formulário limpo');
        
        // Atualizar dashboard e tabela
        atualizarDashboard();
        carregarRelatorios();
        console.log('Dashboard e tabela atualizados');
        
        // Mostrar mensagem de sucesso
        mostrarMensagem('Relatório criado com sucesso!', 'success');
        
        // Ir para a aba de relatórios
        const linkRelatorios = document.querySelector('a[data-section="relatorios"]');
        if (linkRelatorios) {
            linkRelatorios.click();
        }
    }
    
    function gerarIdRelatorio() {
        const timestamp = Date.now();
        const random = Math.floor(Math.random() * 1000);
        return `ATV${timestamp.toString().slice(-6)}${random}`;
    }
    
    function carregarRelatorios() {
        const tbody = document.getElementById('tabela-relatorios');
        const semRelatorios = document.getElementById('sem-relatorios');
        
        if (relatorios.length === 0) {
            semRelatorios.style.display = 'table-row';
            return;
        }
        
        semRelatorios.style.display = 'none';
        
        // Limpar tbody (exceto a linha "sem relatórios")
        const rows = tbody.querySelectorAll('tr:not(#sem-relatorios)');
        rows.forEach(row => row.remove());
        
        // Adicionar cada relatório na tabela
        relatorios.forEach(relatorio => {
            const row = criarLinhaRelatorio(relatorio);
            tbody.appendChild(row);
        });
    }
    
    function criarLinhaRelatorio(relatorio) {
        const tr = document.createElement('tr');
        
        const statusClass = getStatusClass(relatorio.status);
        const statusText = getStatusText(relatorio.status);
        
        tr.innerHTML = `
            <td>${relatorio.id}</td>
            <td>${relatorio.setor}</td>
            <td>${relatorio.responsavel}</td>
            <td>${formatarData(relatorio.inicio)}</td>
            <td>${formatarData(relatorio.previsao)}</td>
            <td>${relatorio.termino ? formatarData(relatorio.termino) : '-'}</td>
            <td><span class="alert-module ${statusClass}" style="padding: 4px 8px; font-size: 0.8rem; margin: 0;">${statusText}</span></td>
            <td>
                <button class="btn-module btn-module-primary" style="padding: 6px 12px; font-size: 0.8rem;" onclick="visualizarRelatorio('${relatorio.id}')">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn-module btn-module-danger" style="padding: 6px 12px; font-size: 0.8rem;" onclick="excluirRelatorio('${relatorio.id}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        return tr;
    }
    
    function getStatusClass(status) {
        const statusClasses = {
            'a-fazer': 'alert-module-info',
            'em-andamento': 'alert-module-warning',
            'concluido': 'alert-module-success',
            'pausado': 'alert-module-secondary',
            'cancelado': 'alert-module-danger'
        };
        return statusClasses[status] || 'alert-module-info';
    }
    
    function getStatusText(status) {
        const statusTexts = {
            'a-fazer': 'A Fazer',
            'em-andamento': 'Em Andamento',
            'concluido': 'Concluído',
            'pausado': 'Pausado',
            'cancelado': 'Cancelado'
        };
        return statusTexts[status] || status;
    }
    
    function formatarData(data) {
        if (!data) return '-';
        const date = new Date(data);
        return date.toLocaleDateString('pt-BR');
    }
    
    function atualizarDashboard() {
        const totalRelatorios = relatorios.length;
        const emAndamento = relatorios.filter(r => r.status === 'em-andamento').length;
        const concluidos = relatorios.filter(r => r.status === 'concluido').length;
        const atrasados = relatorios.filter(r => {
            if (r.status === 'em-andamento' && r.previsao) {
                return new Date(r.previsao) < new Date();
            }
            return false;
        }).length;
        
        // Atualizar números no dashboard
        const stats = document.querySelectorAll('.stat-module-number');
        if (stats.length >= 4) {
            stats[0].textContent = emAndamento; // Atividades em Andamento
            stats[1].textContent = concluidos;  // Atividades Concluídas
            stats[2].textContent = atrasados;   // Atividades Atrasadas
            stats[3].textContent = totalRelatorios; // Total de Relatórios
        }
    }
    
    function mostrarMensagem(mensagem, tipo) {
        // Remover mensagens existentes
        const mensagensExistentes = document.querySelectorAll('.popup-mensagem');
        mensagensExistentes.forEach(msg => msg.remove());
        
        // Criar elemento de mensagem
        const mensagemDiv = document.createElement('div');
        mensagemDiv.className = 'popup-mensagem';
        
        const icone = tipo === 'success' ? '✅' : '❌';
        const corFundo = tipo === 'success' ? '#28a745' : '#dc3545';
        
        mensagemDiv.innerHTML = `
            <div class="popup-content">
                <div class="popup-icon">${icone}</div>
                <div class="popup-text">${mensagem}</div>
                <button class="popup-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        mensagemDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            background: ${corFundo};
            color: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            max-width: 400px;
            animation: slideInPopup 0.4s ease-out;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        `;
        
        // Adicionar ao body
        document.body.appendChild(mensagemDiv);
        
        // Remover após 4 segundos
        setTimeout(() => {
            if (mensagemDiv.parentNode) {
                mensagemDiv.style.animation = 'slideOutPopup 0.3s ease-in';
                setTimeout(() => {
                    if (mensagemDiv.parentNode) {
                        mensagemDiv.parentNode.removeChild(mensagemDiv);
                    }
                }, 300);
            }
        }, 4000);
    }
    
});

// Funções globais para os botões da tabela
window.visualizarRelatorio = function(id) {
    const relatorio = relatorios.find(r => r.id === id);
    if (relatorio) {
        const detalhes = `
ID: ${relatorio.id}
Setor: ${relatorio.setor}
Responsável: ${relatorio.responsavel}
Início: ${formatarData(relatorio.inicio)}
Previsão: ${formatarData(relatorio.previsao)}
Término: ${relatorio.termino ? formatarData(relatorio.termino) : 'Não informado'}
Status: ${getStatusText(relatorio.status)}
Observação: ${relatorio.observacao || 'Nenhuma observação'}
        `;
        alert(detalhes);
    }
};

window.excluirRelatorio = function(id) {
    if (confirm('Tem certeza que deseja excluir este relatório?')) {
        relatorios = relatorios.filter(r => r.id !== id);
        localStorage.setItem('relatorios_atividades', JSON.stringify(relatorios));
        carregarRelatorios();
        atualizarDashboard();
        mostrarMensagem('Relatório excluído com sucesso!', 'success');
    }
};

// Adicionar estilos CSS para animações
const style = document.createElement('style');
style.textContent = `
    .popup-content {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        gap: 12px;
    }
    
    .popup-icon {
        font-size: 24px;
        flex-shrink: 0;
    }
    
    .popup-text {
        flex: 1;
        font-size: 16px;
        font-weight: 500;
    }
    
    .popup-close {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background-color 0.2s ease;
    }
    
    .popup-close:hover {
        background-color: rgba(255,255,255,0.2);
    }
    
    @keyframes slideInPopup {
        from {
            transform: translateX(100%) scale(0.8);
            opacity: 0;
        }
        to {
            transform: translateX(0) scale(1);
            opacity: 1;
        }
    }
    
    @keyframes slideOutPopup {
        from {
            transform: translateX(0) scale(1);
            opacity: 1;
        }
        to {
            transform: translateX(100%) scale(0.8);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
