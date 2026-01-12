<?php
require_once 'config/database.php';
require_once 'includes/header_mobile.php';

$conn = getConnection();

// Busca cartões disponíveis
$sql = "SELECT id, codigo FROM cafe_cartoes WHERE usado = 0 ORDER BY codigo";
$stmt = $conn->prepare($sql);
$stmt->execute();
$cartoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="h4">Nova Pessoa</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form id="formPessoa" method="POST" action="api/cadastrar_pessoa.php" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>

                <div class="mb-3">
                    <label for="cpf" class="form-label">CPF</label>
                    <input type="text" class="form-control" id="cpf" name="cpf" required>
                </div>

                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" required>
                </div>

                <div class="mb-3">
                    <label for="cartao_id" class="form-label">Cartão</label>
                    <select class="form-select" id="cartao_id" name="cartao_id" required>
                        <option value="">Selecione um cartão</option>
                        <?php foreach ($cartoes as $cartao): ?>
                            <option value="<?= $cartao['id'] ?>"><?= htmlspecialchars($cartao['codigo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">Salvar</button>
                    <a href="pessoas_mobile.php" class="btn btn-outline-secondary w-100 mt-2">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Máscara para CPF e telefone
$(document).ready(function() {
    $('#cpf').mask('000.000.000-00');
    $('#telefone').mask('(00) 00000-0000');

    // Validação do formulário
    $('#formPessoa').on('submit', function(e) {
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
                    window.location.href = 'pessoas_mobile.php';
                } else {
                    alert(response.message || 'Erro ao salvar pessoa');
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
