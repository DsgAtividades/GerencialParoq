<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Editar Produto</h2>
    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-info"> <?= htmlspecialchars($mensagem) ?> </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($produto['nome']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="preco" class="form-label">Preço (R$)</label>
            <input type="number" step="0.01" class="form-control" id="preco" name="preco" value="<?= htmlspecialchars($produto['preco']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <input type="text" class="form-control" id="descricao" name="descricao" value="<?= htmlspecialchars($produto['descricao']) ?>">
        </div>
        <div class="mb-3">
            <label for="quantidade_disponivel" class="form-label">Quantidade Disponível</label>
            <input type="number" class="form-control" id="quantidade_disponivel" name="quantidade_disponivel" value="<?= $produto['quantidade_disponivel'] !== null ? htmlspecialchars($produto['quantidade_disponivel']) : '' ?>">
        </div>
        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="index.php?c=produto" class="btn btn-secondary">Voltar</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 