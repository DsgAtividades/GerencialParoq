// JavaScript principal do módulo Lojinha
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar o módulo
    initLojinha();
    
    // Carregar dados iniciais
    carregarDashboard();
    carregarProdutos();
    carregarMovimentacoes();
    verificarStatusCaixa();
});

// Variáveis globais
let carrinho = [];
let produtos = [];
let statusCaixa = 'fechado';

// Inicializar módulo
function initLojinha() {
    console.log('Módulo Lojinha inicializado');
    
    // Configurar navegação
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.getAttribute('data-section');
            mostrarSecao(section);
            
            // Atualizar navegação ativa
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// Mostrar seção específica
function mostrarSecao(sectionId) {
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
        
        // Carregar dados específicos da seção
        switch(sectionId) {
            case 'dashboard':
                carregarDashboard();
                break;
            case 'produtos':
                carregarProdutos();
                break;
            case 'pdv':
                carregarProdutosPDV();
                break;
            case 'estoque':
                carregarMovimentacoes();
                break;
            case 'caixa':
                verificarStatusCaixa();
                carregarMovimentacoesCaixa();
                break;
            case 'relatorios':
                // Relatórios são carregados sob demanda
                break;
        }
    }
}

// ==================== DASHBOARD ====================
function carregarDashboard() {
    // Carregar estatísticas do dashboard
    Promise.all([
        fetch('ajax/dashboard_stats.php'),
        fetch('ajax/vendas_recentes.php')
    ])
    .then(responses => Promise.all(responses.map(r => r.json())))
    .then(([stats, vendas]) => {
        // Atualizar estatísticas
        document.getElementById('total-produtos').textContent = stats.total_produtos || 0;
        document.getElementById('vendas-hoje').textContent = stats.vendas_hoje || 0;
        document.getElementById('faturamento-hoje').textContent = formatarMoeda(stats.faturamento_hoje || 0);
        document.getElementById('estoque-baixo').textContent = stats.estoque_baixo || 0;
        
        // Atualizar vendas recentes
        atualizarVendasRecentes(vendas);
    })
    .catch(error => {
        console.error('Erro ao carregar dashboard:', error);
    });
}

function atualizarVendasRecentes(vendas) {
    const tbody = document.getElementById('vendas-recentes');
    
    if (vendas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; color: #7f8c8d; padding: 40px;">
                    <i class="fas fa-shopping-cart" style="font-size: 2rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                    Nenhuma venda realizada ainda.
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = vendas.map(venda => `
        <tr>
            <td>${venda.numero_venda}</td>
            <td>${formatarData(venda.data_venda)}</td>
            <td>${venda.cliente_nome || 'Cliente não informado'}</td>
            <td>${formatarMoeda(venda.total)}</td>
            <td><span class="status-badge status-${venda.status}">${venda.status}</span></td>
        </tr>
    `).join('');
}

// ==================== PRODUTOS ====================
function carregarProdutos() {
    fetch('ajax/produtos_direto.php')
        .then(response => {
            console.log('Status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Resposta bruta:', text);
            try {
                const data = JSON.parse(text);
                console.log('JSON parseado:', data);
                if (data.success) {
                    produtos = data.produtos || [];
                    atualizarTabelaProdutos(produtos);
                } else {
                    console.error('Erro do servidor:', data.message);
                    mostrarErro('Erro ao carregar produtos: ' + data.message);
                }
            } catch (e) {
                console.error('Erro JSON:', e);
                console.error('Texto recebido:', text);
                mostrarErro('Erro de JSON: ' + e.message);
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            mostrarErro('Erro na requisição: ' + error.message);
        });
}

function atualizarTabelaProdutos(produtos) {
    const tbody = document.getElementById('tabela-produtos');
    
    if (produtos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; color: #7f8c8d; padding: 40px;">
                    <i class="fas fa-box" style="font-size: 2rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                    Nenhum produto cadastrado ainda.
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = produtos.map(produto => `
        <tr>
            <td>${produto.codigo}</td>
            <td>${produto.nome}</td>
            <td>${produto.categoria_nome || 'Sem categoria'}</td>
            <td>${formatarMoeda(produto.preco_venda)}</td>
            <td>
                <span class="${produto.estoque_atual <= produto.estoque_minimo ? 'estoque-baixo' : ''}">
                    ${produto.estoque_atual}
                </span>
            </td>
            <td><span class="status-badge status-${produto.ativo ? 'ativo' : 'inativo'}">${produto.ativo ? 'Ativo' : 'Inativo'}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-view" onclick="visualizarProduto(${produto.id})" title="Visualizar">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-action btn-edit" onclick="editarProduto(${produto.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-action btn-delete" onclick="excluirProduto(${produto.id})" title="Excluir">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function abrirModalProduto(produtoId = null) {
    criarModalProduto(produtoId);
}

function criarModalProduto(produtoId = null) {
    const modal = document.createElement('div');
    modal.className = 'modal-produto';
    modal.innerHTML = `
        <div class="modal-produto-content">
            <div class="modal-produto-header">
                <h3>${produtoId ? 'Editar Produto' : 'Novo Produto'}</h3>
                <button class="modal-produto-close" type="button" onclick="fecharModalProduto()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-produto-body">
                <form id="form-produto" onsubmit="salvarProduto(event, ${produtoId || 'null'})">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="codigo">Código *</label>
                            <input type="text" id="codigo" name="codigo" required placeholder="Ex: PRD001">
                        </div>
                        <div class="form-group">
                            <label for="nome">Nome *</label>
                            <input type="text" id="nome" name="nome" required placeholder="Ex: Bíblia Sagrada">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="categoria_id">Categoria *</label>
                            <select id="categoria_id" name="categoria_id" required>
                                <option value="">Carregando...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fornecedor">Fornecedor</label>
                            <input type="text" id="fornecedor" name="fornecedor" placeholder="Ex: Editora Ave Maria">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="preco_compra">Preço de Compra *</label>
                            <input type="number" id="preco_compra" name="preco_compra" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="preco_venda">Preço de Venda *</label>
                            <input type="number" id="preco_venda" name="preco_venda" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="estoque_atual">Estoque Atual</label>
                            <input type="number" id="estoque_atual" name="estoque_atual" min="0" value="0">
                        </div>
                        <div class="form-group">
                            <label for="estoque_minimo">Estoque Mínimo</label>
                            <input type="number" id="estoque_minimo" name="estoque_minimo" min="0" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tipo_liturgico">Tipo Litúrgico</label>
                        <select id="tipo_liturgico" name="tipo_liturgico">
                            <option value="">Selecione um tipo</option>
                            <option value="sacramental">Sacramental</option>
                            <option value="devoção">Devoção</option>
                            <option value="catequese">Catequese</option>
                            <option value="liturgia">Liturgia</option>
                            <option value="outros">Outros</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="3" placeholder="Descrição detalhada do produto"></textarea>
                    </div>
                    <div class="form-produto-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                        <button type="button" class="btn-secondary" onclick="fecharModal(this)">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Adicionar modal ao DOM primeiro
    document.body.appendChild(modal);
    
    // Depois carregar categorias
    carregarCategorias();
    
    // Se for edição, carregar dados do produto após carregar categorias
    if (produtoId) {
        setTimeout(() => {
            carregarDadosProduto(produtoId);
        }, 500);
    }
    
    return modal;
}

function carregarOpcoesModal() {
    // Carregar categorias
    fetch('ajax/categorias.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('categoria_id');
            select.innerHTML = '<option value="">Selecione uma categoria</option>' +
                data.categorias.map(cat => `<option value="${cat.id}">${cat.nome}</option>`).join('');
        });
    
    // Carregar fornecedores
    fetch('ajax/fornecedores.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('fornecedor_id');
            select.innerHTML = '<option value="">Selecione um fornecedor</option>' +
                data.fornecedores.map(forn => `<option value="${forn.id}">${forn.nome}</option>`).join('');
        });
}

function carregarDadosProduto(produtoId) {
    fetch(`ajax/produto.php?id=${produtoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const produto = data.produto;
                document.getElementById('codigo').value = produto.codigo || '';
                document.getElementById('nome').value = produto.nome || '';
                document.getElementById('categoria_id').value = produto.categoria_id || '';
                document.getElementById('fornecedor').value = produto.fornecedor || '';
                document.getElementById('preco_compra').value = produto.preco_compra || '';
                document.getElementById('preco_venda').value = produto.preco_venda || '';
                document.getElementById('estoque_atual').value = produto.estoque_atual || 0;
                document.getElementById('estoque_minimo').value = produto.estoque_minimo || 0;
                document.getElementById('tipo_liturgico').value = produto.tipo_liturgico || '';
                document.getElementById('descricao').value = produto.descricao || '';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar produto:', error);
            mostrarErro('Erro ao carregar dados do produto');
        });
}

function salvarProduto(event, produtoId) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const url = produtoId ? `ajax/editar_produto.php?id=${produtoId}` : 'ajax/salvar_produto.php';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarSucesso(data.message);
            fecharModal(event.target.closest('.modal-produto'));
            carregarProdutos();
            carregarDashboard(); // Atualizar métricas do dashboard
        } else {
            mostrarErro(data.message);
        }
    })
    .catch(error => {
        console.error('Erro ao salvar produto:', error);
        mostrarErro('Erro ao salvar produto');
    });
}

// ==================== PDV ====================
function carregarProdutosPDV() {
    console.log('Carregando produtos para PDV...');
    fetch('ajax/produtos_pdv.php')
        .then(response => {
            console.log('Status da resposta:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Resposta bruta:', text);
            try {
                const data = JSON.parse(text);
                console.log('JSON parseado:', data);
                if (data.success && data.produtos) {
                    produtos = data.produtos;
                    console.log('Total de produtos carregados:', produtos.length);
                    atualizarGridProdutos(produtos);
                } else {
                    console.error('Erro nos dados:', data.message);
                    const grid = document.getElementById('produtos-grid');
                    if (grid) {
                        grid.innerHTML = '<div class="empty-state">Erro ao carregar produtos</div>';
                    }
                }
            } catch (e) {
                console.error('Erro ao fazer parse do JSON:', e);
                console.error('Texto recebido:', text);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar produtos para PDV:', error);
        });
}

function atualizarGridProdutos(produtos) {
    const grid = document.getElementById('produtos-grid');
    
    if (produtos.length === 0) {
        grid.innerHTML = '<div class="empty-state">Nenhum produto encontrado</div>';
        return;
    }
    
    grid.innerHTML = produtos.map(produto => `
        <div class="produto-card" onclick="adicionarAoCarrinho(${produto.id})">
            <div class="produto-nome">${produto.nome}</div>
            <div class="produto-preco">${formatarMoeda(produto.preco_venda)}</div>
            <div class="produto-estoque">Estoque: ${produto.estoque_atual}</div>
        </div>
    `).join('');
}

function buscarProdutos(termo) {
    if (termo.length < 2) {
        atualizarGridProdutos(produtos);
        return;
    }
    
    const produtosFiltrados = produtos.filter(produto => 
        produto.nome.toLowerCase().includes(termo.toLowerCase()) ||
        produto.codigo.toLowerCase().includes(termo.toLowerCase())
    );
    
    atualizarGridProdutos(produtosFiltrados);
}

function adicionarAoCarrinho(produtoId) {
    console.log('Tentando adicionar produto ao carrinho:', produtoId);
    console.log('Total de produtos disponíveis:', produtos.length);
    console.log('Produtos:', produtos);
    
    const produto = produtos.find(p => p.id == produtoId); // Usar == para comparação flexível
    
    if (!produto) {
        console.error('Produto não encontrado:', produtoId);
        mostrarErro('Produto não encontrado');
        return;
    }
    
    console.log('Produto encontrado:', produto);
    
    const itemExistente = carrinho.find(item => item.id == produtoId);
    
    if (itemExistente) {
        if (itemExistente.quantidade < produto.estoque_atual) {
            itemExistente.quantidade++;
            console.log('Quantidade aumentada:', itemExistente.quantidade);
        } else {
            mostrarErro('Estoque insuficiente');
            return;
        }
    } else {
        const novoItem = {
            id: produto.id,
            nome: produto.nome,
            preco: parseFloat(produto.preco_venda),
            quantidade: 1,
            estoque: parseInt(produto.estoque_atual)
        };
        console.log('Novo item adicionado:', novoItem);
        carrinho.push(novoItem);
    }
    
    console.log('Carrinho atualizado:', carrinho);
    atualizarCarrinho();
    mostrarSucesso('Produto adicionado ao carrinho!');
}

function atualizarCarrinho() {
    const container = document.getElementById('carrinho-itens');
    
    if (carrinho.length === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Carrinho vazio</p>
            </div>
        `;
        atualizarTotalCarrinho();
        return;
    }
    
    container.innerHTML = carrinho.map(item => `
        <div class="carrinho-item">
            <div class="item-info">
                <div class="item-nome">${item.nome}</div>
                <div class="item-preco">${formatarMoeda(item.preco)}</div>
                <div class="item-quantidade">
                    <button class="quantidade-btn" onclick="alterarQuantidade(${item.id}, -1)">-</button>
                    <input type="number" class="quantidade-input" value="${item.quantidade}" 
                           min="1" max="${item.estoque}" onchange="alterarQuantidade(${item.id}, 0, this.value)">
                    <button class="quantidade-btn" onclick="alterarQuantidade(${item.id}, 1)">+</button>
                </div>
            </div>
            <div class="item-total">${formatarMoeda(item.preco * item.quantidade)}</div>
            <div class="item-remove" onclick="removerDoCarrinho(${item.id})">
                <i class="fas fa-trash"></i>
            </div>
        </div>
    `).join('');
    
    atualizarTotalCarrinho();
}

function alterarQuantidade(produtoId, delta, valor = null) {
    const item = carrinho.find(i => i.id === produtoId);
    if (!item) return;
    
    let novaQuantidade;
    if (valor !== null) {
        novaQuantidade = parseInt(valor);
    } else {
        novaQuantidade = item.quantidade + delta;
    }
    
    if (novaQuantidade < 1) {
        removerDoCarrinho(produtoId);
        return;
    }
    
    if (novaQuantidade > item.estoque) {
        mostrarErro('Estoque insuficiente');
        return;
    }
    
    item.quantidade = novaQuantidade;
    atualizarCarrinho();
}

function removerDoCarrinho(produtoId) {
    carrinho = carrinho.filter(item => item.id !== produtoId);
    atualizarCarrinho();
}

function atualizarTotalCarrinho() {
    const subtotal = carrinho.reduce((total, item) => total + (item.preco * item.quantidade), 0);
    const desconto = 0; // Implementar sistema de desconto se necessário
    const total = subtotal - desconto;
    
    document.getElementById('subtotal').textContent = formatarMoeda(subtotal);
    document.getElementById('desconto').textContent = formatarMoeda(desconto);
    document.getElementById('total').textContent = formatarMoeda(total);
}

function limparCarrinho() {
    carrinho = [];
    atualizarCarrinho();
}

function finalizarVenda() {
    if (carrinho.length === 0) {
        mostrarErro('Carrinho vazio');
        return;
    }
    
    // Implementar modal de finalização de venda
    abrirModalFinalizacaoVenda();
}

function abrirModalFinalizacaoVenda() {
    const modal = document.createElement('div');
    modal.className = 'modal-produto';
    modal.innerHTML = `
        <div class="modal-produto-content">
            <div class="modal-produto-header">
                <h3>Finalizar Venda</h3>
                <button class="modal-produto-close" onclick="fecharModal(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-produto-body">
                <form id="form-venda" onsubmit="processarVenda(event)">
                    <div class="form-group">
                        <label for="cliente_nome">Nome do Cliente</label>
                        <input type="text" id="cliente_nome" name="cliente_nome">
                    </div>
                    <div class="form-group">
                        <label for="cliente_telefone">Telefone</label>
                        <input type="text" id="cliente_telefone" name="cliente_telefone">
                    </div>
                    <div class="form-group">
                        <label for="forma_pagamento">Forma de Pagamento *</label>
                        <select id="forma_pagamento" name="forma_pagamento" required>
                            <option value="">Selecione</option>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">PIX</option>
                            <option value="cartao_debito">Cartão de Débito</option>
                            <option value="cartao_credito">Cartão de Crédito</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="desconto">Desconto (R$)</label>
                        <input type="number" id="desconto" name="desconto" step="0.01" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label for="observacoes">Observações</label>
                        <textarea id="observacoes" name="observacoes" rows="3"></textarea>
                    </div>
                    <div class="form-produto-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i> Finalizar Venda
                        </button>
                        <button type="button" class="btn-secondary" onclick="fecharModal(this)">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Carregar apenas categorias (fornecedor agora é campo de texto)
    carregarCategorias();
}

// Função para carregar categorias
function carregarCategorias() {
    console.log('=== INICIANDO CARREGAMENTO DE CATEGORIAS ===');
    const select = document.getElementById('categoria_id');
    
    if (!select) {
        console.log('Select categoria_id não encontrado - pulando carregamento de categorias');
        return;
    }
    
    console.log('Select encontrado:', select);
    select.innerHTML = '<option value="">Carregando...</option>';
    select.disabled = true;
    
    console.log('Fazendo requisição para ajax/categorias.php');
    fetch('ajax/categorias.php')
        .then(response => {
            console.log('Status da resposta:', response.status);
            console.log('Headers:', response.headers);
            return response.text();
        })
        .then(text => {
            console.log('Resposta bruta:', text);
            try {
                const data = JSON.parse(text);
                console.log('JSON parseado:', data);
                
                select.disabled = false;
                
                if (data.success && data.categorias && data.categorias.length > 0) {
                    console.log('Categorias encontradas:', data.categorias.length);
                    select.innerHTML = '<option value="">Selecione uma categoria</option>';
                    
                    data.categorias.forEach((cat, index) => {
                        console.log(`Adicionando categoria ${index + 1}:`, cat);
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.nome;
                        select.appendChild(option);
                    });
                    
                    console.log('Total de options no select:', select.options.length);
                } else {
                    console.warn('Nenhuma categoria disponível');
                    select.innerHTML = '<option value="">Nenhuma categoria disponível</option>';
                }
            } catch (e) {
                console.error('Erro ao fazer parse do JSON:', e);
                select.disabled = false;
                select.innerHTML = '<option value="">Erro de JSON</option>';
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            select.disabled = false;
            select.innerHTML = '<option value="">Erro ao carregar</option>';
        });
}

// Função para carregar fornecedores
function carregarFornecedores() {
    const select = document.getElementById('fornecedor_id');
    if (!select) {
        console.log('Select fornecedor_id não encontrado - pulando carregamento de fornecedores');
        return;
    }
    
    select.innerHTML = '<option value="">Carregando...</option>';
    select.disabled = true;
    
    fetch('ajax/fornecedores.php')
        .then(response => response.json())
        .then(data => {
            select.disabled = false;
            if (data.success && data.fornecedores) {
                select.innerHTML = '<option value="">Selecione um fornecedor</option>';
                data.fornecedores.forEach(forn => {
                    const option = document.createElement('option');
                    option.value = forn.id;
                    option.textContent = forn.nome;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">Nenhum fornecedor disponível</option>';
            }
        })
        .catch(error => {
            select.disabled = false;
            select.innerHTML = '<option value="">Erro ao carregar</option>';
            console.error('Erro ao carregar fornecedores:', error);
        });
}

function processarVenda(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('itens', JSON.stringify(carrinho));
    
    fetch('ajax/finalizar_venda.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarSucesso('Venda finalizada com sucesso!');
            fecharModal(event.target.closest('.modal-produto'));
            limparCarrinho();
            carregarDashboard();
        } else {
            mostrarErro(data.message);
        }
    })
    .catch(error => {
        console.error('Erro ao finalizar venda:', error);
        mostrarErro('Erro ao finalizar venda');
    });
}

// ==================== ESTOQUE ====================
function carregarMovimentacoes() {
    fetch('ajax/movimentacoes_estoque.php')
        .then(response => response.json())
        .then(data => {
            atualizarTabelaMovimentacoes(data.movimentacoes || []);
        })
        .catch(error => {
            console.error('Erro ao carregar movimentações:', error);
        });
}

function atualizarTabelaMovimentacoes(movimentacoes) {
    const tbody = document.getElementById('tabela-movimentacoes');
    
    if (movimentacoes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; color: #7f8c8d; padding: 40px;">
                    <i class="fas fa-warehouse" style="font-size: 2rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                    Nenhuma movimentação encontrada.
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = movimentacoes.map(mov => `
        <tr>
            <td>${formatarData(mov.data_movimentacao)}</td>
            <td>${mov.produto_nome}</td>
            <td><span class="status-badge status-${mov.tipo}">${mov.tipo}</span></td>
            <td>${mov.quantidade}</td>
            <td>${mov.motivo || '-'}</td>
            <td>${mov.usuario_nome || '-'}</td>
        </tr>
    `).join('');
}

// ==================== CAIXA ====================
function verificarStatusCaixa() {
    fetch('ajax/status_caixa.php')
        .then(response => response.json())
        .then(data => {
            statusCaixa = data.status;
            atualizarStatusCaixa(data);
        })
        .catch(error => {
            console.error('Erro ao verificar status do caixa:', error);
        });
}

function atualizarStatusCaixa(data) {
    const statusElement = document.getElementById('status-caixa');
    const saldoElement = document.getElementById('saldo-atual');
    const btnAbrir = document.getElementById('btn-abrir-caixa');
    const btnFechar = document.getElementById('btn-fechar-caixa');
    
    if (data.status === 'aberto') {
        statusElement.textContent = 'Aberto';
        statusElement.className = 'status-caixa-aberto';
        saldoElement.textContent = formatarMoeda(data.saldo_atual || 0);
        btnAbrir.style.display = 'none';
        btnFechar.style.display = 'inline-flex';
    } else {
        statusElement.textContent = 'Fechado';
        statusElement.className = 'status-caixa-fechado';
        saldoElement.textContent = 'R$ 0,00';
        btnAbrir.style.display = 'inline-flex';
        btnFechar.style.display = 'none';
    }
}

function abrirCaixa() {
    const valor = prompt('Digite o valor inicial do caixa:');
    if (valor === null) return;
    
    const valorNumerico = parseFloat(valor);
    if (isNaN(valorNumerico) || valorNumerico < 0) {
        mostrarErro('Valor inválido');
        return;
    }
    
    fetch('ajax/abrir_caixa.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ saldo_inicial: valorNumerico })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarSucesso('Caixa aberto com sucesso!');
            verificarStatusCaixa();
            carregarMovimentacoesCaixa();
        } else {
            mostrarErro(data.message);
        }
    })
    .catch(error => {
        console.error('Erro ao abrir caixa:', error);
        mostrarErro('Erro ao abrir caixa');
    });
}

function fecharCaixa() {
    if (!confirm('Tem certeza que deseja fechar o caixa?')) return;
    
    fetch('ajax/fechar_caixa.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarSucesso('Caixa fechado com sucesso!');
            verificarStatusCaixa();
            carregarMovimentacoesCaixa();
        } else {
            mostrarErro(data.message);
        }
    })
    .catch(error => {
        console.error('Erro ao fechar caixa:', error);
        mostrarErro('Erro ao fechar caixa');
    });
}

function carregarMovimentacoesCaixa() {
    fetch('ajax/movimentacoes_caixa.php')
        .then(response => response.json())
        .then(data => {
            atualizarTabelaMovimentacoesCaixa(data.movimentacoes || []);
        })
        .catch(error => {
            console.error('Erro ao carregar movimentações do caixa:', error);
        });
}

function atualizarTabelaMovimentacoesCaixa(movimentacoes) {
    const tbody = document.getElementById('tabela-movimentacoes-caixa');
    
    if (movimentacoes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; color: #7f8c8d; padding: 40px;">
                    <i class="fas fa-cash-register" style="font-size: 2rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                    Nenhuma movimentação encontrada.
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = movimentacoes.map(mov => `
        <tr>
            <td>${formatarData(mov.data_movimentacao)}</td>
            <td>
                <span class="badge ${mov.tipo === 'entrada' ? 'badge-success' : 'badge-danger'}">
                    ${mov.tipo === 'entrada' ? 'Entrada' : 'Saída'}
                </span>
            </td>
            <td class="${mov.tipo === 'entrada' ? 'text-success' : 'text-danger'}">
                ${mov.tipo === 'entrada' ? '+' : '-'}${formatarMoeda(mov.valor)}
            </td>
            <td>${mov.descricao}</td>
            <td>
                <span class="badge badge-info">${mov.categoria || 'Outros'}</span>
            </td>
            <td>${mov.usuario_nome || '-'}</td>
        </tr>
    `).join('');
}

// ==================== RELATÓRIOS ====================
function gerarRelatorio(tipo) {
    const titulos = {
        'vendas': 'Vendas',
        'estoque': 'Estoque',
        'financeiro': 'Financeiro',
        'produtos': 'Produtos Mais Vendidos'
    };
    
    const modal = document.createElement('div');
    modal.className = 'modal-produto';
    modal.innerHTML = `
        <div class="modal-produto-content" style="max-width: 900px;">
            <div class="modal-produto-header">
                <h3>Relatório de ${titulos[tipo] || tipo}</h3>
                <button class="modal-produto-close" onclick="fecharModal(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-produto-body">
                <div class="form-group">
                    <label for="data_inicio">Data Início</label>
                    <input type="date" id="data_inicio" name="data_inicio" value="${getDataAtual(-30)}">
                </div>
                <div class="form-group">
                    <label for="data_fim">Data Fim</label>
                    <input type="date" id="data_fim" name="data_fim" value="${getDataAtual()}">
                </div>
                <div class="form-produto-actions">
                    <button class="btn-primary" onclick="gerarRelatorioCompleto('${tipo}')">
                        <i class="fas fa-file-alt"></i> Gerar Relatório
                    </button>
                    <button class="btn-secondary" onclick="visualizarRelatorio('${tipo}')">
                        <i class="fas fa-eye"></i> Visualizar
                    </button>
                </div>
                <div id="resultado-relatorio" style="margin-top: 20px;"></div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function getDataAtual(diasAtras = 0) {
    const data = new Date();
    data.setDate(data.getDate() + diasAtras);
    return data.toISOString().split('T')[0];
}

function visualizarRelatorio(tipo) {
    const dataInicio = document.getElementById('data_inicio').value;
    const dataFim = document.getElementById('data_fim').value;
    const resultado = document.getElementById('resultado-relatorio');
    
    resultado.innerHTML = '<p style="text-align: center; padding: 20px;">Carregando relatório...</p>';
    
    fetch(`ajax/relatorio_${tipo}.php?data_inicio=${dataInicio}&data_fim=${dataFim}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                exibirDadosRelatorio(data, tipo);
            } else {
                resultado.innerHTML = '<p style="color: #e53e3e; text-align: center;">Erro ao carregar relatório</p>';
            }
        })
        .catch(error => {
            resultado.innerHTML = '<p style="color: #e53e3e; text-align: center;">Erro na requisição</p>';
        });
}

function exibirDadosRelatorio(data, tipo) {
    const resultado = document.getElementById('resultado-relatorio');
    
    switch (tipo) {
        case 'vendas':
            exibirRelatorioVendas(data);
            break;
        case 'estoque':
            exibirRelatorioEstoque(data);
            break;
        case 'financeiro':
            exibirRelatorioFinanceiro(data);
            break;
        case 'produtos':
            exibirRelatorioProdutos(data);
            break;
        default:
            resultado.innerHTML = '<p style="color: #e53e3e; text-align: center;">Tipo de relatório não reconhecido</p>';
    }
}

function exibirRelatorioVendas(data) {
    const resultado = document.getElementById('resultado-relatorio');
    const { dados_gerais, vendas_por_pagamento, top_produtos, comparacao } = data;
    
    resultado.innerHTML = `
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">📊 Resumo de Vendas</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #007bff;">
                    <h5 style="margin: 0 0 5px 0; color: #007bff;">Total de Vendas</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">${dados_gerais.total_vendas}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #28a745;">
                    <h5 style="margin: 0 0 5px 0; color: #28a745;">Faturamento Total</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">R$ ${parseFloat(dados_gerais.faturamento_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #ffc107;">
                    <h5 style="margin: 0 0 5px 0; color: #ffc107;">Ticket Médio</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">R$ ${parseFloat(dados_gerais.ticket_medio).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #dc3545;">
                    <h5 style="margin: 0 0 5px 0; color: #dc3545;">Total Descontos</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">R$ ${parseFloat(dados_gerais.total_descontos).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                </div>
            </div>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">💳 Vendas por Forma de Pagamento</h4>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr style="background: #e9ecef;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Forma de Pagamento</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Quantidade</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Valor Total</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Ticket Médio</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${vendas_por_pagamento.map(venda => `
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${venda.forma_pagamento}</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">${venda.quantidade}</td>
                                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">R$ ${parseFloat(venda.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">R$ ${parseFloat(venda.ticket_medio).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">🏆 Top 10 Produtos Mais Vendidos</h4>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr style="background: #e9ecef;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Produto</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Qtd. Vendida</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Receita Total</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Preço Médio</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${top_produtos.slice(0, 10).map(produto => `
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${produto.produto}</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">${produto.quantidade_vendida}</td>
                                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">R$ ${parseFloat(produto.receita_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">R$ ${parseFloat(produto.preco_medio).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function exibirRelatorioEstoque(data) {
    const resultado = document.getElementById('resultado-relatorio');
    const { resumo_estoque, produtos_em_falta, produtos_zerados } = data;
    
    resultado.innerHTML = `
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">📦 Resumo do Estoque</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #007bff;">
                    <h5 style="margin: 0 0 5px 0; color: #007bff;">Total de Produtos</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">${resumo_estoque.total_produtos}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #28a745;">
                    <h5 style="margin: 0 0 5px 0; color: #28a745;">Total em Estoque</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">${resumo_estoque.total_estoque} unidades</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #ffc107;">
                    <h5 style="margin: 0 0 5px 0; color: #ffc107;">Valor Total Estoque</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">R$ ${parseFloat(resumo_estoque.valor_total_estoque).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #dc3545;">
                    <h5 style="margin: 0 0 5px 0; color: #dc3545;">Produtos em Falta</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">${resumo_estoque.produtos_em_falta}</p>
                </div>
            </div>
        </div>
        
        ${produtos_em_falta.length > 0 ? `
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #dc3545;">⚠️ Produtos em Falta</h4>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr style="background: #f8d7da;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #f5c6cb;">Produto</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #f5c6cb;">Estoque Atual</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #f5c6cb;">Estoque Mín.</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #f5c6cb;">Faltante</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${produtos_em_falta.slice(0, 10).map(produto => `
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #f5c6cb;">${produto.nome}</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #f5c6cb;">${produto.estoque_atual}</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #f5c6cb;">${produto.estoque_minimo}</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #f5c6cb; color: #dc3545; font-weight: bold;">${produto.quantidade_faltante}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
        ` : ''}
        
        ${produtos_zerados.length > 0 ? `
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
            <h4 style="margin: 0 0 15px 0; color: #dc3545;">🚫 Produtos Zerados</h4>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr style="background: #f8d7da;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #f5c6cb;">Produto</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #f5c6cb;">Estoque Atual</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #f5c6cb;">Preço Venda</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${produtos_zerados.slice(0, 10).map(produto => `
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #f5c6cb;">${produto.nome}</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #f5c6cb; color: #dc3545; font-weight: bold;">0</td>
                                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #f5c6cb;">R$ ${parseFloat(produto.preco_venda).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
        ` : ''}
    `;
}

function exibirRelatorioFinanceiro(data) {
    const resultado = document.getElementById('resultado-relatorio');
    const { resumo_financeiro, analise_lucro, faturamento_por_pagamento, rentabilidade } = data;
    
    resultado.innerHTML = `
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">💰 Resumo Financeiro</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #007bff;">
                    <h5 style="margin: 0 0 5px 0; color: #007bff;">Faturamento Bruto</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">R$ ${parseFloat(resumo_financeiro.faturamento_bruto).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #28a745;">
                    <h5 style="margin: 0 0 5px 0; color: #28a745;">Faturamento Líquido</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">R$ ${parseFloat(resumo_financeiro.faturamento_liquido).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #ffc107;">
                    <h5 style="margin: 0 0 5px 0; color: #ffc107;">Total Descontos</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">R$ ${parseFloat(resumo_financeiro.total_descontos).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #dc3545;">
                    <h5 style="margin: 0 0 5px 0; color: #dc3545;">Total Vendas</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">${resumo_financeiro.total_vendas}</p>
                </div>
            </div>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">📈 Análise de Lucro</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #6f42c1;">
                    <h5 style="margin: 0 0 5px 0; color: #6f42c1;">Custo Total</h5>
                    <p style="font-size: 20px; font-weight: bold; margin: 0; color: #2c3e50;">R$ ${parseFloat(analise_lucro.custo_total_vendas).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #20c997;">
                    <h5 style="margin: 0 0 5px 0; color: #20c997;">Receita Total</h5>
                    <p style="font-size: 20px; font-weight: bold; margin: 0; color: #2c3e50;">R$ ${parseFloat(analise_lucro.receita_total_vendas).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #fd7e14;">
                    <h5 style="margin: 0 0 5px 0; color: #fd7e14;">Lucro Bruto</h5>
                    <p style="font-size: 20px; font-weight: bold; margin: 0; color: #2c3e50;">R$ ${parseFloat(analise_lucro.lucro_bruto).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #e83e8c;">
                    <h5 style="margin: 0 0 5px 0; color: #e83e8c;">Margem de Lucro</h5>
                    <p style="font-size: 20px; font-weight: bold; margin: 0; color: #2c3e50;">${parseFloat(analise_lucro.margem_lucro_percentual).toFixed(2)}%</p>
                </div>
            </div>
        </div>
    `;
}

function exibirRelatorioProdutos(data) {
    const resultado = document.getElementById('resultado-relatorio');
    const { ranking_produtos, estatisticas_gerais } = data;
    
    resultado.innerHTML = `
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">📊 Estatísticas Gerais</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #007bff;">
                    <h5 style="margin: 0 0 5px 0; color: #007bff;">Produtos Vendidos</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">${estatisticas_gerais.total_produtos_vendidos}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #28a745;">
                    <h5 style="margin: 0 0 5px 0; color: #28a745;">Unidades Vendidas</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">${estatisticas_gerais.total_unidades_vendidas}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #ffc107;">
                    <h5 style="margin: 0 0 5px 0; color: #ffc107;">Receita Total</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">R$ ${parseFloat(estatisticas_gerais.receita_total_geral).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #dc3545;">
                    <h5 style="margin: 0 0 5px 0; color: #dc3545;">Total Vendas</h5>
                    <p style="font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50;">${estatisticas_gerais.total_vendas_geral}</p>
                </div>
            </div>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">🏆 Ranking de Produtos Mais Vendidos</h4>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr style="background: #e9ecef;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Posição</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Produto</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Qtd. Vendida</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Receita Total</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Preço Médio</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${ranking_produtos.slice(0, 20).map((produto, index) => `
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center; font-weight: bold; color: ${index < 3 ? '#ffc107' : '#6c757d'};">
                                    ${index + 1}${index < 3 ? 'º' : ''}
                                </td>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${produto.produto}</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">${produto.quantidade_vendida}</td>
                                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">R$ ${parseFloat(produto.receita_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">R$ ${parseFloat(produto.preco_medio_venda).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function gerarRelatorioCompleto(tipo) {
    console.log('=== GERAR PDF CHAMADO ===');
    console.log('Tipo:', tipo);
    
    const dataInicio = document.getElementById('data_inicio').value;
    const dataFim = document.getElementById('data_fim').value;
    
    console.log('Data início:', dataInicio);
    console.log('Data fim:', dataFim);
    
    if (!dataInicio || !dataFim) {
        mostrarErro('Por favor, selecione as datas de início e fim.');
        return;
    }
    
    if (new Date(dataInicio) > new Date(dataFim)) {
        mostrarErro('A data de início deve ser anterior à data de fim.');
        return;
    }
    
    // Mostrar loading
    const resultado = document.getElementById('resultado-relatorio');
    resultado.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Gerando relatório...</div>';
    
    // Fazer requisição para gerar relatório
    const formData = new FormData();
    formData.append('tipo', tipo);
    formData.append('data_inicio', dataInicio);
    formData.append('data_fim', dataFim);
    
    fetch('ajax/gerar_html.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultado.innerHTML = `
                <div style="text-align: center; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; margin: 10px 0;">
                    <i class="fas fa-check-circle" style="color: #28a745; font-size: 24px; margin-bottom: 10px;"></i>
                    <h4 style="color: #155724; margin: 10px 0;">Relatório Gerado com Sucesso!</h4>
                    <p style="color: #155724; margin: 10px 0;">Arquivo: ${data.arquivo}</p>
                    <a href="${data.url}" target="_blank" class="btn-primary" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;">
                        <i class="fas fa-file-alt"></i> Abrir Relatório
                    </a>
                </div>
            `;
        } else {
            resultado.innerHTML = `
                <div style="text-align: center; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; margin: 10px 0;">
                    <i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 24px; margin-bottom: 10px;"></i>
                    <h4 style="color: #721c24; margin: 10px 0;">Erro ao Gerar Relatório</h4>
                    <p style="color: #721c24; margin: 10px 0;">${data.message}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Erro ao gerar relatório:', error);
        resultado.innerHTML = `
            <div style="text-align: center; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; margin: 10px 0;">
                <i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 24px; margin-bottom: 10px;"></i>
                <h4 style="color: #721c24; margin: 10px 0;">Erro de Conexão</h4>
                <p style="color: #721c24; margin: 10px 0;">Não foi possível gerar o relatório. Tente novamente.</p>
            </div>
        `;
    });
}

// ==================== UTILITÁRIOS ====================
function fecharModal(button) {
    const modal = button.closest('.modal-produto');
    if (modal) {
        modal.remove();
    }
}

function fecharModalProduto() {
    const modal = document.querySelector('.modal-produto');
    if (modal) {
        modal.remove();
    }
}

function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

function formatarData(data) {
    return new Date(data).toLocaleDateString('pt-BR');
}

function mostrarSucesso(mensagem) {
    // Implementar notificação de sucesso
    alert('Sucesso: ' + mensagem);
}

function mostrarErro(mensagem) {
    // Implementar notificação de erro
    alert('Erro: ' + mensagem);
}

// Funções de CRUD de produtos
function visualizarProduto(id) {
    // Implementar visualização de produto
    mostrarSucesso('Visualização de produto será implementada');
}

function editarProduto(id) {
    abrirModalProduto(id);
}

function excluirProduto(id) {
    if (!confirm('Tem certeza que deseja excluir este produto?')) return;
    
    fetch(`ajax/excluir_produto.php?id=${id}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarSucesso('Produto excluído com sucesso!');
            carregarProdutos();
        } else {
            mostrarErro(data.message);
        }
    })
    .catch(error => {
        console.error('Erro ao excluir produto:', error);
        mostrarErro('Erro ao excluir produto');
    });
}

function abrirModalAjusteEstoque() {
    const modal = document.getElementById('modal-ajuste-estoque');
    if (modal) {
        modal.style.display = 'flex';
        carregarProdutosParaAjuste();
    } else {
        mostrarErro('Modal de ajuste de estoque não encontrado');
    }
}

function fecharModalAjusteEstoque() {
    const modal = document.getElementById('modal-ajuste-estoque');
    if (modal) {
        modal.style.display = 'none';
        // Limpar formulário
        document.getElementById('form-ajuste-estoque').reset();
        document.getElementById('estoque-atual-display').textContent = '-';
    }
}

function carregarProdutosParaAjuste() {
    fetch('ajax/produtos_direto.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('produto_ajuste');
            if (data.success && data.produtos) {
                select.innerHTML = '<option value="">Selecione um produto</option>' +
                    data.produtos.map(produto =>
                        `<option value="${produto.id}" data-estoque="${produto.estoque_atual}">${produto.nome} (Estoque: ${produto.estoque_atual})</option>`
                    ).join('');

                // Adicionar evento para mostrar estoque quando produto for selecionado
                select.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const estoqueAtual = selectedOption.getAttribute('data-estoque') || '-';
                    const displayElement = document.getElementById('estoque-atual-display');
                    
                    if (estoqueAtual !== '-') {
                        displayElement.textContent = estoqueAtual;
                        displayElement.style.color = '#4f46e5';
                        displayElement.style.fontWeight = '700';
                    } else {
                        displayElement.textContent = '-';
                        displayElement.style.color = '#6c757d';
                        displayElement.style.fontWeight = '400';
                    }
                });
            } else {
                select.innerHTML = '<option value="">Erro ao carregar produtos</option>';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar produtos:', error);
            document.getElementById('produto_ajuste').innerHTML = '<option value="">Erro ao carregar produtos</option>';
        });
}

function salvarAjusteEstoque(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const produtoSelect = document.getElementById('produto_ajuste');
    const produtoNome = produtoSelect.options[produtoSelect.selectedIndex].text.split(' (')[0];

    // Adicionar nome do produto aos dados
    formData.append('produto_nome', produtoNome);

    fetch('ajax/ajuste_estoque.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarSucesso(data.message);
            fecharModalAjusteEstoque();
            carregarMovimentacoes(); // Recarregar tabela de movimentações
        } else {
            mostrarErro(data.message);
        }
    })
    .catch(error => {
        console.error('Erro ao salvar ajuste:', error);
        mostrarErro('Erro interno do servidor');
    });
}

function abrirModalMovimentacao() {
    // Verificar se há caixa aberto
    if (statusCaixa !== 'aberto') {
        mostrarErro('É necessário ter um caixa aberto para registrar movimentações');
        return;
    }
    
    // Criar modal de movimentação
    const modal = document.createElement('div');
    modal.className = 'modal-produto';
    modal.id = 'modal-movimentacao';
    modal.innerHTML = `
        <div class="modal-produto-content">
            <div class="modal-produto-header">
                <h3><i class="fas fa-plus-circle"></i> Nova Movimentação de Caixa</h3>
                <button class="modal-produto-close" onclick="fecharModalMovimentacao()">&times;</button>
            </div>
            <form id="form-movimentacao" onsubmit="salvarMovimentacao(event)">
                <div class="modal-produto-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tipo_movimentacao">Tipo de Movimentação *</label>
                            <select id="tipo_movimentacao" name="tipo" required>
                                <option value="">Selecione o tipo</option>
                                <option value="entrada">Entrada (+)</option>
                                <option value="saida">Saída (-)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="valor_movimentacao">Valor *</label>
                            <input type="number" id="valor_movimentacao" name="valor" step="0.01" min="0.01" required placeholder="0,00">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="categoria_movimentacao">Categoria</label>
                            <select id="categoria_movimentacao" name="categoria">
                                <option value="">Selecione uma categoria</option>
                                <option value="Vendas">Vendas</option>
                                <option value="Despesas">Despesas</option>
                                <option value="Troco">Troco</option>
                                <option value="Ajuste">Ajuste</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="descricao_movimentacao">Descrição *</label>
                            <input type="text" id="descricao_movimentacao" name="descricao" required placeholder="Ex: Troco para cliente, Despesa com material, etc.">
                        </div>
                    </div>
                </div>
                
                <div class="modal-produto-footer">
                    <button type="button" class="btn-secondary" onclick="fecharModalMovimentacao()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Salvar Movimentação
                    </button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
    modal.style.display = 'flex';
    
    // Focar no primeiro campo
    document.getElementById('tipo_movimentacao').focus();
}

function fecharModalMovimentacao() {
    const modal = document.getElementById('modal-movimentacao');
    if (modal) {
        modal.remove();
    }
}

function salvarMovimentacao(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Mostrar loading
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    submitBtn.disabled = true;
    
    fetch('ajax/salvar_movimentacao_caixa.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarSucesso(data.message);
            fecharModalMovimentacao();
            
            // Atualizar saldo atual
            document.getElementById('saldo-atual').textContent = formatarMoeda(data.novo_saldo);
            
            // Recarregar movimentações
            carregarMovimentacoesCaixa();
        } else {
            mostrarErro(data.message);
        }
    })
    .catch(error => {
        console.error('Erro ao salvar movimentação:', error);
        mostrarErro('Erro interno do servidor');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}
