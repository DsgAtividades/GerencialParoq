<?php
try {
    // Buscar dados para gráficos

    // 1. Status dos Serviços (Gráfico de Pizza)
    $sql = "SELECT 
        status,
        COUNT(*) as total,
        SUM(total) as valor_total
        FROM obras_servicos 
        GROUP BY status";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $status_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Evolução Mensal dos Serviços (Gráfico de Linha)
    $sql = "SELECT 
        DATE_FORMAT(data_ordem_servico, '%Y-%m') as mes,
        COUNT(*) as total_servicos,
        SUM(total) as valor_total
        FROM obras_servicos 
        WHERE data_ordem_servico IS NOT NULL 
        AND data_ordem_servico >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(data_ordem_servico, '%Y-%m')
        ORDER BY mes";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $monthly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Top Responsáveis (Gráfico de Barras)
    $sql = "SELECT 
        responsavel,
        COUNT(*) as total_servicos,
        SUM(total) as valor_total
        FROM obras_servicos
        WHERE responsavel IS NOT NULL AND responsavel != ''
        GROUP BY responsavel
        ORDER BY total_servicos DESC
        LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $responsaveis_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Análise Financeira Mensal (Gráfico de Barras Combinado)
    $sql = "SELECT 
        DATE_FORMAT(data_ordem_servico, '%Y-%m') as mes,
        SUM(total) as valor_total,
        SUM(valor_antecipado) as valor_antecipado,
        SUM(total - COALESCE(valor_antecipado, 0)) as valor_faltante
        FROM obras_servicos 
        WHERE data_ordem_servico IS NOT NULL 
        AND data_ordem_servico >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(data_ordem_servico, '%Y-%m')
        ORDER BY mes";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $financial_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. KPIs principais
    $sql = "SELECT 
        COUNT(*) as total_servicos,
        SUM(total) as valor_total_geral,
        SUM(valor_antecipado) as valor_antecipado_geral,
        AVG(total) as valor_medio_servico,
        COUNT(CASE WHEN status = 'Em Andamento' THEN 1 END) as em_andamento,
        COUNT(CASE WHEN status = 'Concluído' THEN 1 END) as concluido,
        COUNT(CASE WHEN status = 'Pendente' THEN 1 END) as pendente,
        COUNT(CASE WHEN status = 'Cancelado' THEN 1 END) as cancelado
        FROM obras_servicos";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $kpis = $stmt->fetch(PDO::FETCH_ASSOC);

    // 6. Serviços por Período (últimos 6 meses)
    $sql = "SELECT 
        DATE_FORMAT(data_ordem_servico, '%Y-%m') as mes,
        status,
        COUNT(*) as total
        FROM obras_servicos 
        WHERE data_ordem_servico >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(data_ordem_servico, '%Y-%m'), status
        ORDER BY mes, status";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $status_monthly = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Erro ao acessar o banco de dados: ' . htmlspecialchars($e->getMessage()) . '</div>';
    return;
}
?>

<div class="analytics-container">
    <div class="analytics-header">
        <h1><i class="bi bi-graph-up"></i> Analytics BI - Dashboard Inteligente</h1>
        <p>Análise completa dos dados de serviços e obras da Paróquia São Pedro</p>
    </div>

    <!-- KPIs Principais -->
    <div class="stats-grid">
        <div class="kpi-card gradient-primary">
            <div class="kpi-value"><?php echo number_format($kpis['total_servicos'] ?? 0); ?></div>
            <div class="kpi-label">Total de Serviços</div>
        </div>
        
        <div class="kpi-card gradient-success">
            <div class="kpi-value">R$ <?php echo number_format($kpis['valor_total_geral'] ?? 0, 0, ',', '.'); ?></div>
            <div class="kpi-label">Valor Total</div>
        </div>
        
        <div class="kpi-card gradient-info">
            <div class="kpi-value">R$ <?php echo number_format($kpis['valor_medio_servico'] ?? 0, 0, ',', '.'); ?></div>
            <div class="kpi-label">Valor Médio por Serviço</div>
        </div>
        
        <div class="kpi-card gradient-warning">
            <div class="kpi-value">
                <?php 
                $total = $kpis['valor_total_geral'] ?? 0;
                $antecipado = $kpis['valor_antecipado_geral'] ?? 0;
                $percentage = $total > 0 ? (($total - $antecipado) / $total) * 100 : 0;
                echo number_format($percentage, 1); 
                ?>%
            </div>
            <div class="kpi-label">% Valor Faltante</div>
        </div>
    </div>

    <!-- Primeira linha de gráficos -->
    <div class="chart-grid">
        <!-- Gráfico de Status dos Serviços -->
        <div class="chart-container">
            <div class="chart-title">Distribuição por Status</div>
            <div class="chart-wrapper small">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Gráfico Top Responsáveis -->
        <div class="chart-container">
            <div class="chart-title">Top 10 Responsáveis por Quantidade</div>
            <div class="chart-wrapper small">
                <canvas id="responsaveisChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Segunda linha - Gráfico de evolução mensal -->
    <div class="chart-container">
        <div class="chart-title">Evolução Mensal de Serviços (Últimos 12 Meses)</div>
        <div class="chart-wrapper">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <!-- Terceira linha - Análise financeira -->
    <div class="chart-container">
        <div class="chart-title">Análise Financeira Mensal</div>
        <div class="chart-wrapper">
            <canvas id="financialChart"></canvas>
        </div>
    </div>

    <!-- Quarta linha - Status por período -->
    <div class="chart-container">
        <div class="chart-title">Evolução dos Status por Mês (Últimos 6 Meses)</div>
        <div class="chart-wrapper large">
            <canvas id="statusMonthlyChart"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .analytics-container {
        padding: 20px;
    }
    
    .kpi-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 20px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .kpi-card:hover {
        transform: translateY(-5px);
    }
    
    .kpi-value {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .kpi-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        border: 1px solid #f0f0f0;
    }
    
    .chart-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .chart-wrapper {
        position: relative;
        height: 400px;
        margin: 20px 0;
    }
    
    .chart-wrapper.small {
        height: 300px;
    }
    
    .chart-wrapper.large {
        height: 500px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .chart-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    @media (max-width: 768px) {
        .chart-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .gradient-success {
        background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
    }
    
    .gradient-warning {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #333 !important;
    }
    
    .gradient-danger {
        background: linear-gradient(135deg, #ff8a80 0%, #ff5722 100%);
    }
    
    .gradient-info {
        background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    }
    
    .analytics-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .analytics-header h1 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .analytics-header p {
        color: #7f8c8d;
        font-size: 1.1rem;
    }
</style>

<script>
// Configurações globais do Chart.js
Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.plugins.legend.position = 'top';
Chart.defaults.plugins.legend.labels.usePointStyle = true;

// Dados PHP para JavaScript
const statusData = <?php echo json_encode($status_data ?? []); ?>;
const monthlyData = <?php echo json_encode($monthly_data ?? []); ?>;
const responsaveisData = <?php echo json_encode($responsaveis_data ?? []); ?>;
const financialData = <?php echo json_encode($financial_data ?? []); ?>;
const statusMonthlyData = <?php echo json_encode($status_monthly ?? []); ?>;

// Cores para os gráficos
const colors = {
    primary: '#667eea',
    success: '#56ab2f',
    warning: '#fcb69f',
    danger: '#ff5722',
    info: '#74b9ff',
    secondary: '#6c757d'
};

const statusColors = {
    'Em Andamento': '#667eea',
    'Concluído': '#56ab2f', 
    'Pendente': '#fcb69f',
    'Cancelado': '#ff5722'
};

// Verificar se há dados antes de criar os gráficos
if (statusData && statusData.length > 0) {
    // 1. Gráfico de Status (Pizza)
    const statusChart = new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statusData.map(item => item.status),
            datasets: [{
                data: statusData.map(item => item.total),
                backgroundColor: statusData.map(item => statusColors[item.status] || colors.secondary),
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = statusData.reduce((sum, item) => sum + parseInt(item.total), 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

if (responsaveisData && responsaveisData.length > 0) {
    // 2. Gráfico de Responsáveis (Barras horizontais)
    const responsaveisChart = new Chart(document.getElementById('responsaveisChart'), {
        type: 'bar',
        data: {
            labels: responsaveisData.map(item => item.responsavel.length > 20 ? 
                item.responsavel.substring(0, 20) + '...' : item.responsavel),
            datasets: [{
                label: 'Quantidade de Serviços',
                data: responsaveisData.map(item => item.total_servicos),
                backgroundColor: colors.primary,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return responsaveisData[context[0].dataIndex].responsavel;
                        },
                        label: function(context) {
                            const item = responsaveisData[context.dataIndex];
                            return [
                                `Serviços: ${item.total_servicos}`,
                                `Valor Total: R$ ${parseFloat(item.valor_total || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}`
                            ];
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: '#f0f0f0'
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

if (monthlyData && monthlyData.length > 0) {
    // 3. Gráfico de Evolução Mensal (Linha)
    const monthlyChart = new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: monthlyData.map(item => {
                const [year, month] = item.mes.split('-');
                return new Date(year, month - 1).toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Quantidade de Serviços',
                data: monthlyData.map(item => item.total_servicos),
                borderColor: colors.primary,
                backgroundColor: colors.primary + '20',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const item = monthlyData[context.dataIndex];
                            return [
                                `Serviços: ${item.total_servicos}`,
                                `Valor: R$ ${parseFloat(item.valor_total || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}`
                            ];
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f0f0f0'
                    }
                },
                x: {
                    grid: {
                        color: '#f0f0f0'
                    }
                }
            }
        }
    });
}

if (financialData && financialData.length > 0) {
    // 4. Gráfico Financeiro (Barras)
    const financialChart = new Chart(document.getElementById('financialChart'), {
        type: 'bar',
        data: {
            labels: financialData.map(item => {
                const [year, month] = item.mes.split('-');
                return new Date(year, month - 1).toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' });
            }),
            datasets: [
                {
                    label: 'Valor Total',
                    data: financialData.map(item => parseFloat(item.valor_total || 0)),
                    backgroundColor: colors.info,
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Valor Antecipado',
                    data: financialData.map(item => parseFloat(item.valor_antecipado || 0)),
                    backgroundColor: colors.success,
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Valor Faltante',
                    data: financialData.map(item => parseFloat(item.valor_faltante || 0)),
                    backgroundColor: colors.warning,
                    borderRadius: 4,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: R$ ${context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    },
                    grid: {
                        color: '#f0f0f0'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

if (statusMonthlyData && statusMonthlyData.length > 0) {
    // 5. Gráfico de Status por Mês (Área Empilhada)
    // Preparar dados para o gráfico de status mensal
    const months = [...new Set(statusMonthlyData.map(item => item.mes))].sort();
    const statuses = ['Em Andamento', 'Concluído', 'Pendente', 'Cancelado'];

    const statusMonthlyDatasets = statuses.map(status => {
        const data = months.map(month => {
            const found = statusMonthlyData.find(item => item.mes === month && item.status === status);
            return found ? parseInt(found.total) : 0;
        });
        
        return {
            label: status,
            data: data,
            backgroundColor: (statusColors[status] || colors.secondary) + '80',
            borderColor: statusColors[status] || colors.secondary,
            borderWidth: 2,
            fill: true
        };
    });

    const statusMonthlyChart = new Chart(document.getElementById('statusMonthlyChart'), {
        type: 'line',
        data: {
            labels: months.map(month => {
                const [year, m] = month.split('-');
                return new Date(year, m - 1).toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' });
            }),
            datasets: statusMonthlyDatasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    stacked: true,
                    grid: {
                        color: '#f0f0f0'
                    }
                },
                x: {
                    grid: {
                        color: '#f0f0f0'
                    }
                }
            }
        }
    });
}

// Adicionar interatividade e animações
document.addEventListener('DOMContentLoaded', function() {
    // Animar os KPIs
    const kpiValues = document.querySelectorAll('.kpi-value');
    kpiValues.forEach(kpi => {
        const finalValue = kpi.textContent;
        kpi.textContent = '0';
        
        const isPercentage = finalValue.includes('%');
        const isCurrency = finalValue.includes('R$');
        let targetValue = parseFloat(finalValue.replace(/[^\d.,]/g, '').replace(',', '.'));
        
        if (isNaN(targetValue)) return;
        
        let currentValue = 0;
        const increment = targetValue / 100;
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= targetValue) {
                currentValue = targetValue;
                clearInterval(timer);
            }
            
            let displayValue = Math.floor(currentValue);
            if (isCurrency) {
                displayValue = 'R$ ' + displayValue.toLocaleString('pt-BR');
            } else if (isPercentage) {
                displayValue = displayValue.toFixed(1) + '%';
            } else {
                displayValue = displayValue.toLocaleString('pt-BR');
            }
            
            kpi.textContent = displayValue;
        }, 20);
    });
});
</script>
