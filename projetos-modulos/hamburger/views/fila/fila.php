<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fila de Espera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .atendendo { background: #d1e7dd !important; font-weight: bold; }
        .fila-lista { font-size: 2rem; }
        body { overflow-x: hidden; }
        .titulo-principal {
            font-size: 3rem;
            font-weight: 900;
            color: #212529;
            letter-spacing: 1px;
            text-shadow: 1px 2px 8px #e9ecef;
        }
        .subtitulo {
            font-size: 2rem;
            font-weight: 700;
            color: #495057;
            margin-top: 2.5rem;
            margin-bottom: 1.5rem;
        }
        .linha-divisoria {
            border-top: 4px solid #ffc107;
            margin: 3rem 0 2rem 0;
        }
        .alert-total {
            background: #fff3cd;
            border: 2px solid #ffe082;
        }
    </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col-auto p-0">
      <?php include __DIR__ . '/../_menu.php'; ?>
    </div>
    <div class="col mt-5">
      <h1 class="mb-4 titulo-principal text-center">Fila de Espera do Hambúrguer</h1>
      <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalEntrada">Adicionar à Fila</button>
      <!-- Modal Bootstrap -->
      <div class="modal fade" id="modalEntrada" tabindex="-1" aria-labelledby="modalEntradaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalEntradaLabel">Adicionar à Fila</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-0" style="min-height:600px;">
              <iframe src="index.php?c=fila&a=entrada" width="100%" height="600" frameborder="0" style="border:0;"></iframe>
            </div>
          </div>
        </div>
      </div>
      <?php
      // Calcular total de hambúrgueres na fila somando o campo quantidade da tabela ingresso_produto
      $produtoHamburguerId = 5;
      $totalHamburgueresFila = 0;
      $db = \Core\Database::getInstance()->getConnection();
      $idsFila = array_map(function($item) { return $item['ingresso_id']; }, $dados);
      if (!empty($idsFila)) {
          $placeholders = implode(',', array_fill(0, count($idsFila), '?'));
          $params = $idsFila;
          array_unshift($params, $produtoHamburguerId); // produto_id primeiro
          $sql = 'SELECT SUM(quantidade) as total FROM ingresso_produto WHERE produto_id = ? AND ingresso_id IN (' . $placeholders . ')';
          $stmt = $db->prepare($sql);
          $stmt->execute($params);
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          $totalHamburgueresFila = $row && $row['total'] !== null ? (int)$row['total'] : 0;
      }
      ?>
      <div class="alert alert-warning text-center py-4 alert-total mb-5">
        <span class="fw-bold fs-1">Total de hambúrgueres na fila: <?= $totalHamburgueresFila ?></span>
      </div>
      <div id="fila-lista">
        <?php if (empty($dados)): ?>
            <div class="alert alert-info">Nenhum cliente na fila.</div>
        <?php else: ?>
            <ol class="list-group list-group-numbered fila-lista">
                <?php foreach ($dados as $i => $item): ?>
                    <li class="list-group-item<?= $i === 0 ? ' atendendo' : '' ?>">
                        <?= htmlspecialchars($item['nome'] ?? '') ?>
                        <?php 
                        $qtd = isset($quantidadesPorProduto[$item['ingresso_id']]) ? $quantidadesPorProduto[$item['ingresso_id']] : (isset($item['quantidade']) ? $item['quantidade'] : 1);
                        if ($qtd > 1): ?>
                            <span class="badge bg-primary ms-2"><?= $qtd ?> hambúrgueres</span>
                        <?php elseif ($qtd == 1): ?>
                            <span class="badge bg-secondary ms-2">1 hambúrguer</span>
                        <?php endif; ?>
                        <span class="badge bg-dark ms-2 timer-badge" id="timer-entrada-<?= $item['id'] ?>" data-hora-entrada="<?= htmlspecialchars($item['hora_entrada']) ?>">
                            <span id="timer-text-<?= $item['id'] ?>">00:00:00</span>
                        </span>
                        <?php if ($i === 0): ?>
                            <span class="badge bg-success ms-2">Sendo atendido</span>
                        <?php endif; ?>
                        <form method="post" action="index.php?c=fila&a=entregar" class="d-inline float-end ms-2">
                            <input type="hidden" name="fila_id" value="<?= $item['id'] ?>">
                            <input type="hidden" name="ingresso_id" value="<?= $item['ingresso_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-primary">Marcar Entregue</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
      </div>
      <hr class="linha-divisoria">
      <h2 class="subtitulo text-center">Já Entregues</h2>
      <div id="entregues-lista">
          <?php if (empty($entregues)): ?>
              <div class="alert alert-info">Nenhum hambúrguer entregue ainda.</div>
          <?php else: ?>
              <ol class="list-group list-group-numbered">
                  <?php foreach ($entregues as $item): ?>
                      <li class="list-group-item">
                          <?= htmlspecialchars($item['nome']) ?>
                          <?php
                              $qtd = isset($quantidadesPorProduto[$item['ingresso_id']]) ? $quantidadesPorProduto[$item['ingresso_id']] : (isset($item['quantidade']) ? $item['quantidade'] : 1);
                              if ($qtd > 1):
                          ?>
                              <span class="badge bg-primary ms-2"><?= $qtd ?> hambúrgueres</span>
                          <?php elseif ($qtd == 1): ?>
                              <span class="badge bg-secondary ms-2">1 hambúrguer</span>
                          <?php endif; ?>
                          <span class="text-muted ms-2">(<?= date('d/m/Y H:i:s', strtotime($item['hora_entrada'])) ?>)</span>
                      </li>
                  <?php endforeach; ?>
              </ol>
          <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function atualizarFila() {
    $.get('index.php?c=fila&a=fila', function(data) {
        var html = $(data).find('#fila-lista').html();
        $('#fila-lista').html(html);
    });
}
setInterval(atualizarFila, 5000);

function startAllTimers() {
    document.querySelectorAll('.timer-badge').forEach(function(badge) {
        var id = badge.id.replace('timer-entrada-', '');
        var horaEntrada = badge.getAttribute('data-hora-entrada');
        if (!horaEntrada) return;
        var entradaDate = new Date(horaEntrada.replace(' ', 'T'));
        function updateTimer() {
            var now = new Date();
            var diff = Math.floor((now - entradaDate) / 1000);
            if (diff < 0) diff = 0;
            var h = Math.floor(diff / 3600).toString().padStart(2, '0');
            var m = Math.floor((diff % 3600) / 60).toString().padStart(2, '0');
            var s = (diff % 60).toString().padStart(2, '0');
            document.getElementById('timer-text-' + id).textContent = h + ':' + m + ':' + s;
        }
        updateTimer();
        setInterval(updateTimer, 1000);
    });
}
document.addEventListener('DOMContentLoaded', startAllTimers);
</script>
</body>
</html> 