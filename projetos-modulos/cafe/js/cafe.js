// Módulo Café - JavaScript Principal

let carrinho = [];
let produtos = [];

// Sistema de Toast Notifications
function mostrarToast(tipo, titulo, mensagem) {
    // Criar container se não existir
    let container = document.querySelector('.cafe-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'cafe-toast-container';
        document.body.appendChild(container);
    }
    
    // Criar toast
    const toast = document.createElement('div');
    toast.className = `cafe-toast cafe-toast-${tipo}`;
    
    // Ícones por tipo
    const icones = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-times-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info: '<i class="fas fa-info-circle"></i>'
    };
    
    toast.innerHTML = `
        <div class="cafe-toast-icon">${icones[tipo] || icones.info}</div>
        <div class="cafe-toast-content">
            <div class="cafe-toast-title">${titulo}</div>
            <div class="cafe-toast-message">${mensagem}</div>
        </div>
        <button class="cafe-toast-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    container.appendChild(toast);
    
    // Auto-remover após 3 segundos
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Loading Overlay
function mostrarLoading(mensagem = 'Carregando...') {
    const overlay = document.createElement('div');
    overlay.className = 'cafe-loading-overlay';
    overlay.id = 'cafe-loading';
    overlay.innerHTML = `
        <div>
            <div class="cafe-loading-spinner"></div>
            <div class="cafe-loading-text">${mensagem}</div>
        </div>
    `;
    document.body.appendChild(overlay);
}

function ocultarLoading() {
    const overlay = document.getElementById('cafe-loading');
    if (overlay) {
        overlay.remove();
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    initNavigation();
    loadDashboard();
    loadProdutos();
    loadEstoque();
    loadVendas();
    
    // Event listeners
    document.getElementById('form-produto').addEventListener('submit', salvarProduto);
    document.getElementById('form-venda').addEventListener('submit', confirmarVenda);
    document.getElementById('buscar-produto').addEventListener('input', filtrarProdutos);
    document.getElementById('venda-desconto').addEventListener('input', atualizarTotalVenda);
});

// Navegação entre seções
function initNavigation() {
    const links = document.querySelectorAll('.cafe-nav-link');
    const sections = document.querySelectorAll('.cafe-section');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetSection = this.getAttribute('data-section');
            
            // Atualizar links
            links.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Atualizar seções
            sections.forEach(s => s.classList.remove('active'));
            document.getElementById(targetSection).classList.add('active');
            
            // Carregar dados da seção
            if (targetSection === 'dashboard') {
                loadDashboard();
            } else if (targetSection === 'produtos') {
                loadProdutos();
            } else if (targetSection === 'estoque') {
                loadEstoque();
            } else if (targetSection === 'vendas') {
                loadVendas();
            } else if (targetSection === 'pdv') {
                loadProdutosPDV();
            }
        });
    });
}

// Dashboard
async function loadDashboard() {
    try {
        const response = await fetch('ajax/dashboard_stats.php');
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('stat-total-produtos').textContent = result.data.total_produtos;
            document.getElementById('stat-vendas-hoje').textContent = formatarMoeda(result.data.vendas_hoje);
            document.getElementById('stat-estoque-baixo').textContent = result.data.estoque_baixo;
            document.getElementById('stat-total-vendas').textContent = result.data.total_vendas;
            
            // Vendas recentes
            const tbody = document.getElementById('vendas-recentes-tbody');
            if (result.data.vendas_recentes.length > 0) {
                tbody.innerHTML = result.data.vendas_recentes.map(venda => `
                    <tr>
                        <td>${venda.numero_venda}</td>
                        <td>${formatarDataHora(venda.data_venda)}</td>
                        <td>${venda.cliente_nome || '-'}</td>
                        <td>${formatarMoeda(venda.total)}</td>
                        <td>${formatarPagamento(venda.forma_pagamento)}</td>
                        <td><span class="cafe-badge cafe-badge-success">${venda.status}</span></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px;">Nenhuma venda realizada ainda.</td></tr>';
            }
        }
    } catch(error) {
        console.error('Erro ao carregar dashboard:', error);
    }
}

// Produtos
async function loadProdutos() {
    try {
        const response = await fetch('ajax/produtos.php');
        const result = await response.json();
        
        if (result.success) {
            produtos = result.data;
            const tbody = document.getElementById('produtos-tbody');
            
            if (produtos.length > 0) {
                tbody.innerHTML = produtos.map(produto => `
                    <tr>
                        <td>${produto.codigo}</td>
                        <td>${produto.nome}</td>
                        <td>${produto.categoria || '-'}</td>
                        <td>${formatarMoeda(produto.preco_venda)}</td>
                        <td>${produto.estoque_atual} ${produto.unidade_medida}</td>
                        <td>
                            ${produto.ativo == 1 
                                ? '<span class="cafe-badge cafe-badge-success">Ativo</span>' 
                                : '<span class="cafe-badge cafe-badge-danger">Inativo</span>'}
                        </td>
                        <td>
                            <button class="cafe-btn cafe-btn-secondary" onclick="editarProduto(${produto.id})" style="padding: 8px 15px; font-size: 0.9rem;">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">Nenhum produto cadastrado.</td></tr>';
            }
        }
    } catch(error) {
        console.error('Erro ao carregar produtos:', error);
    }
}

async function loadProdutosPDV() {
    try {
        const response = await fetch('ajax/produtos.php');
        const result = await response.json();
        
        if (result.success) {
            produtos = result.data.filter(p => p.ativo == 1 && p.estoque_atual > 0);
            const grid = document.getElementById('produtos-grid');
            
            if (produtos.length > 0) {
                grid.innerHTML = produtos.map(produto => `
                    <div class="cafe-produto-card" onclick="adicionarAoCarrinho(${produto.id}, '${produto.nome}', ${produto.preco_venda}, ${produto.estoque_atual})">
                        <div class="cafe-produto-nome">${produto.nome}</div>
                        <div class="cafe-produto-preco">${formatarMoeda(produto.preco_venda)}</div>
                        <div class="cafe-produto-estoque">Estoque: ${produto.estoque_atual} ${produto.unidade_medida}</div>
                    </div>
                `).join('');
            } else {
                grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--cafe-creme);">Nenhum produto disponível.</div>';
            }
        }
    } catch(error) {
        console.error('Erro ao carregar produtos PDV:', error);
    }
}

function filtrarProdutos() {
    const termo = document.getElementById('buscar-produto').value.toLowerCase();
    const cards = document.querySelectorAll('.cafe-produto-card');
    
    cards.forEach(card => {
        const nome = card.querySelector('.cafe-produto-nome').textContent.toLowerCase();
        if (nome.includes(termo)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Carrinho
function adicionarAoCarrinho(id, nome, preco, estoque) {
    const itemExistente = carrinho.find(item => item.id === id);
    
    if (itemExistente) {
        if (itemExistente.quantidade >= estoque) {
            mostrarToast('warning', 'Estoque Insuficiente', `Não há mais unidades de "${nome}" disponíveis.`);
            return;
        }
        itemExistente.quantidade++;
        mostrarToast('success', 'Quantidade Atualizada', `${nome} - ${itemExistente.quantidade} unidades`);
    } else {
        carrinho.push({
            id: id,
            nome: nome,
            preco: preco,
            quantidade: 1,
            estoque: estoque
        });
        mostrarToast('success', 'Produto Adicionado', `${nome} adicionado ao carrinho`);
    }
    
    atualizarCarrinho();
}

function removerDoCarrinho(index) {
    carrinho.splice(index, 1);
    atualizarCarrinho();
}

function atualizarQuantidadeCarrinho(index, novaQuantidade) {
    if (novaQuantidade <= 0) {
        removerDoCarrinho(index);
        return;
    }
    
    const item = carrinho[index];
    if (novaQuantidade > item.estoque) {
        mostrarToast('warning', 'Estoque Insuficiente', `Apenas ${item.estoque} unidades disponíveis.`);
        // Restaurar valor anterior
        const input = document.querySelector(`input[onchange*="atualizarQuantidadeCarrinho(${index}"]`);
        if (input) input.value = item.quantidade;
        return;
    }
    
    item.quantidade = novaQuantidade;
    atualizarCarrinho();
}

function atualizarCarrinho() {
    const container = document.getElementById('carrinho-itens');
    const total = carrinho.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
    
    if (carrinho.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px; color: var(--cafe-creme); opacity: 0.7;">
                <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                Carrinho vazio
            </div>
        `;
    } else {
        container.innerHTML = carrinho.map((item, index) => `
            <div class="cafe-carrinho-item">
                <div>
                    <div style="font-weight: 700; color: var(--cafe-amarelo); margin-bottom: 5px;">${item.nome}</div>
                    <div style="font-size: 0.9rem; color: var(--cafe-creme); opacity: 0.8;">
                        ${formatarMoeda(item.preco)} x 
                        <input type="number" value="${item.quantidade}" min="1" max="${item.estoque}" 
                               onchange="atualizarQuantidadeCarrinho(${index}, parseInt(this.value))"
                               style="width: 60px; padding: 5px; background: var(--cafe-preto-claro); 
                                      border: 1px solid var(--cafe-amarelo); border-radius: 5px; 
                                      color: var(--cafe-branco); text-align: center;">
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-weight: 700; color: var(--cafe-amarelo); margin-bottom: 10px;">
                        ${formatarMoeda(item.preco * item.quantidade)}
                    </div>
                    <button onclick="removerDoCarrinho(${index})" 
                            style="background: none; border: none; color: #dc3545; cursor: pointer; font-size: 1.2rem;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    document.getElementById('carrinho-total').textContent = formatarMoeda(total);
}

function limparCarrinho() {
    if (confirm('Deseja limpar o carrinho?')) {
        carrinho = [];
        atualizarCarrinho();
    }
}

function finalizarVenda() {
    if (carrinho.length === 0) {
        mostrarToast('warning', 'Carrinho Vazio', 'Adicione produtos ao carrinho antes de finalizar.');
        return;
    }
    
    const total = carrinho.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
    document.getElementById('venda-subtotal').textContent = formatarMoeda(total);
    document.getElementById('venda-desconto-display').textContent = formatarMoeda(0);
    document.getElementById('venda-total').textContent = formatarMoeda(total);
    document.getElementById('venda-desconto').value = 0;
    
    document.getElementById('modal-venda').classList.add('active');
}

function atualizarTotalVenda() {
    const desconto = parseFloat(document.getElementById('venda-desconto').value) || 0;
    const subtotal = carrinho.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);
    const total = subtotal - desconto;
    
    document.getElementById('venda-desconto-display').textContent = formatarMoeda(desconto);
    document.getElementById('venda-total').textContent = formatarMoeda(Math.max(0, total));
}

async function confirmarVenda(e) {
    e.preventDefault();
    
    const cliente = document.getElementById('venda-cliente').value;
    const forma_pagamento = document.getElementById('venda-pagamento').value;
    const desconto = parseFloat(document.getElementById('venda-desconto').value) || 0;
    
    const btnSubmit = e.target.querySelector('button[type="submit"]');
    btnSubmit.classList.add('loading');
    btnSubmit.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('cliente', cliente);
        formData.append('forma_pagamento', forma_pagamento);
        formData.append('desconto', desconto);
        formData.append('itens', JSON.stringify(carrinho));
        
        const response = await fetch('ajax/finalizar_venda.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarToast('success', 'Venda Finalizada!', `Venda #${result.numero_venda} registrada com sucesso.`);
            carrinho = [];
            atualizarCarrinho();
            fecharModalVenda();
            loadDashboard();
            loadProdutosPDV();
        } else {
            mostrarToast('error', 'Erro na Venda', result.message);
        }
    } catch(error) {
        console.error('Erro ao finalizar venda:', error);
        mostrarToast('error', 'Erro ao Finalizar', 'Não foi possível finalizar a venda. Tente novamente.');
    } finally {
        btnSubmit.classList.remove('loading');
        btnSubmit.disabled = false;
    }
}

function fecharModalVenda() {
    document.getElementById('modal-venda').classList.remove('active');
    document.getElementById('form-venda').reset();
}

// Estoque
async function loadEstoque() {
    try {
        const response = await fetch('ajax/produtos.php');
        const result = await response.json();
        
        if (result.success) {
            const tbody = document.getElementById('estoque-tbody');
            const produtosEstoque = result.data.filter(p => p.ativo == 1);
            
            if (produtosEstoque.length > 0) {
                tbody.innerHTML = produtosEstoque.map(produto => {
                    const estoqueBaixo = produto.estoque_atual <= produto.estoque_minimo;
                    return `
                        <tr>
                            <td>${produto.nome}</td>
                            <td>${produto.estoque_atual} ${produto.unidade_medida}</td>
                            <td>${produto.estoque_minimo} ${produto.unidade_medida}</td>
                            <td>
                                ${estoqueBaixo 
                                    ? '<span class="cafe-badge cafe-badge-danger">Estoque Baixo</span>' 
                                    : '<span class="cafe-badge cafe-badge-success">Normal</span>'}
                            </td>
                            <td>
                                <button class="cafe-btn cafe-btn-secondary" onclick="ajustarEstoque(${produto.id})" style="padding: 8px 15px; font-size: 0.9rem;">
                                    <i class="fas fa-edit"></i> Ajustar
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px;">Nenhum produto cadastrado.</td></tr>';
            }
        }
    } catch(error) {
        console.error('Erro ao carregar estoque:', error);
    }
}

function ajustarEstoque(id) {
    // Implementar modal de ajuste de estoque se necessário
    mostrarToast('info', 'Em Desenvolvimento', 'Funcionalidade de ajuste de estoque em breve.');
}

// Vendas
async function loadVendas() {
    try {
        const response = await fetch('ajax/vendas.php');
        const result = await response.json();
        
        if (result.success) {
            const tbody = document.getElementById('vendas-tbody');
            
            if (result.data.length > 0) {
                tbody.innerHTML = result.data.map(venda => `
                    <tr>
                        <td>${venda.numero_venda}</td>
                        <td>${formatarDataHora(venda.data_venda)}</td>
                        <td>${venda.cliente_nome || '-'}</td>
                        <td>${venda.total_itens} itens</td>
                        <td>${formatarMoeda(venda.total)}</td>
                        <td>${formatarPagamento(venda.forma_pagamento)}</td>
                        <td>
                            <button class="cafe-btn cafe-btn-secondary" onclick="verDetalhesVenda(${venda.id})" style="padding: 8px 15px; font-size: 0.9rem;">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">Nenhuma venda encontrada.</td></tr>';
            }
        }
    } catch(error) {
        console.error('Erro ao carregar vendas:', error);
    }
}

function filtrarVendas() {
    const dataInicio = document.getElementById('filtro-data-inicio').value;
    const dataFim = document.getElementById('filtro-data-fim').value;
    
    // Implementar filtro
    loadVendas();
}

function verDetalhesVenda(id) {
    // Implementar modal de detalhes
    mostrarToast('info', 'Em Desenvolvimento', 'Visualização de detalhes da venda em breve.');
}

// Modal Produto
function abrirModalProduto() {
    document.getElementById('modal-produto-titulo').textContent = 'Novo Produto';
    document.getElementById('form-produto').reset();
    document.getElementById('produto-id').value = '';
    document.getElementById('modal-produto').classList.add('active');
}

function editarProduto(id) {
    const produto = produtos.find(p => p.id == id);
    if (!produto) return;
    
    document.getElementById('modal-produto-titulo').textContent = 'Editar Produto';
    document.getElementById('produto-id').value = produto.id;
    document.getElementById('produto-codigo').value = produto.codigo;
    document.getElementById('produto-nome').value = produto.nome;
    document.getElementById('produto-descricao').value = produto.descricao || '';
    document.getElementById('produto-categoria').value = produto.categoria || '';
    document.getElementById('produto-preco').value = produto.preco_venda;
    document.getElementById('produto-estoque').value = produto.estoque_atual;
    document.getElementById('produto-estoque-minimo').value = produto.estoque_minimo;
    document.getElementById('produto-unidade').value = produto.unidade_medida;
    document.getElementById('produto-ativo').value = produto.ativo;
    
    document.getElementById('modal-produto').classList.add('active');
}

function fecharModalProduto() {
    document.getElementById('modal-produto').classList.remove('active');
    document.getElementById('form-produto').reset();
}

async function salvarProduto(e) {
    e.preventDefault();
    
    const btnSubmit = e.target.querySelector('button[type="submit"]');
    btnSubmit.classList.add('loading');
    btnSubmit.disabled = true;
    
    const formData = new FormData();
    formData.append('id', document.getElementById('produto-id').value);
    formData.append('codigo', document.getElementById('produto-codigo').value);
    formData.append('nome', document.getElementById('produto-nome').value);
    formData.append('descricao', document.getElementById('produto-descricao').value);
    formData.append('categoria', document.getElementById('produto-categoria').value);
    formData.append('preco_venda', document.getElementById('produto-preco').value);
    formData.append('estoque_atual', document.getElementById('produto-estoque').value);
    formData.append('estoque_minimo', document.getElementById('produto-estoque-minimo').value);
    formData.append('unidade_medida', document.getElementById('produto-unidade').value);
    formData.append('ativo', document.getElementById('produto-ativo').value);
    
    const isEdicao = !!document.getElementById('produto-id').value;
    
    try {
        const response = await fetch('ajax/salvar_produto.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarToast('success', 
                isEdicao ? 'Produto Atualizado!' : 'Produto Cadastrado!', 
                `${document.getElementById('produto-nome').value} foi salvo com sucesso.`
            );
            fecharModalProduto();
            loadProdutos();
            loadEstoque();
            loadDashboard();
        } else {
            mostrarToast('error', 'Erro ao Salvar', result.message);
        }
    } catch(error) {
        console.error('Erro ao salvar produto:', error);
        mostrarToast('error', 'Erro ao Salvar', 'Não foi possível salvar o produto. Tente novamente.');
    } finally {
        btnSubmit.classList.remove('loading');
        btnSubmit.disabled = false;
    }
}

// Utilitários
function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

function formatarDataHora(dataHora) {
    const data = new Date(dataHora);
    return data.toLocaleString('pt-BR');
}

function formatarPagamento(forma) {
    const formas = {
        'dinheiro': '💵 Dinheiro',
        'pix': '📱 PIX',
        'cartao_debito': '💳 Débito',
        'cartao_credito': '💳 Crédito'
    };
    return formas[forma] || forma;
}
