<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('gerenciar_dashboard');

// Filtros de data
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d');
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');

// Buscar histórico de caixas fechados no período
$stmt = $pdo->prepare("
    SELECT * FROM vw_cafe_caixas_resumo 
    WHERE status = 'fechado' 
        AND DATE(data_fechamento) BETWEEN ? AND ?
    ORDER BY data_fechamento DESC
");
$stmt->execute([$data_inicio, $data_fim]);
$caixasFechados = $stmt->fetchAll(PDO::FETCH_ASSOC);


include 'includes/header.php';
?>

<style>
    /* Responsividade da tabela de histórico */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    #tabelaHistoricoCaixas {
        min-width: 700px;
    }
    
    #tabelaHistoricoCaixas th,
    #tabelaHistoricoCaixas td {
        vertical-align: middle;
    }
    
    /* Colunas de data podem quebrar linha em mobile */
    #tabelaHistoricoCaixas td:nth-child(2),
    #tabelaHistoricoCaixas td:nth-child(3) {
        white-space: normal;
        min-width: 100px;
    }
    
    /* Colunas de valores não quebram */
    #tabelaHistoricoCaixas th:last-child,
    #tabelaHistoricoCaixas td:last-child {
        position: sticky;
        right: 0;
        background-color: white;
        z-index: 10;
        box-shadow: -2px 0 4px rgba(0,0,0,0.1);
        white-space: nowrap;
    }
    
    #tabelaHistoricoCaixas tbody tr:hover td:last-child {
        background-color: #f8f9fa;
    }
    
    /* Ajustes para telas pequenas */
    @media (max-width: 768px) {
        .card-header .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
        }
        
        .card-header .d-flex .d-flex {
            width: 100%;
            justify-content: flex-start;
        }
        
        #tabelaHistoricoCaixas {
            min-width: 500px;
        }
        
        /* Ajustar tamanho das colunas em mobile */
        #tabelaHistoricoCaixas th:nth-child(1),
        #tabelaHistoricoCaixas td:nth-child(1) {
            min-width: 50px;
            width: 50px;
        }
        
        #tabelaHistoricoCaixas th:nth-child(2),
        #tabelaHistoricoCaixas td:nth-child(2),
        #tabelaHistoricoCaixas th:nth-child(3),
        #tabelaHistoricoCaixas td:nth-child(3) {
            min-width: 90px;
            font-size: 0.875rem;
        }
        
        #tabelaHistoricoCaixas th:nth-child(6),
        #tabelaHistoricoCaixas td:nth-child(6) {
            min-width: 100px;
            font-size: 0.9rem;
        }
    }
    
    /* Garantir que a coluna de ações seja sempre visível */
    #tabelaHistoricoCaixas td:last-child .btn {
        white-space: nowrap;
    }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-clock-history"></i> Histórico de Caixas</h1>
        <a href="index.php" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-5">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>">
                </div>
                <div class="col-md-5">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumo de Caixas -->
    <?php if (!empty($caixasFechados)): 
        $totalCaixas = count($caixasFechados);
        $totalVendasCaixas = array_sum(array_column($caixasFechados, 'total_vendas'));
        $totalGeralCaixas = array_sum(array_column($caixasFechados, 'total_geral'));
        $totalDinheiroCaixas = array_sum(array_column($caixasFechados, 'total_dinheiro'));
        $totalCreditoCaixas = array_sum(array_column($caixasFechados, 'total_credito'));
        $totalDebitoCaixas = array_sum(array_column($caixasFechados, 'total_debito'));
        
        // Verificar se a view tem as colunas total_pix e total_cortesia
        $viewTemPix = isset($caixasFechados[0]['total_pix']);
        $viewTemCortesia = isset($caixasFechados[0]['total_cortesia']);
        
        if ($viewTemPix && $viewTemCortesia) {
            // View atualizada: usar valores da view
            $totalPixCaixas = array_sum(array_column($caixasFechados, 'total_pix'));
            $totalCortesiaCaixas = array_sum(array_column($caixasFechados, 'total_cortesia'));
        } else {
            // View não atualizada: calcular diretamente do banco
            $idsCaixas = array_column($caixasFechados, 'id');
            if (!empty($idsCaixas)) {
                $placeholders = str_repeat('?,', count($idsCaixas) - 1) . '?';
                
                if (!$viewTemPix) {
                    $stmt = $pdo->prepare("
                        SELECT SUM(valor_total) as total_pix
                        FROM cafe_vendas 
                        WHERE caixa_id IN ($placeholders) 
                          AND (estornada IS NULL OR estornada = 0)
                          AND LOWER(TRIM(Tipo_venda)) = 'pix'
                    ");
                    $stmt->execute($idsCaixas);
                    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                    $totalPixCaixas = $resultado['total_pix'] ?? 0;
                } else {
                    $totalPixCaixas = array_sum(array_column($caixasFechados, 'total_pix'));
                }
                
                if (!$viewTemCortesia) {
                    $stmt = $pdo->prepare("
                        SELECT SUM(valor_total) as total_cortesia
                        FROM cafe_vendas 
                        WHERE caixa_id IN ($placeholders) 
                          AND (estornada IS NULL OR estornada = 0)
                          AND LOWER(TRIM(Tipo_venda)) = 'cortesia'
                    ");
                    $stmt->execute($idsCaixas);
                    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                    $totalCortesiaCaixas = $resultado['total_cortesia'] ?? 0;
                } else {
                    $totalCortesiaCaixas = array_sum(array_column($caixasFechados, 'total_cortesia'));
                }
            } else {
                $totalPixCaixas = 0;
                $totalCortesiaCaixas = 0;
            }
        }
        $totalTrocoInicialCaixas = array_sum(array_column($caixasFechados, 'valor_troco_inicial'));
        $totalTrocoFinalCaixas = array_sum(array_column($caixasFechados, 'valor_troco_final'));
        
        // Buscar resumo de sobras do período e contar vendas com troco
        $idsCaixas = array_column($caixasFechados, 'id');
        
        // Contar vendas com troco (vendas em dinheiro do período)
        $totalVendasComTroco = 0;
        if (!empty($idsCaixas)) {
            $placeholdersTroco = str_repeat('?,', count($idsCaixas) - 1) . '?';
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_vendas_troco
                FROM cafe_vendas 
                WHERE caixa_id IN ($placeholdersTroco) 
                  AND (estornada IS NULL OR estornada = 0)
                  AND LOWER(TRIM(Tipo_venda)) = 'dinheiro'
            ");
            $stmt->execute($idsCaixas);
            $resultadoTroco = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalVendasComTroco = $resultadoTroco['total_vendas_troco'] ?? 0;
        }
        $totalSobrasProdutos = 0;
        $totalSobrasQuantidade = 0;
        $totalSobrasValorPerdido = 0;
        
        if (!empty($idsCaixas)) {
            $placeholders = str_repeat('?,', count($idsCaixas) - 1) . '?';
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_produtos_sobras,
                    SUM(quantidade) as total_quantidade_sobras,
                    SUM(valor_total_perdido) as total_valor_perdido_sobras
                FROM vw_cafe_caixas_sobras
                WHERE caixa_id IN ($placeholders)
            ");
            $stmt->execute($idsCaixas);
            $resumoSobras = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalSobrasProdutos = $resumoSobras['total_produtos_sobras'] ?? 0;
            $totalSobrasQuantidade = $resumoSobras['total_quantidade_sobras'] ?? 0;
            $totalSobrasValorPerdido = $resumoSobras['total_valor_perdido_sobras'] ?? 0;
        }
    ?>
    <!-- Resumo de Caixas do Período (Com Receita) -->
    <div class="card mt-4 mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-bar-chart-fill"></i> Resumo de Caixas do Período (Com Receita)</h5>
        </div>
        <div class="card-body">
            <!-- Estatísticas Gerais -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Total de Caixas</h6>
                            <h3 class="mb-0"><?= $totalCaixas ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Total de Vendas</h6>
                            <h3 class="mb-0"><?= $totalVendasCaixas ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Média por Caixa</h6>
                            <h3 class="mb-0"><?= $totalCaixas > 0 ? number_format($totalVendasCaixas / $totalCaixas, 1) : '0' ?></h3>
                        </div>
                    </div>
                </div>
            </div>
 
            
            <!-- Formas de Pagamento que Geram Receita -->
            <div class="border-top pt-4 mb-3">
                <h6 class="text-success mb-3"><i class="bi bi-cash-coin"></i> Formas de Pagamento</h6>
                <div class="row">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <p class="text-muted mb-1 small"><i class="bi bi-cash-stack"></i> Dinheiro</p>
                                <h4 class="mb-0 text-success">R$ <?= number_format($totalDinheiroCaixas, 2, ',', '.') ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <p class="text-muted mb-1 small"><i class="bi bi-credit-card"></i> Crédito</p>
                                <h4 class="mb-0 text-info">R$ <?= number_format($totalCreditoCaixas, 2, ',', '.') ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <p class="text-muted mb-1 small"><i class="bi bi-credit-card-2-front"></i> Débito</p>
                                <h4 class="mb-0 text-warning">R$ <?= number_format($totalDebitoCaixas, 2, ',', '.') ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <p class="text-muted mb-1 small"><i class="bi bi-qr-code"></i> Pix</p>
                                <h4 class="mb-0 text-primary">R$ <?= number_format($totalPixCaixas, 2, ',', '.') ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <p class="mb-1"><strong><i class="bi bi-cash-coin"></i> Total de Receita</strong></p>
                                <h3 class="mb-0">R$ <?= number_format($totalDinheiroCaixas + $totalCreditoCaixas + $totalDebitoCaixas + $totalPixCaixas, 2, ',', '.') ?></h3>
                                <small class="text-white-50">Soma de todas as formas de pagamento recebidas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Troco -->
            <div class="border-top pt-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="text-muted mb-1 small"><i class="bi bi-cash-stack"></i> Troco (Inicial → Final)</p>
                                <h5 class="mb-0">R$ <?= number_format($totalTrocoInicialCaixas, 2, ',', '.') ?> → R$ <?= number_format($totalTrocoFinalCaixas, 2, ',', '.') ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="text-muted mb-1 small"><i class="bi bi-receipt"></i> Vendas com Troco</p>
                                <h5 class="mb-0"><?= $totalVendasComTroco ?> venda(s)</h5>
    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Itens que Não Geram Receita -->
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Itens que Não Geram Receita</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card border-danger">
                        <div class="card-body">
                            <p class="text-muted mb-2"><i class="bi bi-gift"></i> <strong>Cortesias</strong></p>
                            <h3 class="mb-0 text-danger">R$ <?= number_format($totalCortesiaCaixas, 2, ',', '.') ?></h3>
                            <small class="text-muted">Vendas sem pagamento</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card border-warning">
                        <div class="card-body">
                            <p class="text-muted mb-2"><i class="bi bi-box-seam"></i> <strong>Sobras Registradas</strong></p>
                            <h3 class="mb-0 text-warning">R$ <?= number_format($totalSobrasValorPerdido, 2, ',', '.') ?></h3>
                            <small class="text-muted"><?= $totalSobrasProdutos ?> produto(s) - <?= number_format($totalSobrasQuantidade, 0, ',', '.') ?> unidade(s)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Histórico de Caixas -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Histórico de Caixas do Período</h5>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-success" onclick="exportarHistoricoExcel()">
                        <i class="bi bi-file-earmark-excel"></i> <span class="d-none d-sm-inline">Excel</span>
        </button>
                    <button class="btn btn-sm btn-danger" onclick="exportarHistoricoPDF()">
                        <i class="bi bi-file-earmark-pdf"></i> <span class="d-none d-sm-inline">PDF</span>
        </button>
    </div>
            </div>
        </div>
        <div class="card-body p-0">
    <div class="table-responsive">
                <table class="table table-hover mb-0" id="tabelaHistoricoCaixas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Abertura</th>
                            <th>Fechamento</th>
                            <th class="d-none d-md-table-cell">Usuário</th>
                            <th class="text-center d-none d-sm-table-cell">Vendas</th>
                            <th class="text-end">Total</th>
                            <th class="d-none d-lg-table-cell text-end">Troco Inicial</th>
                            <th class="d-none d-lg-table-cell text-end">Troco Final</th>
                            <th class="text-center" style="min-width: 80px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                        <?php foreach ($caixasFechados as $caixa): ?>
                        <tr>
                            <td><?= $caixa['id'] ?></td>
                            <td>
                                <div><?= date('d/m/Y', strtotime($caixa['data_abertura'])) ?></div>
                                <small class="text-muted d-md-none"><?= date('H:i', strtotime($caixa['data_abertura'])) ?></small>
                            </td>
                            <td>
                                <div><?= date('d/m/Y', strtotime($caixa['data_fechamento'])) ?></div>
                                <small class="text-muted d-md-none"><?= date('H:i', strtotime($caixa['data_fechamento'])) ?></small>
                            </td>
                            <td class="d-none d-md-table-cell"><?= htmlspecialchars($caixa['usuario_abertura_nome']) ?></td>
                            <td class="text-center d-none d-sm-table-cell"><?= $caixa['total_vendas'] ?></td>
                            <td class="text-end"><strong>R$ <?= number_format($caixa['total_geral'], 2, ',', '.') ?></strong></td>
                            <td class="d-none d-lg-table-cell text-end">R$ <?= number_format($caixa['valor_troco_inicial'], 2, ',', '.') ?></td>
                            <td class="d-none d-lg-table-cell text-end">R$ <?= number_format($caixa['valor_troco_final'], 2, ',', '.') ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" onclick="verDetalhesCaixa(<?= $caixa['id'] ?>)">
                                    <i class="bi bi-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                </button>
                            </td>
                    </tr>
                <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Detalhes do Caixa -->
<div class="modal fade" id="modalDetalhesCaixa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle-fill"></i> Detalhes do Caixa #<span id="detalhesCaixaId">-</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading -->
                <div id="detalhesCaixaLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-3 text-muted">Carregando detalhes do caixa...</p>
                </div>
                
                <!-- Error -->
                <div id="detalhesCaixaError" class="alert alert-danger" style="display: none;"></div>
                
                <!-- Content -->
                <div id="detalhesCaixaContent" style="display: none;">
                    <!-- Informações do Caixa -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-calendar"></i> Informações do Caixa</h6>
                                    <p class="mb-2"><strong>Abertura:</strong> <span id="detalhesCaixaAbertura">-</span></p>
                                    <p class="mb-2"><strong>Fechamento:</strong> <span id="detalhesCaixaFechamento">-</span></p>
                                    <p class="mb-0"><strong>Usuário:</strong> <span id="detalhesCaixaUsuario">-</span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-cash-stack"></i> Controle de Troco</h6>
                                    <p class="mb-2"><strong>Troco Inicial:</strong> R$ <span id="detalhesCaixaTrocoInicial">0,00</span></p>
                                    <p class="mb-2"><strong>Trocos Dados:</strong> R$ <span id="detalhesCaixaTrocosDados">0,00</span></p>
                                    <p class="mb-0"><strong>Troco Final:</strong> R$ <span id="detalhesCaixaTrocoFinal">0,00</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resumo Financeiro -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title mb-3"><i class="bi bi-graph-up"></i> Resumo Financeiro</h6>
                                    <div class="row">
                                        <div class="col-6 col-md-4 col-lg-2 mb-2">
                                            <p class="mb-1"><small>Total de Vendas</small></p>
                                            <h4 class="mb-0"><span id="detalhesCaixaTotalVendas">0</span></h4>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-2 mb-2">
                                            <p class="mb-1"><small>Dinheiro</small></p>
                                            <h4 class="mb-0">R$ <span id="detalhesCaixaTotalDinheiro">0,00</span></h4>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-2 mb-2">
                                            <p class="mb-1"><small>Crédito</small></p>
                                            <h4 class="mb-0">R$ <span id="detalhesCaixaTotalCredito">0,00</span></h4>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-2 mb-2">
                                            <p class="mb-1"><small>Débito</small></p>
                                            <h4 class="mb-0">R$ <span id="detalhesCaixaTotalDebito">0,00</span></h4>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-2 mb-2">
                                            <p class="mb-1"><small>Pix</small></p>
                                            <h4 class="mb-0">R$ <span id="detalhesCaixaTotalPix">0,00</span></h4>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-2 mb-2">
                                            <p class="mb-1"><small>Cortesia</small></p>
                                            <h4 class="mb-0">R$ <span id="detalhesCaixaTotalCortesia">0,00</span></h4>
                                        </div>
                                    </div>
                                    <hr class="bg-white">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p class="mb-0"><strong>Total Geral:</strong> 
                                                <span class="h3">R$ <span id="detalhesCaixaTotalGeral">0,00</span></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Observações -->
                    <div id="detalhesCaixaObsAberturaContainer" class="alert alert-secondary mb-3" style="display: none;">
                        <strong><i class="bi bi-chat-left-text"></i> Observação da Abertura:</strong><br>
                        <span id="detalhesCaixaObsAbertura"></span>
                    </div>
                    <div id="detalhesCaixaObsFechamentoContainer" class="alert alert-secondary mb-3" style="display: none;">
                        <strong><i class="bi bi-chat-left-text"></i> Observação do Fechamento:</strong><br>
                        <span id="detalhesCaixaObsFechamento"></span>
                    </div>
                    
                    <!-- Sobras -->
                    <div id="detalhesCaixaSobrasContainer" style="display: none;">
                        <h6 class="mb-3"><i class="bi bi-box-seam"></i> Sobras Registradas</h6>
                        <div id="detalhesCaixaSobrasContent"></div>
                    </div>
                    
                    <!-- Lista de Vendas -->
                    <h6 class="mb-3 mt-4"><i class="bi bi-list-ul"></i> Vendas Realizadas</h6>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Data/Hora</th>
                                    <th>Atendente</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="detalhesCaixaVendasBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Carregando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Itens da Venda -->
<div class="modal fade" id="modalItensVenda" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-list-ul"></i> Itens da Venda #<span id="itensVendaId">-</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <p class="mb-1"><strong>Data:</strong> <span id="itensVendaData">-</span></p>
                    <p class="mb-1"><strong>Tipo:</strong> <span id="itensVendaTipo">-</span></p>
                    <p class="mb-0"><strong>Total:</strong> <span id="itensVendaTotal">-</span></p>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th class="text-center">Quantidade</th>
                                <th class="text-end">Valor Unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="itensVendaBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted">Carregando...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const inicio = new Date(document.getElementById('data_inicio').value);
    const fim = new Date(document.getElementById('data_fim').value);
    if (fim < inicio) {
        e.preventDefault();
        alert('A data final não pode ser anterior à data inicial.');
    }
});

// Funções para modal de detalhes do caixa
function verDetalhesCaixa(id) {
    // Mostrar loading
    document.getElementById('detalhesCaixaLoading').style.display = 'block';
    document.getElementById('detalhesCaixaContent').style.display = 'none';
    document.getElementById('detalhesCaixaError').style.display = 'none';
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalDetalhesCaixa'));
    modal.show();
    
    // Buscar detalhes
    fetch(`api/caixa_detalhes.php?id=${id}`)
        .then(response => {
            // Verificar se o Content-Type é JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error('Resposta não é JSON. Resposta: ' + text.substring(0, 200));
                });
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('detalhesCaixaLoading').style.display = 'none';
            
            if (data.success) {
                mostrarDetalhesCaixa(data.caixa, data.vendas);
                document.getElementById('detalhesCaixaContent').style.display = 'block';
                
                // Carregar sobras se existirem
                if (data.sobras && data.sobras.length > 0) {
                    mostrarSobrasDetalhes(data.sobras, data.resumo_sobras, data.caixa.status);
                } else {
                    document.getElementById('detalhesCaixaSobrasContainer').style.display = 'none';
                }
            } else {
                document.getElementById('detalhesCaixaError').textContent = data.message || 'Erro desconhecido';
                document.getElementById('detalhesCaixaError').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('detalhesCaixaLoading').style.display = 'none';
            document.getElementById('detalhesCaixaError').textContent = 'Erro ao carregar detalhes do caixa: ' + error.message;
            document.getElementById('detalhesCaixaError').style.display = 'block';
        });
}

function mostrarDetalhesCaixa(caixa, vendas) {
    // Informações do caixa
    document.getElementById('detalhesCaixaId').textContent = caixa.id;
    
    // Usar data formatada do servidor ou formatar localmente
    if (caixa.data_abertura_formatada) {
        document.getElementById('detalhesCaixaAbertura').textContent = caixa.data_abertura_formatada;
    } else {
        const dataAbertura = new Date(caixa.data_abertura);
        document.getElementById('detalhesCaixaAbertura').textContent = 
            dataAbertura.toLocaleString('pt-BR', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'});
    }
    
    // Formatar data de fechamento
    if (caixa.data_fechamento_formatada) {
        document.getElementById('detalhesCaixaFechamento').textContent = caixa.data_fechamento_formatada;
    } else if (caixa.data_fechamento) {
        const dataFechamento = new Date(caixa.data_fechamento);
        document.getElementById('detalhesCaixaFechamento').textContent = 
            dataFechamento.toLocaleString('pt-BR', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'});
    } else {
        document.getElementById('detalhesCaixaFechamento').textContent = 'Não fechado';
    }
    
    document.getElementById('detalhesCaixaUsuario').textContent = caixa.usuario_abertura_nome || 'N/A';
    document.getElementById('detalhesCaixaTrocoInicial').textContent = 
        parseFloat(caixa.valor_troco_inicial).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('detalhesCaixaTrocosDados').textContent = 
        parseFloat(caixa.total_trocos_dados).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('detalhesCaixaTrocoFinal').textContent = 
        caixa.valor_troco_final ? parseFloat(caixa.valor_troco_final).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-';
    
    // Resumo
    document.getElementById('detalhesCaixaTotalVendas').textContent = caixa.total_vendas;
    document.getElementById('detalhesCaixaTotalDinheiro').textContent = 
        parseFloat(caixa.total_dinheiro || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('detalhesCaixaTotalCredito').textContent = 
        parseFloat(caixa.total_credito || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('detalhesCaixaTotalDebito').textContent = 
        parseFloat(caixa.total_debito || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('detalhesCaixaTotalPix').textContent = 
        parseFloat(caixa.total_pix || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('detalhesCaixaTotalCortesia').textContent = 
        parseFloat(caixa.total_cortesia || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('detalhesCaixaTotalGeral').textContent = 
        parseFloat(caixa.total_geral || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Observações
    if (caixa.observacao_abertura) {
        document.getElementById('detalhesCaixaObsAbertura').textContent = caixa.observacao_abertura;
        document.getElementById('detalhesCaixaObsAberturaContainer').style.display = 'block';
    } else {
        document.getElementById('detalhesCaixaObsAberturaContainer').style.display = 'none';
    }
    
    if (caixa.observacao_fechamento) {
        document.getElementById('detalhesCaixaObsFechamento').textContent = caixa.observacao_fechamento;
        document.getElementById('detalhesCaixaObsFechamentoContainer').style.display = 'block';
    } else {
        document.getElementById('detalhesCaixaObsFechamentoContainer').style.display = 'none';
    }
    
    // Lista de vendas
    const tbody = document.getElementById('detalhesCaixaVendasBody');
    tbody.innerHTML = '';
    
    if (vendas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Nenhuma venda registrada</td></tr>';
        return;
    }
    
    vendas.forEach(venda => {
        const tipoBadge = {
            'dinheiro': 'success',
            'credito': 'info',
            'debito': 'warning',
            'pix': 'primary',
            'cortesia': 'danger'
        }[venda.tipo_venda] || 'secondary';
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>#${venda.id_venda}</td>
            <td>${venda.data_venda_formatada}</td>
            <td>${venda.atendente || 'N/A'}</td>
            <td><span class="badge bg-${tipoBadge}">${venda.tipo_venda.toUpperCase()}</span></td>
            <td><strong>R$ ${venda.valor_formatado}</strong></td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="verItensVenda(${venda.id_venda}, '${venda.data_venda_formatada.replace(/'/g, "\\'")}', '${venda.tipo_venda}', '${venda.valor_formatado}')">
                    <i class="bi bi-list-ul"></i> Itens
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function verItensVenda(idVenda, dataVenda, tipoVenda, valorTotal) {
    // Mostrar loading
    document.getElementById('itensVendaBody').innerHTML = '<tr><td colspan="4" class="text-center text-muted">Carregando...</td></tr>';
    
    // Abrir modal primeiro
    const modal = new bootstrap.Modal(document.getElementById('modalItensVenda'));
    modal.show();
    
    // Preencher informações básicas
    document.getElementById('itensVendaId').textContent = idVenda;
    document.getElementById('itensVendaData').textContent = dataVenda;
    document.getElementById('itensVendaTipo').textContent = tipoVenda.toUpperCase();
    document.getElementById('itensVendaTotal').textContent = `R$ ${valorTotal}`;
    
    // Buscar itens da venda
    fetch(`api/venda_itens.php?id_venda=${idVenda}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarItensVenda(data.itens);
            } else {
                document.getElementById('itensVendaBody').innerHTML = 
                    '<tr><td colspan="4" class="text-center text-danger">Erro ao carregar itens</td></tr>';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('itensVendaBody').innerHTML = 
                '<tr><td colspan="4" class="text-center text-danger">Erro ao carregar itens</td></tr>';
        });
}

function mostrarItensVenda(itens) {
    const tbody = document.getElementById('itensVendaBody');
    tbody.innerHTML = '';
    
    if (itens.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Nenhum item encontrado</td></tr>';
        return;
    }
    
    itens.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${escapeHtml(item.nome_produto)}</td>
            <td class="text-center">${item.quantidade}</td>
            <td class="text-end">R$ ${item.valor_unitario_formatado}</td>
            <td class="text-end"><strong>R$ ${item.subtotal_formatado}</strong></td>
        `;
        tbody.appendChild(row);
    });
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
}

function mostrarSobrasDetalhes(sobras, resumo) {
    const container = document.getElementById('detalhesCaixaSobrasContent');
    const containerDiv = document.getElementById('detalhesCaixaSobrasContainer');
    
    if (!sobras || sobras.length === 0) {
        containerDiv.style.display = 'none';
        return;
    }
    
    container.innerHTML = `
        <div class="alert alert-warning mb-3">
            <div class="row">
                <div class="col-md-4">
                    <strong>Total de Produtos:</strong> ${resumo.total_produtos}
                </div>
                <div class="col-md-4">
                    <strong>Quantidade Total:</strong> ${resumo.total_quantidade}
                </div>
                <div class="col-md-4">
                    <strong>Valor Perdido:</strong> <span class="text-danger">R$ ${parseFloat(resumo.total_valor_perdido).toFixed(2).replace('.', ',')}</span>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-warning">
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Valor Unit.</th>
                        <th>Valor Total Perdido</th>
                        <th>Data Registro</th>
                    </tr>
                </thead>
                <tbody>
                    ${sobras.map(sobra => `
                        <tr>
                            <td>${escapeHtml(sobra.produto_nome)}</td>
                            <td>${sobra.quantidade}</td>
                            <td>R$ ${parseFloat(sobra.produto_valor_unitario).toFixed(2).replace('.', ',')}</td>
                            <td class="text-danger">
                                <strong>R$ ${parseFloat(sobra.valor_total_perdido).toFixed(2).replace('.', ',')}</strong>
                            </td>
                            <td>${sobra.data_registro_formatada || '-'}</td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot class="table-secondary">
                    <tr class="fw-bold">
                        <td>TOTAL</td>
                        <td>${resumo.total_quantidade}</td>
                        <td>-</td>
                        <td class="text-danger">
                            R$ ${parseFloat(resumo.total_valor_perdido).toFixed(2).replace('.', ',')}
                        </td>
                        <td>-</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
    
    containerDiv.style.display = 'block';
}

// Funções de exportação do histórico de caixas
function exportarHistoricoExcel() {
    const table = document.getElementById('tabelaHistoricoCaixas');
    
    // Criar uma cópia da tabela sem a coluna de ações
    const clonedTable = table.cloneNode(true);
    const rows = clonedTable.querySelectorAll('tr');
    
    rows.forEach(row => {
        // Remover a última célula (coluna Ações) de cada linha
        const lastCell = row.querySelector('th:last-child, td:last-child');
        if (lastCell) {
            lastCell.remove();
        }
    });
    
    const ws = XLSX.utils.table_to_sheet(clonedTable);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Histórico Caixas');
    XLSX.writeFile(wb, 'historico_caixas.xlsx');
    
    // Remover a tabela clonada do DOM (se foi adicionada)
    if (clonedTable.parentNode) {
        clonedTable.parentNode.removeChild(clonedTable);
    }
}

function exportarHistoricoPDF() {
    const doc = new window.jspdf.jsPDF('l', 'pt', 'a4');
    const dataInicio = document.getElementById('data_inicio').value;
    const dataFim = document.getElementById('data_fim').value;
    const usuario = "<?= isset($_SESSION['usuario_nome']) ? addslashes($_SESSION['usuario_nome']) : 'Usuário' ?>";
    const agora = new Date();
    const dataExport = agora.toLocaleDateString('pt-BR');
    const horaExport = agora.toLocaleTimeString('pt-BR');
    
    // Criar uma cópia da tabela sem a coluna de ações
    const table = document.getElementById('tabelaHistoricoCaixas');
    const clonedTable = table.cloneNode(true);
    const rows = clonedTable.querySelectorAll('tr');
    
    rows.forEach(row => {
        // Remover a última célula (coluna Ações) de cada linha
        const lastCell = row.querySelector('th:last-child, td:last-child');
        if (lastCell) {
            lastCell.remove();
        }
    });
    
    // Adicionar a tabela clonada temporariamente ao DOM para o autoTable funcionar
    clonedTable.style.display = 'none';
    document.body.appendChild(clonedTable);
    
    let y = 30;
    doc.setFontSize(16);
    doc.text('Histórico de Caixas', 40, y);
    doc.setFontSize(10);
    y += 18;
    doc.text('Período: ' + dataInicio + ' até ' + dataFim, 40, y);
    y += 14;
    doc.text('Usuário: ' + usuario, 40, y);
    y += 14;
    doc.text('Data da exportação: ' + dataExport + '   Hora: ' + horaExport, 40, y);
    y += 10;
    
    doc.autoTable({
        html: clonedTable,
        startY: y + 10,
        styles: { fontSize: 8 },
        headStyles: { fillColor: [108, 117, 125] },
        theme: 'grid'
    });
    
    // Remover a tabela clonada do DOM
    document.body.removeChild(clonedTable);
    
    doc.save('historico_caixas.pdf');
}
</script>
<?php include 'includes/footer.php'; ?> 