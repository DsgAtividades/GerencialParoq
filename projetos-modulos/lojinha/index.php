<?php
session_start();
require_once 'config/database.php';

// Configurar sessão do módulo automaticamente para teste
if (!isset($_SESSION['module_logged_in'])) {
    $_SESSION['module_logged_in'] = true;
    $_SESSION['module_access'] = 'lojinha';
    $_SESSION['module_user_id'] = 1;
    $_SESSION['module_username'] = 'admin';
    $_SESSION['module_login_time'] = time();
}

$module_name = 'Lojinha';
$module_description = 'Sistema de controle de estoque, vendas e caixa para produtos católicos';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $module_name; ?> - Sistema de Gestão Paroquial</title>
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link rel="stylesheet" href="../../assets/css/module.css">
    <link rel="stylesheet" href="css/lojinha.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="module-container">
        <!-- Header do Módulo -->
        <header class="module-header">
            <div class="header-content">
                <div class="module-info">
                    <h1><?php echo $module_name; ?></h1>
                    <p><?php echo $module_description; ?></p>
                </div>
                <div class="user-info">
                    <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION['module_username'] ?? 'Usuário'); ?>!</span>
                    <a href="../../auth/module_logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Sair do Módulo
                    </a>
                </div>
            </div>
        </header>

        <!-- Navegação do Módulo -->
        <nav class="module-nav">
            <ul>
                <li><a href="#" class="nav-link active" data-section="dashboard">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a></li>
                <li><a href="#" class="nav-link" data-section="produtos">
                    <i class="fas fa-box"></i> Produtos
                </a></li>
                <li><a href="#" class="nav-link" data-section="pdv">
                    <i class="fas fa-cash-register"></i> PDV - Vendas
                </a></li>
                <li><a href="#" class="nav-link" data-section="estoque">
                    <i class="fas fa-warehouse"></i> Estoque
                </a></li>
                <li><a href="#" class="nav-link" data-section="caixa">
                    <i class="fas fa-cash-register"></i> Caixa
                </a></li>
                <li><a href="#" class="nav-link" data-section="relatorios">
                    <i class="fas fa-chart-bar"></i> Relatórios
                </a></li>
            </ul>
        </nav>

        <!-- Conteúdo Principal -->
        <main class="module-main">
            <!-- Dashboard -->
            <section id="dashboard" class="content-section active">
                <div class="section-header">
                    <h2>Dashboard da Lojinha</h2>
                    <p>Visão geral das operações da lojinha</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" id="total-produtos">0</div>
                            <div class="stat-label">Total de Produtos</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" id="vendas-hoje">0</div>
                            <div class="stat-label">Vendas Hoje</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" id="faturamento-hoje">R$ 0,00</div>
                            <div class="stat-label">Faturamento Hoje</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" id="estoque-baixo">0</div>
                            <div class="stat-label">Estoque Baixo</div>
                        </div>
                    </div>
                </div>

                <!-- Vendas Recentes -->
                <div class="content-card">
                    <h3>Vendas Recentes</h3>
                    <div class="table-container">
                        <table class="table-module">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Data</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="vendas-recentes">
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #7f8c8d; padding: 40px;">
                                        <i class="fas fa-shopping-cart" style="font-size: 2rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                        Nenhuma venda realizada ainda.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Produtos -->
            <section id="produtos" class="content-section">
                <div class="section-header">
                    <h2>Gestão de Produtos</h2>
                    <p>Cadastre e gerencie os produtos da lojinha</p>
                </div>
                
                <div class="content-card">
                    <div class="card-content">
                        <div class="form-actions" style="margin-bottom: 30px;">
                            <button class="btn-primary" onclick="abrirModalProduto()">
                                <i class="fas fa-plus"></i> Novo Produto
                            </button>
                            <button class="btn-secondary" onclick="carregarProdutos()">
                                <i class="fas fa-refresh"></i> Atualizar
                            </button>
                        </div>

                        <div class="table-container">
                            <table class="table-module">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nome</th>
                                        <th>Categoria</th>
                                        <th>Preço Venda</th>
                                        <th>Estoque</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tabela-produtos">
                                    <tr>
                                        <td colspan="7" style="text-align: center; color: #7f8c8d; padding: 40px;">
                                            <i class="fas fa-box" style="font-size: 2rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                            Carregando produtos...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- PDV - Vendas -->
            <section id="pdv" class="content-section">
                <div class="section-header">
                    <h2>PDV - Ponto de Venda</h2>
                    <p>Sistema de vendas rápido e intuitivo</p>
                </div>
                
                <div class="content-card">
                    <div class="card-content">
                        <div class="pdv-container">
                            <div class="pdv-left">
                                <h3>Buscar Produtos</h3>
                                <div class="form-group">
                                    <input type="text" id="busca-produto" placeholder="Digite o nome ou código do produto..." 
                                           onkeyup="buscarProdutos(this.value)">
                                </div>
                                
                                <div class="produtos-grid" id="produtos-grid">
                                    <!-- Produtos serão carregados aqui via AJAX -->
                                </div>
                            </div>
                            
                            <div class="pdv-right">
                                <h3>Carrinho de Vendas</h3>
                                <div class="carrinho-container">
                                    <div class="carrinho-itens" id="carrinho-itens">
                                        <div class="empty-cart">
                                            <i class="fas fa-shopping-cart"></i>
                                            <p>Carrinho vazio</p>
                                        </div>
                                    </div>
                                    
                                    <div class="carrinho-total">
                                        <div class="total-line">
                                            <span>Subtotal:</span>
                                            <span id="subtotal">R$ 0,00</span>
                                        </div>
                                        <div class="total-line">
                                            <span>Desconto:</span>
                                            <span id="desconto">R$ 0,00</span>
                                        </div>
                                        <div class="total-line total-final">
                                            <span>Total:</span>
                                            <span id="total">R$ 0,00</span>
                                        </div>
                                    </div>
                                    
                                    <div class="carrinho-actions">
                                        <button class="btn-primary" onclick="finalizarVenda()">
                                            <i class="fas fa-check"></i> Finalizar Venda
                                        </button>
                                        <button class="btn-secondary" onclick="limparCarrinho()">
                                            <i class="fas fa-trash"></i> Limpar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Estoque -->
            <section id="estoque" class="content-section">
                <div class="section-header">
                    <h2>Controle de Estoque</h2>
                    <p>Gerencie o estoque e movimentações</p>
                </div>
                
                <div class="content-card">
                    <div class="card-content">
                        <div class="form-actions" style="margin-bottom: 30px;">
                            <button class="btn-primary" onclick="abrirModalAjusteEstoque()">
                                <i class="fas fa-plus"></i> Ajuste de Estoque
                            </button>
                            <button class="btn-secondary" onclick="carregarMovimentacoes()">
                                <i class="fas fa-refresh"></i> Atualizar
                            </button>
                        </div>

                        <div class="table-container">
                            <table class="table-module">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Produto</th>
                                        <th>Tipo</th>
                                        <th>Quantidade</th>
                                        <th>Motivo</th>
                                        <th>Usuário</th>
                                    </tr>
                                </thead>
                                <tbody id="tabela-movimentacoes">
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #7f8c8d; padding: 40px;">
                                            <i class="fas fa-warehouse" style="font-size: 2rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                            Carregando movimentações...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Caixa -->
            <section id="caixa" class="content-section">
                <div class="section-header">
                    <h2>Controle de Caixa</h2>
                    <p>Gerencie entradas, saídas e fechamento de caixa</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" id="status-caixa">Fechado</div>
                            <div class="stat-label">Status do Caixa</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" id="saldo-atual">R$ 0,00</div>
                            <div class="stat-label">Saldo Atual</div>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-content">
                        <div class="form-actions" style="margin-bottom: 30px;">
                            <button class="btn-primary" id="btn-abrir-caixa" onclick="abrirCaixa()">
                                <i class="fas fa-unlock"></i> Abrir Caixa
                            </button>
                            <button class="btn-secondary" id="btn-fechar-caixa" onclick="fecharCaixa()" style="display: none;">
                                <i class="fas fa-lock"></i> Fechar Caixa
                            </button>
                            <button class="btn-secondary" onclick="abrirModalMovimentacao()">
                                <i class="fas fa-plus"></i> Nova Movimentação
                            </button>
                        </div>

                        <div class="table-container">
                        <table class="table-module">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    <th>Usuário</th>
                                </tr>
                            </thead>
                                <tbody id="tabela-movimentacoes-caixa">
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #7f8c8d; padding: 40px;">
                                            <i class="fas fa-cash-register" style="font-size: 2rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                            Carregando movimentações...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Relatórios -->
            <section id="relatorios" class="content-section">
                <div class="section-header">
                    <h2>Relatórios</h2>
                    <p>Relatórios de vendas, estoque e financeiro</p>
                </div>
                
                <div class="content-card">
                    <div class="card-content">
                        <div class="relatorios-grid">
                            <div class="relatorio-card" onclick="gerarRelatorio('vendas')">
                                <div class="relatorio-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h3>Relatório de Vendas</h3>
                                <p>Vendas por período, vendedor e forma de pagamento</p>
                            </div>
                            
                            <div class="relatorio-card" onclick="gerarRelatorio('estoque')">
                                <div class="relatorio-icon">
                                    <i class="fas fa-warehouse"></i>
                                </div>
                                <h3>Relatório de Estoque</h3>
                                <p>Produtos em estoque e movimentações</p>
                            </div>
                            
                            <div class="relatorio-card" onclick="gerarRelatorio('financeiro')">
                                <div class="relatorio-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <h3>Relatório Financeiro</h3>
                                <p>Faturamento, lucro e movimentações de caixa</p>
                            </div>
                            
                            <div class="relatorio-card" onclick="gerarRelatorio('produtos')">
                                <div class="relatorio-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <h3>Produtos Mais Vendidos</h3>
                                <p>Ranking dos produtos mais vendidos</p>
                            </div>
                        </div>
                    </div>
            </div>
            </section>
        </main>
    </div>

    <!-- Modal de Ajuste de Estoque -->
    <div id="modal-ajuste-estoque" class="modal-produto" style="display: none;">
        <div class="modal-produto-content">
            <div class="modal-produto-header">
                <h3><i class="fas fa-warehouse"></i> Ajuste de Estoque</h3>
                <button class="modal-produto-close" onclick="fecharModalAjusteEstoque()">&times;</button>
            </div>
            <form id="form-ajuste-estoque" onsubmit="salvarAjusteEstoque(event)">
                <div class="modal-produto-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="produto_ajuste">Produto *</label>
                            <select id="produto_ajuste" name="produto_id" required>
                                <option value="">Carregando produtos...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tipo_ajuste">Tipo de Ajuste *</label>
                            <select id="tipo_ajuste" name="tipo" required>
                                <option value="">Selecione o tipo</option>
                                <option value="entrada">Entrada (+)</option>
                                <option value="saida">Saída (-)</option>
                                <option value="ajuste">Ajuste Manual</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="quantidade_ajuste">Quantidade *</label>
                            <input type="number" id="quantidade_ajuste" name="quantidade" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Estoque Atual</label>
                            <div class="estoque-display">
                                <span id="estoque-atual-display">-</span>
                                <small>unidades</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="motivo_ajuste">Motivo do Ajuste *</label>
                            <textarea id="motivo_ajuste" name="motivo" rows="3" required placeholder="Explique o motivo do ajuste (ex: Recebimento de mercadoria, Produto danificado, Contagem física, etc.)"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-produto-footer">
                    <button type="button" class="btn-secondary" onclick="fecharModalAjusteEstoque()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Salvar Ajuste
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modais serão incluídos aqui -->
    <div id="modals-container"></div>

    <script src="../../assets/js/paginas/modulo.js"></script>
    <script src="js/lojinha.js?v=<?php echo time(); ?>"></script>
</body>
</html>