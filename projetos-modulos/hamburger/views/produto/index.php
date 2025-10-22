<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col-auto p-0">
      <?php include __DIR__ . '/../_menu.php'; ?>
    </div>
    <div class="col mt-5">
      <div class="container-fluid px-0">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between mb-4">
          <div>
            <h1 class="display-5 fw-bold mb-1 text-primary">🛒 Produtos</h1>
            <p class="lead text-secondary mb-0">Gerencie os produtos do evento</p>
          </div>
          <a href="index.php?c=produto&a=criar" class="btn btn-success btn-lg shadow-sm mt-3 mt-md-0"><i class="bi bi-plus-circle me-2"></i>Cadastrar Produto</a>
        </div>
        <div class="card border-0 shadow-lg mb-4">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-bordered align-middle bg-white mb-0">
                <thead class="table-primary">
                  <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Preço (R$)</th>
                    <th>Descrição</th>
                    <th>Qtd. Disponível</th>
                    <th>Vendidos</th>
                    <th>Estoque Disponível</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($produtos as $p): ?>
                  <tr>
                    <td><?= htmlspecialchars($p['id']) ?></td>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td><?= number_format($p['preco'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($p['descricao']) ?></td>
                    <td><?= $p['quantidade_disponivel'] !== null ? htmlspecialchars($p['quantidade_disponivel']) : '-' ?></td>
                    <td><?= isset($vendidos[$p['id']]) ? $vendidos[$p['id']] : 0 ?></td>
                    <td>
                      <?php
                        $estoque = ($p['quantidade_disponivel'] !== null ? (int)$p['quantidade_disponivel'] : 0) - (isset($vendidos[$p['id']]) ? $vendidos[$p['id']] : 0);
                        echo $estoque;
                      ?>
                    </td>
                    <td>
                      <a href="index.php?c=produto&a=editar&id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                      <a href="index.php?c=produto&a=deletar&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja deletar este produto?')">Deletar</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <a href="index.php" class="btn btn-secondary mt-3">Voltar ao início</a>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 