<?php
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/../includes/verifica_permissao.php';
require_once __DIR__ . '/../includes/funcoes.php';

// Mantém a página fora do menu. Apenas acesso direto por URL.
verificarLogin();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Relatório de Custos - Festa Junina</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="./custos.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
  <main id="reportRoot" class="container-xxl py-4">
    <header class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h1 class="page-title mb-1">Relatório de Custos</h1>
        <p class="text-muted mb-0">Resumo executivo dos custos da Festa Junina</p>
      </div>
      <div class="d-none d-md-flex gap-2">
        <a class="btn btn-outline-secondary" href="../index.php"><i class="bi bi-house"></i> Início</a>
      </div>
    </header>

    <section class="card p-3 p-md-4 mb-4 shadow-sm">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-md-4">
          <label for="dataInicio" class="form-label">Data inicial</label>
          <input type="date" class="form-control" id="dataInicio" />
        </div>
        <div class="col-12 col-md-4">
          <label for="dataFim" class="form-label">Data final</label>
          <input type="date" class="form-control" id="dataFim" />
        </div>
        <div class="col-12 col-md-4 d-grid d-md-flex gap-2">
          <button id="btnUltimos60" class="btn btn-outline-primary flex-grow-1"><i class="bi bi-calendar3"></i> 60 dias</button>
          <button id="btnUltimos90" class="btn btn-outline-primary flex-grow-1"><i class="bi bi-calendar3"></i> 90 dias</button>
          <button id="btnAplicar" class="btn btn-primary flex-grow-1"><i class="bi bi-arrow-repeat"></i> Aplicar</button>
        </div>
      </div>
    </section>

    <section class="mb-4">
      <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="receita">
            <div class="kpi-icon bg-primary-subtle text-primary"><i class="bi bi-credit-card"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Receita de Cartões</span>
              <h2 id="kpiReceita" class="kpi-value">R$ 0,00</h2>
              <span id="kpiQtdCartao" class="kpi-sub">Cartões movimentados</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="vendas">
            <div class="kpi-icon bg-warning-subtle text-warning"><i class="bi bi-arrow-counterclockwise"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Vendas (Itens)</span>
              <h2 id="kpiVendas" class="kpi-value">R$ 0,00</h2>
              <span id="kpiQtdVendas" class="kpi-sub">0 itens</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="saldo">
            <div class="kpi-icon bg-info-subtle text-info"><i class="bi bi-graph-down"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Saldo deixado em Cartões</span>
              <h2 id="kpiSaldoCartoes" class="kpi-value">R$ 0,00</h2>
              <span class="kpi-sub"><span id="kpiCartoesAtivos">0</span> cartões com saldo</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="resultado">
            <div class="kpi-icon bg-danger-subtle text-danger"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Resultado</span>
              <h2 id="kpiResultado" class="kpi-value">R$ 0,00</h2>
              <span class="kpi-sub">Receita - Custos</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="ticket">
            <div class="kpi-icon bg-success-subtle text-success"><i class="bi bi-receipt"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Ticket Médio</span>
              <h2 id="kpiTicketMedio" class="kpi-value">R$ 0,00</h2>
              <span class="kpi-sub"><span id="kpiNumVendas">0</span> vendas</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="receita_cartoes">
            <div class="kpi-icon bg-purple-subtle text-purple"><i class="bi bi-credit-card-fill"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Receita de Cartões</span>
              <h2 id="kpiReceitaCartoes" class="kpi-value">R$ 0,00</h2>
              <span class="kpi-sub">Custo inicial cobrado</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal de explicação matemática -->
    <div class="modal fade" id="modalExplicacao" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Como calculamos</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="explicacaoBody" class="small"></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary" data-bs-dismiss="modal">Entendi</button>
          </div>
        </div>
      </div>
    </div>

    <section class="row g-4 mb-4">
      <div class="col-12 col-xl-8">
        <div class="card p-3 p-md-4 shadow-sm h-100" data-chart="evolucao">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Receitas x Vendas x Custos</h5>
            <span id="periodoStr" class="text-muted small"></span>
          </div>
          <canvas id="chartEvolucao" height="120"></canvas>
        </div>
      </div>
      <div class="col-12 col-xl-4">
        <div class="card p-3 p-md-4 shadow-sm h-100" data-chart="pagamentos">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Meios de Pagamento</h5>
            <span class="text-muted small">Distribuição</span>
          </div>
          <canvas id="chartPagamentos" height="120"></canvas>
        </div>
      </div>
    </section>

    <!-- KPIs Detalhados -->
    <section class="mb-4">
      <h5 class="mb-3">Detalhamento Financeiro</h5>
      <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="custo_cartao">
            <div class="kpi-icon bg-secondary-subtle text-secondary"><i class="bi bi-credit-card-2-front"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Custo de Cartões</span>
              <h2 id="kpiCustoCartao" class="kpi-value">R$ 0,00</h2>
              <span class="kpi-sub">Taxa por cartão emitido</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="estornos">
            <div class="kpi-icon bg-warning-subtle text-warning"><i class="bi bi-arrow-counterclockwise"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Estornos</span>
              <h2 id="kpiEstornos" class="kpi-value">R$ 0,00</h2>
              <span class="kpi-sub">Devoluções e ajustes</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="margem">
            <div class="kpi-icon bg-success-subtle text-success"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Margem Líquida</span>
              <h2 id="kpiMargem" class="kpi-value">0%</h2>
              <span class="kpi-sub">Resultado / Receita</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="conversao">
            <div class="kpi-icon bg-info-subtle text-info"><i class="bi bi-percent"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Taxa de Conversão</span>
              <h2 id="kpiConversao" class="kpi-value">0%</h2>
              <span class="kpi-sub">Vendas / Receita</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="numero_vendas">
            <div class="kpi-icon bg-primary-subtle text-primary"><i class="bi bi-receipt-cutoff"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Número de Vendas</span>
              <h2 id="kpiNumVendasCard" class="kpi-value">0</h2>
              <span class="kpi-sub">Transações concluídas</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="numero_itens">
            <div class="kpi-icon bg-info-subtle text-info"><i class="bi bi-basket2"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Número de Itens</span>
              <h2 id="kpiNumItensCard" class="kpi-value">0</h2>
              <span class="kpi-sub">Unidades vendidas</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="row g-4 mb-4">
      <div class="col-12 col-xl-6">
        <div class="card p-3 p-md-4 shadow-sm h-100" data-chart="top_produtos">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Top Produtos</h5>
            <span class="text-muted small">Valor vendido</span>
          </div>
          <canvas id="chartTopProdutos" height="140"></canvas>
        </div>
      </div>
      <div class="col-12 col-xl-6">
        <div class="card p-3 p-md-4 shadow-sm h-100" data-chart="top_categorias">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Top Categorias</h5>
            <span class="text-muted small">Valor vendido</span>
          </div>
          <canvas id="chartTopCategorias" height="140"></canvas>
        </div>
      </div>
      <div class="col-12">
        <div class="card p-3 p-md-4 shadow-sm h-100" data-chart="pay_daily">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Créditos por Meio de Pagamento (Diário)</h5>
            <span class="text-muted small">PIX x Dinheiro</span>
          </div>
          <canvas id="chartPayDaily" height="110"></canvas>
        </div>
      </div>
    </section>

    <!-- Módulo de Custos -->
    <section class="mb-4">
      <h5 class="mb-3">Custos</h5>
      <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="custos_totais">
            <div class="kpi-icon bg-danger-subtle text-danger"><i class="bi bi-clipboard2-minus"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Custos Totais</span>
              <h2 id="kpiCustosTotais" class="kpi-value">R$ 0,00</h2>
              <span class="kpi-sub">Cartões + Estornos</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="custo_receita">
            <div class="kpi-icon bg-warning-subtle text-warning"><i class="bi bi-pie-chart"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Custos/Receita</span>
              <h2 id="kpiCustoReceita" class="kpi-value">0%</h2>
              <span class="kpi-sub">Percentual do custo</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="custo_venda">
            <div class="kpi-icon bg-info-subtle text-info"><i class="bi bi-basket"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Custo por Venda</span>
              <h2 id="kpiCustoVenda" class="kpi-value">R$ 0,00</h2>
              <span class="kpi-sub">Média por transação</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="kpi-card shadow-sm" data-kpi="custo_item">
            <div class="kpi-icon bg-secondary-subtle text-secondary"><i class="bi bi-box-seam"></i></div>
            <div class="kpi-body">
              <span class="kpi-label">Custo por Item</span>
              <h2 id="kpiCustoItem" class="kpi-value">R$ 0,00</h2>
              <span class="kpi-sub">Média por unidade</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <footer class="text-center text-muted small py-4">
      <span>Relatório confidencial • Festa Junina</span>
    </footer>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="./custos.js"></script>
</body>
</html>


