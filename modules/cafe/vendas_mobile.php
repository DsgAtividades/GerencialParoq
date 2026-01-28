<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('vendas_mobile');

// Buscar produtos disponíveis agrupados por categoria
// Todos os usuários com permissão vendas_mobile podem ver todos os produtos

$stmt = $pdo->prepare("SELECT p.id, p.nome_produto, p.preco, p.estoque, p.bloqueado,
                       c.id as id_categoria, c.nome as nome_categoria, c.icone
                FROM cafe_produtos p
                LEFT JOIN cafe_categorias c ON p.categoria_id = c.id
                WHERE p.estoque > 0 AND p.bloqueado = 0 
                ORDER BY c.nome, p.nome_produto");
$stmt->execute();

    
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar produtos por categoria
$categorias = [];
foreach ($produtos as $produto) {
    $idCategoria = $produto['id_categoria'] ?? 0;
    $nomeCategoria = $produto['nome_categoria'] ?? 'Sem Categoria';
    
    if (!isset($categorias[$idCategoria])) {
        $categorias[$idCategoria] = [
            'nome' => $nomeCategoria,
            'icone' => $produto['icone'],
            'produtos' => []
        ];
    }
    $categorias[$idCategoria]['produtos'][] = $produto;
}

include 'includes/header.php';
?>
<link rel="stylesheet" href="css/vendas_mobile.css">

<!-- Layout Moderno e Intuitivo -->
<style>
    /* ====== BASE ====== */
    body {
        background: linear-gradient(180deg, #f8f5f0 0%, #fff 100%) !important;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        min-height: 100vh;
    }
    
    /* Header da página */
    .h2.mb-0 {
        font-weight: 700;
        color: var(--cafe-dark);
        letter-spacing: -0.5px;
    }
    
    .bottom-bar, #carrinho, .carrinho-overlay { display: none !important; }
    
    /* ====== CATEGORIA ====== */
    .categoria-linha {
        width: 100%;
        margin-bottom: 2rem;
        position: relative;
    }
    
    .categoria-linha > .categoria-header-horizontal {
        margin-left: 12px;
    }
    
    /* Header da categoria - Design moderno com pill/chip */
    .categoria-header-horizontal {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        background: linear-gradient(135deg, var(--cafe-brown) 0%, var(--cafe-brown-dark) 100%);
        color: #fff;
        border-radius: 50px;
        box-shadow: 0 4px 15px rgba(172, 74, 0, 0.25);
        font-size: 0.95rem;
        font-weight: 600;
        position: sticky;
        top: 10px;
        z-index: 10;
        width: fit-content;
        max-width: 100%;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }
    
    .categoria-header-horizontal:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(172, 74, 0, 0.35);
    }
    
    .categoria-header-horizontal i {
        font-size: 1.2rem;
        flex-shrink: 0;
        opacity: 0.9;
    }
    
    .categoria-header-horizontal span:not(.badge) {
        white-space: nowrap;
        flex-shrink: 0;
        letter-spacing: 0.3px;
    }
    
    .categoria-header-horizontal .badge {
        flex-shrink: 0;
        background: rgba(255,255,255,0.25) !important;
        color: #fff !important;
        font-weight: 500;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        backdrop-filter: blur(10px);
    }
    
    /* ====== CONTAINER DE PRODUTOS ====== */
    .produtos-horizontal-container {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        padding: 16px 0 20px 0;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Firefox */
        position: relative;
    }
    
    .produtos-horizontal-container::-webkit-scrollbar {
        display: none; /* Chrome/Safari */
    }
    
    /* Indicador de scroll */
    .categoria-linha::after {
        content: '→';
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.5rem;
        color: var(--cafe-brown);
        opacity: 0.3;
        animation: scrollHint 1.5s ease-in-out infinite;
        pointer-events: none;
    }
    
    @keyframes scrollHint {
        0%, 100% { transform: translateY(-50%) translateX(0); opacity: 0.3; }
        50% { transform: translateY(-50%) translateX(5px); opacity: 0.6; }
    }
    
    .produtos-horizontal-scroll {
        display: flex;
        flex-direction: row;
        gap: 14px;
        padding: 4px 12px 4px 12px;
        width: max-content;
    }
    
    /* ====== CARD DE PRODUTO - Design Moderno ====== */
    .produto-card-horizontal {
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        padding: 16px 14px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-width: 155px;
        max-width: 155px;
        width: 155px;
        min-height: 190px;
        position: relative;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        box-sizing: border-box;
        flex-shrink: 0;
        border: 2px solid transparent;
        overflow: hidden;
    }
    
    .produto-card-horizontal::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--cafe-brown), var(--cafe-brown-light));
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .produto-card-horizontal:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        border-color: rgba(172, 74, 0, 0.15);
    }
    
    .produto-card-horizontal:hover::before {
        opacity: 1;
    }
    
    .produto-card-horizontal:active {
        transform: scale(0.98);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    /* Card com itens selecionados */
    .produto-card-horizontal.tem-quantidade {
        border-color: var(--cafe-brown);
        background: linear-gradient(180deg, #fff 0%, rgba(248, 240, 175, 0.15) 100%);
    }
    
    .produto-card-horizontal.tem-quantidade::before {
        opacity: 1;
    }
    
    .produto-card-horizontal .quantidade-controls, 
    .produto-card-horizontal .quantidade-input, 
    .produto-card-horizontal button {
        cursor: auto;
    }
    
    /* ====== INFORMAÇÕES DO PRODUTO ====== */
    .produto-nome {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 6px;
        color: #1a1a1a;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .produto-preco {
        color: var(--cafe-brown);
        font-weight: 800;
        font-size: 1.15rem;
        margin-bottom: 4px;
        letter-spacing: -0.5px;
    }
    
    .produto-estoque {
        font-size: 0.7rem;
        color: #888;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .produto-estoque::before {
        content: '●';
        color: #4CAF50;
        font-size: 0.5rem;
    }
    
    /* ====== CONTROLES DE QUANTIDADE ====== */
    .quantidade-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: auto;
        width: 100%;
        padding-top: 8px;
        flex-shrink: 0;
        box-sizing: border-box;
    }
    
    .btn-quantidade {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 2px solid var(--cafe-brown);
        background: #fff;
        color: var(--cafe-brown);
        font-size: 1.2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 0;
        flex-shrink: 0;
    }
    
    .btn-quantidade:hover {
        background: var(--cafe-brown);
        color: #fff;
        transform: scale(1.1);
    }
    
    .btn-quantidade:active {
        transform: scale(0.95);
    }
    
    .quantidade-input {
        width: 42px;
        height: 34px;
        text-align: center;
        font-size: 1rem;
        font-weight: 700;
        border-radius: 10px;
        border: 2px solid #e0e0e0;
        background: #fafafa;
        color: #1a1a1a;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    
    .quantidade-input:focus {
        border-color: var(--cafe-brown);
        outline: none;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(172, 74, 0, 0.1);
    }
    
    /* Input com valor > 0 */
    .quantidade-input.tem-valor {
        border-color: var(--cafe-brown);
        background: rgba(248, 240, 175, 0.3);
        color: var(--cafe-brown);
    }
    
    /* ====== MOBILE RESPONSIVO ====== */
    @media (max-width: 600px) {
        .categoria-linha > .categoria-header-horizontal {
            margin-left: 8px;
        }
        
        .categoria-header-horizontal {
            padding: 10px 16px;
            font-size: 0.9rem;
            max-width: calc(100% - 16px);
            border-radius: 25px;
        }
        
        .categoria-header-horizontal i {
            font-size: 1.1rem;
        }
        
        .categoria-header-horizontal span:not(.badge) {
            white-space: normal;
            word-break: break-word;
        }
        
        .categoria-linha::after {
            display: none;
        }
        
        .produto-card-horizontal {
            min-width: 145px;
            max-width: 145px;
            width: 145px;
            padding: 14px 12px;
            border-radius: 14px;
            min-height: 180px;
        }
        
        .produtos-horizontal-scroll {
            gap: 10px;
            padding: 4px 8px;
        }
        
        .produto-nome {
            font-size: 0.85rem;
        }
        
        .produto-preco {
            font-size: 1.05rem;
        }
        
        .btn-quantidade {
            width: 30px;
            height: 30px;
            font-size: 1rem;
        }
        
        .quantidade-input {
            width: 38px;
            height: 30px;
            font-size: 0.9rem;
        }
    }
    
    /* ====== CARD DE ITENS SELECIONADOS ====== */
    .itens-selecionados-card {
        border-radius: 20px;
        border: none;
        background: linear-gradient(135deg, #fff 0%, #f8f5f0 100%);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .itens-selecionados-card.tem-itens {
        border: 2px solid var(--cafe-brown);
        box-shadow: 0 6px 25px rgba(172, 74, 0, 0.15);
    }
    
    .itens-selecionados-header {
        background: linear-gradient(135deg, var(--cafe-brown) 0%, var(--cafe-brown-dark) 100%);
        color: #fff;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .itens-selecionados-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #ffffff !important;
    }
    
    .itens-selecionados-header h5 i {
        color: #ffffff !important;
    }
    
    .itens-selecionados-header .total-badge {
        background: rgba(255,255,255,0.2);
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .itens-lista-resumo {
        padding: 16px 20px;
        max-height: 200px;
        overflow-y: auto;
    }
    
    .item-resumo {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }
    
    .item-resumo:last-child {
        border-bottom: none;
    }
    
    .item-resumo-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .item-resumo-qtd {
        background: var(--cafe-brown);
        color: #fff;
        font-weight: 700;
        font-size: 0.8rem;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .item-resumo-nome {
        font-weight: 500;
        color: #333;
        font-size: 0.9rem;
    }
    
    .item-resumo-preco {
        font-weight: 700;
        color: var(--cafe-brown);
        font-size: 0.95rem;
    }
    
    .itens-vazio {
        text-align: center;
        padding: 30px 20px;
        color: #999;
    }
    
    .itens-vazio i {
        font-size: 2.5rem;
        margin-bottom: 10px;
        opacity: 0.5;
    }
    
    .itens-vazio p {
        margin: 0;
        font-size: 0.9rem;
    }
    
    /* ====== TIPOS DE PAGAMENTO - Moderno ====== */
    .payment-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--cafe-dark);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .payment-section-title i {
        color: var(--cafe-brown);
    }
    
    .payment-types {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
    
    @media (min-width: 576px) {
        .payment-types {
            grid-template-columns: repeat(5, 1fr);
        }
    }
    
    .btn-payment {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px 10px;
        border: 2px solid #e8e8e8;
        border-radius: 16px;
        background: #fff;
        color: #777;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        min-height: 100px;
        position: relative;
        overflow: hidden;
    }
    
    .btn-payment::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, var(--cafe-brown) 0%, var(--cafe-brown-light) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .btn-payment:hover {
        border-color: var(--cafe-brown);
        color: var(--cafe-brown);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(172, 74, 0, 0.15);
    }
    
    .btn-payment.active {
        border-color: var(--cafe-brown-dark);
        color: #fff;
        box-shadow: 0 6px 20px rgba(172, 74, 0, 0.3);
    }
    
    .btn-payment.active::before {
        opacity: 1;
    }
    
    .btn-payment i, .btn-payment span {
        position: relative;
        z-index: 1;
    }
    
    .btn-payment i {
        font-size: 2rem;
        margin-bottom: 8px;
        transition: transform 0.3s ease;
    }
    
    .btn-payment:hover i {
        transform: scale(1.1);
    }
    
    .btn-payment span {
        font-size: 0.95rem;
        font-weight: 600;
    }
    
    /* ====== BOTÃO FINALIZAR ====== */
    #btn-finalizar {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        border-radius: 16px;
        padding: 18px 24px;
        font-weight: 700;
        font-size: 1.1rem;
        color: #fff;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
    }
    
    #btn-finalizar:not(:disabled):hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
    }
    
    #btn-finalizar:not(:disabled):active {
        transform: translateY(-1px);
    }
    
    #btn-finalizar:disabled {
        background: #ccc;
        color: #888;
        box-shadow: none;
        cursor: not-allowed;
    }
    
    /* ====== ANIMAÇÕES DE FEEDBACK ====== */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .item-resumo {
        animation: slideUp 0.3s ease;
    }
    
    .produto-card-horizontal.tem-quantidade {
        animation: pulse 0.3s ease;
    }
    
    /* Toast de sucesso */
    .toast-success {
        position: fixed;
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #fff;
        padding: 12px 24px;
        border-radius: 50px;
        font-weight: 600;
        box-shadow: 0 4px 20px rgba(40, 167, 69, 0.4);
        z-index: 9999;
        animation: toastIn 0.3s ease;
    }
    
    @keyframes toastIn {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }
    
    /* ====== MENSAGEM DE FINALIZAÇÃO ====== */
    #finalizar-msg {
        background: rgba(172, 74, 0, 0.08);
        padding: 10px 16px;
        border-radius: 10px;
        color: var(--cafe-brown);
        font-weight: 500;
    }
    
    /* Estilos do Modal de Troco */
    #modalTroco .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    
    #modalTroco .modal-header {
        border-radius: 15px 15px 0 0;
    }
    
    #modalTroco .input-group-lg .form-control {
        font-size: 1.5rem;
        font-weight: 600;
        text-align: center;
    }
    
    #modalTroco #valorRecebido {
        font-size: 2rem;
        font-weight: 700;
    }
    
    #modalTroco #trocoInfo {
        border-radius: 10px;
        border-left: 4px solid #0d6efd;
    }
    
    #modalTroco #trocoNegativo {
        border-radius: 10px;
        border-left: 4px solid #ffc107;
    }
    
    @media (max-width: 600px) {
        .payment-types {
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }
        .btn-payment {
            padding: 15px 8px;
            min-height: 85px;
        }
        .btn-payment i {
            font-size: 1.5rem;
            margin-bottom: 6px;
        }
        .btn-payment span {
            font-size: 0.85rem;
        }
    }
    .container.mb-5.pb-5 {
        padding-bottom: 220px !important; /* Espaço extra para barra e drawer */
        max-width: 100vw;
        overflow-x: hidden;
        overflow-y: visible;
        box-sizing: border-box;
    }
    
    /* Garantir que os containers de categorias sejam visíveis */
    .categorias-container {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .categoria-coluna {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .produtos-lista {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .produto-card {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Estilos para modal de caixa fechado */
    #modalCaixaFechado {
        z-index: 1050 !important;
    }
    
    #modalCaixaFechado .modal-content {
        border: none;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        background: #fff !important;
    }
    
    #modalCaixaFechado .modal-header {
        background: #dc3545 !important;
        color: #fff !important;
    }
    
    #modalCaixaFechado .modal-body {
        padding: 2rem;
        background: #fff !important;
    }
    
    #modalCaixaFechado .modal-footer {
        background: #fff !important;
        border-top: 1px solid #dee2e6;
    }
    
    /* Garantir que o modal backdrop não bloqueie navegação do navegador */
    .modal-backdrop {
        z-index: 1040;
    }
    
    /* Permitir que o backdrop não bloqueie completamente (para navegação) */
    #modalCaixaFechado + .modal-backdrop {
        pointer-events: auto;
    }
</style>


<div class="container mb-5 pb-5">

    <!-- Produtos Organizados por Categoria - Cada categoria em uma linha horizontal -->
    <?php if (empty($categorias)): ?>
        <div class="alert alert-warning text-center">
            <i class="bi bi-exclamation-triangle"></i> Nenhum produto disponível no momento.
            <br><small>Total de produtos encontrados: <?php echo count($produtos); ?></small>
        </div>
    <?php else: ?>
        <div id="categorias-produtos" style="margin-top: 20px;">
            <?php foreach ($categorias as $idCategoria => $categoria): ?>
                <?php if (!empty($categoria['produtos'])): ?>
                    <div class="categoria-linha mb-4">
                        <!-- Cabeçalho da Categoria -->
                        <div class="categoria-header-horizontal mb-3">
                            <?php if ($categoria['icone']): ?>
                                <i class="bi bi-<?php echo htmlspecialchars($categoria['icone']); ?>"></i>
                            <?php else: ?>
                                <i class="bi bi-box"></i>
                            <?php endif; ?>
                            <span><?php echo htmlspecialchars($categoria['nome']); ?></span>
                            <span class="badge bg-secondary ms-2"><?php echo count($categoria['produtos']); ?> produtos</span>
                        </div>
                        
                        <!-- Produtos da Categoria em Linha Horizontal -->
                        <div class="produtos-horizontal-container">
                            <div class="produtos-horizontal-scroll">
                                <?php foreach ($categoria['produtos'] as $produto): ?>
                                    <div class="produto-card-horizontal" onclick="cardClick(event, <?php echo $produto['id']; ?>)">
                                        <div class="produto-nome"><?php echo htmlspecialchars($produto['nome_produto']); ?></div>
                                        <div class="produto-preco">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></div>
                                        <div class="produto-estoque">Disponível: <?php echo $produto['estoque']; ?></div>
                                        <div class="quantidade-controls">
                                            <button class="btn-quantidade" onclick="event.stopPropagation(); diminuirQuantidade(<?php echo $produto['id']; ?>)" title="Diminuir">-</button>
                                            <input type="number" id="qtd_<?php echo $produto['id']; ?>" 
                                                   class="quantidade-input" 
                                                   value="0" min="0" max="<?php echo $produto['estoque']; ?>" 
                                                   data-max="<?php echo $produto['estoque']; ?>"
                                                   onchange="validarQuantidade(this)" onclick="event.stopPropagation();">
                                            <button class="btn-quantidade" onclick="event.stopPropagation(); aumentarQuantidade(<?php echo $produto['id']; ?>)" title="Aumentar">+</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Modal: Caixa Fechado (Centralizado) -->
    <div class="modal fade" id="modalCaixaFechado" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="modalCaixaFechadoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalCaixaFechadoLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i> Caixa Fechado
                    </h5>
                </div>
                <div class="modal-body bg-white">
                    <div class="text-center mb-3">
                        <i class="bi bi-lock-fill text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <p class="text-center mb-0">
                        <strong>Não é possível realizar vendas sem um caixa aberto.</strong><br>
                        Por favor, abra um caixa antes de continuar.
                    </p>
                </div>
                <div class="modal-footer bg-white">
                    <a href="caixa.php" class="btn btn-warning w-100">
                        <i class="bi bi-cash-stack"></i> Ir para Página de Caixa
                    </a>
                </div>
            </div>
        </div>
    </div>


    <!-- Carrinho Resumo -->
    <div id="carrinho-resumo" class="mt-4 mb-3" style="display:none;"></div>

    <!-- Seleção de Tipo de Pagamento -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="border-radius: 20px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                <div class="card-body p-4">
                    <div class="payment-section-title">
                        <i class="bi bi-wallet2"></i> Como deseja pagar?
                    </div>
                    <div class="payment-types">
                        <button type="button" class="btn-payment" data-tipo="dinheiro" onclick="selecionarTipoPagamento('dinheiro')">
                            <i class="bi bi-cash-stack"></i>
                            <span>Dinheiro</span>
                        </button>
                        <button type="button" class="btn-payment" data-tipo="credito" onclick="selecionarTipoPagamento('credito')">
                            <i class="bi bi-credit-card"></i>
                            <span>Crédito</span>
                        </button>
                        <button type="button" class="btn-payment" data-tipo="debito" onclick="selecionarTipoPagamento('debito')">
                            <i class="bi bi-credit-card-2-front"></i>
                            <span>Débito</span>
                        </button>
                        <button type="button" class="btn-payment" data-tipo="pix" onclick="selecionarTipoPagamento('pix')">
                            <i class="bi bi-qr-code"></i>
                            <span>Pix</span>
                        </button>
                        <button type="button" class="btn-payment" data-tipo="cortesia" onclick="selecionarTipoPagamento('cortesia')">
                            <i class="bi bi-gift"></i>
                            <span>Cortesia</span>
                        </button>
                    </div>
                    
                    <!-- Campo de Observação para Cortesia -->
                    <div id="observacao-cortesia-container" class="mt-3" style="display: none;">
                        <label for="observacao-cortesia" class="form-label fw-bold" style="color: var(--cafe-brown);">
                            <i class="bi bi-chat-left-text"></i> Observação (Obrigatória)
                        </label>
                        <textarea 
                            id="observacao-cortesia" 
                            class="form-control" 
                            rows="3" 
                            placeholder="Digite o motivo da cortesia..."
                            style="border-radius: 12px; border: 2px solid #e0e0e0; padding: 12px; font-size: 0.95rem;"
                            oninput="validarObservacaoCortesia()"
                        ></textarea>
                        <small class="text-danger mt-1" id="observacao-erro" style="display: none;">
                            <i class="bi bi-exclamation-circle"></i> A observação é obrigatória para cortesias.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Finalizar Venda - Botão Moderno -->
    <button id="btn-finalizar" class="btn btn-lg w-100 mb-4" onclick="finalizarVenda()" disabled style="
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        border-radius: 16px;
        padding: 18px 24px;
        font-weight: 700;
        font-size: 1.1rem;
        color: #fff;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
    ">
        <i class="bi bi-check-circle-fill me-2"></i> Finalizar Pedido
    </button>
    <div id="finalizar-msg" class="text-center text-muted small mt-2 mb-4" style="display:none;"></div>
</div>

<!-- Modal de Troco (para pagamento em dinheiro) -->
<div class="modal fade" id="modalTroco" tabindex="-1" aria-labelledby="modalTrocoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalTrocoLabel">
                    <i class="bi bi-cash-stack"></i> Pagamento em Dinheiro
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4 text-center">
                    <h4 class="text-muted mb-2">Total da Venda</h4>
                    <h2 class="text-success fw-bold" id="totalVendaModal">R$ 0,00</h2>
                </div>
                
                <div class="mb-3">
                    <label for="valorRecebido" class="form-label fw-bold">
                        <i class="bi bi-currency-dollar"></i> Valor Recebido
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">R$</span>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="valorRecebido" 
                               placeholder="0,00"
                               autofocus
                               oninput="calcularTroco()">
                    </div>
                    <small class="text-muted">Digite o valor recebido do cliente</small>
                </div>
                
                <div class="alert alert-info mb-0" id="trocoInfo" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-calculator"></i> <strong>Troco:</strong>
                        </div>
                        <div>
                            <span class="h4 mb-0 text-primary fw-bold" id="valorTroco">R$ 0,00</span>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3" id="trocoNegativo" style="display: none;">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>Atenção!</strong> O valor recebido é menor que o total da venda.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelarVenda()">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnConfirmarTroco" onclick="confirmarVendaDinheiro()" disabled>
                    <i class="bi bi-check-circle"></i> Confirmar Venda
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let tipoPagamentoSelecionado = null;
    let carrinho = [];
    const produtos = <?php echo json_encode($produtos); ?>;
    const ID_PESSOA_DEFAULT = 1; // ID da pessoa "Default"

    function selecionarTipoPagamento(tipo) {
        if (!caixaAberto) {
            alert('Não é possível selecionar forma de pagamento sem um caixa aberto.');
            return;
        }
        
        // Remover classe active de todos os botões
        document.querySelectorAll('.btn-payment').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Adicionar classe active ao botão selecionado
        const btnSelecionado = document.querySelector(`.btn-payment[data-tipo="${tipo}"]`);
        if (btnSelecionado) {
            btnSelecionado.classList.add('active');
        }
        
        // Armazenar tipo selecionado
        tipoPagamentoSelecionado = tipo;
        
        // Mostrar/esconder campo de observação para cortesia
        const observacaoContainer = document.getElementById('observacao-cortesia-container');
        const observacaoInput = document.getElementById('observacao-cortesia');
        const observacaoErro = document.getElementById('observacao-erro');
        
        if (tipo === 'cortesia') {
            observacaoContainer.style.display = 'block';
            observacaoInput.focus();
        } else {
            observacaoContainer.style.display = 'none';
            observacaoInput.value = '';
            observacaoErro.style.display = 'none';
        }
        
        // Habilitar botão finalizar se houver itens no carrinho
        atualizarCarrinho();
    }
    
    // Função para validar observação de cortesia
    function validarObservacaoCortesia() {
        const observacaoInput = document.getElementById('observacao-cortesia');
        const observacaoErro = document.getElementById('observacao-erro');
        const valor = observacaoInput.value.trim();
        
        if (valor.length === 0) {
            observacaoErro.style.display = 'block';
            observacaoInput.style.borderColor = '#dc3545';
        } else {
            observacaoErro.style.display = 'none';
            observacaoInput.style.borderColor = '#28a745';
        }
        
        // Atualizar estado do botão finalizar
        atualizarCarrinho();
    }

    // Variável global para controlar se há caixa aberto
    let caixaAberto = false;

    // Função para verificar status do caixa
    function verificarStatusCaixa() {
        fetch('api/caixa_status_vendas.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    caixaAberto = data.caixa_aberto;
                    
                    if (!caixaAberto) {
                        // Mostrar modal centralizado
                        const modal = new bootstrap.Modal(document.getElementById('modalCaixaFechado'));
                        modal.show();
                        
                        // Desabilitar interações
                        desabilitarInteracoes();
                    } else {
                        // Ocultar modal
                        const modalElement = document.getElementById('modalCaixaFechado');
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }
                        
                        // Reabilitar interações
                        habilitarInteracoes();
                    }
                } else {
                    console.error('Erro ao verificar status do caixa:', data.message);
                    // Em caso de erro, assumir que não há caixa aberto por segurança
                    caixaAberto = false;
                    const modal = new bootstrap.Modal(document.getElementById('modalCaixaFechado'));
                    modal.show();
                    desabilitarInteracoes();
                }
            })
            .catch(error => {
                console.error('Erro ao verificar status do caixa:', error);
                // Em caso de erro, assumir que não há caixa aberto por segurança
                caixaAberto = false;
                const modal = new bootstrap.Modal(document.getElementById('modalCaixaFechado'));
                modal.show();
                desabilitarInteracoes();
            });
    }

    // Função para desabilitar interações quando caixa fechado
    function desabilitarInteracoes() {
        // Desabilitar todos os cards de produtos
        document.querySelectorAll('.produto-card').forEach(card => {
            card.style.pointerEvents = 'none';
            card.style.opacity = '0.5';
        });
        
        // Desabilitar botões de quantidade
        document.querySelectorAll('.btn-quantidade').forEach(btn => {
            btn.disabled = true;
        });
        
        // Desabilitar inputs de quantidade
        document.querySelectorAll('.quantidade-input').forEach(input => {
            input.disabled = true;
        });
        
        // Desabilitar botões de pagamento
        document.querySelectorAll('.btn-payment').forEach(btn => {
            btn.disabled = true;
        });
        
        // Desabilitar botão finalizar
        const btnFinalizar = document.getElementById('btn-finalizar');
        if (btnFinalizar) {
            btnFinalizar.disabled = true;
        }
    }

    // Função para reabilitar interações quando caixa aberto
    function habilitarInteracoes() {
        // Reabilitar todos os cards de produtos
        document.querySelectorAll('.produto-card').forEach(card => {
            card.style.pointerEvents = 'auto';
            card.style.opacity = '1';
        });
        
        // Reabilitar botões de quantidade
        document.querySelectorAll('.btn-quantidade').forEach(btn => {
            btn.disabled = false;
        });
        
        // Reabilitar inputs de quantidade
        document.querySelectorAll('.quantidade-input').forEach(input => {
            input.disabled = false;
        });
        
        // Reabilitar botões de pagamento
        document.querySelectorAll('.btn-payment').forEach(btn => {
            btn.disabled = false;
        });
        
        // Reabilitar botão finalizar (se houver itens no carrinho)
        atualizarCarrinho();
    }

    // Inicialização
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar status do caixa ao carregar
        verificarStatusCaixa();
        
        // Verificar status do caixa a cada 30 segundos
        setInterval(verificarStatusCaixa, 30000);
        
        // Formatar campo de valor recebido como moeda
        const valorRecebidoInput = document.getElementById('valorRecebido');
        if (valorRecebidoInput) {
            valorRecebidoInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0) {
                    value = (parseInt(value) / 100).toFixed(2);
                    value = value.replace('.', ',');
                    e.target.value = value;
                } else {
                    e.target.value = '';
                }
                calcularTroco();
            });
            
            // Permitir Enter para confirmar
            valorRecebidoInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const btnConfirmar = document.getElementById('btnConfirmarTroco');
                    if (!btnConfirmar.disabled) {
                        confirmarVendaDinheiro();
                    }
                }
            });
        }
    });
    
    function validarQuantidade(input) {
        let valor = parseInt(input.value) || 0;
        const max = parseInt(input.dataset.max);
        
        if (valor < 0) valor = 0;
        if (valor > max) valor = max;
        
        input.value = valor;
        atualizarCarrinho();
    }

    function aumentarQuantidade(idProduto) {
        if (!caixaAberto) {
            return; // Não permitir se caixa fechado
        }
        
        const input = document.getElementById(`qtd_${idProduto}`);
        const atual = parseInt(input.value) || 0;
        const max = parseInt(input.dataset.max);
        
        if (atual < max) {
            input.value = atual + 1;
            atualizarCarrinho();
        }
    }

    function diminuirQuantidade(idProduto) {
        if (!caixaAberto) {
            return; // Não permitir se caixa fechado
        }
        
        const input = document.getElementById(`qtd_${idProduto}`);
        const atual = parseInt(input.value) || 0;
        
        if (atual > 0) {
            input.value = atual - 1;
            atualizarCarrinho();
        }
    }


    function atualizarCarrinho() {
        carrinho = [];
        let totalItens = 0;
        let totalValor = 0;
        produtos.forEach(produto => {
            const inputElement = document.getElementById(`qtd_${produto.id}`);
            if (inputElement) {
                const qtd = parseInt(inputElement.value) || 0;
                if (qtd > 0) {
                    const valorUnitario = parseFloat(produto.preco);
                    const total = qtd * valorUnitario;
                    totalItens += qtd;
                    totalValor += total;
                    carrinho.push({
                        id_produto: parseInt(produto.id),
                        quantidade: parseInt(qtd),
                        preco: Number(valorUnitario.toFixed(2)),
                        nome_produto: produto.nome_produto,
                        total: Number(total.toFixed(2))
                    });
                }
            }
        });
        
        // Resumo do carrinho
        const carrinhoResumo = document.getElementById('carrinho-resumo');
        const btnFinalizar = document.getElementById('btn-finalizar');
        const finalizarMsg = document.getElementById('finalizar-msg');
        
        // Atualizar estado visual dos cards de produtos
        document.querySelectorAll('.quantidade-input').forEach(input => {
            const card = input.closest('.produto-card-horizontal') || input.closest('.produto-card');
            const valor = parseInt(input.value) || 0;
            
            if (card) {
                if (valor > 0) {
                    card.classList.add('tem-quantidade');
                    input.classList.add('tem-valor');
                } else {
                    card.classList.remove('tem-quantidade');
                    input.classList.remove('tem-valor');
                }
            }
        });
        
        if (carrinho.length > 0) {
            carrinhoResumo.style.display = 'block';
            carrinhoResumo.innerHTML = `
                <div class='carrinho-card'>
                    <div class='carrinho-header'>
                        <div class='carrinho-title'>
                            <i class='bi bi-cart-fill'></i>
                            <span>Carrinho</span>
                            <span class='carrinho-badge'>${totalItens}</span>
                        </div>
                        <button class='btn-limpar-carrinho' onclick='limparCarrinho()' title='Limpar carrinho'>
                            <i class='bi bi-trash'></i>
                        </button>
                    </div>
                    <div class='carrinho-body'>
                        ${carrinho.map(item => `
                            <div class='carrinho-item'>
                                <div class='carrinho-item-info'>
                                    <div class='carrinho-item-nome'>${item.nome_produto}</div>
                                    <div class='carrinho-item-detalhes'>
                                        <span class='carrinho-item-qtd'>${item.quantidade}x</span>
                                        <span class='carrinho-item-preco-unit'>R$ ${item.preco.toFixed(2).replace('.', ',')}</span>
                                    </div>
                                </div>
                                <div class='carrinho-item-total'>
                                    R$ ${item.total.toFixed(2).replace('.', ',')}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <div class='carrinho-footer'>
                        <div class='carrinho-total-label'>Total</div>
                        <div class='carrinho-total-valor'>R$ ${totalValor.toFixed(2).replace('.', ',')}</div>
                    </div>
                </div>
            `;
        } else {
            carrinhoResumo.style.display = 'none';
            carrinhoResumo.innerHTML = '';
        }
        // Validar observação de cortesia se necessário
        let observacaoValida = true;
        if (tipoPagamentoSelecionado === 'cortesia') {
            const observacaoInput = document.getElementById('observacao-cortesia');
            const observacao = observacaoInput ? observacaoInput.value.trim() : '';
            observacaoValida = observacao.length > 0;
        }
        
        // Atualizar botão finalizar (só habilita se houver caixa aberto, produtos, tipo de pagamento e observação válida se for cortesia)
        const podeFinalizar = caixaAberto && carrinho.length > 0 && tipoPagamentoSelecionado && observacaoValida;
        btnFinalizar.disabled = !podeFinalizar;
        if (!podeFinalizar) {
            if (!tipoPagamentoSelecionado && carrinho.length === 0) {
                finalizarMsg.textContent = 'Selecione o tipo de pagamento e adicione produtos para finalizar a venda.';
            } else if (!tipoPagamentoSelecionado) {
                finalizarMsg.textContent = 'Selecione o tipo de pagamento para finalizar a venda.';
            } else if (carrinho.length === 0) {
                finalizarMsg.textContent = 'Adicione produtos ao carrinho para finalizar a venda.';
            } else if (tipoPagamentoSelecionado === 'cortesia' && !observacaoValida) {
                finalizarMsg.textContent = 'Por favor, preencha a observação obrigatória para cortesias.';
            }
            finalizarMsg.style.display = 'block';
        } else {
            finalizarMsg.style.display = 'none';
        }
    }

    function desabilita(){
        var button = document.getElementById('btn-finalizar');
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Processando...';
    }

    function habilita(){
        var button = document.getElementById('btn-finalizar');
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Finalizar Pedido';
    }
    
    // Função utilitária para escapar HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function finalizarVenda() {
        if (!caixaAberto) {
            alert('Não é possível finalizar venda sem um caixa aberto. Por favor, abra um caixa primeiro.');
            return;
        }
        
        if (!tipoPagamentoSelecionado) {
            alert('Por favor, selecione o tipo de pagamento antes de finalizar a venda.');
            return;
        }

        if (carrinho.length === 0) {
            alert('O carrinho está vazio.');
            return;
        }

        // Calcular total da venda
        let totalVenda = 0;
        carrinho.forEach(item => {
            totalVenda += item.total;
        });

        // Validar observação se for cortesia
        if (tipoPagamentoSelecionado === 'cortesia') {
            const observacaoInput = document.getElementById('observacao-cortesia');
            const observacao = observacaoInput ? observacaoInput.value.trim() : '';
            if (!observacao || observacao.length === 0) {
                alert('Por favor, preencha a observação obrigatória para cortesias.');
                observacaoInput.focus();
                return;
            }
        }
        
        // Se for pagamento em dinheiro, mostrar modal de troco
        if (tipoPagamentoSelecionado === 'dinheiro') {
            mostrarModalTroco(totalVenda);
        } else {
            // Para crédito/débito/pix/cortesia, usar confirmação simples
            const tipoTexto = {
                'credito': 'Cartão de Crédito',
                'debito': 'Cartão de Débito',
                'pix': 'Pix',
                'cortesia': 'Cortesia'
            };

            if (confirm(`Confirmar venda no ${tipoTexto[tipoPagamentoSelecionado]}?`)) {
                processarVenda();
            }
        }
    }

    function mostrarModalTroco(totalVenda) {
        // Formatar total para exibição
        const totalFormatado = totalVenda.toFixed(2).replace('.', ',');
        document.getElementById('totalVendaModal').textContent = `R$ ${totalFormatado}`;
        
        // Limpar campo de valor recebido
        document.getElementById('valorRecebido').value = '';
        document.getElementById('trocoInfo').style.display = 'none';
        document.getElementById('trocoNegativo').style.display = 'none';
        document.getElementById('btnConfirmarTroco').disabled = true;
        
        // Armazenar total da venda para uso posterior
        window.totalVendaAtual = totalVenda;
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('modalTroco'));
        modal.show();
        
        // Focar no campo de valor recebido
        setTimeout(() => {
            document.getElementById('valorRecebido').focus();
        }, 300);
    }

    function calcularTroco() {
        const valorRecebidoInput = document.getElementById('valorRecebido');
        const valorRecebidoStr = valorRecebidoInput.value.replace(/[^\d,]/g, '').replace(',', '.');
        const valorRecebido = parseFloat(valorRecebidoStr) || 0;
        const totalVenda = window.totalVendaAtual || 0;
        
        const trocoInfo = document.getElementById('trocoInfo');
        const trocoNegativo = document.getElementById('trocoNegativo');
        const valorTrocoSpan = document.getElementById('valorTroco');
        const btnConfirmar = document.getElementById('btnConfirmarTroco');
        
        if (valorRecebido > 0) {
            const troco = valorRecebido - totalVenda;
            
            if (troco >= 0) {
                // Troco positivo ou zero
                trocoInfo.style.display = 'block';
                trocoNegativo.style.display = 'none';
                valorTrocoSpan.textContent = `R$ ${troco.toFixed(2).replace('.', ',')}`;
                valorTrocoSpan.className = 'h4 mb-0 text-success fw-bold';
                btnConfirmar.disabled = false;
            } else {
                // Valor insuficiente
                trocoInfo.style.display = 'block';
                trocoNegativo.style.display = 'block';
                valorTrocoSpan.textContent = `R$ ${Math.abs(troco).toFixed(2).replace('.', ',')}`;
                valorTrocoSpan.className = 'h4 mb-0 text-danger fw-bold';
                btnConfirmar.disabled = true;
            }
        } else {
            trocoInfo.style.display = 'none';
            trocoNegativo.style.display = 'none';
            btnConfirmar.disabled = true;
        }
    }

    function confirmarVendaDinheiro() {
        const valorRecebidoInput = document.getElementById('valorRecebido');
        const valorRecebidoStr = valorRecebidoInput.value.replace(/[^\d,]/g, '').replace(',', '.');
        const valorRecebido = parseFloat(valorRecebidoStr) || 0;
        const totalVenda = window.totalVendaAtual || 0;
        
        if (valorRecebido < totalVenda) {
            alert('O valor recebido deve ser maior ou igual ao total da venda!');
            return;
        }
        
        // Calcular troco dado
        const trocoDado = valorRecebido - totalVenda;
        
        // Armazenar troco para uso no processarVenda
        window.trocoDadoAtual = trocoDado;
        
        // Fechar modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalTroco'));
        modal.hide();
        
        // Processar venda
        processarVenda();
    }

    function cancelarVenda() {
        // Reabilitar botão finalizar
        habilita();
    }

    function processarVenda() {
        desabilita();
        
        const dados = {
            pessoa_id: ID_PESSOA_DEFAULT, // Usar pessoa "Default" (ID 1)
            tipo_venda: tipoPagamentoSelecionado,
            itens: carrinho
        };
        
        // Se for venda em dinheiro, incluir o troco dado
        if (tipoPagamentoSelecionado === 'dinheiro' && window.trocoDadoAtual !== undefined) {
            dados.troco_dado = window.trocoDadoAtual;
        }
        
        // Se for cortesia, incluir a observação
        if (tipoPagamentoSelecionado === 'cortesia') {
            const observacaoInput = document.getElementById('observacao-cortesia');
            const observacao = observacaoInput ? observacaoInput.value.trim() : '';
            dados.observacao = observacao;
        }
        
        fetch('api/finalizar_venda.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Venda finalizada com sucesso!');
                // Limpar variáveis temporárias
                window.totalVendaAtual = undefined;
                window.trocoDadoAtual = undefined;
                // Limpar carrinho
                limparCarrinho();
                // Limpar seleção de tipo de pagamento
                tipoPagamentoSelecionado = null;
                document.querySelectorAll('.btn-payment').forEach(btn => btn.classList.remove('active'));
                
                // Limpar campo de observação se existir
                const observacaoInput = document.getElementById('observacao-cortesia');
                if (observacaoInput) {
                    observacaoInput.value = '';
                    const observacaoContainer = document.getElementById('observacao-cortesia-container');
                    if (observacaoContainer) {
                        observacaoContainer.style.display = 'none';
                    }
                }
                
                // Atualizar página para recarregar estoque
                location.reload();
            } else {
                alert('Erro: ' + data.message);
                habilita();
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao finalizar a venda');
            habilita();
        });
    }

    function limparCarrinho() {
        if (!confirm('Tem certeza que deseja limpar o carrinho?')) {
            return;
        }
        carrinho = [];
        produtos.forEach(produto => {
            const input = document.getElementById(`qtd_${produto.id}`);
            if (input) input.value = 0;
        });
        
        // Limpar campo de observação se existir
        const observacaoInput = document.getElementById('observacao-cortesia');
        if (observacaoInput) {
            observacaoInput.value = '';
            const observacaoContainer = document.getElementById('observacao-cortesia-container');
            if (observacaoContainer) {
                observacaoContainer.style.display = 'none';
            }
        }
        
        atualizarCarrinho();
    }

    function cardClick(event, idProduto) {
        if (!caixaAberto) {
            return; // Não permitir se caixa fechado
        }
        
        // Evita conflito se clicar nos controles de quantidade
        if (
            event.target.closest('.quantidade-controls') ||
            event.target.classList.contains('quantidade-input') ||
            event.target.tagName === 'BUTTON'
        ) {
            return;
        }
        aumentarQuantidade(idProduto);
    }
</script>

<?php include 'includes/footer.php'; ?>