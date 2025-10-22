<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Ingressos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body * { visibility: hidden !important; }
            .print-area, .print-area * { visibility: visible !important; }
            .print-area { position: absolute; left: 0; top: 0; width: 100vw; margin: 0; padding: 0; background: #fff; }
            .no-print { display: none !important; }
        }
        body { overflow-x: hidden; }
        /* Responsividade extra para tabela */
        @media (max-width: 767.98px) {
          .table-responsive { font-size: 0.95rem; }
          .table thead { display: none; }
          .table tbody tr { display: block; margin-bottom: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 8px #0001; background: #fff; }
          .table tbody td { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border: none; border-bottom: 1px solid #f1f1f1; }
          .table tbody td:last-child { border-bottom: none; }
          .table tbody td:before {
            content: attr(data-label);
            font-weight: 600;
            color: #0d6efd;
            flex: 1 0 40%;
            text-align: left;
          }
          .table tbody td { flex: 1 0 60%; text-align: right; }
          .table tbody tr { border: 1px solid #e3e3e3; }
          .table .no-print { text-align: right; }
        }
        @media (max-width: 575.98px) {
          .display-5 { font-size: 1.5rem; }
          .lead { font-size: 1rem; }
          .btn-lg, .form-control-lg { font-size: 1rem; padding: 0.5rem 1rem; }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>
<div class="container-fluid bg-light min-vh-100">
  <div class="row">
    <div class="col-auto p-0">
      <?php include __DIR__ . '/../_menu_ingresso.php'; ?>
    </div>
    <div class="col mt-5">
      <div class="mb-4 text-center">
        <h1 class="display-5 fw-bold mb-2 text-primary">Ingressos Gerados</h1>
        <p class="lead text-secondary">Consulte, pesquise e imprima os ingressos do evento.</p>
      </div>
      <div class="card shadow-sm mb-4 p-3">
        <div class="row g-2 align-items-center">
          <div class="col-md-6">
            <input type="text" class="form-control form-control-lg" id="pesquisa-pessoa" placeholder="Pesquisar por nome ou telefone...">
          </div>
          <div class="col-md-6 text-end">
            <a href="index.php?c=ingresso" class="btn btn-secondary btn-lg no-print">Voltar</a>
          </div>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle bg-white shadow-sm rounded" id="tabela-ingressos">
          <thead class="table-primary">
              <tr>
                  <th>Código</th>
                  <th>Nome</th>
                  <th>Telefone</th>
                  <th>Qtd. Hambúrgueres</th>
                  <th class="no-print">Ações</th>
              </tr>
          </thead>
          <tbody>
          <?php foreach ($ingressos as $ing): ?>
              <tr>
                  <td data-label="Código"><span class="fw-bold text-primary"> <?= htmlspecialchars($ing['codigo'] ?? '') ?> </span></td>
                  <td data-label="Nome"><?= htmlspecialchars($ing['nome'] ?? '') ?></td>
                  <td data-label="Telefone"><?= htmlspecialchars($ing['telefone'] ?? '') ?></td>
                  <td data-label="Qtd. Hambúrgueres"><span class="badge bg-primary fs-6">
                    <?php
                    $qtd = isset($quantidadesPorProduto[$ing['id']]) ? $quantidadesPorProduto[$ing['id']] : (isset($ing['quantidade']) ? $ing['quantidade'] : 1);
                    echo htmlspecialchars($qtd);
                    ?>
                  </span></td>
                  <td class="no-print" data-label="Ações">
                      <button class="btn btn-outline-primary btn-sm w-100 my-1" onclick="imprimirIngresso('<?= $ing['id'] ?>', '<?= htmlspecialchars($ing['codigo']) ?>')">
                        <i class="bi bi-printer"></i> Imprimir
                      </button>
                  </td>
              </tr>
              <tr>
                  <td colspan="5">
                      <div id="ingresso-<?= $ing['id'] ?>" class="d-none print-area">
                          <div class="border p-4 text-center bg-white">
                              <h3>🍔 Festa do Hambúrguer</h3>
                              <div class="display-6 fw-bold mb-2">Código: <?= htmlspecialchars($ing['codigo'] ?? '') ?></div>
                              <div>Nome: <strong><?= htmlspecialchars($ing['nome'] ?? '') ?></strong></div>
                              <div>Telefone: <strong><?= htmlspecialchars($ing['telefone'] ?? '') ?></strong></div>
                              <div>Qtd. Hambúrgueres: <strong>
                                <?php
                                $qtd = isset($quantidadesPorProduto[$ing['id']]) ? $quantidadesPorProduto[$ing['id']] : (isset($ing['quantidade']) ? $ing['quantidade'] : 1);
                                echo htmlspecialchars($qtd);
                                ?>
                              </strong></div>
                              <div class="my-3 d-flex justify-content-center">
                                  <div id="qrcode-<?= $ing['id'] ?>"></div>
                              </div>
                              <div class="mt-3">Apresente este ingresso no evento.</div>
                          </div>
                      </div>
                  </td>
              </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<script>
function imprimirIngresso(id, codigo) {
    // Esconde todos os ingressos
    document.querySelectorAll('.print-area').forEach(e => e.classList.add('d-none'));
    var area = document.getElementById('ingresso-' + id);
    area.classList.remove('d-none');
    // Gera o QR Code localmente
    var qrDiv = document.getElementById('qrcode-' + id);
    qrDiv.innerHTML = '';
    new QRCode(qrDiv, {
        text: codigo,
        width: 150,
        height: 150
    });
    setTimeout(function() {
        window.print();
        area.classList.add('d-none');
    }, 300);
}
// Filtro de pesquisa por pessoa
const inputPesquisa = document.getElementById('pesquisa-pessoa');
inputPesquisa.addEventListener('input', function() {
    const termo = this.value.toLowerCase();
    const linhas = document.querySelectorAll('#tabela-ingressos tbody tr');
    linhas.forEach(function(tr) {
        // Só filtra linhas de dados (não as de impressão)
        if (tr.children.length < 4) return;
        const nome = tr.children[1].textContent.toLowerCase();
        const tel = tr.children[2].textContent.toLowerCase();
        if (nome.includes(termo) || tel.includes(termo) || termo === '') {
            tr.style.display = '';
        } else {
            tr.style.display = 'none';
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 