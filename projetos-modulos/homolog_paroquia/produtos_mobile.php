<?php
require_once 'config/database.php';
require_once 'includes/header_mobile.php';

$conn = getConnection();

// Busca todos os produtos
$sql = "SELECT p.id, p.nome, p.preco, p.estoque as quantidade_estoque, p.ativo as bloqueado 
        FROM produtos p 
        ORDER BY p.nome";
$stmt = $conn->prepare($sql);
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="h4">Produtos</h2>
            <div class="btn-group">
                <a href="produtos_novo_mobile.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Novo Produto
                </a>
                <a href="produtos_ajuste_estoque_mobile.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-seam"></i> Ajuste de Estoque
                </a>
            </div>
        </div>
    </div>

    <!-- Lista de Produtos -->
    <div class="row">
        <div class="col-12">
            <div class="list-group">
                <?php foreach ($produtos as $produto): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1"><?= htmlspecialchars($produto['nome']) ?></h5>
                                <p class="mb-1">
                                    <strong>Preço:</strong> R$ <?= number_format($produto['preco'], 2, ',', '.') ?><br>
                                    <strong>Estoque:</strong> <?= $produto['quantidade_estoque'] ?>
                                </p>
                            </div>
                            <div>
                                <a href="produtos_editar_mobile.php?id=<?= $produto['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($produto['bloqueado']): ?>
                                    <button class="btn btn-outline-success btn-sm" onclick="alterarStatus(<?= $produto['id'] ?>, 0)">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-danger btn-sm" onclick="alterarStatus(<?= $produto['id'] ?>, 1)">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function alterarStatus(id, status) {
    if (confirm('Deseja realmente alterar o status deste produto?')) {
        $.post('produtos_alterar_status.php', {
            id: id,
            status: status
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Erro ao alterar status do produto');
            }
        });
    }
}
</script>

<?php
require_once 'includes/footer_mobile.php';
?>
