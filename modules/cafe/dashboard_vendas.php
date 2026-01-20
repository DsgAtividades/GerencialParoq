<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'includes/verifica_permissao.php';

// Verificar permissão antes de qualquer output
if (!temPermissao('visualizar_dashboard')) {
    header('Location: index.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Filtros
$data_inicial = isset($_POST['data_inicial']) ? $_POST['data_inicial'] : date('Y-m-d');
$data_final = isset($_POST['data_final']) ? $_POST['data_final'] : date('Y-m-d');
$categoria_id = $_POST['categoria_id'] ?? '';

// Monta WHERE dinâmico
$where = [];
$params = [];
if ($data_inicial) {
    $where[] = 've.data_venda >= :data_inicial';
    $params[':data_inicial'] = $data_inicial;
}
if ($data_final) {
    $where[] = 've.data_venda <= :data_final';
    $params[':data_final'] = $data_final;
}
if ($categoria_id) {
    $where[] = 'ca.id = :categoria_id';
    $params[':categoria_id'] = $categoria_id;
}

// Incluir header depois das verificações
require_once 'includes/header.php';
?>

<style>
    .dashboard-hero {
        background: linear-gradient(135deg, #002930 0%, #004d5a 100%);
        border-radius: 16px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        color: #f8f0af !important;
        box-shadow: 0 8px 24px rgba(0, 41, 48, 0.2);
    }
    
    .dashboard-hero h1,
    .dashboard-hero h1 * {
        font-size: 2.5rem !important;
        font-weight: 700 !important;
        margin-bottom: 0.5rem !important;
        color: #f8f0af !important;
    }
    
    .dashboard-hero h1 i {
        color: #f8f0af !important;
    }
    
    .dashboard-hero p {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 0;
        color: #f8f0af !important;
    }
    
    .filters-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 1.75rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(0, 41, 48, 0.08);
        border: none;
    }
    
    .filters-card .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }
    
    .filters-card .form-control,
    .filters-card .form-select {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 0.625rem 1rem;
        transition: all 0.3s ease;
    }
    
    .filters-card .form-control:focus,
    .filters-card .form-select:focus {
        border-color: #002930;
        box-shadow: 0 0 0 0.2rem rgba(0, 41, 48, 0.15);
    }
    
    .btn-filter {
        background: linear-gradient(135deg, #002930 0%, #004d5a 100%);
        border: none;
        border-radius: 8px;
        padding: 0.625rem 1.5rem;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 41, 48, 0.3);
        color: white;
    }
    
    .stat-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 1.75rem;
        height: 100%;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 41, 48, 0.08);
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--card-color-start), var(--card-color-end));
    }
    
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 41, 48, 0.15);
    }
    
    .stat-card-primary {
        --card-color-start: #0d6efd;
        --card-color-end: #0a58ca;
    }
    
    .stat-card-success {
        --card-color-start: #198754;
        --card-color-end: #146c43;
    }
    
    .stat-card-danger {
        --card-color-start: #dc3545;
        --card-color-end: #bb2d3b;
    }
    
    .stat-card-secondary {
        --card-color-start: #6c757d;
        --card-color-end: #5c636a;
    }
    
    .stat-card-dark {
        --card-color-start: #212529;
        --card-color-end: #1a1e21;
    }
    
    .stat-card-info {
        --card-color-start: #0dcaf0;
        --card-color-end: #0aa2c0;
    }
    
    .stat-icon {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, var(--card-color-start), var(--card-color-end));
        color: white !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .stat-icon i {
        color: white !important;
        font-size: 2rem !important;
        display: inline-block !important;
    }
    
    .stat-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 0.25rem;
    }
    
    .stat-comparison {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }
    
    .stat-comparison.positive {
        color: #198754;
    }
    
    .stat-comparison.negative {
        color: #dc3545;
    }
    
    .products-table-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 4px 12px rgba(0, 41, 48, 0.08);
        border: none;
    }
    
    .products-table-card .table {
        margin-bottom: 0;
    }
    
    .products-table-card .table thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1rem;
    }
    
    .products-table-card .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .products-table-card .table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .products-table-card .table tbody a {
        color: #002930;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .products-table-card .table tbody a:hover {
        color: #004d5a;
        text-decoration: underline;
    }
    
    .progress-modern {
        height: 24px;
        border-radius: 12px;
        background: #e9ecef;
        overflow: hidden;
    }
    
    .progress-modern .progress-bar {
        background: linear-gradient(90deg, #198754, #146c43);
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    @media (max-width: 768px) {
        .dashboard-hero {
            padding: 1.5rem;
        }
        
        .dashboard-hero h1 {
            font-size: 1.75rem !important;
        }
        
        .stat-value {
            font-size: 1.5rem;
        }
        
        .filters-card {
            padding: 1.25rem;
        }
    }
</style>

<div class="container">
    <!-- Hero Section -->
    <div class="dashboard-hero">
        <h1><i class="bi bi-graph-up-arrow"></i> Dashboard de Vendas</h1>
        <p>Análise completa de vendas, receitas e desempenho de produtos</p>
    </div>
    
    <!-- Filtros -->
    <div class="filters-card">
        <form id="filtroForm" class="row g-3" method="post">
            <div class="col-md-3"> 
                <label for="data_inicial" class="form-label">Data Inicial</label>
                <input type="date" class="form-control" id="data_inicial" name="data_inicial" value="<?= htmlspecialchars($data_inicial) ?>">
            </div>
            <div class="col-md-3"> 
                <label for="data_final" class="form-label">Data Final</label>
                <input type="date" class="form-control" id="data_final" name="data_final" value="<?= htmlspecialchars($data_final) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Categoria</label>
                <select class="form-select" id="categoria" name="categoria">
                    <option value="">Todas</option>
                    <?php
                    $stmt = $db->query("SELECT id, nome FROM cafe_categorias ORDER BY nome");
                    while ($cat = $stmt->fetch()) {
                        echo "<option value='{$cat['id']}'>{$cat['nome']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Buscar Produto</label>
                <input type="text" class="form-control" id="busca" name="busca" placeholder="Nome do produto">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-filter w-100">
                    <i class="bi bi-search"></i> 
                </button>
            </div>
        </form>
    </div>

    <!-- Cards de Resumo -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-label">Total de Créditos Inseridos</div>
                <div class="stat-value" id="totalCreditosCartoes">R$ 0,00</div>
                <div class="stat-comparison" id="teste">&nbsp;</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div class="stat-label">Total de Vendas</div>
                <div class="stat-value" id="totalVendas">R$ 0,00</div>
                <div class="stat-comparison" id="comparacaoVendas"></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card stat-card-danger">
                <div class="stat-icon">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </div>
                <div class="stat-label">Total Estornado</div>
                <div class="stat-value" id="estornoTotalCartoes">R$ 0,00</div>
                <div class="stat-comparison" id="qtdeEstorno">&nbsp;</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card stat-card-secondary">
                <div class="stat-icon">
                    <i class="bi bi-credit-card"></i>
                </div>
                <div class="stat-label">Receita com Cartão</div>
                <div class="stat-value" id="custoCartao">R$ 0,00</div>
                <div class="stat-comparison" id="qtdeCartao">&nbsp;</div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card stat-card-dark">
                <div class="stat-icon">
                    <i class="bi bi-wallet"></i>
                </div>
                <div class="stat-label">Saldo Total em Cartões</div>
                <div class="stat-value" id="saldoTotalCartoes">R$ 0,00</div>
                <div class="stat-comparison" id="teste">&nbsp;</div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card stat-card-secondary">
                <div class="stat-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-label">Quantidade Vendida</div>
                <div class="stat-value" id="quantidadeVendida">0</div>
                <div class="stat-comparison" id="comparacaoQuantidade"></div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-label">Cartões Ativos</div>
                <div class="stat-value" id="qtdCartoesAtivos">0</div>
                <div class="stat-comparison" id="teste">&nbsp;</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stat-label">Receita Total</div>
                <div class="stat-value" id="totalReceita">R$ 0,00</div>
                <div class="stat-comparison">&nbsp;</div>
            </div>
        </div>
    </div>
    
    <!-- Linha debaixo: Cards de Saldos dos Cartões -->
    <div class="row mb-4" id="linhaSaldosCartao"></div>
    
    <!-- Tabela de Produtos -->
    <div class="products-table-card">
        <h5 class="mb-4" style="color: #002930; font-weight: 600;">
            <i class="bi bi-list-ul"></i> Produtos Vendidos
        </h5>
        <div class="table-responsive">
            <table class="table table-hover" id="tabelaProdutos">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Categoria</th>
                        <th>Estoque Atual</th>
                        <th>Qtd. Vendida</th>
                        <th>Valor Vendido</th>
                        <th>% do Total</th>
                        <th>Tendência</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Preenchido via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Detalhes -->
<div class="modal fade" id="modalDetalhes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #002930 0%, #004d5a 100%); color: #f8f0af; border-radius: 16px 16px 0 0; border: none;">
                <h5 class="modal-title" style="font-weight: 600;">
                    <i class="bi bi-info-circle"></i> Detalhes do Produto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 1.75rem;">
                <div id="graficoVendas" style="height: 300px; margin-bottom: 1.5rem;"></div>
                <h6 style="color: #002930; font-weight: 600; margin-bottom: 1rem;">
                    <i class="bi bi-list-check"></i> Histórico de Vendas
                </h6>
                <div class="table-responsive">
                    <table class="table table-hover" id="tabelaDetalhes">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="font-weight: 600; color: #495057;">Data/Hora</th>
                                <th style="font-weight: 600; color: #495057;">Quantidade</th>
                                <th style="font-weight: 600; color: #495057;">Valor</th>
                                <th style="font-weight: 600; color: #495057;">Cliente</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Preenchido via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adicionar ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
let atualizacaoAutomatica;
let chartVendas;

// Função para formatar números como moeda
function formatMoney(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

// Função para atualizar os dados
function atualizarDados() {
    data_inicial = document.getElementById('data_inicial').value;
    data_final = document.getElementById('data_final').value;
    categoria = document.getElementById('categoria');
    busca = document.getElementById('busca').value;
    if((data_inicial != '' && data_final == '') || (data_inicial == '' && data_final != '')){
        alert('Os filtros de data precisam ser preenchidos');
    }else{
        const dados = {
                    data_inicial: data_inicial,
                    data_final: data_final,
                    categoria: categoria.value,
                    busca: busca
                };
        
        fetch('ajax/get_dashboard_data.php', {
            method: 'POST',
            headers: {
                        'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            // Verificar se há erro na resposta
            if (data.success === false) {
                alert('Erro: ' + (data.message || 'Erro desconhecido'));
                console.error('Erro do servidor:', data);
                return;
            }
            
            // Verificar se a estrutura esperada existe
            if (!data.resumo || !data.produtos) {
                console.error('Estrutura de dados inválida:', data);
                alert('Erro: Dados recebidos em formato inválido');
                return;
            }
            
            // Atualizar cards
            document.getElementById('totalVendas').textContent = formatMoney(data.resumo.total_vendas || 0);
            document.getElementById('quantidadeVendida').textContent = data.resumo.quantidade_vendida || 0;
            
            document.getElementById('custoCartao').textContent = formatMoney(data.resumo.custo_cartao || 0);
            const qtdeCartaoEl = document.getElementById('qtdeCartao');
            if (qtdeCartaoEl) {
                qtdeCartaoEl.textContent = "Quantidade: " + (data.resumo.qtde_cartao || 0);
            }
            document.getElementById('estornoTotalCartoes').textContent = formatMoney(data.resumo.total_estorno || 0);
            const qtdeEstornoEl = document.getElementById('qtdeEstorno');
            if (qtdeEstornoEl) {
                qtdeEstornoEl.textContent = "Quantidade: " + (data.resumo.qtde_estorno || 0);
            }

            // Atualizar comparações com classes CSS
            const comparacaoVendasEl = document.getElementById('comparacaoVendas');
            const variacaoVendas = data.resumo.variacao_vendas || 0;
            comparacaoVendasEl.textContent = `${variacaoVendas > 0 ? '+' : ''}${variacaoVendas}% vs período anterior`;
            comparacaoVendasEl.className = 'stat-comparison ' + (variacaoVendas > 0 ? 'positive' : variacaoVendas < 0 ? 'negative' : '');
            
            const comparacaoQuantidadeEl = document.getElementById('comparacaoQuantidade');
            const variacaoQuantidade = data.resumo.variacao_quantidade || 0;
            comparacaoQuantidadeEl.textContent = `${variacaoQuantidade > 0 ? '+' : ''}${variacaoQuantidade}% vs período anterior`;
            comparacaoQuantidadeEl.className = 'stat-comparison ' + (variacaoQuantidade > 0 ? 'positive' : variacaoQuantidade < 0 ? 'negative' : '');

            // Atualizar cards de saldo dos cartões
            if (data.saldos_cartao) {
                const totalCreditos = data.saldos_cartao.total_creditos || 0;
                const saldoTotal = data.saldos_cartao.saldo_total || 0;
                const custoCartao = data.resumo.custo_cartao || 0;
                document.getElementById('saldoTotalCartoes').textContent = formatMoney(totalCreditos > 0 ? (totalCreditos - saldoTotal - custoCartao) : 0);
                document.getElementById('qtdCartoesAtivos').textContent = totalCreditos > 0 ? (data.saldos_cartao.qtd_cartoes || 0) : 0;
                document.getElementById('totalCreditosCartoes').textContent = formatMoney(totalCreditos);
            }
            
            document.getElementById('totalReceita').textContent = formatMoney((data.resumo.total_vendas || 0) + (data.resumo.custo_cartao || 0));

            // Limpar e preencher tabela
            const tbody = document.querySelector('#tabelaProdutos tbody');
            tbody.innerHTML = '';

            // Verificar se produtos é um array
            if (!Array.isArray(data.produtos)) {
                console.error('Produtos não é um array:', data.produtos);
                data.produtos = [];
            }

            data.produtos.forEach(produto => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <a href="#" onclick="mostrarDetalhes(${produto.id})" class="text-decoration-none">
                            ${produto.nome_produto}
                        </a>
                    </td>
                    <td>${produto.categoria}</td>
                    <td>
                        <span class="badge bg-${produto.estoque > 10 ? 'success' : 'danger'}" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">
                            ${produto.estoque}
                        </span>
                    </td>
                    <td><strong>${produto.quantidade_vendida}</strong></td>
                    <td><strong style="color: #198754;">${formatMoney(produto.valor_vendido)}</strong></td>
                    <td>
                        <div class="progress-modern">
                            <div class="progress-bar" role="progressbar" style="width: ${produto.percentual}%">
                                ${produto.percentual.toFixed(1)}%
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-${produto.tendencia > 0 ? 'success' : produto.tendencia < 0 ? 'danger' : 'secondary'}" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">
                            ${produto.tendencia > 0 ? '↑' : produto.tendencia < 0 ? '↓' : '→'} 
                            ${Math.abs(produto.tendencia).toFixed(1)}%
                        </span>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Erro ao buscar dados:', error);
            alert('Erro ao carregar dados do dashboard. Verifique o console para mais detalhes.');
        });
    }
}

// Função para mostrar detalhes do produto
function mostrarDetalhes(produtoId) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetalhes'));
    
    fetch(`ajax/get_produto_detalhes.php?id=${produtoId}`)
        .then(response => response.json())
        .then(data => {
            // Configurar e atualizar o gráfico
            const options = {
                series: [{
                    name: 'Vendas',
                    data: data.grafico.valores
                }],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                xaxis: {
                    categories: data.grafico.labels
                },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return formatMoney(value);
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(value) {
                            return formatMoney(value);
                        }
                    }
                }
            };

            if (chartVendas) {
                chartVendas.destroy();
            }
            chartVendas = new ApexCharts(document.querySelector("#graficoVendas"), options);
            chartVendas.render();

            // Preencher tabela de detalhes
            const tbody = document.querySelector('#tabelaDetalhes tbody');
            tbody.innerHTML = '';
            
            if (data.vendas && data.vendas.length > 0) {
                data.vendas.forEach(venda => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${venda.data_hora}</td>
                        <td><strong>${venda.quantidade}</strong></td>
                        <td><strong style="color: #198754;">${formatMoney(venda.valor)}</strong></td>
                        <td>${venda.cliente || 'N/A'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td colspan="4" class="text-center text-muted" style="padding: 2rem;">
                        <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i><br>
                        Nenhuma venda encontrada para este produto no período selecionado.
                    </td>
                `;
                tbody.appendChild(tr);
            }

            modal.show();
        });
}

// Iniciar atualização automática
document.addEventListener('DOMContentLoaded', function() {
    atualizarDados();
    atualizacaoAutomatica = setInterval(atualizarDados, 30000); // Atualiza a cada 30 segundos
});

// Parar atualização quando a página for fechada
window.addEventListener('beforeunload', function() {
    clearInterval(atualizacaoAutomatica);
});

// Atualizar ao mudar filtros
document.getElementById('filtroForm').addEventListener('submit', function(e) {
    e.preventDefault();
    atualizarDados();
});
</script>

<?php include 'includes/footer.php'; ?>
