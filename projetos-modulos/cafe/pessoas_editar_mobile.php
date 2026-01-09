<?php
require_once 'config/database.php';
require_once 'includes/header_mobile.php';

if (!isset($_GET['id'])) {
    header('Location: pessoas_mobile.php');
    exit;
}

$conn = getConnection();
$id = $_GET['id'];

// Busca dados da pessoa
$sql = "SELECT p.*, c.codigo as cartao_codigo FROM pessoas p 
        LEFT JOIN cartoes c ON p.cartao_id = c.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pessoa) {
    header('Location: pessoas_mobile.php');
    exit;
}

// Busca saldo atual
$sql = "SELECT saldo FROM saldos_cartao WHERE pessoa_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$saldo = $stmt->fetch(PDO::FETCH_ASSOC);
$saldoAtual = $saldo ? number_format($saldo['saldo'], 2, ',', '.') : '0,00';
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="h4">Editar Pessoa</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form id="formPessoa" method="POST" action="api/atualizar_pessoa.php" class="needs-validation" novalidate>
                <input type="hidden" name="id" value="<?= $id ?>">
                
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" 
                           value="<?= htmlspecialchars($pessoa['nome']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="cpf" class="form-label">CPF</label>
                    <input type="text" class="form-control" id="cpf" name="cpf" 
                           value="<?= htmlspecialchars($pessoa['cpf']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" 
                           value="<?= htmlspecialchars($pessoa['telefone']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cartão Atual</label>
                    <input type="text" class="form-control" 
                           value="<?= htmlspecialchars($pessoa['cartao_codigo'] ?? 'Não alocado') ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Saldo Atual</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" class="form-control" value="<?= $saldoAtual ?>" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">Salvar</button>
                    <a href="pessoas_mobile.php" class="btn btn-outline-secondary w-100 mt-2">Voltar</a>
                    <a href="saldos_credito_mobile.php?pessoa_id=<?= $id ?>" class="btn btn-success w-100 mt-2">
                        <i class="bi bi-cash"></i> Gerenciar Créditos
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#cpf').mask('000.000.000-00');
    $('#telefone').mask('(00) 00000-0000');

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
                    alert(response.message || 'Erro ao atualizar pessoa');
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
