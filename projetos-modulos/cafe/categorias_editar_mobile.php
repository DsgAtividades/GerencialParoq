<?php
require_once 'config/database.php';
require_once 'includes/header_mobile.php';

if (!isset($_GET['id'])) {
    header('Location: categorias_mobile.php');
    exit;
}

$conn = getConnection();
$id = $_GET['id'];

// Busca dados da categoria
$sql = "SELECT c.*, COUNT(p.id) as total_produtos 
FROM cafe_categorias c
        LEFT JOIN cafe_produtos p ON c.id = p.categoria_id
        WHERE c.id = ? 
        GROUP BY c.id";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    header('Location: categorias_mobile.php');
    exit;
}
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="h4">Editar Categoria</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form id="formCategoria" method="POST" action="api/atualizar_categoria.php" class="needs-validation" novalidate>
                <input type="hidden" name="id" value="<?= $id ?>">
                
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome da Categoria</label>
                    <input type="text" class="form-control" id="nome" name="nome" 
                           value="<?= htmlspecialchars($categoria['nome']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Total de Produtos</label>
                    <input type="text" class="form-control" value="<?= $categoria['total_produtos'] ?>" readonly>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">Salvar</button>
                    <a href="categorias_mobile.php" class="btn btn-outline-secondary w-100 mt-2">Voltar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#formCategoria').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        if (!form[0].checkValidity()) {
            e.stopPropagation();
            form.addClass('was-validated');
            return;
        }

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = 'categorias_mobile.php';
                } else {
                    alert(response.message || 'Erro ao atualizar categoria');
                }
            },
            error: function() {
                alert('Erro ao processar requisição');
            }
        });
    });
});
</script>

<?php
require_once 'includes/footer_mobile.php';
?>
