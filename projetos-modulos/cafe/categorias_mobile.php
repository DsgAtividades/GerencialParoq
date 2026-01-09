<?php
require_once 'config/database.php';
require_once 'includes/header_mobile.php';

$conn = getConnection();

// Buscar todas as categorias com contagem de produtos
$sql = "SELECT c.id, c.nome,
        COUNT(p.id) as total_produtos
        FROM categorias c
        LEFT JOIN produtos p ON p.categoria_id = c.id
        GROUP BY c.id
        ORDER BY c.nome";

$stmt = $conn->prepare($sql);
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="h4">Categorias</h2>
            <a href="categorias_novo_mobile.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Nova Categoria
            </a>
        </div>
    </div>

    <!-- Lista de Categorias -->
    <div class="row">
        <div class="col-12">
            <div class="list-group">
                <?php foreach ($categorias as $categoria): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1"><?= htmlspecialchars($categoria['nome']) ?></h5>
                                <small class="text-muted">
                                    <?= $categoria['total_produtos'] ?> produto(s)
                                </small>
                            </div>
                            <div class="btn-group">
                                <a href="categorias_editar_mobile.php?id=<?= $categoria['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-outline-danger btn-sm" onclick="excluirCategoria(<?= $categoria['id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function excluirCategoria(id) {
    if (confirm('Tem certeza que deseja excluir esta categoria?')) {
        $.post('categorias_excluir.php', {
            id: id
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Erro ao excluir categoria: ' + response.message);
            }
        });
    }
}
</script>

<?php
require_once 'includes/footer_mobile.php';
?>
