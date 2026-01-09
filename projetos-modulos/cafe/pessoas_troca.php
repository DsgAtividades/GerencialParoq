<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('pessoas_editar');

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cartao'])) {
    $busca = trim($_POST['cartao']);
    $stmt = $pdo->prepare("
        SELECT p.id_pessoa
        FROM cafe_pessoas p
        LEFT JOIN cafe_cartoes c ON c.id_pessoa = p.id_pessoa
        WHERE c.codigo = ? OR p.cpf = ?
    ");
    $stmt->execute([$busca, $busca]);
    $pessoa = $stmt->fetch();
    if ($pessoa) {
        header('Location: pessoas_editar.php?id=' . $pessoa['id_pessoa']);
        exit;
    } else {
        $erro = 'Cartão ou CPF não encontrado ou não vinculado a nenhum participante.';
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Buscar Participante pelo Cartão</h1>
        <div>
          
            <a href="pessoas.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($erro) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="cartao" class="form-control" placeholder="Digite o número do cartão ou CPF" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
