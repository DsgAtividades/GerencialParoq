<?php
require_once 'config/database.php';
require_once 'includes/header_mobile.php';

if (!isset($_GET['id'])) {
    header('Location: produtos_mobile.php');
    exit;
}

$conn = getConnection();
$id = $_GET['id'];

// Busca dados do produto
$sql = "SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header('Location: produtos_mobile.php');
    exit;
}

// Busca categorias
$sql = "SELECT id, nome FROM categorias ORDER BY nome";
$stmt = $conn->prepare($sql);
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="h4">Editar Produto</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form id="formProduto" method="POST" action="api/atualizar_produto.php" class="needs-validation" novalidate>
                <input type="hidden" name="id" value="<?= $id ?>">
                
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" 
                           value="<?= htmlspecialchars($produto['nome']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="preco" class="form-label">Preço</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" class="form-control" id="preco" name="preco" 
                               value="<?= number_format($produto['preco'], 2, ',', '.') ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="estoque" class="form-label">Estoque Atual</label>
                    <input type="number" class="form-control" id="estoque" 
                           value="<?= $produto['estoque'] ?>" readonly>
                    <small class="form-text text-muted">
                        Use a página de Ajuste de Estoque para modificar a quantidade
                    </small>
                </div>

                <div class="mb-3">
                    <label for="categoria_id" class="form-label">Categoria</label>
                    <select class="form-select" id="categoria_id" name="categoria_id">
                        <option value="">Selecione uma categoria</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= $categoria['id'] ?>" 
                                <?= $categoria['id'] == $produto['categoria_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categoria['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">Salvar</button>
                    <a href="produtos_mobile.php" class="btn btn-outline-secondary w-100 mt-2">Voltar</a>
                    <a href="produtos_ajuste_estoque_mobile.php?id=<?= $id ?>" class="btn btn-success w-100 mt-2">
                        <i class="bi bi-box-seam"></i> Ajustar Estoque
                    </a>
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
                    alert(response.message || 'Erro ao atualizar produto');
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
