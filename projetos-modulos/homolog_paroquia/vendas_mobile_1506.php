<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('vendas_mobile');
$permissao_categoria = verificaGrupoPermissao();

// Buscar produtos disponíveis agrupados por categoria
if($permissao_categoria != 'Administrador' && $permissao_categoria != 'Gerente' && $permissao_categoria != 'Admin_Paroquia'){
    $stmt = $pdo->prepare("SELECT p.id, p.nome_produto, p.preco, p.estoque, p.bloqueado,
                           c.id as id_categoria, c.nome as nome_categoria, c.icone
                    FROM produtos p
                    LEFT JOIN categorias c ON p.categoria_id = c.id
                    WHERE p.estoque > 0 AND p.bloqueado = 0 
                    AND c.nome in (?)
                    ORDER BY c.nome, p.nome_produto");
                    $stmt->execute([$permissao_categoria]);
}else{
    $stmt = $pdo->prepare("SELECT p.id, p.nome_produto, p.preco, p.estoque, p.bloqueado,
                           c.id as id_categoria, c.nome as nome_categoria, c.icone
                    FROM produtos p
                    LEFT JOIN categorias c ON p.categoria_id = c.id
                    WHERE p.estoque > 0 AND p.bloqueado = 0 
                    ORDER BY c.nome, p.nome_produto");
                    $stmt->execute();
}

    
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

<style>
    body {
        background: #f6f8fa;
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
    .categorias-nav {
        background: none;
        box-shadow: none;
        padding: 0 0 10px 0;
        margin-bottom: 10px;
        overflow-x: auto;
        white-space: nowrap;
        display: flex;
        gap: 10px;
        scrollbar-width: thin;
    }
    .categoria-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 10px 12px;
        border-radius: 12px;
        border: none;
        background: #f1f3f6;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 70px;
        font-size: 13px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .categoria-btn.active, .categoria-btn:hover {
        background: #0d6efd;
        color: #fff;
        box-shadow: 0 2px 8px rgba(13,110,253,0.08);
    }
    .categoria-btn i {
        font-size: 22px;
        margin-bottom: 3px;
    }
    .categoria-section {
        display: none;
        margin-bottom: 20px;
    }
    .categoria-section.active {
        display: block;
    }
    .produtos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
        padding: 0 2px;
    }
    .produto-card {
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        padding: 10px 6px 10px 6px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-height: 120px;
        position: relative;
        transition: box-shadow 0.2s;
        cursor: pointer;
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
    }
    .quantidade-controls button {
        width: 32px;
        height: 32px;
        font-size: 1.2rem;
        border-radius: 50%;
        border: none;
        background: #f1f3f6;
        color: #0d6efd;
        transition: background 0.2s;
    }
    .quantidade-controls button:hover {
        background: #0d6efd;
        color: #fff;
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
    /* Participante */
    #participanteInfo {
        background: #f1f3f6;
        border-radius: 12px;
        padding: 12px 14px;
        margin-top: 10px;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    #participanteInfo h5 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 2px;
    }
    #participanteInfo .text-success {
        font-size: 1.1rem;
        font-weight: 700;
    }
    /* Responsivo */
    @media (max-width: 600px) {
        .produtos-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        #carrinho {
            max-width: 100vw;
            padding-bottom: 12px;
        }
        .container.mb-5.pb-5 {
            padding-bottom: 240px !important;
        }
        .header-mobile {
            padding-left: 8px;
            padding-right: 8px;
        }
        .bottom-bar {
            padding-left: 4px;
            padding-right: 4px;
        }
    }
    .container.mb-5.pb-5 {
        padding-bottom: 220px !important; /* Espaço extra para barra e drawer */
        max-width: 100vw;
        overflow-x: hidden;
    }
</style>


<div class="container mb-5 pb-5">
    <!-- Área do Cliente -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <button class="btn btn-primary btn-lg w-100" type="button" id="btnLerQRCode">
                            <i class="bi bi-qr-code-scan"></i> Ler QR Code do Participante
                        </button>
                    </div>
                    <div id="participanteInfo" class="d-none">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1" id="participanteNome"></h5>
                                <div class="text-muted small" id="participanteCPF"></div>
                            </div>
                            <div class="text-end">
                                <div class="text-success fw-bold" id="participanteSaldo"></div>
                                <div class="text-muted small">Saldo disponível</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categorias -->
    <div class="categorias-nav">
        <?php foreach ($categorias as $idCategoria => $categoria): ?>
            <button class="categoria-btn" data-categoria="<?php echo $idCategoria; ?>">
                <?php if ($categoria['icone']): ?>
                    <i class="bi bi-<?php echo $categoria['icone']; ?>"></i>
                <?php else: ?>
                    <i class="bi bi-box"></i>
                <?php endif; ?>
                <span><?php echo $categoria['nome']; ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Produtos por Categoria -->
    <?php foreach ($categorias as $idCategoria => $categoria): ?>
        <div class="categoria-section" id="categoria-<?php echo $idCategoria; ?>">
            <div class="produtos-grid">
                <?php foreach ($categoria['produtos'] as $produto): ?>
                    <div class="produto-card" onclick="cardClick(event, <?php echo $produto['id']; ?>)">
                        <div class="produto-nome"><?php echo $produto['nome_produto']; ?></div>
                        <div class="produto-preco">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></div>
                        <div class="produto-estoque">Disponível: <?php echo $produto['estoque']; ?></div>
                        <div class="quantidade-controls mt-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation(); diminuirQuantidade(<?php echo $produto['id']; ?>)">-</button>
                            <input type="number" id="qtd_<?php echo $produto['id']; ?>" 
                                   class="form-control form-control-sm quantidade-input" 
                                   value="0" min="0" max="<?php echo $produto['estoque']; ?>" 
                                   data-max="<?php echo $produto['estoque']; ?>"
                                   onchange="validarQuantidade(this)" onclick="event.stopPropagation();" style="width: 60px">
                            <button class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation(); aumentarQuantidade(<?php echo $produto['id']; ?>)">+</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Carrinho e Finalizar Venda -->
    <div id="carrinho-resumo" class="mt-4 mb-3" style="display:none;"></div>
    <button id="btn-finalizar" class="btn btn-success btn-lg w-100 mb-4" onclick="finalizarVenda()" disabled>
        Finalizar Venda
    </button>
    <div id="finalizar-msg" class="text-center text-muted small mt-2 mb-4" style="display:none;"></div>
</div>

<!-- Modal QR Code -->
<div class="modal fade" id="qrcodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ler QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reader"></div>
                <div id="qrSuccessMessage" class="alert alert-success mt-3" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let participanteSelecionado = null;
    let carrinho = [];
    let scanner = null;
    let scanning = false;
    const produtos = <?php echo json_encode($produtos); ?>;

    // Inicialização
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar primeira categoria
        const primeiroBotao = document.querySelector('.categoria-btn');
        if (primeiroBotao) {
            primeiroBotao.click();
        }

        // Configurar botões de categoria
        document.querySelectorAll('.categoria-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const categoriaId = this.dataset.categoria;
                
                // Atualizar botões
                document.querySelectorAll('.categoria-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Atualizar seções
                document.querySelectorAll('.categoria-section').forEach(section => section.classList.remove('active'));
                document.getElementById(`categoria-${categoriaId}`).classList.add('active');
            });
        });
    });

    // Configurar scanner QR Code
    document.getElementById('btnLerQRCode').addEventListener('click', function() {
        if (scanner) {
            scanner.clear();
        }
        
        const modal = new bootstrap.Modal(document.getElementById('qrcodeModal'));
        modal.show();

        scanner = new Html5QrcodeScanner("reader", { 
            fps: 10,
            qrbox: {width: 250, height: 250},
            aspectRatio: 1.0
        });

        scanner.render(onScanSuccess, onScanFailure);
        scanning = true;
    });

    function onScanSuccess(decodedText, decodedResult) {
        if (scanning) {
            scanning = false;
            scanner.clear();
            document.getElementById('qrcodeModal').querySelector('.btn-close').click();

            // Buscar informações do participante
            fetch('api/buscar_participante.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ codigo: decodedText })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    participanteSelecionado = data.participante;
                    document.getElementById('participanteInfo').classList.remove('d-none');
                    document.getElementById('participanteNome').textContent = participanteSelecionado.nome;
                    document.getElementById('participanteCPF').textContent = 'CPF: ' + participanteSelecionado.cpf;
                    document.getElementById('participanteSaldo').textContent = 'R$ ' + participanteSelecionado.saldo;
                    document.getElementById('btn-finalizar').disabled = false;

                    // Mostrar mensagem de sucesso
                    const msg = document.getElementById('qrSuccessMessage');
                    msg.textContent = 'QR Code lido com sucesso!';
                    msg.style.display = 'block';
                    setTimeout(() => msg.style.display = 'none', 2000);
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao buscar informações do participante');
            });
        }
    }

    function onScanFailure(error) {
        // console.warn(`QR Code scanning failed: ${error}`);
    }

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
        const podeFinalizar = carrinho.length > 0 && participanteSelecionado;
        btnFinalizar.disabled = !podeFinalizar;
        if (!podeFinalizar) {
            if (!participanteSelecionado && carrinho.length === 0) {
                finalizarMsg.textContent = 'Selecione um participante e adicione produtos para finalizar a venda.';
            } else if (!participanteSelecionado) {
                finalizarMsg.textContent = 'Selecione um participante para finalizar a venda.';
            } else if (carrinho.length === 0) {
                finalizarMsg.textContent = 'Adicione produtos ao carrinho para finalizar a venda.';
            }
            finalizarMsg.style.display = 'block';
        } else {
            finalizarMsg.style.display = 'none';
        }
    }

    function finalizarVenda() {
        if (!participanteSelecionado) {
            alert('Por favor, selecione um participante antes de finalizar a venda.');
            return;
        }

        if (carrinho.length === 0) {
            alert('O carrinho está vazio.');
            return;
        }

        if (confirm('Confirmar a finalização da venda?')) {
            const dados = {
                pessoa_id: participanteSelecionado.id,
                itens: carrinho
            };
            console.log(dados);
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
                    // Atualizar saldo do participante
                    participanteSelecionado.saldo = data.novo_saldo;
                    document.getElementById('participanteSaldo').textContent = 'R$ ' + data.novo_saldo;
                    // Limpar carrinho
                    limparCarrinho();
                    // Atualizar quantidades em estoque
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao finalizar a venda');
            });
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