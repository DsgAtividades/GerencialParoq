<?php
session_start();
require_once 'config/config.php';

// Verificar autenticação
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
    header('Location: ../../module_login.html?module=cafe');
    exit;
}

if (!isset($_SESSION['module_access']) || $_SESSION['module_access'] !== 'cafe') {
    header('Location: ../../module_login.html?module=cafe');
    exit;
}

if (isset($_SESSION['module_login_time']) && (time() - $_SESSION['module_login_time'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: ../../module_login.html?module=cafe');
    exit;
}

$module_name = '☕ Café Paroquial';
$module_description = 'Sistema de vendas e controle de estoque';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $module_name; ?> - Sistema de Gestão Paroquial</title>
    <link rel="stylesheet" href="css/cafe.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="cafe-container">
        <!-- Header -->
        <header class="cafe-header">
            <div class="cafe-header-content">
                <div class="cafe-module-info">
                    <h1><?php echo $module_name; ?></h1>
                    <p><?php echo $module_description; ?></p>
                </div>
                <div class="cafe-user-info">
                    <span>👤 <?php echo htmlspecialchars($_SESSION['module_username'] ?? 'Usuário'); ?></span>
                    <a href="../../auth/module_logout.php" class="cafe-btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </div>
            </div>
        </header>

        <!-- Navegação -->
        <nav class="cafe-nav">
            <ul>
                <li><a href="#" class="cafe-nav-link active" data-section="dashboard">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a></li>
                <li><a href="#" class="cafe-nav-link" data-section="pdv">
                    <i class="fas fa-cash-register"></i> PDV - Vendas
                </a></li>
                <li><a href="#" class="cafe-nav-link" data-section="produtos">
                    <i class="fas fa-coffee"></i> Produtos
                </a></li>
                <li><a href="#" class="cafe-nav-link" data-section="estoque">
                    <i class="fas fa-boxes"></i> Estoque
                </a></li>
                <li><a href="#" class="cafe-nav-link" data-section="vendas">
                    <i class="fas fa-receipt"></i> Histórico de Vendas
                </a></li>
            </ul>
        </nav>

        <!-- Conteúdo Principal -->
        <main class="cafe-main">
            <!-- Dashboard -->
            <section id="dashboard" class="cafe-section active">
                <div class="cafe-card">
                    <h2>Dashboard do Café</h2>
                    <div class="cafe-stats-grid">
                        <div class="cafe-stat-card">
                            <div class="cafe-stat-icon">☕</div>
                            <div class="cafe-stat-number" id="stat-total-produtos">0</div>
                            <div class="cafe-stat-label">Produtos Cadastrados</div>
                        </div>
                        <div class="cafe-stat-card">
                            <div class="cafe-stat-icon">💰</div>
                            <div class="cafe-stat-number" id="stat-vendas-hoje">R$ 0,00</div>
                            <div class="cafe-stat-label">Vendas Hoje</div>
                        </div>
                        <div class="cafe-stat-card">
                            <div class="cafe-stat-icon">📦</div>
                            <div class="cafe-stat-number" id="stat-estoque-baixo">0</div>
                            <div class="cafe-stat-label">Estoque Baixo</div>
                        </div>
                        <div class="cafe-stat-card">
                            <div class="cafe-stat-icon">🛒</div>
                            <div class="cafe-stat-number" id="stat-total-vendas">0</div>
                            <div class="cafe-stat-label">Total de Vendas</div>
                        </div>
                    </div>
                </div>

                <div class="cafe-card">
                    <h2>Vendas Recentes</h2>
                    <div class="cafe-table-container">
                        <table class="cafe-table">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Data/Hora</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Pagamento</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="vendas-recentes-tbody">
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--cafe-creme);">
                                        <i class="fas fa-receipt" style="font-size: 2rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                        Nenhuma venda realizada ainda.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- PDV - Ponto de Venda -->
            <section id="pdv" class="cafe-section">
                <div class="cafe-card">
                    <h2>Ponto de Venda</h2>
                    <div class="cafe-pdv-container">
                        <!-- Produtos -->
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h3 style="color: var(--cafe-amarelo);">Produtos Disponíveis</h3>
                                <input type="text" id="buscar-produto" placeholder="🔍 Buscar produto..." 
                                       style="padding: 10px 15px; background: var(--cafe-preto-claro); border: 2px solid var(--cafe-amarelo); 
                                              border-radius: 25px; color: var(--cafe-branco); width: 300px;">
                            </div>
                            <div class="cafe-produtos-grid" id="produtos-grid">
                                <!-- Produtos serão carregados aqui via AJAX -->
                            </div>
                        </div>

                        <!-- Carrinho -->
                        <div class="cafe-carrinho">
                            <div class="cafe-carrinho-header">
                                <h3><i class="fas fa-shopping-cart"></i> Carrinho</h3>
                            </div>
                            <div class="cafe-carrinho-itens" id="carrinho-itens">
                                <div style="text-align: center; padding: 40px; color: var(--cafe-creme); opacity: 0.7;">
                                    <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                                    Carrinho vazio
                                </div>
                            </div>
                            <div class="cafe-carrinho-total">
                                <div class="cafe-carrinho-total-valor" id="carrinho-total">R$ 0,00</div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <button class="cafe-btn cafe-btn-secondary" onclick="limparCarrinho()">
                                        <i class="fas fa-trash"></i> Limpar
                                    </button>
                                    <button class="cafe-btn cafe-btn-primary" onclick="finalizarVenda()">
                                        <i class="fas fa-check"></i> Finalizar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Produtos -->
            <section id="produtos" class="cafe-section">
                <div class="cafe-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <h2>Gestão de Produtos</h2>
                        <button class="cafe-btn cafe-btn-primary" onclick="abrirModalProduto()">
                            <i class="fas fa-plus"></i> Novo Produto
                        </button>
                    </div>
                    <div class="cafe-table-container">
                        <table class="cafe-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th>Preço</th>
                                    <th>Estoque</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="produtos-tbody">
                                <!-- Produtos serão carregados aqui -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Estoque -->
            <section id="estoque" class="cafe-section">
                <div class="cafe-card">
                    <h2>Controle de Estoque</h2>
                    <div class="cafe-table-container">
                        <table class="cafe-table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Estoque Atual</th>
                                    <th>Estoque Mínimo</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="estoque-tbody">
                                <!-- Estoque será carregado aqui -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Histórico de Vendas -->
            <section id="vendas" class="cafe-section">
                <div class="cafe-card">
                    <h2>Histórico de Vendas</h2>
                    <div style="margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap;">
                        <input type="date" id="filtro-data-inicio" class="cafe-form-group" style="padding: 10px; background: var(--cafe-preto-claro); border: 2px solid var(--cafe-amarelo); border-radius: 10px; color: var(--cafe-branco);">
                        <input type="date" id="filtro-data-fim" class="cafe-form-group" style="padding: 10px; background: var(--cafe-preto-claro); border: 2px solid var(--cafe-amarelo); border-radius: 10px; color: var(--cafe-branco);">
                        <button class="cafe-btn cafe-btn-primary" onclick="filtrarVendas()">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                    <div class="cafe-table-container">
                        <table class="cafe-table">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Data/Hora</th>
                                    <th>Cliente</th>
                                    <th>Itens</th>
                                    <th>Total</th>
                                    <th>Pagamento</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="vendas-tbody">
                                <!-- Vendas serão carregadas aqui -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal Produto -->
    <div id="modal-produto" class="cafe-modal">
        <div class="cafe-modal-content">
            <div class="cafe-modal-header">
                <h3 id="modal-produto-titulo">Novo Produto</h3>
                <button class="cafe-modal-close" onclick="fecharModalProduto()">&times;</button>
            </div>
            <form id="form-produto">
                <input type="hidden" id="produto-id">
                <div class="cafe-form-group">
                    <label>Código *</label>
                    <input type="text" id="produto-codigo" required>
                </div>
                <div class="cafe-form-group">
                    <label>Nome *</label>
                    <input type="text" id="produto-nome" required>
                </div>
                <div class="cafe-form-row">
                    <div class="cafe-form-group">
                        <label>Categoria</label>
                        <input type="text" id="produto-categoria" placeholder="Ex: Bebidas, Alimentos">
                    </div>
                    <div class="cafe-form-group">
                        <label>Unidade de Medida</label>
                        <select id="produto-unidade" required>
                            <option value="unidade">Unidade</option>
                            <option value="kg">Quilograma</option>
                            <option value="litro">Litro</option>
                            <option value="pacote">Pacote</option>
                        </select>
                    </div>
                </div>
                <div class="cafe-form-row">
                    <div class="cafe-form-group">
                        <label>Preço de Venda *</label>
                        <input type="number" id="produto-preco" step="0.01" min="0" required>
                    </div>
                    <div class="cafe-form-group">
                        <label>Estoque Inicial</label>
                        <input type="number" id="produto-estoque" min="0" value="0">
                    </div>
                </div>
                <div class="cafe-form-row">
                    <div class="cafe-form-group">
                        <label>Estoque Mínimo</label>
                        <input type="number" id="produto-estoque-minimo" min="0" value="0">
                    </div>
                    <div class="cafe-form-group">
                        <label>Status</label>
                        <select id="produto-ativo">
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="cafe-form-group">
                    <label>Descrição</label>
                    <textarea id="produto-descricao" rows="3"></textarea>
                </div>
                <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <button type="submit" class="cafe-btn cafe-btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                    <button type="button" class="cafe-btn cafe-btn-secondary" onclick="fecharModalProduto()" style="flex: 1;">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Finalizar Venda -->
    <div id="modal-venda" class="cafe-modal">
        <div class="cafe-modal-content">
            <div class="cafe-modal-header">
                <h3>Finalizar Venda</h3>
                <button class="cafe-modal-close" onclick="fecharModalVenda()">&times;</button>
            </div>
            <form id="form-venda">
                <div class="cafe-form-group">
                    <label>Cliente (opcional)</label>
                    <input type="text" id="venda-cliente" placeholder="Nome do cliente">
                </div>
                <div class="cafe-form-group">
                    <label>Forma de Pagamento *</label>
                    <select id="venda-pagamento" required>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="pix">PIX</option>
                        <option value="cartao_debito">Cartão Débito</option>
                        <option value="cartao_credito">Cartão Crédito</option>
                    </select>
                </div>
                <div class="cafe-form-group">
                    <label>Desconto (R$)</label>
                    <input type="number" id="venda-desconto" step="0.01" min="0" value="0">
                </div>
                <div style="background: rgba(255, 215, 0, 0.1); padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid var(--cafe-amarelo);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: var(--cafe-creme);">Subtotal:</span>
                        <span style="color: var(--cafe-amarelo); font-weight: 700;" id="venda-subtotal">R$ 0,00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: var(--cafe-creme);">Desconto:</span>
                        <span style="color: var(--cafe-amarelo); font-weight: 700;" id="venda-desconto-display">R$ 0,00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 2px solid var(--cafe-amarelo); padding-top: 10px; margin-top: 10px;">
                        <span style="color: var(--cafe-amarelo); font-weight: 900; font-size: 1.2rem;">Total:</span>
                        <span style="color: var(--cafe-amarelo); font-weight: 900; font-size: 1.5rem;" id="venda-total">R$ 0,00</span>
                    </div>
                </div>
                <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <button type="submit" class="cafe-btn cafe-btn-primary" style="flex: 1;">
                        <i class="fas fa-check"></i> Confirmar Venda
                    </button>
                    <button type="button" class="cafe-btn cafe-btn-secondary" onclick="fecharModalVenda()" style="flex: 1;">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/cafe.js"></script>
</body>
</html>
