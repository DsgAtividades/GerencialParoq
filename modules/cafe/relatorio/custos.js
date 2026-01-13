(function() {
  'use strict';

  const el = (id) => document.getElementById(id);
  const fmt = (n) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(n || 0));

  const todayStr = () => new Date().toISOString().slice(0, 10);
  const addDays = (date, days) => {
    const d = new Date(date);
    d.setDate(d.getDate() + days);
    return d.toISOString().slice(0, 10);
  };

  let chartEvolucao, chartPagamentos, chartTopProdutos, chartTopCategorias, chartPayDaily;

  function setDefaultPeriod() {
    el('dataFim').value = todayStr();
    el('dataInicio').value = addDays(todayStr(), -7);
  }

  function updatePeriodoStr(inicio, fim) {
    const from = new Date(inicio);
    const to = new Date(fim);
    const fmtShort = (d) => d.toLocaleDateString('pt-BR');
    el('periodoStr').textContent = `${fmtShort(from)} – ${fmtShort(to)}`;
  }

  async function fetchData() {
    const inicio = el('dataInicio').value;
    const fim = el('dataFim').value;
    updatePeriodoStr(inicio, fim);
    const res = await fetch(`./custos_data.php?data_inicio=${encodeURIComponent(inicio)}&data_fim=${encodeURIComponent(fim)}`, {
      headers: { 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error('Falha ao carregar dados');
    return res.json();
  }

  function renderKPIs(data) {
    el('kpiReceita').textContent = fmt(data.kpis.receita_total);
    el('kpiQtdCartao').textContent = `${data.kpis.qtd_cartoes} cartões`;
    el('kpiVendas').textContent = fmt(data.kpis.vendas_total);
    el('kpiQtdVendas').textContent = `${data.kpis.itens_vendidos} itens`;
    el('kpiSaldoCartoes').textContent = fmt(data.kpis.saldo_cartoes);
    el('kpiCartoesAtivos').textContent = data.kpis.cartoes_ativos;
    el('kpiResultado').textContent = fmt(data.kpis.resultado);
    el('kpiTicketMedio').textContent = fmt(data.kpis.ticket_medio);
    el('kpiNumVendas').textContent = data.kpis.num_vendas;
    // Novos cards de número de vendas e itens
    const numVendasCard = document.getElementById('kpiNumVendasCard');
    if (numVendasCard) numVendasCard.textContent = Number(data.kpis.num_vendas).toLocaleString('pt-BR');
    const numItensCard = document.getElementById('kpiNumItensCard');
    if (numItensCard) numItensCard.textContent = Number(data.kpis.itens_vendidos).toLocaleString('pt-BR');
    
    // KPIs detalhados
    el('kpiCustoCartao').textContent = fmt(data.kpis.custo_cartao_total);
    el('kpiEstornos').textContent = fmt(data.kpis.estornos_total);
    el('kpiMargem').textContent = `${data.kpis.margem_liquida.toFixed(1)}%`;
    el('kpiConversao').textContent = `${data.kpis.taxa_conversao.toFixed(1)}%`;
    el('kpiReceitaCartoes').textContent = fmt(data.kpis.receita_cartoes);
    // Módulo de custos
    el('kpiCustosTotais').textContent = fmt(data.kpis.custos_total);
    el('kpiCustoReceita').textContent = `${(data.kpis.receita_total > 0 ? (100*data.kpis.custos_total/data.kpis.receita_total) : 0).toFixed(1)}%`;
    el('kpiCustoVenda').textContent = fmt(data.kpis.num_vendas > 0 ? (data.kpis.custos_total / data.kpis.num_vendas) : 0);
    el('kpiCustoItem').textContent = fmt(data.kpis.itens_vendidos > 0 ? (data.kpis.custos_total / data.kpis.itens_vendidos) : 0);

    // Guardar últimos valores para explicações
    window.__kpiData = data;
  }

  function renderEvolucao(data) {
    const labels = data.evolucao.map(x => x.dia);
    const serieReceita = data.evolucao.map(x => Number(x.receita));
    const serieVendas  = data.evolucao.map(x => Number(x.vendas));
    const serieCustos  = data.evolucao.map(x => Number(x.custos));
    const ctx = document.getElementById('chartEvolucao');
    if (chartEvolucao) chartEvolucao.destroy();
    chartEvolucao = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          {
            label: 'Receitas',
            data: serieReceita,
            borderColor: '#20c997',
            backgroundColor: 'rgba(32,201,151,0.12)',
            fill: true,
            tension: 0.35,
            pointRadius: 2
          },
          {
            label: 'Vendas',
            data: serieVendas,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,0.12)',
            fill: true,
            tension: 0.35,
            pointRadius: 2
          },
          {
            label: 'Custos',
            data: serieCustos,
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220,53,69,0.12)',
            fill: true,
            tension: 0.35,
            pointRadius: 2
          }
        ]
      },
      options: {
        plugins: {
          legend: { display: true },
          tooltip: {
            callbacks: {
              label: (ctx) => fmt(ctx.parsed.y)
            }
          }
        },
        scales: {
          y: {
            ticks: {
              callback: (v) => fmt(v)
            }
          }
        }
      }
    });
  }

  function renderPagamentos(data) {
    const labels = ['PIX', 'Dinheiro', 'Cartão'];
    const valores = [
      Number(data.pagamentos.pix || 0),
      Number(data.pagamentos.dinheiro || 0),
      Number(data.pagamentos.cartao || 0)
    ];
    const ctx = document.getElementById('chartPagamentos');
    if (chartPagamentos) chartPagamentos.destroy();
    chartPagamentos = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data: valores,
          backgroundColor: ['#20c997','#ffc107','#0d6efd']
        }]
      },
      options: {
        plugins: {
          legend: { position: 'bottom' },
          tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${fmt(ctx.parsed)}` } }
        }
      }
    });
  }

  function renderTops(data) {
    // Top produtos
    {
      const labels = data.top_produtos.map(x => x.nome_produto);
      const valores = data.top_produtos.map(x => Number(x.valor_vendido));
      const ctx = document.getElementById('chartTopProdutos');
      if (chartTopProdutos) chartTopProdutos.destroy();
      chartTopProdutos = new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Vendido', data: valores, backgroundColor: '#0d6efd' }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => fmt(v) } } } }
      });
    }
    // Top categorias
    {
      const labels = data.top_categorias.map(x => x.categoria);
      const valores = data.top_categorias.map(x => Number(x.valor_vendido));
      const ctx = document.getElementById('chartTopCategorias');
      if (chartTopCategorias) chartTopCategorias.destroy();
      chartTopCategorias = new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Vendido', data: valores, backgroundColor: '#20c997' }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => fmt(v) } } } }
      });
    }
  }

  function renderPayDaily(data) {
    const labels = data.pagamentos_diario.map(x => x.dia);
    const seriePIX = data.pagamentos_diario.map(x => Number(x.pix));
    const serieDin = data.pagamentos_diario.map(x => Number(x.dinheiro));
    const serieCar = data.pagamentos_diario.map(x => Number(x.cartao));
    const ctx = document.getElementById('chartPayDaily');
    if (chartPayDaily) chartPayDaily.destroy();
    chartPayDaily = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          { label: 'PIX', data: seriePIX, backgroundColor: '#20c997' },
          { label: 'Dinheiro', data: serieDin, backgroundColor: '#ffc107' },
          { label: 'Cartão', data: serieCar, backgroundColor: '#0d6efd' }
        ]
      },
      options: {
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { ticks: { callback: v => fmt(v) } } }
      }
    });
  }

  async function refresh() {
    try {
      const data = await fetchData();
      renderKPIs(data);
      renderEvolucao(data);
      renderPagamentos(data);
      renderTops(data);
      renderPayDaily(data);
    } catch (e) {
      console.error(e);
      alert('Não foi possível carregar o relatório de custos.');
    }
  }

  function bind() {
    const aplicar = () => {
      const i = new Date(el('dataInicio').value);
      const f = new Date(el('dataFim').value);
      if (isNaN(i) || isNaN(f) || f < i) {
        alert('Período inválido.');
        return;
        }
      refresh();
    };
    el('btnAplicar').addEventListener('click', aplicar);
    // atalhos 60/90 dias
    const setRange = (days) => {
      el('dataFim').value = todayStr();
      el('dataInicio').value = addDays(todayStr(), -days);
      aplicar();
    };
    const b60 = document.getElementById('btnUltimos60');
    if (b60) b60.addEventListener('click', () => setRange(60));
    const b90 = document.getElementById('btnUltimos90');
    if (b90) b90.addEventListener('click', () => setRange(90));
    // Enter em qualquer campo de data aplica
    ['dataInicio','dataFim'].forEach(id => {
      el(id).addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          aplicar();
        }
      });
    });
  }

  // init
  setDefaultPeriod();
  bind();
  refresh();

     

  // Interações de explicação
  document.addEventListener('click', (e) => {
    const card = e.target.closest('.kpi-card');
    const chart = e.target.closest('[data-chart]');
    if ((!card && !chart) || !window.__kpiData) return;
    const bsModal = new bootstrap.Modal(document.getElementById('modalExplicacao'));
    const data = window.__kpiData;
    let html = '';
    const fmt2 = (v) => `<strong>${fmt(v)}</strong>`;
    if (card) {
      const kpi = card.getAttribute('data-kpi');
      switch (kpi) {
      case 'receita': {
        html = `
          <div class="mb-3">
            <strong>Receita de Cartões</strong>
          </div>
          <div class="mb-2">
            Soma de todos os créditos depositados nos cartões durante o período.
          </div>
          <div class="border-top pt-2">
            <strong>Total: ${fmt2(data.kpis.receita_total)}</strong><br/>
            <small class="text-muted">${data.kpis.qtd_cartoes} cartões movimentados</small>
          </div>
        `;
        break;
      }
      case 'vendas': {
        html = `
          <div class="mb-3">
            <strong>Vendas = Quantidade × Valor Unitário</strong>
          </div>
          <div class="mb-2">
            <span class="text-muted">Total de itens vendidos:</span> ${data.kpis.itens_vendidos.toLocaleString('pt-BR')}
          </div>
          <div class="mb-2">
            <span class="text-muted">Número de vendas:</span> ${data.kpis.num_vendas.toLocaleString('pt-BR')}
          </div>
          <div class="border-top pt-2">
            <strong>Total em Vendas: ${fmt2(data.kpis.vendas_total)}</strong><br/>
            <small class="text-muted">Vendas estornadas não incluídas</small>
          </div>
        `;
        break;
      }
      case 'saldo': {
        html = `
          <div class="mb-3">
            <strong>Saldo Deixado em Cartões</strong>
          </div>
          <div class="mb-2">
            Valor que os clientes ainda possuem disponível nos cartões (não consumido).
          </div>
          <div class="border-top pt-2">
            <strong>Total: ${fmt2(data.kpis.saldo_cartoes)}</strong><br/>
            <small class="text-muted">${data.kpis.cartoes_ativos} cartões com saldo positivo</small>
          </div>
        `;
        break;
      }
      case 'resultado': {
        html = `
          <div class="mb-3">
            <strong>Resultado = Receita − Custos Totais</strong>
          </div>
          <div class="mb-2">
            <span class="text-muted">Receita:</span> ${fmt2(data.kpis.receita_total)}
          </div>
          <div class="mb-2">
            <span class="text-muted">Custos Totais:</span> ${fmt2(data.kpis.custos_total)}
            <div class="ms-3 small text-muted">
              • Custo de Cartões: ${fmt2(data.kpis.custo_cartao_total)}<br/>
              • Estornos: ${fmt2(data.kpis.estornos_total)}
            </div>
          </div>
          <div class="border-top pt-2">
            <strong>Resultado Final: ${fmt2(data.kpis.resultado)}</strong>
          </div>
        `;
        break;
      }
      case 'ticket': {
        html = `
          <div class="mb-3">
            <strong>Ticket Médio = Vendas ÷ Número de Vendas</strong>
          </div>
          <div class="mb-2">
            <span class="text-muted">Total em vendas:</span> ${fmt2(data.kpis.vendas_total)}
          </div>
          <div class="mb-2">
            <span class="text-muted">Número de vendas:</span> ${data.kpis.num_vendas.toLocaleString('pt-BR')}
          </div>
          <div class="border-top pt-2">
            <strong>Ticket Médio: ${fmt2(data.kpis.ticket_medio)}</strong>
          </div>
        `;
        break;
      }
      case 'custo_cartao': {
        html = `
          <div class="mb-3">
            <strong>Custo de Cartões</strong>
          </div>
          <div class="mb-2">
            <span class="text-muted">Total gasto com cartões:</span> ${fmt2(data.kpis.custo_cartao_total)}
          </div>
          <div class="mb-2">
            <span class="text-muted">Quantidade de cartões:</span> ${data.kpis.qtd_cartoes}
          </div>
          <div class="border-top pt-2">
            <strong>Custo médio por cartão: ${fmt2(data.kpis.custo_medio_por_cartao)}</strong>
          </div>
        `;
        break;
      }
      case 'estornos': {
        html = `
          <div class="mb-3">
            <strong>Estornos</strong>
          </div>
          <div class="mb-2">
            Soma de todos os valores devolvidos aos clientes (devoluções e ajustes).
          </div>
          <div class="border-top pt-2">
            <strong>Total estornado: ${fmt2(data.kpis.estornos_total)}</strong>
          </div>
        `;
        break;
      }
      case 'margem': {
        html = `
          <div class="mb-3">
            <strong>Margem Líquida = (Resultado ÷ Receita) × 100</strong>
          </div>
          <div class="mb-2">
            <span class="text-muted">Resultado:</span> ${fmt2(data.kpis.resultado)}
          </div>
          <div class="mb-2">
            <span class="text-muted">Receita:</span> ${fmt2(data.kpis.receita_total)}
          </div>
          <div class="border-top pt-2">
            <strong>Margem: ${data.kpis.margem_liquida.toFixed(1)}%</strong>
          </div>
        `;
        break;
      }
      case 'conversao': {
        html = `
          <div class="mb-3">
            <strong>Taxa de Conversão = (Vendas ÷ Receita) × 100</strong>
          </div>
          <div class="mb-2">
            <span class="text-muted">Vendas:</span> ${fmt2(data.kpis.vendas_total)}
          </div>
          <div class="mb-2">
            <span class="text-muted">Receita:</span> ${fmt2(data.kpis.receita_total)}
          </div>
          <div class="border-top pt-2">
            <strong>Taxa: ${data.kpis.taxa_conversao.toFixed(1)}%</strong><br/>
            <small class="text-muted">Mostra quanto da receita se converteu em vendas</small>
          </div>
        `;
        break;
      }
      case 'numero_vendas': {
        html = `
          <div class="mb-3"><strong>Número de Vendas</strong></div>
          <div class="mb-2"><span class="text-muted">Transações no período:</span> ${data.kpis.num_vendas.toLocaleString('pt-BR')}</div>
          <div class="border-top pt-2"><strong>Média de itens por venda: ${(Number(data.kpis.itens_vendidos)/Math.max(1, Number(data.kpis.num_vendas))).toFixed(2)}</strong></div>
        `;
        break;
      }
      case 'numero_itens': {
        html = `
          <div class="mb-3"><strong>Número de Itens</strong></div>
          <div class="mb-2"><span class="text-muted">Total de unidades vendidas:</span> ${data.kpis.itens_vendidos.toLocaleString('pt-BR')}</div>
          <div class="border-top pt-2"><strong>Itens por venda: ${(Number(data.kpis.itens_vendidos)/Math.max(1, Number(data.kpis.num_vendas))).toFixed(2)}</strong></div>
        `;
        break;
      }
      case 'receita_cartoes': {
        html = `
          <div class="mb-3">
            <strong>Receita de Cartões</strong>
          </div>
          <div class="mb-2">
            Valor cobrado dos clientes pela emissão dos cartões (taxa de entrada).
          </div>
          <div class="mb-2">
            <span class="text-muted">Cartões emitidos:</span> ${data.kpis.qtd_cartoes}
          </div>
          <div class="border-top pt-2">
            <strong>Total arrecadado: ${fmt2(data.kpis.receita_cartoes)}</strong><br/>
            <small class="text-muted">Valor médio por cartão: ${fmt2(data.kpis.custo_medio_por_cartao)}</small>
          </div>
        `;
        break;
      }
      case 'custos_totais': {
        html = `
          <div class="mb-3"><strong>Custos Totais</strong></div>
          <div class="mb-2"><span class="text-muted">Fórmula:</span> Custo de Cartões + Estornos</div>
          <div class="mb-2"><span class="text-muted">Custo de Cartões:</span> ${fmt2(data.kpis.custo_cartao_total)}</div>
          <div class="mb-2"><span class="text-muted">Estornos:</span> ${fmt2(data.kpis.estornos_total)}</div>
          <div class="border-top pt-2"><strong>Total: ${fmt2(data.kpis.custos_total)}</strong></div>
        `;
        break;
      }
      case 'custo_receita': {
        const perc = (data.kpis.receita_total > 0 ? (100*data.kpis.custos_total/data.kpis.receita_total) : 0);
        html = `
          <div class="mb-3"><strong>Custos / Receita</strong></div>
          <div class="mb-2"><span class="text-muted">Fórmula:</span> (Custos Totais ÷ Receita) × 100</div>
          <div class="mb-2"><span class="text-muted">Custos Totais:</span> ${fmt2(data.kpis.custos_total)}</div>
          <div class="mb-2"><span class="text-muted">Receita:</span> ${fmt2(data.kpis.receita_total)}</div>
          <div class="border-top pt-2"><strong>Percentual: ${perc.toFixed(1)}%</strong></div>
        `;
        break;
      }
      case 'custo_venda': {
        const custoVenda = (data.kpis.num_vendas > 0) ? (data.kpis.custos_total / data.kpis.num_vendas) : 0;
        html = `
          <div class="mb-3"><strong>Custo por Venda</strong></div>
          <div class="mb-2"><span class="text-muted">Fórmula:</span> Custos Totais ÷ Número de Vendas</div>
          <div class="mb-2"><span class="text-muted">Custos Totais:</span> ${fmt2(data.kpis.custos_total)}</div>
          <div class="mb-2"><span class="text-muted">Número de Vendas:</span> ${data.kpis.num_vendas.toLocaleString('pt-BR')}</div>
          <div class="border-top pt-2"><strong>Média por venda: ${fmt2(custoVenda)}</strong></div>
        `;
        break;
      }
      case 'custo_item': {
        const custoItem = (data.kpis.itens_vendidos > 0) ? (data.kpis.custos_total / data.kpis.itens_vendidos) : 0;
        html = `
          <div class="mb-3"><strong>Custo por Item</strong></div>
          <div class="mb-2"><span class="text-muted">Fórmula:</span> Custos Totais ÷ Número de Itens</div>
          <div class="mb-2"><span class="text-muted">Custos Totais:</span> ${fmt2(data.kpis.custos_total)}</div>
          <div class="mb-2"><span class="text-muted">Número de Itens:</span> ${data.kpis.itens_vendidos.toLocaleString('pt-BR')}</div>
          <div class="border-top pt-2"><strong>Média por item: ${fmt2(custoItem)}</strong></div>
        `;
        break;
      }
      default: return;
      }
    } else if (chart) {
      const type = chart.getAttribute('data-chart');
      switch (type) {
        case 'evolucao':
          html = `
            <div class="mb-2"><strong>Receitas x Vendas x Custos</strong></div>
            <div class="small text-muted">
              • Receitas = Créditos em cartões no dia<br/>
              • Vendas = Σ (quantidade × valor_unitário) de vendas não estornadas<br/>
              • Custos = Custo de Cartões + Estornos no dia
            </div>`;
          break;
        case 'pagamentos':
          html = `
            <div class="mb-2"><strong>Meios de Pagamento</strong></div>
            <div class="small text-muted">
              PIX, Dinheiro e Cartão conforme registros em histórico de transações do sistema. Cartão soma variações (Cartão/Cartao/Crédito/Débito).
            </div>`;
          break;
        case 'top_produtos':
          html = `
            <div class="mb-2"><strong>Top Produtos</strong></div>
            <div class="small text-muted">Ranking por valor vendido (quantidade × valor_unitário).</div>`;
          break;
        case 'top_categorias':
          html = `
            <div class="mb-2"><strong>Top Categorias</strong></div>
            <div class="small text-muted">Soma do valor vendido por categoria no período.</div>`;
          break;
        case 'pay_daily':
          html = `
            <div class="mb-2"><strong>Créditos por Meio (Diário)</strong></div>
            <div class="small text-muted">Barras empilhadas de PIX, Dinheiro e Cartão (inclui variações Cartão/Cartao/Crédito/Débito) por dia.</div>`;
          break;
        default:
          return;
      }
    }
    document.getElementById('explicacaoBody').innerHTML = html;
    bsModal.show();
  });
})();


