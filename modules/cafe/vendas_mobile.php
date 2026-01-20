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

<!-- Layout em Colunas por Categoria -->
<style>
    body {
        background: var(--cafe-bg) !important;
        font-family: 'Inter', Arial, sans-serif;
    }
    .header-mobile {
        position: sticky;
        top: 0;
        z-index: 1100;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        padding: 16px 0 8px 0;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .header-mobile h2 {
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0;
        color: #0d6efd;
        letter-spacing: 1px;
    }
    .produto-card {
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        padding: 12px 10px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-height: 150px;
        position: relative;
        transition: box-shadow 0.2s;
        cursor: pointer;
        box-sizing: border-box;
        width: 100%;
        overflow: visible;
    }
    .produto-card:active {
        box-shadow: 0 4px 16px rgba(13,110,253,0.10);
        background: #e9f2ff;
    }
    .produto-card .quantidade-controls, .produto-card .quantidade-input, .produto-card button {
        cursor: auto;
    }
    .produto-nome {
        font-weight: 600;
        font-size: 1.05rem;
        margin-bottom: 2px;
        color: #222;
    }
    .produto-preco {
        color: #28a745;
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 2px;
    }
    .produto-estoque {
        font-size: 12px;
        color: #888;
        margin-bottom: 8px;
    }
    .quantidade-controls {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: auto;
        width: 100%;
        padding-top: 8px;
        flex-shrink: 0;
        box-sizing: border-box;
    }
    .quantidade-input {
        width: 48px;
        text-align: center;
        font-size: 1rem;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
    }
    .bottom-bar, #carrinho, .carrinho-overlay { display: none !important; }
    #carrinho-resumo .card {
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    #carrinho-resumo .card-header, #carrinho-resumo .card-footer {
        background: #f8f9fa;
        border-radius: 14px 14px 0 0;
    }
    #carrinho-resumo .card-footer {
        border-radius: 0 0 14px 14px;
    }
    /* Tipos de Pagamento */
    .payment-types {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
    
    .btn-payment {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px 10px;
        border: 2px solid #dee2e6;
        border-radius: 12px;
        background: #fff;
        color: #6c757d;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        min-height: 100px;
    }
    
    .btn-payment:hover {
        border-color: var(--cafe-brown);
        color: var(--cafe-brown);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(172, 74, 0, 0.2);
        }
    
    .btn-payment.active {
        background: linear-gradient(135deg, var(--cafe-brown) 0%, var(--cafe-brown-light) 100%);
        border-color: var(--cafe-brown-dark);
        color: #fff;
        box-shadow: 0 4px 16px rgba(172, 74, 0, 0.3);
        }
    
    .btn-payment i {
        font-size: 2rem;
        margin-bottom: 8px;
        }
    
    .btn-payment span {
        font-size: 1rem;
        font-weight: 600;
        }
    
    #tipoPagamentoSelecionado {
        text-align: center;
        margin-bottom: 0;
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
</style>


<div class="container mb-5 pb-5">
    <div class="header-mobile">
        <h2>Vender</h2>
    </div>
    <!-- Seleção de Tipo de Pagamento -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"><i class="bi bi-credit-card"></i> Selecione o Tipo de Pagamento</h5>
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
                    </div>
                    <div id="tipoPagamentoSelecionado" class="alert alert-info mt-3" style="display: none;">
                        <i class="bi bi-check-circle-fill"></i> <strong>Forma de pagamento:</strong> <span id="tipoPagamentoTexto"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Produtos Organizados por Categoria em Colunas -->
    <?php 
    // Debug: Verificar se há categorias
    if (empty($categorias)): 
    ?>
        <div class="alert alert-warning text-center">
            <i class="bi bi-exclamation-triangle"></i> Nenhum produto disponível no momento.
            <br><small>Total de produtos encontrados: <?php echo count($produtos); ?></small>
        </div>
    <?php else: ?>
        <div class="categorias-container">
        <?php foreach ($categorias as $idCategoria => $categoria): ?>
                <?php if (!empty($categoria['produtos'])): ?>
                    <div class="categoria-coluna" data-categoria="<?php echo $idCategoria; ?>">
                        <!-- Cabeçalho da Categoria -->
                        <div class="categoria-header">
                <?php if ($categoria['icone']): ?>
                                <i class="bi bi-<?php echo htmlspecialchars($categoria['icone']); ?>"></i>
                <?php else: ?>
                    <i class="bi bi-box"></i>
                <?php endif; ?>
                            <span><?php echo htmlspecialchars($categoria['nome']); ?></span>
    </div>

                        <!-- Produtos da Categoria -->
                        <div class="produtos-lista">
                <?php foreach ($categoria['produtos'] as $produto): ?>
                    <div class="produto-card" onclick="cardClick(event, <?php echo $produto['id']; ?>)">
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
                <?php endif; ?>
    <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Carrinho e Finalizar Venda -->
    <div id="carrinho-resumo" class="mt-4 mb-3" style="display:none;"></div>
    <button id="btn-finalizar" class="btn btn-success btn-lg w-100 mb-4" onclick="finalizarVenda()" disabled>
        Finalizar Venda
    </button>
    <div id="finalizar-msg" class="text-center text-muted small mt-2 mb-4" style="display:none;"></div>
</div>

<script>
    let tipoPagamentoSelecionado = null;
    let carrinho = [];
    const produtos = <?php echo json_encode($produtos); ?>;
    const ID_PESSOA_DEFAULT = 1; // ID da pessoa "Default"

    function selecionarTipoPagamento(tipo) {
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
        
        // Mostrar mensagem de confirmação
        const tipoTexto = {
            'dinheiro': 'Dinheiro',
            'credito': 'Cartão de Crédito',
            'debito': 'Cartão de Débito'
        };
        
        document.getElementById('tipoPagamentoTexto').textContent = tipoTexto[tipo];
        document.getElementById('tipoPagamentoSelecionado').style.display = 'block';
        
        // Habilitar botão finalizar se houver itens no carrinho
        atualizarCarrinho();
    }

    // Inicialização
    document.addEventListener('DOMContentLoaded', function() {
        // Layout em colunas - nenhuma inicialização especial necessária
        console.log('Layout de colunas por categoria carregado');
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
        const input = document.getElementById(`qtd_${idProduto}`);
        const atual = parseInt(input.value) || 0;
        const max = parseInt(input.dataset.max);
        
        if (atual < max) {
            input.value = atual + 1;
            atualizarCarrinho();
        }
    }

    function diminuirQuantidade(idProduto) {
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
            const qtd = parseInt(document.getElementById(`qtd_${produto.id}`).value) || 0;
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
        });
        // Resumo do carrinho
        const carrinhoResumo = document.getElementById('carrinho-resumo');
        const btnFinalizar = document.getElementById('btn-finalizar');
        const finalizarMsg = document.getElementById('finalizar-msg');
        if (carrinho.length > 0) {
            carrinhoResumo.style.display = 'block';
            carrinhoResumo.innerHTML = `
                <div class='card shadow-sm mb-2'>
                    <div class='card-header d-flex justify-content-between align-items-center'>
                        <span><i class='bi bi-cart'></i> Carrinho (${totalItens} itens)</span>
                        <button class='btn btn-sm btn-outline-danger' onclick='limparCarrinho()'>Limpar</button>
                    </div>
                    <div class='card-body p-2'>
                        ${carrinho.map(item => `
                            <div class='d-flex justify-content-between align-items-center border-bottom py-1'>
                                <div>
                                    <strong>${item.nome_produto}</strong><br>
                                    <small>${item.quantidade}x R$ ${item.preco.toFixed(2).replace('.', ',')}</small>
                                </div>
                                <div class='text-success fw-bold'>R$ ${item.total.toFixed(2).replace('.', ',')}</div>
                            </div>
                        `).join('')}
                    </div>
                    <div class='card-footer d-flex justify-content-between align-items-center'>
                        <span class='fw-bold'>Total:</span>
                        <span class='text-success fw-bold fs-5'>R$ ${totalValor.toFixed(2).replace('.', ',')}</span>
                    </div>
                </div>
            `;
        } else {
            carrinhoResumo.style.display = 'none';
            carrinhoResumo.innerHTML = '';
        }
        // Atualizar botão finalizar
        const podeFinalizar = carrinho.length > 0 && tipoPagamentoSelecionado;
        btnFinalizar.disabled = !podeFinalizar;
        if (!podeFinalizar) {
            if (!tipoPagamentoSelecionado && carrinho.length === 0) {
                finalizarMsg.textContent = 'Selecione o tipo de pagamento e adicione produtos para finalizar a venda.';
            } else if (!tipoPagamentoSelecionado) {
                finalizarMsg.textContent = 'Selecione o tipo de pagamento para finalizar a venda.';
            } else if (carrinho.length === 0) {
                finalizarMsg.textContent = 'Adicione produtos ao carrinho para finalizar a venda.';
            }
            finalizarMsg.style.display = 'block';
        } else {
            finalizarMsg.style.display = 'none';
        }
    }

    function desabilita(){
        var button = document.getElementById('btn-finalizar');
        button.disabled = true;
        button.innerText = 'Processando Venda...'; //
    }

    function habilita(){
        var button = document.getElementById('btn-finalizar');
        button.disabled = false;
        button.innerText = 'Finalizar Venda'; // Opcional
    }

    function finalizarVenda() {
        desabilita();
        
        if (!tipoPagamentoSelecionado) {
            alert('Por favor, selecione o tipo de pagamento antes de finalizar a venda.');
            habilita();
            return;
        }

        if (carrinho.length === 0) {
            alert('O carrinho está vazio.');
            habilita();
            return;
        }

        const tipoTexto = {
            'dinheiro': 'Dinheiro',
            'credito': 'Cartão de Crédito',
            'debito': 'Cartão de Débito'
        };

        if (confirm(`Confirmar venda no ${tipoTexto[tipoPagamentoSelecionado]}?`)) {
            const dados = {
                pessoa_id: ID_PESSOA_DEFAULT, // Usar pessoa "Default" (ID 1)
                tipo_venda: tipoPagamentoSelecionado,
                itens: carrinho
            };
            
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
                    // Limpar carrinho
                    limparCarrinho();
                    // Limpar seleção de tipo de pagamento
                    tipoPagamentoSelecionado = null;
                    document.querySelectorAll('.btn-payment').forEach(btn => btn.classList.remove('active'));
                    document.getElementById('tipoPagamentoSelecionado').style.display = 'none';
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
        } else {
            habilita();
        }
    }

    function limparCarrinho() {
        carrinho = [];
        produtos.forEach(produto => {
            const input = document.getElementById(`qtd_${produto.id}`);
            if (input) input.value = 0;
        });
        atualizarCarrinho();
    }

    function cardClick(event, idProduto) {
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