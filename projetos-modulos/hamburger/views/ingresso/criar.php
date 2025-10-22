<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Ingresso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { overflow-x: hidden; }</style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col-auto p-0">
      <?php include __DIR__ . '/../_menu.php'; ?>
    </div>
    <div class="col mt-5">
      <h2>Comprar Ingresso</h2>
      <form method="post">
          <div class="mb-3">
              <label for="nome" class="form-label">Nome</label>
              <input type="text" class="form-control" id="nome" name="nome" required>
          </div>
          <div class="mb-3">
              <label for="cpf" class="form-label">CPF</label>
              <input type="text" class="form-control" id="cpf" name="cpf" required maxlength="14">
          </div>
          <div class="mb-3">
              <label class="form-label">Produtos</label>
              <div class="row" id="produtos-lista">
                  <?php foreach ($produtos as $produto): ?>
                      <div class="col-md-4 mb-3">
                          <div class="card h-100 produto-card" data-produto-id="<?= $produto['id'] ?>">
                              <div class="card-body">
                                  <h5 class="card-title mb-2"><?= htmlspecialchars($produto['nome']) ?></h5>
                                  <p class="card-text mb-1">R$ <?= number_format($produto['preco'],2,',','.') ?></p>
                                  <?php if (!empty($produto['descricao'])): ?>
                                      <p class="text-muted small mb-2"><?= htmlspecialchars($produto['descricao']) ?></p>
                                  <?php endif; ?>
                                  <div class="input-group input-group-sm mt-2">
                                      <button type="button" class="btn btn-outline-secondary" onclick="alterarQtd(<?= $produto['id'] ?>, -1)">-</button>
                                      <input type="number" class="form-control text-center" name="quantidade_produto[<?= $produto['id'] ?>]" id="qtd-produto-<?= $produto['id'] ?>" value="0" min="0" style="max-width:60px;" readonly>
                                      <button type="button" class="btn btn-outline-secondary" onclick="alterarQtd(<?= $produto['id'] ?>, 1)">+</button>
                                  </div>
                              </div>
                          </div>
                      </div>
                  <?php endforeach; ?>
              </div>
              <small class="text-muted">Clique + para adicionar produtos ao ingresso.</small>
          </div>
          <button type="submit" class="btn btn-success">Gerar Ingresso</button>
          <a href="index.php" class="btn btn-secondary">Voltar</a>
      </form>
      <script>
      function alterarQtd(id, delta) {
          var input = document.getElementById('qtd-produto-' + id);
          var val = parseInt(input.value) || 0;
          val += delta;
          if (val < 0) val = 0;
          input.value = val;
      }
      </script>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 