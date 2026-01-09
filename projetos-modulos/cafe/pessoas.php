<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';


verificarPermissao('gerenciar_pessoas');

// Buscar todas as pessoas com seus saldos e status do cartão
$query = "SELECT p.*, COALESCE(sc.saldo, 0.00) as saldo, c.usado as cartao_usado, c.codigo as cartao_codigo
          FROM cafe_pessoas p 
          LEFT JOIN cafe_saldos_cartao sc ON p.id_pessoa = sc.id_pessoa 
          LEFT JOIN cafe_cartoes c ON p.id_pessoa = c.id_pessoa";

// Adicionar filtro de pesquisa se fornecido
if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $busca = '%' . $_GET['busca'] . '%';
    $query .= " WHERE p.nome LIKE :busca 
                OR p.cpf LIKE :busca 
                OR c.codigo LIKE :busca";
}

$query .= " ORDER BY p.nome limit 20";

$stmt = $pdo->prepare($query);

// Bind do parâmetro de busca se existir
if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $stmt->bindParam(':busca', $busca);
}

$stmt->execute();
$pessoas = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Biblioteca QR Code -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Pessoas Cadastradas</h1>
        <div>
            <!-- <a href="gerar_cartoes.php" class="btn btn-success me-2">
                <i class="bi bi-upc-scan"></i> Gerar Cartões
            </a> -->
            <?php if(temPermissao('pessoas_incluir')): ?>
            <a href="alocar_cartao_mobile.php" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Novo Cadastro
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Barra de Pesquisa -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               name="busca" 
                               class="form-control" 
                               placeholder="Pesquisar por nome, CPF ou número do cartão..." 
                               value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Pesquisar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($pessoas)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                <?= isset($_GET['busca']) ? 'Nenhum resultado encontrado para a pesquisa.' : 'Nenhuma pessoa cadastrada.' ?>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>Cartão</th>
                            <th>Saldo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pessoas as $pessoa): ?>
                        <tr>
                            <td><?= htmlspecialchars($pessoa['nome']) ?></td>
                            <td><?= htmlspecialchars($pessoa['cpf']) ?></td>
                            <td><?= htmlspecialchars($pessoa['telefone']) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge <?= $pessoa['cartao_usado'] ? 'bg-success' : 'bg-danger' ?> me-2">
                                        <i class="bi bi-<?= $pessoa['cartao_usado'] ? 'check' : 'x' ?>"></i>
                                    </span>
                                     <span class="badge bg-secondary" style="font-size: 0.9em; font-family: monospace;">
                                        <?=$pessoa['cartao_codigo']?>
                                    </span> 
                                </div>
                            </td>
                            <td>R$ <?= number_format($pessoa['saldo'], 2, ',', '.') ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button"
                                            class="btn btn-sm btn-info text-white"
                                            title="Ver QR Code"
                                            onclick="mostrarQRCode('<?= $pessoa['cartao_codigo'] ?>', '<?= htmlspecialchars($pessoa['nome']) ?>', '<?= htmlspecialchars($pessoa['cartao_codigo']) ?>')">
                                        <i class="bi bi-qr-code"></i>
                                    </button>
                                    <?php if(temPermissao('produtos_saldo')): ?>
                                    <a href="saldos_credito.php?id=<?= $pessoa['id_pessoa'] ?>" 
                                       class="btn btn-sm btn-success" title="Adicionar Crédito">
                                        <i class="bi bi-cash"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if(temPermissao('pessoas_editar')): ?>
                                    <a href="pessoas_editar.php?id=<?= $pessoa['id_pessoa'] ?>" 
                                       class="btn btn-sm btn-warning text-white" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if(temPermissao('pessoas_excluir')): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-danger"
                                            title="Excluir"
                                            onclick="confirmarExclusao(<?= $pessoa['id_pessoa'] ?>, '<?= htmlspecialchars($pessoa['nome']) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de QR Code -->
<div class="modal fade" id="qrCodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Code do Participante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <h6 id="qrCodeNome" class="mb-3"></h6>
                <div id="qrCodeContainer" class="mb-3"></div>
                <code id="qrCodeValor" class="d-block mb-3"></code>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="confirmarExclusaoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o participante <strong id="nomeParticipante"></strong>?</p>
                <p class="text-danger">Esta ação não pode ser desfeita!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="btnConfirmarExclusao" class="btn btn-danger">Excluir</a>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarQRCode(codigo, nome, valor) {
    // Limpa o container anterior
    document.getElementById('qrCodeContainer').innerHTML = '';
    document.getElementById('qrCodeNome').textContent = nome;
    document.getElementById('qrCodeValor').textContent = valor;
    
    // Gera o novo QR Code
    new QRCode(document.getElementById('qrCodeContainer'), {
        text: codigo,
        width: 256,
        height: 256
    });
    
    // Mostra o modal
    new bootstrap.Modal(document.getElementById('qrCodeModal')).show();
}

function confirmarExclusao(id, nome) {
    document.getElementById('nomeParticipante').textContent = nome;
    document.getElementById('btnConfirmarExclusao').href = 'pessoas_excluir.php?id=' + id;
    new bootstrap.Modal(document.getElementById('confirmarExclusaoModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>
