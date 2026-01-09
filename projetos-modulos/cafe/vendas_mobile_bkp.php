<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('vendas_mobile');
$permissao_categoria = verificaGrupoPermissao();
header('Content-Type: text/html; charset=utf-8');

// Buscar produtos disponíveis agrupados por categoria
if($permissao_categoria != 'Administrador'){
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
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<style>
    .categorias-nav {
        background-color: white;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }
    .categoria-btn {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        padding: 10px 15px;
        margin: 0 5px;
        border: none;
        background: none;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .categoria-btn:hover,
    .categoria-btn.active {
        color: #0d6efd;
    }
    .categoria-btn i {
        font-size: 24px;
        margin-bottom: 5px;
    }
    .categoria-btn span {
        font-size: 12px;
        text-align: center;
    }
    .categoria-section {
        display: none;
        margin-bottom: 30px;
    }
    .categoria-section.active {
        display: block;
    }
    .produtos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        padding: 15px;
    }
    .produto-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 15px;
        background-color: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .produto-nome {
        font-weight: bold;
        margin-bottom: 5px;
    }
    .produto-preco {
        color: #28a745;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .quantidade-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .bottom-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: white;
        padding: 15px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
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
                    <div class="produto-card">
                        <div class="produto-nome"><?php echo $produto['nome_produto']; ?></div>
                        <div class="produto-preco">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></div>
                        <div class="produto-estoque">Disponível: <?php echo $produto['estoque']; ?></div>
                        <div class="quantidade-controls mt-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="diminuirQuantidade(<?php echo $produto['id']; ?>)">-</button>
                            <input type="number" id="qtd_<?php echo $produto['id']; ?>" 
                                   class="form-control form-control-sm quantidade-input" 
                                   value="0" min="0" max="<?php echo $produto['estoque']; ?>" 
                                   data-max="<?php echo $produto['estoque']; ?>"
                                   onchange="validarQuantidade(this)" style="width: 60px">
                            <button class="btn btn-outline-primary btn-sm" onclick="aumentarQuantidade(<?php echo $produto['id']; ?>)">+</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Carrinho -->
    <div id="carrinho" class="d-none">
        <div class="carrinho-header">
            <h5 class="mb-0">Carrinho</h5>
            <button class="btn btn-sm btn-outline-danger" onclick="limparCarrinho()">Limpar</button>
        </div>
        <div id="carrinho-itens"></div>
        <div class="carrinho-total">
            <div class="d-flex justify-content-between">
                <span>Total:</span>
                <span class="text-success fw-bold" id="carrinho-total">R$ 0,00</span>
            </div>
        </div>
    </div>

    <!-- Barra inferior -->
    <div class="bottom-bar">
        <button id="btn-finalizar" class="btn btn-success btn-lg w-100" onclick="finalizarVenda()" disabled>
            Finalizar Venda
        </button>
    </div>
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
    const produtos = <?=json_encode($produtos); ?>;

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

        const carrinhoDiv = document.getElementById('carrinho');
        const carrinhoItens = document.getElementById('carrinho-itens');
        const carrinhoTotal = document.getElementById('carrinho-total');
        
        if (carrinho.length > 0) {
            carrinhoDiv.classList.remove('d-none');
            carrinhoTotal.textContent = `R$ ${totalValor.toFixed(2).replace('.', ',')}`;
            carrinhoItens.innerHTML = carrinho.map(item => 
            `
                <div class="carrinho-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${item.nome_produto}</strong><br>
                            <small>${item.quantidade}x R$ ${item.preco.toFixed(2).replace('.', ',')}</small>
                        </div>
                        <div class="text-success fw-bold">R$ ${item.total.toFixed(2).replace('.', ',')}</div>
                    </div>
                </div>
            `).join('');
        } else {
            carrinhoDiv.classList.add('d-none');
        }

        // Atualizar botão finalizar
        document.getElementById('btn-finalizar').disabled = carrinho.length === 0 || !participanteSelecionado;
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
</script>

<?php include 'includes/footer.php'; ?>