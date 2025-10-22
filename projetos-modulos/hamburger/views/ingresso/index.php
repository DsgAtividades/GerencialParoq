<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venda de Ingressos</title>
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
      <div class="container-fluid px-0">
        <!-- Cabeçalho -->
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between mb-4">
          <div>
            <h1 class="display-5 fw-bold mb-1 text-primary">🍔 Festa do Hambúrguer</h1>
            <p class="lead text-secondary mb-0">Gestão de Ingressos e Vendas</p>
          </div>
        </div>
      
        <!-- Barra de Ações -->
        <div class="row g-3 mb-4">
          <div class="col-12 col-md-4 col-lg-4">
            <a href="index.php?c=ingresso&a=listar" class="card card-ataalho border-0 shadow-lg text-decoration-none h-100">
              <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                <span class="fs-1 mb-2 text-primary"><i class="bi bi-list-ul"></i></span>
                <div class="fw-bold text-dark">Lista de Ingressos</div>
              </div>
            </a>
          </div>
          <div class="col-12 col-md-4 col-lg-4">
            <a href="index.php?c=ingresso&a=vincular" class="card card-ataalho border-0 shadow-lg text-decoration-none h-100">
              <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                <span class="fs-1 mb-2 text-info"><i class="bi bi-link-45deg"></i></span>
                <div class="fw-bold text-dark">Vincular Ingresso</div>
              </div>
            </a>
          </div>
          <div class="col-12 col-md-4 col-lg-4">
            <a href="index.php?c=fila&a=fila" class="card card-ataalho border-0 shadow-lg text-decoration-none h-100">
              <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                <span class="fs-1 mb-2 text-success"><i class="bi bi-people"></i></span>
                <div class="fw-bold text-dark">Fila de Espera</div>
              </div>
            </a>
          </div>
          <div class="col-12 col-md-4 col-lg-4">
            <a href="index.php?c=dashboard" class="card card-ataalho border-0 shadow-lg text-decoration-none h-100">
              <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                <span class="fs-1 mb-2 text-dark"><i class="bi bi-bar-chart"></i></span>
                <div class="fw-bold text-dark">Dashboard</div>
              </div>
            </a>
          </div>
          <div class="col-12 col-md-4 col-lg-4">
            <a href="index.php?c=produto" class="card card-ataalho border-0 shadow-lg text-decoration-none h-100">
              <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                <span class="fs-1 mb-2 text-primary"><i class="bi bi-bag"></i></span>
                <div class="fw-bold text-dark">Produtos</div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 