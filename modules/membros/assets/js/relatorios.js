/**
 * Relatórios e Análises - Módulo Membros
 * Carrega e exibe relatórios visuais com gráficos
 */

// Variáveis globais para armazenar os gráficos
let charts = {};

// Aguardar CONFIG estar disponível (definido em membros.js)
function getApiBaseUrl() {
    if (typeof CONFIG !== 'undefined' && CONFIG.apiBaseUrl) {
        return CONFIG.apiBaseUrl;
    }
    // Fallback: detectar caminho se CONFIG não estiver disponível
    const path = window.location.pathname;
    const basePath = path.replace(/\/[^\/]*\.php$/, '').replace(/\/index\.html$/, '');
    return basePath + '/api/';
}

/**
 * Carregar todos os relatórios
 */
async function carregarRelatorios() {
    console.log('=== Iniciando carregamento de relatórios ===');
    console.log('API Base URL:', getApiBaseUrl());
    
    // Verificar se Chart.js está disponível
    if (typeof Chart === 'undefined') {
        console.error('Chart.js não está carregado! Aguardando...');
        setTimeout(() => {
            if (typeof Chart !== 'undefined') {
                carregarRelatorios();
            } else {
                mostrarErroGeral('Biblioteca de gráficos não carregada. Recarregue a página.');
            }
        }, 1000);
        return;
    }
    
    try {
        // Carregar todos os relatórios em paralelo
        await Promise.all([
            carregarMembrosPorPastoral(),
            carregarMembrosPorStatus(),
            carregarMembrosPorGenero(),
            carregarMembrosPorFaixaEtaria(),
            carregarCrescimentoTemporal(),
            carregarMembrosSemPastoral(),
            carregarAniversariantes()
        ]);
        
        console.log('✅ Todos os relatórios carregados com sucesso');
    } catch (error) {
        console.error('❌ Erro ao carregar relatórios:', error);
        mostrarErroGeral('Erro ao carregar alguns relatórios. Verifique o console para mais detalhes.');
    }
}

/**
 * R1: Membros por Pastoral
 */
async function carregarMembrosPorPastoral() {
    try {
        const url = `${getApiBaseUrl()}relatorios/membros-por-pastoral`;
        console.log('Carregando membros por pastoral:', url);
        
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Resposta membros por pastoral:', result);
        
        if (result.success && result.data) {
            const data = result.data;
            
            // Atualizar total
            const totalElement = document.getElementById('total-pastorais');
            if (totalElement) {
                totalElement.textContent = `${data.total} membros em ${data.pastorais} pastorais`;
            }
            
            // Criar gráfico pizza
            const ctx = document.getElementById('chart-membros-pastoral');
            if (ctx) {
                if (charts.membrosPastoral) {
                    charts.membrosPastoral.destroy();
                }
                
                charts.membrosPastoral = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 10
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        } else {
            console.warn('Resposta sem dados válidos:', result);
        }
    } catch (error) {
        console.error('Erro ao carregar membros por pastoral:', error);
        const ctx = document.getElementById('chart-membros-pastoral');
        if (ctx && ctx.parentElement) {
            ctx.parentElement.innerHTML = '<div class="relatorio-list-empty">Erro ao carregar dados</div>';
        }
    }
}

/**
 * R2: Membros por Status
 */
async function carregarMembrosPorStatus() {
    try {
        const response = await fetch(`${getApiBaseUrl()}relatorios/membros-por-status`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            
            document.getElementById('total-status').textContent = `${data.total} membros`;
            
            const ctx = document.getElementById('chart-membros-status');
            if (ctx) {
                if (charts.membrosStatus) {
                    charts.membrosStatus.destroy();
                }
                
                charts.membrosStatus = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        }
    } catch (error) {
        console.error('Erro ao carregar membros por status:', error);
    }
}

/**
 * R3: Membros por Gênero
 */
async function carregarMembrosPorGenero() {
    try {
        const response = await fetch(`${getApiBaseUrl()}relatorios/membros-por-genero`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            
            document.getElementById('total-genero').textContent = `${data.total} membros`;
            
            const ctx = document.getElementById('chart-membros-genero');
            if (ctx) {
                if (charts.membrosGenero) {
                    charts.membrosGenero.destroy();
                }
                
                charts.membrosGenero = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    } catch (error) {
        console.error('Erro ao carregar membros por gênero:', error);
    }
}

/**
 * R4: Membros por Faixa Etária
 */
async function carregarMembrosPorFaixaEtaria() {
    try {
        const response = await fetch(`${getApiBaseUrl()}relatorios/membros-por-faixa-etaria`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            
            document.getElementById('total-faixa-etaria').textContent = `${data.total} membros`;
            
            const ctx = document.getElementById('chart-faixa-etaria');
            if (ctx) {
                if (charts.faixaEtaria) {
                    charts.faixaEtaria.destroy();
                }
                
                charts.faixaEtaria = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        }
    } catch (error) {
        console.error('Erro ao carregar faixa etária:', error);
    }
}

/**
 * R5: Crescimento Temporal
 */
async function carregarCrescimentoTemporal() {
    try {
        const response = await fetch(`${getApiBaseUrl()}relatorios/crescimento-temporal`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            
            const ctx = document.getElementById('chart-crescimento');
            if (ctx) {
                if (charts.crescimento) {
                    charts.crescimento.destroy();
                }
                
                charts.crescimento = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        }
    } catch (error) {
        console.error('Erro ao carregar crescimento temporal:', error);
    }
}

/**
 * R6: Membros sem Pastoral
 */
async function carregarMembrosSemPastoral() {
    try {
        const response = await fetch(`${getApiBaseUrl()}relatorios/membros-sem-pastoral`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            
            // Atualizar número
            document.getElementById('numero-sem-pastoral').textContent = data.total;
            
            // Atualizar lista
            const lista = document.getElementById('lista-sem-pastoral');
            if (lista) {
                if (data.membros && data.membros.length > 0) {
                    lista.innerHTML = data.membros.slice(0, 5).map(membro => `
                        <div class="relatorio-list-item">
                            <i class="fas fa-user"></i>
                            <span>${membro.nome_completo || 'Sem nome'}</span>
                        </div>
                    `).join('');
                    
                    if (data.total > 5) {
                        lista.innerHTML += `
                            <div class="relatorio-list-more">
                                +${data.total - 5} mais...
                            </div>
                        `;
                    }
                } else {
                    lista.innerHTML = '<div class="relatorio-list-empty">Nenhum membro sem pastoral</div>';
                }
            }
        }
    } catch (error) {
        console.error('Erro ao carregar membros sem pastoral:', error);
    }
}

/**
 * R7: Aniversariantes do Mês
 */
async function carregarAniversariantes() {
    try {
        const response = await fetch(`${getApiBaseUrl()}relatorios/aniversariantes`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            
            // Atualizar número
            document.getElementById('numero-aniversariantes').textContent = data.total;
            
            // Atualizar lista
            const lista = document.getElementById('lista-aniversariantes');
            if (lista) {
                if (data.aniversariantes && data.aniversariantes.length > 0) {
                    lista.innerHTML = data.aniversariantes.slice(0, 5).map(pessoa => {
                        const idade = pessoa.idade ? ` (${pessoa.idade} anos)` : '';
                        return `
                            <div class="relatorio-list-item">
                                <i class="fas fa-birthday-cake"></i>
                                <span>${pessoa.nome_completo || 'Sem nome'}</span>
                                <small>Dia ${pessoa.dia}${idade}</small>
                            </div>
                        `;
                    }).join('');
                    
                    if (data.total > 5) {
                        lista.innerHTML += `
                            <div class="relatorio-list-more">
                                +${data.total - 5} mais...
                            </div>
                        `;
                    }
                } else {
                    lista.innerHTML = '<div class="relatorio-list-empty">Nenhum aniversariante este mês</div>';
                }
            }
        }
    } catch (error) {
        console.error('Erro ao carregar aniversariantes:', error);
    }
}

/**
 * Mostrar erro geral
 */
function mostrarErroGeral(mensagem) {
    // Criar notificação de erro
    const notification = document.createElement('div');
    notification.className = 'notification error';
    notification.textContent = mensagem;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Carregar relatórios quando a seção for exibida
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se já está na seção de relatórios
    const relatoriosSection = document.getElementById('relatorios');
    if (relatoriosSection && relatoriosSection.classList.contains('active')) {
        // Aguardar um pouco para garantir que Chart.js está carregado
        setTimeout(() => {
            carregarRelatorios();
        }, 500);
    }
    
    // Observar mudanças na seção de relatórios
    if (relatoriosSection) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    if (relatoriosSection.classList.contains('active')) {
                        // Verificar se os gráficos já foram carregados
                        const firstChart = document.getElementById('chart-membros-pastoral');
                        if (firstChart && !charts.membrosPastoral) {
                            console.log('Carregando relatórios pela primeira vez...');
                            carregarRelatorios();
                        }
                    }
                }
            });
        });
        
        observer.observe(relatoriosSection, {
            attributes: true,
            attributeFilter: ['class']
        });
    }
    
    // Também escutar eventos de clique nos links de navegação
    const navLinks = document.querySelectorAll('.nav-link[data-section="relatorios"]');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            setTimeout(() => {
                if (relatoriosSection && relatoriosSection.classList.contains('active')) {
                    if (!charts.membrosPastoral) {
                        console.log('Carregando relatórios ao clicar no link...');
                        carregarRelatorios();
                    }
                }
            }, 300);
        });
    });
});

// Tornar função global para o botão de atualizar
window.carregarRelatorios = carregarRelatorios;

