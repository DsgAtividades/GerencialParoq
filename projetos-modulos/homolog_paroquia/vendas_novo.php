<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';


verificarPermissao('vendas_incluir');

// Buscar produtos disponíveis
$stmt = $pdo->query("SELECT id, nome_produto, preco, estoque 
                    FROM produtos 
                    WHERE estoque > 0 AND bloqueado = 0 
                    ORDER BY nome_produto");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <!-- Coluna de Identificação do Cliente -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Identificação do Cliente</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="codigoParticipante" 
                                   placeholder="Digite o CPF ou código">
                            <button class="btn btn-primary" type="button" id="btnBuscarParticipante">
                                <i class="bi bi-search"></i>
                            </button>
                            <button class="btn btn-success" type="button" onclick="abrirLeitorQR()">
                                <i class="bi bi-qr-code-scan"></i>
                            </button>
                        </div>
                    </div>
                    <div id="dadosParticipante" class="d-none">
                        <h6>Cliente Selecionado:</h6>
                        <p class="mb-1" id="nomeParticipante"></p>
                        <p class="mb-1" id="cpfParticipante" class="text-muted small"></p>
                        <p class="mb-0" id="saldoParticipante" class="text-success"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna dos Produtos e Carrinho -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Produtos Disponíveis</h5>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($produtos as $produto): ?>
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($produto['nome_produto']) ?></h5>
                                    <p class="card-text">
                                        Preço: R$ <?= number_format($produto['preco'], 2, ',', '.') ?><br>
                                        Estoque: <?= $produto['estoque'] ?>
                                    </p>
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary" type="button" 
                                            onclick="alterarQuantidade(<?= $produto['id'] ?>, -1)">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number" class="form-control text-center" 
                                            id="qtd_<?= $produto['id'] ?>" value="0" min="0" 
                                            max="<?= $produto['estoque'] ?>" style="width: 60px;"
                                            onchange="atualizarQuantidadeManual(<?= $produto['id'] ?>, this.value)">
                                        <button class="btn btn-outline-secondary" type="button" 
                                            onclick="alterarQuantidade(<?= $produto['id'] ?>, 1)">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Carrinho -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Carrinho</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-end">Preço Un.</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="carrinhoItems">
                                <tr id="carrinhoVazio">
                                    <td colspan="5" class="text-center">Nenhum item no carrinho</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong id="totalVenda">R$ 0,00</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button id="btnFinalizar" class="btn btn-primary" onclick="finalizarVenda()" disabled>
                            Finalizar Venda
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal do Leitor QR Code -->
<div class="modal fade" id="qrcodeModal" tabindex="-1" aria-labelle$pdoy="qrcodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrcodeModalLabel">Ler QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-center">
                <div id="reader" style="width: 100%; max-width: 400px; margin: 0 auto;"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Dados globais
const produtos = <?= json_encode($produtos) ?>;
let carrinho = [];
let participanteSelecionado = null;

// Função para alterar quantidade
function alterarQuantidade(produtoId, delta) {
    const input = document.getElementById(`qtd_${produtoId}`);
    if (!input) return;

    const produto = produtos.find(p => p.id == produtoId);

    if (!produto) return;
    
    let quantidade = parseInt(input.value || 0);
    quantidade += delta;
    
    if (quantidade < 0) quantidade = 0;
    if (quantidade > produto.estoque) {
        alert('Quantidade excede o estoque disponível!');
        quantidade = produto.estoque;
    }
    
    input.value = quantidade;
    atualizarCarrinho(produtoId, quantidade);
}

// Função para atualizar quantidade manualmente
function atualizarQuantidadeManual(produtoId, novaQuantidade) {
    const produto = produtos.find(p => p.id == produtoId);
    if (!produto) return;
    
    novaQuantidade = parseInt(novaQuantidade) || 0;
    if (novaQuantidade < 0) novaQuantidade = 0;
    if (novaQuantidade > produto.estoque) {
        alert('Quantidade excede o estoque disponível!');
        novaQuantidade = produto.estoque;
    }
    
    document.getElementById(`qtd_${produtoId}`).value = novaQuantidade;
    atualizarCarrinho(produtoId, novaQuantidade);
}

// Função para atualizar o carrinho
function atualizarCarrinho(produtoId, quantidade) {
    const produto = produtos.find(p => p.id == produtoId);
    if (!produto) return;
    
    // Remover produto do carrinho se existir
    carrinho = carrinho.filter(item => item.id != produtoId);
    
    // Adicionar produto se quantidade > 0
    if (quantidade > 0) {
        carrinho.push({
            id: produtoId,
            nome: produto.nome_produto,
            quantidade: quantidade,
            preco: parseFloat(produto.preco)
        });
    }
    
    atualizarVisualizacaoCarrinho();
}

// Função para atualizar a visualização do carrinho
function atualizarVisualizacaoCarrinho() {
    const tbody = document.getElementById('carrinhoItems');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (carrinho.length === 0) {
        tbody.innerHTML = `
            <tr id="carrinhoVazio">
                <td colspan="5" class="text-center">Nenhum item no carrinho</td>
            </tr>`;
        document.getElementById('totalVenda').textContent = 'R$ 0,00';
        document.getElementById('btnFinalizar').disabled = true;
        return;
    }
    
    let total = 0;
    
    carrinho.forEach(item => {
        const subtotal = item.quantidade * item.preco;
        total += subtotal;
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.nome}</td>
            <td class="text-center">${item.quantidade}</td>
            <td class="text-end">R$ ${item.preco.toFixed(2).replace('.', ',')}</td>
            <td class="text-end">R$ ${subtotal.toFixed(2).replace('.', ',')}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-danger" onclick="removerDoCarrinho(${item.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    document.getElementById('totalVenda').textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
    document.getElementById('btnFinalizar').disabled = !participanteSelecionado || carrinho.length === 0;
}

// Função para remover item do carrinho
function removerDoCarrinho(produtoId) {
    document.getElementById(`qtd_${produtoId}`).value = 0;
    atualizarCarrinho(produtoId, 0);
}

// Funções do QR Code
function abrirLeitorQR() {
    if (typeof Html5QrcodeScanner === 'undefined') {
        console.error('Biblioteca QR Code não carregada');
        alert('Erro: Biblioteca de leitura QR Code não está carregada.');
        return;
    }

    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap não carregado');
        alert('Erro: Bootstrap não está carregado.');
        return;
    }

    const modalElement = document.getElementById('qrcodeModal');
    if (!modalElement) {
        console.error('Modal não encontrado');
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    modal.show();

    const html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        { 
            fps: 10,
            qrbox: { width: 250, height: 250 }
        },
        false
    );

    html5QrcodeScanner.render((decodedText) => {
        // Parar o scanner
        html5QrcodeScanner.clear();
        
        // Fechar o modal
        modal.hide();
        //alert(decodedText);
        // Buscar informações do participante
        fetch('api/buscar_participante.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ codigo: decodedText })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                participanteSelecionado = data.participante;
                mostrarDadosParticipante(data.participante);
            } else {
                throw new Error(data.message || 'Participante não encontrado');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao buscar participante: ' + error.message);
        });
    });

    // Limpar scanner quando o modal for fechado
    modalElement.addEventListener('hidden.bs.modal', () => {
        html5QrcodeScanner.clear();
    });
}

// Função para mostrar dados do participante
function mostrarDadosParticipante(participante) {
    const div = document.getElementById('dadosParticipante');
    document.getElementById('nomeParticipante').textContent = participante.nome;
    document.getElementById('cpfParticipante').textContent = `CPF: ${participante.cpf}`;
    if (participante.saldo !== undefined) {
        document.getElementById('saldoParticipante').textContent = 
            `Saldo: R$ ${parseFloat(participante.saldo).toFixed(2).replace('.', ',')}`;
    }
    div.classList.remove('d-none');
    
    // Atualizar estado do botão finalizar
    document.getElementById('btnFinalizar').disabled = carrinho.length === 0;
}

// Função para finalizar a venda
function finalizarVenda() {
    if (!participanteSelecionado) {
        alert('Por favor, selecione um cliente primeiro.');
        return;
    }

    if (carrinho.length === 0) {
        alert('O carrinho está vazio!');
        return;
    }
    
    const dados = {
        pessoa_id: participanteSelecionado.id,
        itens: carrinho.map(item => ({
            produto_id: item.id,
            quantidade: item.quantidade,
            preco: item.preco
        }))
    };
    
    fetch('api/processar_venda.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    })
    .then(response => {
        responseClone = response.clone()
        return response.json()
    })
    .then(data => {
        if (data.success) {
            alert('Venda realizada com sucesso!');
            window.location.href = 'vendas.php';
        } else {
            throw new Error(data.message || 'Erro ao processar venda');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar venda: ' + error.message);
    });
}

// Evento para buscar participante
document.getElementById('btnBuscarParticipante').addEventListener('click', function() {
    const codigo = document.getElementById('codigoParticipante').value.trim();
    if (!codigo) {
        alert('Por favor, digite um código.');
        return;
    }
    
    fetch('api/buscar_participante.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ codigo: codigo })
    })
    .then(response => {
        responseClone = response.clone();
        console.log(responseClone);
        return response.json()
    })
    .then(data => {
        if (data.success) {
            participanteSelecionado = data.participante;
            mostrarDadosParticipante(data.participante);
        } else {
            throw new Error(data.message || 'Participante não encontrado');
        }
    })
    .catch(error => {
        alert('Erro ao buscar participante: ' + error.message);
    });
});

// Evento para buscar ao pressionar Enter
document.getElementById('codigoParticipante').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('btnBuscarParticipante').click();
    }
});

// Inicializar carrinho quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    atualizarVisualizacaoCarrinho();
});
</script>

<!-- Bootstrap JS e dependências -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<!-- QR Code library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<?php include 'includes/footer.php'; ?>
