<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
      <h2 style="font-size:2.5rem; font-weight: bold;">Dashboard do Evento</h2>
      <div class="row mt-4">
          <div class="col-md-4">
              <div class="card text-bg-success mb-3">
                  <div class="card-body">
                      <h5 class="card-title">Clientes Atendidos</h5>
                      <p class="card-text display-5"><?= $totalEntregues ?></p>
                  </div>
              </div>
          </div>
          <div class="col-md-4">
              <div class="card text-bg-warning mb-3">
                  <div class="card-body">
                      <h5 class="card-title">Na Fila de Espera (clientes)</h5>
                      <p class="card-text display-5"><?= $totalFila ?></p>
                  </div>
              </div>
          </div>
          <div class="col-md-4">
              <div class="card text-bg-info mb-3">
                  <div class="card-body">
                      <h5 class="card-title">Hambúrgueres Entregues</h5>
                      <p class="card-text display-5"><?= $totalHamburgueresEntregues ?></p>
                  </div>
              </div>
          </div>
          <div class="col-md-4">
              <div class="card text-bg-danger mb-3">
                  <div class="card-body">
                      <h5 class="card-title">Hambúrgueres a Entregar</h5>
                      <p class="card-text display-5"><?= $totalHamburgueresFaltam ?></p>
                  </div>
              </div>
          </div>
          <div class="col-md-4">
              <div class="card text-bg-warning mb-3">
                  <div class="card-body">
                      <h5 class="card-title">Hambúrgueres Não Entregues</h5>
                      <p class="card-text display-5"><?= $totalHamburgueresNaoEntregues ?></p>
                  </div>
              </div>
          </div>
          <div class="col-md-4">
              <div class="card text-bg-primary mb-3">
                  <div class="card-body">
                      <h5 class="card-title">Hambúrgueres Vendidos</h5>
                      <p class="card-text display-5"><?= $hamburgueresVendidos ?></p>
                  </div>
              </div>
          </div>
          <div class="col-md-4">
              <div class="card text-bg-dark mb-3">
                  <div class="card-body">
                      <h5 class="card-title">Total Vendido em Hambúrgueres</h5>
                      <p class="card-text display-5">R$ <?= number_format($totalVendido, 2, ',', '.') ?></p>
                  </div>
              </div>
          </div>
          <!-- Exibir estoque de todos os produtos cadastrados -->
          <?php if (isset($produtos) && is_array($produtos)): ?>
            <?php foreach ($produtos as $produto): ?>
              <?php if ($produto['id'] == 5): // Exibir apenas o hambúrguer ?>
                <div class="col-md-4">
                  <div class="card text-bg-secondary mb-3 border border-3 border-warning">
                    <div class="card-body">
                      <h5 class="card-title">Estoque de Hambúrguer</h5>
                      <p class="card-text display-5"><?= isset($produto['quantidade_disponivel']) ? htmlspecialchars($produto['quantidade_disponivel']) : '-' ?></p>
                      <small class="text-white-50">(Valor do campo quantidade_disponivel)</small>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
      </div>
      <?php if (!empty($clientesNaoEntregues)): ?>
      <div class="alert alert-danger mt-4">
        <strong>Cliente(s) com hambúrguer não entregue:</strong>
        <ul class="mb-0">
          <?php foreach ($clientesNaoEntregues as $cli): ?>
            <li>
              <strong>ID:</strong> <?= htmlspecialchars($cli['id']) ?>
              <?php if (!empty($cli['codigo'])): ?> - <strong>Código:</strong> <?= htmlspecialchars($cli['codigo']) ?><?php endif; ?>
              <?php if (!empty($cli['nome'])): ?> - <strong>Nome:</strong> <?= htmlspecialchars($cli['nome']) ?><?php endif; ?>
              <?php if (!empty($cli['telefone'])): ?> - <strong>Telefone:</strong> <?= htmlspecialchars($cli['telefone']) ?><?php endif; ?>
              - <strong>Qtd.:</strong> <?= htmlspecialchars($cli['quantidade']) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?><BR>
      <!-- Tabela de histórico logo abaixo dos cards -->
      <h3 class="mt-5" style="font-size:2.5rem; font-weight: bold;">Histórico de Entradas e Entregas</h3><BR>
      <div class="table-responsive">
          <table class="table table-bordered table-striped">
              <thead>
                  <tr>
                      <th>Ingresso ID</th>
                      <th>Nome</th>
                      <th>Qtd. Hambúrgueres</th>
                      <th>Hora Entrada na Fila</th>
                      <th>Hora Entrega</th>
                      <th>Tempo de Entrega</th>
                  </tr>
              </thead>
              <tbody>
              <?php
              // Montar um array indexado por ingresso_id para facilitar o match
              $entradasPorIngresso = [];
              foreach ($entradasFila as $e) {
                  $entradasPorIngresso[$e['ingresso_id']] = $e['hora_entrada'];
              }
              $entregasPorIngresso = [];
              foreach ($entregas as $e) {
                  $entregasPorIngresso[$e['ingresso_id']] = $e['hora_entrega'];
              }
              $todosIds = array_unique(array_merge(array_keys($entradasPorIngresso), array_keys($entregasPorIngresso)));
              $totalQtd = 0;
              $totalRegistros = 0;
              foreach ($todosIds as $id) {
                  echo '<tr>';
                  echo '<td>' . htmlspecialchars($id) . '</td>';
                  echo '<td>' . (isset($nomesIngressos[$id]) ? htmlspecialchars($nomesIngressos[$id]) : '-') . '</td>';
                  $qtd = (isset($quantidadesIngressos[$id]) ? (int)$quantidadesIngressos[$id] : 0);
                  echo '<td>' . ($qtd > 0 ? htmlspecialchars($qtd) : '-') . '</td>';
                  $totalQtd += $qtd;
                  $totalRegistros++;
                  $horaEntrada = isset($entradasPorIngresso[$id]) ? $entradasPorIngresso[$id] : null;
                  $horaEntrega = isset($entregasPorIngresso[$id]) ? $entregasPorIngresso[$id] : null;
                  echo '<td>' . ($horaEntrada ? date('d/m/Y H:i:s', strtotime($horaEntrada)) : '-') . '</td>';
                  echo '<td>' . ($horaEntrega ? date('d/m/Y H:i:s', strtotime($horaEntrega)) : '-') . '</td>';
                  // Calcular tempo de entrega em minutos e segundos
                  if ($horaEntrada && $horaEntrega) {
                      $entrada = strtotime($horaEntrada);
                      $entrega = strtotime($horaEntrega);
                      $segundos = $entrega - $entrada;
                      if ($segundos < 0) {
                          echo '<td class="text-danger">Erro nas datas</td>';
                      } else {
                          $minutos = floor($segundos / 60);
                          $segundosRestantes = $segundos % 60;
                          printf('<td>%02d:%02d</td>', $minutos, $segundosRestantes);
                      }
                  } else {
                      echo '<td>-</td>';
                  }
                  echo '</tr>';
              }
              ?>
              <!-- Linha de totais -->
              <tr class="table-secondary fw-bold">
                <td colspan="2">Totais</td>
                <td><?= $totalQtd ?></td>
                <td colspan="3">Total de registros: <?= $totalRegistros ?></td>
              </tr>
              </tbody>
          </table>
      </div>
      <a href="index.php" class="btn btn-secondary mt-3">Voltar ao início</a>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 