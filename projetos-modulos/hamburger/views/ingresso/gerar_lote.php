<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar Lote de Ingressos</title>
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
      <h2>Gerar Lote de Ingressos</h2>
      <form method="post" class="mb-4">
        <div class="mb-3">
          <label for="quantidade" class="form-label">Quantidade de ingressos a gerar</label>
          <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" required>
        </div>
        <div class="mb-3">
            <label for="qtd_hamburguer" class="form-label">Quantidade de Hambúrgueres por Ingresso</label>
            <input type="number" class="form-control" id="qtd_hamburguer" name="qtd_hamburguer" min="1" value="1" required>
        </div>
        <button type="submit" class="btn btn-success">Gerar Lote</button>
        <a href="index.php?c=ingresso&a=listar" class="btn btn-secondary">Voltar</a>
      </form>
    </div>
  </div>
</div>
</body>
</html> 