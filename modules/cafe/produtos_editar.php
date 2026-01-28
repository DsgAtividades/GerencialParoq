<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

//$message = '';
//$error = '';
verificarPermissaoProduto('produtos_editar');

// Buscar categorias para o select
$stmt = $pdo->query("SELECT id, nome, icone FROM cafe_categorias ORDER BY ordem, nome");
$categorias = $stmt->fetchAll();

// Verificar se foi passado um ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: produtos.php');
    exit;
}

// Buscar dados do produto
$stmt = $pdo->prepare("
    SELECT p.*, c.nome as nome_categoria, c.icone as categoria_icone 
    FROM cafe_produtos p 
    LEFT JOIN cafe_categorias c ON p.categoria_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$produto = $stmt->fetch();

if (!$produto) {
    header('Location: produtos.php');
    exit;
}

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar dados
        $nome = trim($_POST['nome'] ?? '');
        $preco = str_replace(',', '.', $_POST['preco'] ?? '0');
        $estoque = (int)($_POST['estoque'] ?? 0);
        $categoria_id = (int)($_POST['categoria_id'] ?? 0);
        
        if (empty($nome)) {
            throw new Exception('O nome do produto é obrigatório');
        }
        
        if (!is_numeric($preco) || $preco <= 0) {
            throw new Exception('Preço inválido');
        }
        
        if ($estoque < 0) {
            throw new Exception('Quantidade inválida');
        }
        
        if ($categoria_id <= 0) {
            throw new Exception('Selecione uma categoria');
        }
        
        // Atualizar produto
        $stmt = $pdo->prepare("UPDATE cafe_produtos SET nome_produto = ?, categoria_id = ?, preco = ?, estoque = ? WHERE id = ?");
        $stmt->execute([$nome, $categoria_id, $preco, $estoque, $id]);
        
        header('Location: produtos.php?success=updated');
        exit;
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Editar Produto</h1>
        <a href="produtos.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Produto atualizado com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="post" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome do Produto</label>
                                <input type="text" class="form-control" id="nome" name="nome" required 
                                       value="<?= htmlspecialchars($produto['nome_produto']) ?>">
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
                                            <?= $categoria['id'] == $produto['categoria_id'] ? 'selected' : '' ?>>
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
                                           value="<?= number_format($produto['preco'], 2, ',', '') ?>">
                                </div>
                                <div class="invalid-feedback">
                                    Por favor, informe um preço válido.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="estoque" class="form-label">Estoque</label>
                                <input type="number" class="form-control" id="estoque" name="estoque" required min="0"
                                       value="<?= (int)$produto['estoque'] ?>">
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

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Categoria</dt>
                        <dd class="col-sm-8">
                            <?php if ($produto['categoria_icone']): ?>
                                <i class="bi bi-<?= htmlspecialchars($produto['categoria_icone']) ?>"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($produto['nome_categoria']) ?>
                        </dd>

                        <dt class="col-sm-4">Preço</dt>
                        <dd class="col-sm-8">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></dd>

                        <dt class="col-sm-4">Estoque</dt>
                        <dd class="col-sm-8"><?= $produto['estoque'] ?> unidade(s)</dd>
                    </dl>
                </div>
            </div>
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
