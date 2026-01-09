<?php
require_once 'config/database.php';
require_once 'includes/header_mobile.php';

$conn = getConnection();

// Busca categorias
$sql = "SELECT id, nome FROM cafe_categorias ORDER BY nome";
$stmt = $conn->prepare($sql);
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="h4">Novo Produto</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form id="formProduto" method="POST" action="api/cadastrar_produto.php" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>

                <div class="mb-3">
                    <label for="preco" class="form-label">Preço</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" class="form-control" id="preco" name="preco" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="estoque" class="form-label">Estoque Inicial</label>
                    <input type="number" class="form-control" id="estoque" name="estoque" min="0" value="0" required>
                </div>

                <div class="mb-3">
                    <label for="categoria_id" class="form-label">Categoria</label>
                    <select class="form-select" id="categoria_id" name="categoria_id">
                        <option value="">Selecione uma categoria</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">Salvar</button>
                    <a href="produtos_mobile.php" class="btn btn-outline-secondary w-100 mt-2">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#preco').mask('#.##0,00', {reverse: true});

    $('#formProduto').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        if (!form[0].checkValidity()) {
            e.stopPropagation();
            form.addClass('was-validated');
            return;
        }

        // Converte o preço para formato aceito pelo backend
        const preco = form.find('#preco').val().replace('.', '').replace(',', '.');

        const formData = new FormData(form[0]);
        formData.set('preco', preco);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: Object.fromEntries(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = 'produtos_mobile.php';
                } else {
                    alert(response.message || 'Erro ao salvar produto');
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
