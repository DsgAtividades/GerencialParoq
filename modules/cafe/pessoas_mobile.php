<?php
require_once 'config/database.php';
require_once 'includes/header_mobile.php';

// Inicializa a conexão com o banco de dados
$conn = getConnection();

// Busca todas as pessoas com seus cartões
$sql = "SELECT p.*, c.codigo as cartao_codigo 
FROM cafe_pessoas p
        LEFT JOIN cafe_cartoes c ON p.cartao_id = c.id
        ORDER BY p.nome";
$stmt = $conn->prepare($sql);
$stmt->execute();
$pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="h4">Pessoas</h2>
            <a href="pessoas_novo_mobile.php" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus"></i> Nova Pessoa
            </a>
        </div>
    </div>

    <div class="row">
        <?php foreach ($pessoas as $pessoa): ?>
            <div class="col-12 col-md-6 col-lg-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($pessoa['nome']) ?></h5>
                        <p class="card-text small mb-1">
                            <strong>CPF:</strong> <?= htmlspecialchars($pessoa['cpf']) ?><br>
                            <strong>Telefone:</strong> <?= htmlspecialchars($pessoa['telefone']) ?><br>
                            <strong>Cartão:</strong> <?= htmlspecialchars($pessoa['cartao_codigo']) ?>
                        </p>
                        <div class="btn-group btn-group-sm">
                            <a href="pessoas_editar_mobile.php?id=<?= $pessoa['id'] ?>" class="btn btn-outline-primary">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <button type="button" class="btn btn-outline-danger" onclick="confirmarExclusao(<?= $pessoa['id'] ?>)">
                                <i class="bi bi-trash"></i> Excluir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
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
                <p>Tem certeza que deseja excluir esta pessoa?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="btnConfirmarExclusao" class="btn btn-danger">Excluir</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarExclusao(id) {
    document.getElementById('btnConfirmarExclusao').href = 'pessoas_excluir.php?id=' + id;
    new bootstrap.Modal(document.getElementById('confirmarExclusaoModal')).show();
}
</script>

<?php
require_once 'includes/footer_mobile.php';
?>
