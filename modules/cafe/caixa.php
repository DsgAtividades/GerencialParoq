<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('visualizar_caixa');

// Buscar caixa aberto
$stmt = $pdo->query("SELECT * FROM vw_cafe_caixas_resumo WHERE status = 'aberto' ORDER BY data_abertura DESC LIMIT 1");
$caixaAberto = $stmt->fetch(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<link rel="stylesheet" href="css/cafe-theme.css">

<style>
    .caixa-status-card {
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .status-badge {
        font-size: 1.2rem;
        padding: 10px 20px;
        border-radius: 50px;
    }
    
    .valor-destaque {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--cafe-brown);
    }
    
    .kpi-card {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid var(--cafe-brown);
        transition: transform 0.2s;
    }
    
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    
    .kpi-valor {
        font-size: 1.8rem;
        font-weight: 700;
        color: #333;
    }
    
    .kpi-label {
        font-size: 0.9rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .btn-caixa {
        padding: 15px 30px;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s;
    }
    
    .btn-caixa:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    
    .produto-sobra-card {
        transition: all 0.2s;
        border: 2px solid transparent;
    }
    
    .produto-sobra-card:hover {
        border-color: var(--cafe-brown);
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <i class="bi bi-cash-register"></i> Gerenciamento de Caixa
        </h1>
    </div>

    <?php if ($caixaAberto): ?>
        <!-- Caixa Aberto -->
        <div class="card caixa-status-card border-success">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-check-circle-fill"></i> Caixa Aberto
                    </h4>
                    <span class="status-badge bg-white text-success">
                        <i class="bi bi-circle-fill" style="font-size: 0.8rem;"></i> Ativo
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-2"><strong><i class="bi bi-calendar"></i> Abertura:</strong> 
                            <?= date('d/m/Y H:i', strtotime($caixaAberto['data_abertura'])) ?>
                        </p>
                        <p class="mb-2"><strong><i class="bi bi-person"></i> Aberto por:</strong> 
                            <?= htmlspecialchars($caixaAberto['usuario_abertura_nome']) ?>
                        </p>
                        <p class="mb-0"><strong><i class="bi bi-clock"></i> Tempo aberto:</strong> 
                            <?= floor($caixaAberto['horas_abertas']) ?>h <?= ($caixaAberto['horas_abertas'] % 1) * 60 ?>min
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted d-block">Troco Inicial</small>
                                <div class="h4 text-primary mb-0">
                                    R$ <?= number_format($caixaAberto['valor_troco_inicial'], 2, ',', '.') ?>
                                </div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Trocos Dados</small>
                                <div class="h4 text-warning mb-0">
                                    R$ <?= number_format($caixaAberto['total_trocos_dados'], 2, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div>
                            <small class="text-muted d-block">Troco Disponível</small>
                            <div class="valor-destaque text-success">
                                R$ <?= number_format($caixaAberto['troco_atual'], 2, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPIs -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="kpi-card">
                            <div class="kpi-label"><i class="bi bi-cart-fill"></i> Total de Vendas</div>
                            <div class="kpi-valor"><?= $caixaAberto['total_vendas'] ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card border-left-success" style="border-left-color: #28a745 !important;">
                            <div class="kpi-label"><i class="bi bi-cash-stack"></i> Dinheiro</div>
                            <div class="kpi-valor text-success">
                                R$ <?= number_format($caixaAberto['total_dinheiro'], 2, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card border-left-info" style="border-left-color: #17a2b8 !important;">
                            <div class="kpi-label"><i class="bi bi-credit-card"></i> Crédito</div>
                            <div class="kpi-valor text-info">
                                R$ <?= number_format($caixaAberto['total_credito'], 2, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card border-left-warning" style="border-left-color: #ffc107 !important;">
                            <div class="kpi-label"><i class="bi bi-credit-card-2-front"></i> Débito</div>
                            <div class="kpi-valor text-warning">
                                R$ <?= number_format($caixaAberto['total_debito'], 2, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-calculator"></i> Total Geral</h5>
                        <h3 class="mb-0 text-primary">R$ <?= number_format($caixaAberto['total_geral'], 2, ',', '.') ?></h3>
                    </div>
                    <?php if (temPermissao('fechar_caixa')): ?>
                    <button class="btn btn-danger btn-caixa" onclick="abrirModalFecharCaixa()">
                        <i class="bi bi-lock-fill"></i> Fechar Caixa
                    </button>
                    <?php endif; ?>
                </div>

                <?php if ($caixaAberto['observacao_abertura']): ?>
                <div class="alert alert-secondary">
                    <strong><i class="bi bi-chat-left-text"></i> Observação da Abertura:</strong><br>
                    <?= nl2br(htmlspecialchars($caixaAberto['observacao_abertura'])) ?>
                </div>
                <?php endif; ?>

                <!-- Vendas do Caixa -->
                <h5 class="mt-4 mb-3"><i class="bi bi-list-ul"></i> Vendas Realizadas</h5>
                <div id="vendasCaixa"></div>
            </div>
        </div>
    <?php else: ?>
        <!-- Nenhum Caixa Aberto -->
        <div class="card caixa-status-card border-warning">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">
                    <i class="bi bi-exclamation-triangle-fill"></i> Nenhum Caixa Aberto
                </h4>
            </div>
            <div class="card-body text-center py-5">
                <i class="bi bi-cash-register" style="font-size: 4rem; color: #ffc107;"></i>
                <h4 class="mt-3 mb-4">Não há caixa aberto no momento</h4>
                <p class="text-muted mb-4">É necessário abrir um caixa para realizar vendas</p>
                <?php if (temPermissao('abrir_caixa')): ?>
                <button class="btn btn-success btn-caixa" onclick="abrirModalAbrirCaixa()">
                    <i class="bi bi-unlock-fill"></i> Abrir Novo Caixa
                </button>
                <?php endif; ?>
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
                                        <div class="col-md-3">
                                            <p class="mb-1"><small>Total de Vendas</small></p>
                                            <h4 class="mb-0"><span id="detalhesCaixaTotalVendas">0</span></h4>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-1"><small>Dinheiro</small></p>
                                            <h4 class="mb-0">R$ <span id="detalhesCaixaTotalDinheiro">0,00</span></h4>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-1"><small>Crédito</small></p>
                                            <h4 class="mb-0">R$ <span id="detalhesCaixaTotalCredito">0,00</span></h4>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-1"><small>Débito</small></p>
                                            <h4 class="mb-0">R$ <span id="detalhesCaixaTotalDebito">0,00</span></h4>
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

<!-- Modal Abrir Caixa -->
<div class="modal fade" id="modalAbrirCaixa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-unlock-fill"></i> Abrir Novo Caixa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAbrirCaixa">
                    <div class="mb-3">
                        <label for="valorTrocoInicial" class="form-label">
                            <i class="bi bi-cash-stack"></i> Valor de Troco Inicial
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control" id="valorTrocoInicial" required autofocus>
                        </div>
                        <small class="text-muted">Digite o valor em dinheiro disponível para troco</small>
                    </div>
                    <div class="mb-3">
                        <label for="observacaoAbertura" class="form-label">
                            <i class="bi bi-chat-left-text"></i> Observações (opcional)
                        </label>
                        <textarea class="form-control" id="observacaoAbertura" rows="3" 
                                  placeholder="Ex: Fundo de troco do caixa anterior..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmarAbrirCaixa()">
                    <i class="bi bi-check-circle"></i> Abrir Caixa
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Fechar Caixa -->
<div class="modal fade" id="modalFecharCaixa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-lock-fill"></i> Fechar Caixa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill"></i> 
                    <strong>Atenção!</strong> Após fechar o caixa, não será possível reabri-lo.
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-muted">Resumo do Caixa</h6>
                                <p class="mb-1"><strong>Total em Dinheiro:</strong> 
                                    R$ <span id="resumoDinheiro">0,00</span>
                                </p>
                                <p class="mb-1"><strong>Total em Crédito:</strong> 
                                    R$ <span id="resumoCredito">0,00</span>
                                </p>
                                <p class="mb-1"><strong>Total em Débito:</strong> 
                                    R$ <span id="resumoDebito">0,00</span>
                                </p>
                                <hr>
                                <p class="mb-0"><strong>Total Geral:</strong> 
                                    R$ <span id="resumoTotal">0,00</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="mb-3">💰 Controle de Troco</h6>
                                <p class="mb-2"><strong>Troco Inicial:</strong> 
                                    R$ <span id="resumoTrocoInicial">0,00</span>
                                </p>
                                <p class="mb-2"><strong>Trocos Dados:</strong> 
                                    <span class="text-warning">- R$ <span id="resumoTrocosDados">0,00</span></span>
                                </p>
                                <hr class="bg-white">
                                <p class="mb-0"><strong>Troco Final:</strong> 
                                    <span class="h4">R$ <span id="resumoTrocoFinal">0,00</span></span>
                                </p>
                                <small class="d-block mt-2 opacity-75">
                                    <i class="bi bi-info-circle"></i> Calculado automaticamente
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="formFecharCaixa">
                    <!-- Seção de Sobras -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label mb-0">
                                <i class="bi bi-box-seam"></i> Produtos que Sobraram
                            </label>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="abrirModalSobras()">
                                <i class="bi bi-plus-circle"></i> Adicionar Sobras
                            </button>
                        </div>
                        
                        <div id="listaSobras" class="border rounded p-3 bg-light">
                            <div class="text-center text-muted">
                                <i class="bi bi-inbox"></i>
                                <p class="mb-0 small">Nenhuma sobra registrada</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacaoFechamento" class="form-label">
                            <i class="bi bi-chat-left-text"></i> Observações (opcional)
                        </label>
                        <textarea class="form-control" id="observacaoFechamento" rows="3"
                                  placeholder="Ex: Diferenças, ocorrências..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarFecharCaixa()">
                    <i class="bi bi-lock-fill"></i> Confirmar Fechamento
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Sobras - Seleção de Produtos -->
<div class="modal fade" id="modalSobras" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam"></i> Registrar Sobras de Produtos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Selecione os produtos que sobraram ao final do expediente. 
                    Estas quantidades serão descontadas do estoque, mas não geram receita.
                </div>
                
                <!-- Buscar Produto -->
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="buscarProdutoSobra" 
                               placeholder="Buscar produto...">
                    </div>
                </div>
                
                <!-- Lista de Produtos -->
                <div class="row" id="listaProdutosSobras">
                    <div class="col-12 text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <!-- Carrinho de Sobras -->
                <h6 class="mb-3"><i class="bi bi-cart3"></i> Produtos Selecionados</h6>
                <div id="carrinhoSobras" class="border rounded p-3 bg-light mb-3">
                    <div class="text-center text-muted">
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0 small">Nenhum produto selecionado</p>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Total de produtos:</strong> <span id="totalProdutosSobras">0</span><br>
                        <strong>Quantidade total:</strong> <span id="quantidadeTotalSobras">0</span>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Valor total estimado:</small>
                        <h4 class="mb-0 text-danger">R$ <span id="valorTotalSobras">0,00</span></h4>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="confirmarSobras()">
                    <i class="bi bi-check-circle"></i> Confirmar Sobras
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Formatar campos de valor como moeda
document.addEventListener('DOMContentLoaded', function() {
    ['valorTrocoInicial'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0) {
                    value = (parseInt(value) / 100).toFixed(2);
                    value = value.replace('.', ',');
                    e.target.value = value;
                } else {
                    e.target.value = '';
                }
            });
        }
    });
    
    // Carregar vendas se houver caixa aberto
    <?php if ($caixaAberto): ?>
    carregarVendasCaixa();
    // Atualizar a cada 30 segundos
    setInterval(carregarVendasCaixa, 30000);
    <?php endif; ?>
});

function abrirModalAbrirCaixa() {
    document.getElementById('valorTrocoInicial').value = '';
    document.getElementById('observacaoAbertura').value = '';
    new bootstrap.Modal(document.getElementById('modalAbrirCaixa')).show();
}

function confirmarAbrirCaixa() {
    const valorStr = document.getElementById('valorTrocoInicial').value.replace(',', '.');
    const valor = parseFloat(valorStr);
    const observacao = document.getElementById('observacaoAbertura').value;
    
    if (isNaN(valor) || valor < 0) {
        alert('Por favor, informe um valor válido');
        return;
    }
    
    fetch('api/caixa_abrir.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            valor_troco_inicial: valor,
            observacao: observacao
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Caixa aberto com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao abrir caixa');
    });
}

function abrirModalFecharCaixa() {
    <?php if ($caixaAberto): ?>
    document.getElementById('resumoDinheiro').textContent = '<?= number_format($caixaAberto['total_dinheiro'], 2, ',', '.') ?>';
    document.getElementById('resumoCredito').textContent = '<?= number_format($caixaAberto['total_credito'], 2, ',', '.') ?>';
    document.getElementById('resumoDebito').textContent = '<?= number_format($caixaAberto['total_debito'], 2, ',', '.') ?>';
    document.getElementById('resumoTotal').textContent = '<?= number_format($caixaAberto['total_geral'], 2, ',', '.') ?>';
    document.getElementById('resumoTrocoInicial').textContent = '<?= number_format($caixaAberto['valor_troco_inicial'], 2, ',', '.') ?>';
    document.getElementById('resumoTrocosDados').textContent = '<?= number_format($caixaAberto['total_trocos_dados'], 2, ',', '.') ?>';
    document.getElementById('resumoTrocoFinal').textContent = '<?= number_format($caixaAberto['troco_atual'], 2, ',', '.') ?>';
    
    // Carregar sobras registradas
    atualizarListaSobras();
    <?php endif; ?>
    
    document.getElementById('observacaoFechamento').value = '';
    new bootstrap.Modal(document.getElementById('modalFecharCaixa')).show();
}

function confirmarFecharCaixa() {
    const observacao = document.getElementById('observacaoFechamento').value;
    
    if (!confirm('Tem certeza que deseja fechar o caixa? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    fetch('api/caixa_fechar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            observacao: observacao
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Caixa fechado com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao fechar caixa');
    });
}

function carregarVendasCaixa() {
    <?php if ($caixaAberto): ?>
    fetch('api/caixa_vendas.php?caixa_id=<?= $caixaAberto['id'] ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.vendas) {
                mostrarVendas(data.vendas);
            }
        })
        .catch(error => console.error('Erro ao carregar vendas:', error));
    <?php endif; ?>
}

function mostrarVendas(vendas) {
    const container = document.getElementById('vendasCaixa');
    if (vendas.length === 0) {
        container.innerHTML = '<div class="alert alert-info">Nenhuma venda realizada ainda</div>';
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-sm table-hover">';
    html += '<thead><tr><th>ID</th><th>Data/Hora</th><th>Atendente</th><th>Tipo</th><th>Valor</th></tr></thead><tbody>';
    
    vendas.forEach(venda => {
        const tipoBadge = {
            'dinheiro': 'success',
            'credito': 'info',
            'debito': 'warning'
        }[venda.tipo_venda] || 'secondary';
        
        html += `<tr>
            <td>#${venda.id_venda}</td>
            <td>${venda.data_venda_formatada}</td>
            <td>${venda.atendente}</td>
            <td><span class="badge bg-${tipoBadge}">${venda.tipo_venda.toUpperCase()}</span></td>
            <td><strong>R$ ${venda.valor_formatado}</strong></td>
        </tr>`;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

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
        .then(response => response.json())
        .then(data => {
            document.getElementById('detalhesCaixaLoading').style.display = 'none';
            
            if (data.success) {
                mostrarDetalhesCaixa(data.caixa, data.vendas);
                document.getElementById('detalhesCaixaContent').style.display = 'block';
                
                // Carregar sobras
                carregarSobrasDetalhes(id);
            } else {
                document.getElementById('detalhesCaixaError').textContent = data.message;
                document.getElementById('detalhesCaixaError').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('detalhesCaixaLoading').style.display = 'none';
            document.getElementById('detalhesCaixaError').textContent = 'Erro ao carregar detalhes do caixa';
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
        parseFloat(caixa.total_dinheiro).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('detalhesCaixaTotalCredito').textContent = 
        parseFloat(caixa.total_credito).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('detalhesCaixaTotalDebito').textContent = 
        parseFloat(caixa.total_debito).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('detalhesCaixaTotalGeral').textContent = 
        parseFloat(caixa.total_geral).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
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
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nenhuma venda registrada</td></tr>';
        return;
    }
    
    vendas.forEach(venda => {
        const tipoBadge = {
            'dinheiro': 'success',
            'credito': 'info',
            'debito': 'warning'
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

// ==========================================
// SISTEMA DE SOBRAS
// ==========================================

let produtosSobras = [];
let carrinhoSobrasAtual = [];

function abrirModalSobras() {
    // Limpar carrinho
    carrinhoSobrasAtual = [];
    atualizarCarrinhoSobras();
    
    // Carregar produtos
    carregarProdutosSobras();
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalSobras'));
    modal.show();
}

function carregarProdutosSobras() {
    const container = document.getElementById('listaProdutosSobras');
    container.innerHTML = '<div class="col-12 text-center"><div class="spinner-border"></div></div>';
    
    fetch('api/produtos_listar.php')
        .then(response => {
            // Verificar se a resposta é JSON
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                return response.text().then(text => {
                    throw new Error('Resposta não é JSON: ' + text.substring(0, 200));
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                produtosSobras = data.produtos;
                renderizarProdutosSobras(produtosSobras);
                
                // Configurar busca
                const buscaInput = document.getElementById('buscarProdutoSobra');
                if (buscaInput) {
                    buscaInput.addEventListener('input', filtrarProdutosSobras);
                }
            } else {
                container.innerHTML = '<div class="col-12 text-center text-danger">Erro: ' + (data.message || 'Erro ao carregar produtos') + '</div>';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            container.innerHTML = '<div class="col-12 text-center text-danger">Erro ao carregar produtos: ' + error.message + '</div>';
        });
}

function renderizarProdutosSobras(produtos) {
    const container = document.getElementById('listaProdutosSobras');
    
    if (produtos.length === 0) {
        container.innerHTML = '<div class="col-12 text-center text-muted">Nenhum produto encontrado</div>';
        return;
    }
    
    container.innerHTML = produtos.map(produto => `
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card h-100 produto-sobra-card" style="cursor: pointer;" 
                 onclick="selecionarProdutoSobra(${produto.id})">
                <div class="card-body">
                    <h6 class="card-title">${escapeHtml(produto.nome)}</h6>
                    <p class="card-text">
                        <small class="text-muted">Estoque: ${produto.estoque}</small><br>
                        <strong>R$ ${parseFloat(produto.preco).toFixed(2).replace('.', ',')}</strong>
                    </p>
                </div>
            </div>
        </div>
    `).join('');
}

function filtrarProdutosSobras() {
    const termo = document.getElementById('buscarProdutoSobra').value.toLowerCase();
    const produtosFiltrados = produtosSobras.filter(p => 
        p.nome.toLowerCase().includes(termo)
    );
    renderizarProdutosSobras(produtosFiltrados);
}

function selecionarProdutoSobra(produtoId) {
    const produto = produtosSobras.find(p => p.id === produtoId);
    if (!produto) return;
    
    // Verificar se já está no carrinho
    const itemExistente = carrinhoSobrasAtual.find(item => item.produto_id === produtoId);
    
    if (itemExistente) {
        // Incrementar quantidade
        if (itemExistente.quantidade < produto.estoque) {
            itemExistente.quantidade++;
        } else {
            alert('Quantidade não pode ser maior que o estoque disponível!');
        }
    } else {
        // Adicionar novo item
        carrinhoSobrasAtual.push({
            produto_id: produtoId,
            nome: produto.nome,
            preco: parseFloat(produto.preco),
            estoque: produto.estoque,
            quantidade: 1
        });
    }
    
    atualizarCarrinhoSobras();
}

function removerProdutoSobra(produtoId) {
    carrinhoSobrasAtual = carrinhoSobrasAtual.filter(item => item.produto_id !== produtoId);
    atualizarCarrinhoSobras();
}

function alterarQuantidadeSobra(produtoId, novaQuantidade) {
    const item = carrinhoSobrasAtual.find(i => i.produto_id === produtoId);
    if (!item) return;
    
    const quantidade = parseInt(novaQuantidade);
    
    if (quantidade <= 0) {
        removerProdutoSobra(produtoId);
        return;
    }
    
    if (quantidade > item.estoque) {
        alert('Quantidade não pode ser maior que o estoque disponível!');
        return;
    }
    
    item.quantidade = quantidade;
    atualizarCarrinhoSobras();
}

function atualizarCarrinhoSobras() {
    const container = document.getElementById('carrinhoSobras');
    
    if (carrinhoSobrasAtual.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted">
                <i class="bi bi-inbox"></i>
                <p class="mb-0 small">Nenhum produto selecionado</p>
            </div>
        `;
        document.getElementById('totalProdutosSobras').textContent = '0';
        document.getElementById('quantidadeTotalSobras').textContent = '0';
        document.getElementById('valorTotalSobras').textContent = '0,00';
        return;
    }
    
    let totalProdutos = carrinhoSobrasAtual.length;
    let quantidadeTotal = 0;
    let valorTotal = 0;
    
    container.innerHTML = carrinhoSobrasAtual.map(item => {
        const subtotal = item.quantidade * item.preco;
        quantidadeTotal += item.quantidade;
        valorTotal += subtotal;
        
        return `
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                <div class="flex-grow-1">
                    <strong>${escapeHtml(item.nome)}</strong><br>
                    <small class="text-muted">R$ ${item.preco.toFixed(2).replace('.', ',')} cada</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input type="number" class="form-control form-control-sm" 
                           style="width: 80px;" min="1" max="${item.estoque}"
                           value="${item.quantidade}"
                           onchange="alterarQuantidadeSobra(${item.produto_id}, this.value)">
                    <span class="text-nowrap" style="min-width: 80px;">
                        R$ ${subtotal.toFixed(2).replace('.', ',')}
                    </span>
                    <button class="btn btn-sm btn-outline-danger" 
                            onclick="removerProdutoSobra(${item.produto_id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    document.getElementById('totalProdutosSobras').textContent = totalProdutos;
    document.getElementById('quantidadeTotalSobras').textContent = quantidadeTotal;
    document.getElementById('valorTotalSobras').textContent = valorTotal.toFixed(2).replace('.', ',');
}

function confirmarSobras() {
    if (carrinhoSobrasAtual.length === 0) {
        alert('Adicione ao menos um produto!');
        return;
    }
    
    if (!confirm(`Confirmar registro de ${carrinhoSobrasAtual.length} produto(s) como sobra?`)) {
        return;
    }
    
    const caixaId = <?= $caixaAberto ? $caixaAberto['id'] : 'null' ?>;
    
    const dados = {
        caixa_id: caixaId,
        sobras: carrinhoSobrasAtual.map(item => ({
            produto_id: item.produto_id,
            quantidade: item.quantidade
        }))
    };
    
    fetch('api/sobras_adicionar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    })
    .then(response => {
        // Verificar se a resposta é JSON
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            return response.text().then(text => {
                throw new Error('Resposta não é JSON: ' + text.substring(0, 200));
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.sucesso) {
            alert(data.mensagem);
            
            // Atualizar lista de sobras no modal de fechamento
            atualizarListaSobras();
            
            // Fechar modal de seleção
            bootstrap.Modal.getInstance(document.getElementById('modalSobras')).hide();
        } else {
            alert('Erro: ' + data.mensagem);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar sobras: ' + error.message);
    });
}

function atualizarListaSobras() {
    const caixaId = <?= $caixaAberto ? $caixaAberto['id'] : 'null' ?>;
    if (!caixaId) return;
    
    fetch(`api/sobras_listar.php?caixa_id=${caixaId}`)
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                return response.text().then(text => {
                    throw new Error('Resposta não é JSON: ' + text.substring(0, 200));
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.sucesso) {
                renderizarListaSobras(data.sobras, data.resumo);
            } else {
                console.error('Erro ao carregar sobras:', data.mensagem);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar sobras:', error);
            // Não mostrar erro ao usuário, apenas logar
        });
}

function renderizarListaSobras(sobras, resumo) {
    const container = document.getElementById('listaSobras');
    
    if (sobras.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted">
                <i class="bi bi-inbox"></i>
                <p class="mb-0 small">Nenhuma sobra registrada</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = `
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Valor Unit.</th>
                        <th>Valor Total</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    ${sobras.map(sobra => `
                        <tr id="sobra-row-${sobra.id}">
                            <td>${escapeHtml(sobra.produto_nome)}</td>
                            <td>${sobra.quantidade}</td>
                            <td>R$ ${parseFloat(sobra.produto_valor_unitario).toFixed(2).replace('.', ',')}</td>
                            <td class="text-danger">
                                <strong>R$ ${parseFloat(sobra.valor_total_perdido).toFixed(2).replace('.', ',')}</strong>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="event.preventDefault(); event.stopPropagation(); removerSobra(${sobra.id}, '${escapeHtml(sobra.produto_nome)}', event);"
                                        title="Remover sobra">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td>TOTAL</td>
                        <td>${resumo.total_quantidade}</td>
                        <td>-</td>
                        <td class="text-danger">R$ ${parseFloat(resumo.total_valor_perdido).toFixed(2).replace('.', ',')}</td>
                        <td>-</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
}

function removerSobra(sobraId, produtoNome, event) {
    // Prevenir comportamento padrão e propagação do evento
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    if (!confirm(`Deseja realmente remover a sobra do produto "${produtoNome}"?\n\nO estoque será restaurado automaticamente.`)) {
        return false;
    }
    
    fetch('api/sobras_remover.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ sobra_id: sobraId })
    })
    .then(response => {
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            return response.text().then(text => {
                throw new Error('Resposta não é JSON: ' + text.substring(0, 200));
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.sucesso) {
            // Usar toast/notificação ao invés de alert para não fechar o modal
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                <i class="bi bi-check-circle"></i> ${data.mensagem}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            // Remover o alert após 3 segundos
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
            
            // Atualizar lista de sobras no modal de fechamento
            atualizarListaSobras();
        } else {
            alert('Erro: ' + data.mensagem);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao remover sobra: ' + error.message);
    });
    
    return false;
}

function carregarSobrasDetalhes(caixaId) {
    fetch(`api/sobras_listar.php?caixa_id=${caixaId}`)
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                return response.text().then(text => {
                    throw new Error('Resposta não é JSON: ' + text.substring(0, 200));
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.sucesso && data.sobras.length > 0) {
                const container = document.getElementById('detalhesCaixaSobrasContent');
                const containerDiv = document.getElementById('detalhesCaixaSobrasContainer');
                
                container.innerHTML = `
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-warning">
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Valor Unit.</th>
                                    <th>Valor Total Perdido</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.sobras.map(sobra => `
                                    <tr>
                                        <td>${escapeHtml(sobra.produto_nome)}</td>
                                        <td>${sobra.quantidade}</td>
                                        <td>R$ ${parseFloat(sobra.produto_valor_unitario).toFixed(2).replace('.', ',')}</td>
                                        <td class="text-danger">
                                            <strong>R$ ${parseFloat(sobra.valor_total_perdido).toFixed(2).replace('.', ',')}</strong>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr class="fw-bold">
                                    <td>TOTAL</td>
                                    <td>${data.resumo.total_quantidade}</td>
                                    <td>-</td>
                                    <td class="text-danger">
                                        R$ ${parseFloat(data.resumo.total_valor_perdido).toFixed(2).replace('.', ',')}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `;
                
                containerDiv.style.display = 'block';
            } else {
                document.getElementById('detalhesCaixaSobrasContainer').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar sobras:', error);
            document.getElementById('detalhesCaixaSobrasContainer').style.display = 'none';
        });
}
</script>

<?php include 'includes/footer.php'; ?>

