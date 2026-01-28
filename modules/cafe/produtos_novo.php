<?php

require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

$message = '';
$error = '';
verificarPermissaoProduto('produtos_incluir');

// Buscar categorias para o select
$stmt = $pdo->query("SELECT id, nome, icone, ordem FROM cafe_categorias ORDER BY ordem, nome");
$categorias = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validar dados
        $nome = trim($_POST['nome']);
        $preco = str_replace(',', '.', $_POST['preco']);
        $estoque = (int)$_POST['estoque'];
        $categoria_id = (int)$_POST['categoria_id'];
        
        if (empty($nome)) {
            throw new Exception('O nome do produto é obrigatório');
        }
        
        if ($preco <= 0) {
            throw new Exception('O preço deve ser maior que zero');
        }
        
        if ($estoque < 0) {
            throw new Exception('O estoque não pode ser negativo');
        }

        if ($categoria_id <= 0) {
            throw new Exception('Selecione uma categoria válida');
        }
        
        // Inserir produto
        $stmt = $pdo->prepare("INSERT INTO cafe_produtos (nome_produto, categoria_id, preco, estoque) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $categoria_id, $preco, $estoque]);
        
        header("Location: produtos.php?success=created");
        exit;
        
    } catch (Exception $e) {
        $error = 'Erro: ' . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Novo Produto</h1>
        <a href="produtos.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nome" class="form-label">Nome do Produto</label>
                        <input type="text" class="form-control" id="nome" name="nome" required 
                               value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>">
                        <div class="invalid-feedback">
                            Por favor, informe o nome do produto.
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="categoria_id" class="form-label">Categoria</label>
                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" 
                                    <?= (isset($_POST['categoria_id']) && $_POST['categoria_id'] == $categoria['id']) ? 'selected' : '' ?>>
                                    <?php if ($categoria['icone']): ?>
                                        <i class="bi bi-<?= htmlspecialchars($categoria['icone']) ?>"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($categoria['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Por favor, selecione uma categoria.
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="preco" class="form-label">Preço</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control" id="preco" name="preco" required
                                   value="<?= isset($_POST['preco']) ? htmlspecialchars($_POST['preco']) : '0,00' ?>">
                        </div>
                        <div class="invalid-feedback">
                            Por favor, informe um preço válido.
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="estoque" class="form-label">Estoque Inicial</label>
                        <input type="number" class="form-control" id="estoque" name="estoque" required min="0"
                               value="<?= isset($_POST['estoque']) ? (int)$_POST['estoque'] : '0' ?>">
                        <div class="invalid-feedback">
                            Por favor, informe uma quantidade válida.
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Formatar campo de preço
document.getElementById('preco').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    value = (parseInt(value || '0') / 100).toFixed(2);
    e.target.value = value.replace('.', ',');
});

// Validação do formulário
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php include 'includes/footer.php'; ?>
