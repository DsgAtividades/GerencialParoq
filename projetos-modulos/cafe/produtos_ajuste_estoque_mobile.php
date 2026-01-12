<?php
require_once 'config/database.php';
require_once 'includes/header_mobile.php';

$conn = getConnection();

// Se um ID específico foi passado, busca apenas esse produto
$produto_id = isset($_GET['id']) ? $_GET['id'] : null;

// Busca produtos
$sql = "SELECT id_produto, nome_produto, quantidade_estoque FROM cafe_produtos";
if ($produto_id) {
    $sql .= " WHERE id_produto = ?";
}
$sql .= " ORDER BY nome_produto";

$stmt = $conn->prepare($sql);
if ($produto_id) {
    $stmt->execute([$produto_id]);
} else {
    $stmt->execute();
}
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="h4">Ajuste de Estoque</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php foreach ($produtos as $produto): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title h6"><?= htmlspecialchars($produto['nome_produto']) ?></h5>
                        <p class="card-text">
                            Estoque atual: <strong><?= $produto['quantidade_estoque'] ?></strong> unidades
                        </p>
                        
                        <form class="formAjuste" data-produto-id="<?= $produto['id_produto'] ?>">
                            <div class="row g-2">
                                <div class="col-8">
                                    <div class="input-group">
                                        <button type="button" class="btn btn-outline-secondary btn-sm minus">-</button>
                                        <input type="number" class="form-control form-control-sm quantidade" 
                                               value="1" min="1" required>
                                        <button type="button" class="btn btn-outline-secondary btn-sm plus">+</button>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-success btn-sm adicionar">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm remover">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="mt-3">
                <a href="produtos_mobile.php" class="btn btn-outline-secondary w-100">Voltar</a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.minus').click(function() {
        const input = $(this).closest('.input-group').find('input');
        const value = parseInt(input.val());
        if (value > 1) {
            input.val(value - 1);
        }
    });

    $('.plus').click(function() {
        const input = $(this).closest('.input-group').find('input');
        input.val(parseInt(input.val()) + 1);
    });

    $('.formAjuste').on('submit', function(e) {
        e.preventDefault();
        ajustarEstoque($(this), true);
    });

    $('.remover').click(function() {
        const form = $(this).closest('form');
        ajustarEstoque(form, false);
    });

    function ajustarEstoque(form, adicionar) {
        const produtoId = form.data('produto-id');
        const quantidade = parseInt(form.find('.quantidade').val());
        
        if (!quantidade || quantidade < 1) {
            alert('Quantidade inválida');
            return;
        }

        $.ajax({
            url: 'api/ajustar_estoque.php',
            method: 'POST',
            data: {
                produto_id: produtoId,
                quantidade: quantidade,
                operacao: adicionar ? 'adicionar' : 'remover'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Erro ao ajustar estoque');
                }
            },
            error: function() {
                alert('Erro ao processar requisição');
            }
        });
    }
});
</script>

<?php
require_once 'includes/footer_mobile.php';
?>
