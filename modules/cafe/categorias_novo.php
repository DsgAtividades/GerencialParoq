<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $icone = trim($_POST['icone'] ?? '');
    $ordem = (int)($_POST['ordem'] ?? 0);
    
    $erro = false;
    
    // Validações
    if (empty($nome)) {
        $erro = "O nome da categoria é obrigatório.";
    } else {
        // Verificar se já existe uma categoria com este nome
        $stmt = $db->prepare("SELECT COUNT(*) FROM cafe_categorias WHERE nome = ?");
        $stmt->execute([$nome]);
        if ($stmt->fetchColumn() > 0) {
            $erro = "Já existe uma categoria com este nome.";
        }
    }
    
    if (!$erro) {
        try {
            $stmt = $db->prepare("INSERT INTO cafe_categorias (nome, icone, ordem) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $icone, $ordem]);
            
            header("Location: categorias.php?success=created");
            exit;
        } catch(PDOException $e) {
            $erro = "Erro ao cadastrar categoria: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Nova Categoria</h1>
        <a href="categorias.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required 
                               value="<?= $_POST['nome'] ?? '' ?>">
                        <div class="invalid-feedback">
                            Por favor, informe o nome da categoria.
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="icone" class="form-label">Ícone</label>
                        <div class="input-group">
                            <span class="input-group-text">bi-</span>
                            <input type="text" class="form-control" id="icone" name="icone"
                                   value="<?= $_POST['icone'] ?? '' ?>"
                                   placeholder="cart, bag-check, etc">
                        </div>
                        <div class="form-text">
                            Nome do ícone do <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="ordem" class="form-label">Ordem</label>
                        <input type="number" class="form-control" id="ordem" name="ordem" 
                               value="<?= (int)($_POST['ordem'] ?? 0) ?>" min="0">
                        <div class="form-text">
                            Ordem de exibição (0 = primeiro)
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

    <div class="mt-4">
        <h3 class="h5 mb-3">Prévia do Ícone</h3>
        <div class="p-3 bg-light rounded">
            <i id="previewIcone" class="bi bi-<?= $_POST['icone'] ?? '' ?> fs-3"></i>
        </div>
    </div>
</div>

<script>
// Preview do ícone
document.getElementById('icone').addEventListener('input', function(e) {
    const preview = document.getElementById('previewIcone');
    preview.className = 'bi bi-' + e.target.value + ' fs-3';
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
