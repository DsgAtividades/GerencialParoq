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

// Incluir header depois das verificações
require_once 'includes/header.php';
?>

<div class="container">
    <!-- Cabeçalho com Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="filtroForm" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Período</label>
                            <select class="form-select" id="periodo" name="periodo">
                                <option value="hoje">Hoje</option>
                                <option value="ontem">Ontem</option>
                                <option value="7dias">Últimos 7 dias</option>
                                <option value="30dias">Últimos 30 dias</option>
                                <option value="personalizado">Personalizado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Categoria</label>
                            <select class="form-select" id="categoria" name="categoria">
                                <option value="">Todas</option>
                                <?php
                                $stmt = $db->query("SELECT id, nome FROM categorias ORDER BY nome");
                                while ($cat = $stmt->fetch()) {
                                    echo "<option value='{$cat['id']}'>{$cat['nome']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Buscar Produto</label>
                            <input type="text" class="form-control" id="busca" name="busca" placeholder="Nome do produto">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row mb-4">
        <div class="col-md-3 py-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total de Vendas</h6>
                    <h2 class="mb-0" id="totalVendas">R$ 0,00</h2>
                    <small class="text-white-50" id="comparacaoVendas"></small>
                </div>
            </div>
        </div>
        <div class="col-md-3 py-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Quantidade Vendida</h6>
                    <h2 class="mb-0" id="quantidadeVendida">0</h2>
                    <small class="text-white-50" id="comparacaoQuantidade"></small>
                </div>
            </div>
        </div>
        <div class="col-md-3 py-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Produtos Diferentes</h6>
                    <h2 class="mb-0" id="produtosDiferentes">0</h2>
                    <small class="text-white-50">produtos vendidos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 py-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Ticket Médio</h6>
                    <h2 class="mb-0" id="ticketMedio">R$ 0,00</h2>
                    <small class="text-white-50" id="comparacaoTicket"></small>
                </div>
            </div>
        </div>
    </div>
    <!-- Linha debaixo: Cards de Saldos dos Cartões -->
    <div class="row mb-4" id="linhaSaldosCartao">
        <div class="col-md-3 py-2">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h6 class="card-title">Saldo Total em Cartões</h6>
                    <h2 class="mb-0" id="saldoTotalCartoes">R$ 0,00</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 py-2">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h6 class="card-title">Saldo Médio por Cartão</h6>
                    <h2 class="mb-0" id="saldoMedioCartoes">R$ 0,00</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 py-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Cartões Ativos</h6>
                    <h2 class="mb-0" id="qtdCartoesAtivos">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 py-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total de Créditos Inseridos</h6>
                    <h2 class="mb-0" id="totalCreditosCartoes">R$ 0,00</h2>
                </div>
            </div>
        </div>
    </div>
    <!-- Tabela de Produtos -->
    <div class="card">
        <div class="card-body">
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
</div>

<!-- Modal de Detalhes -->
<div class="modal fade" id="modalDetalhes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="graficoVendas" style="height: 300px;"></div>
                <div class="table-responsive mt-3">
                    <table class="table table-sm" id="tabelaDetalhes">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Quantidade</th>
                                <th>Valor</th>
                                <th>Cliente</th>
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
    const formData = new FormData(document.getElementById('filtroForm'));
    
    fetch('ajax/get_dashboard_data.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Atualizar cards
        document.getElementById('totalVendas').textContent = formatMoney(data.resumo.total_vendas);
        document.getElementById('quantidadeVendida').textContent = data.resumo.quantidade_vendida;
        document.getElementById('produtosDiferentes').textContent = data.resumo.produtos_diferentes;
        document.getElementById('ticketMedio').textContent = formatMoney(data.resumo.ticket_medio);

        // Atualizar comparações
        document.getElementById('comparacaoVendas').textContent = `${data.resumo.variacao_vendas}% vs período anterior`;
        document.getElementById('comparacaoQuantidade').textContent = `${data.resumo.variacao_quantidade}% vs período anterior`;
        document.getElementById('comparacaoTicket').textContent = `${data.resumo.variacao_ticket}% vs período anterior`;

        // Atualizar cards de saldo dos cartões
        if (data.saldos_cartao) {
            document.getElementById('saldoTotalCartoes').textContent = formatMoney(data.saldos_cartao.saldo_total);
            document.getElementById('saldoMedioCartoes').textContent = formatMoney(data.saldos_cartao.saldo_medio);
            document.getElementById('qtdCartoesAtivos').textContent = data.saldos_cartao.qtd_cartoes;
            document.getElementById('totalCreditosCartoes').textContent = formatMoney(data.saldos_cartao.total_creditos);
        }

        // Limpar e preencher tabela
        const tbody = document.querySelector('#tabelaProdutos tbody');
        tbody.innerHTML = '';

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
                    <span class="badge bg-${produto.estoque > 10 ? 'success' : 'danger'}">
                        ${produto.estoque}
                    </span>
                </td>
                <td>${produto.quantidade_vendida}</td>
                <td>${formatMoney(produto.valor_vendido)}</td>
                <td>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" style="width: ${produto.percentual}%">
                            ${produto.percentual}%
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-${produto.tendencia > 0 ? 'success' : produto.tendencia < 0 ? 'danger' : 'secondary'}">
                        ${produto.tendencia > 0 ? '↑' : produto.tendencia < 0 ? '↓' : '→'} 
                        ${Math.abs(produto.tendencia)}%
                    </span>
                </td>
            `;
            tbody.appendChild(tr);
        });
    });
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
            
            data.vendas.forEach(venda => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${venda.data_hora}</td>
                    <td>${venda.quantidade}</td>
                    <td>${formatMoney(venda.valor)}</td>
                    <td>${venda.cliente}</td>
                `;
                tbody.appendChild(tr);
            });

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
