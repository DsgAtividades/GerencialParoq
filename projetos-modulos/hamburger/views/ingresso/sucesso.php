<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresso Gerado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { overflow-x: hidden; }</style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col-auto p-0">
      <?php include __DIR__ . '/../_menu.php'; ?>
    </div>
    <div class="col mt-5 text-center">
      <h2>Ingresso Gerado com Sucesso!</h2>
      <p class="mt-3">Guarde este código para apresentar no evento:</p>
      <div class="display-4 fw-bold mb-4"><?= htmlspecialchars($codigo) ?></div>
      <?php if (!empty($ingresso)): ?>
          <div class="mb-2">Nome: <strong><?= htmlspecialchars($ingresso['nome']) ?></strong></div>
          <div class="mb-4">CPF: <strong><?= htmlspecialchars(substr($ingresso['cpf'], 0, 4)) ?></strong></div>
      <?php endif; ?>
      <a href="index.php" class="btn btn-primary">Voltar ao início</a>
    </div>
  </div>
</div>
</body>
</html> 