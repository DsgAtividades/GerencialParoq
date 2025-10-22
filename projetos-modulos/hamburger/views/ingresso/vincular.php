<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincular Ingresso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { overflow-x: hidden; }</style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col-auto p-0">
      <?php include __DIR__ . '/../_menu.php'; ?>
    </div>
    <div class="col mt-5">
      <div class="mb-4 text-center">
        <h1 class="display-5 fw-bold mb-2">Vincular Ingresso a Pessoa</h1>
        <p class="lead text-secondary">Preencha os dados do cliente e selecione os produtos desejados para este ingresso.</p>
      </div>
      <?php if (!empty($mensagem)): ?>
        <div class="alert alert-info"> <?= htmlspecialchars($mensagem) ?> </div>
      <?php endif; ?>
      <form method="post" class="bg-white p-4 rounded shadow-sm">
        <input type="hidden" id="novo_codigo" name="novo_codigo">
        <div class="mb-3">
          <label for="codigo" class="form-label">Código do Ingresso (ou leia o QR Code)</label>
          <div class="input-group mb-3">
            <input type="text" class="form-control" id="codigo" name="codigo" required autofocus>
            <button type="button" class="btn btn-outline-secondary" onclick="abrirLeitor()">Ler QR Code</button>
          </div>
        </div>
        <div id="qr-reader" style="display: none; max-width: 320px; margin: 0 auto;" class="mb-3"></div>
        <button type="button" class="btn btn-outline-danger mb-3" id="btn-fechar-qr" style="display:none;" onclick="stopScanning()">Fechar Leitor</button>
        <div class="row g-3">
          <div class="col-md-6">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
          </div>
          <div class="col-md-6">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" class="form-control" id="telefone" name="telefone" required maxlength="20" placeholder="(99) 99999-9999">
          </div>
        </div>
        <hr class="my-4">
        <div class="mb-3">
          <label class="form-label fs-5"></label>
          <div class="mb-2 text-muted text-center small">Toque em + para adicionar produtos ao ingresso.</div>
          <div class="row g-3" id="produtos-lista">
            <?php foreach ($produtos as $produto): ?>
              <div class="col-12 col-sm-6 col-md-4">
                <div class="card h-100 produto-card shadow-sm border-0" data-produto-id="<?= $produto['id'] ?>">
                  <div class="card-body d-flex flex-column align-items-center justify-content-between">
                    <h5 class="card-title mb-2 text-primary text-center"><?= htmlspecialchars($produto['nome']) ?></h5>
                    <p class="card-text mb-1 text-center fw-bold">R$ <?= number_format($produto['preco'],2,',','.') ?></p>
                    <?php if (!empty($produto['descricao'])): ?>
                      <p class="text-muted small mb-2 text-center"><?= htmlspecialchars($produto['descricao']) ?></p>
                    <?php endif; ?>
                    <div class="input-group input-group-sm mt-2 w-75 mx-auto">
                      <button type="button" class="btn btn-outline-primary fw-bold" onclick="alterarQtd(<?= $produto['id'] ?>, -1)">-</button>
                      <input type="number" class="form-control text-center border-primary fw-bold" name="quantidade_produto[<?= $produto['id'] ?>]" id="qtd-produto-<?= $produto['id'] ?>" value="0" min="0" style="max-width:60px;" readonly>
                      <button type="button" class="btn btn-outline-primary fw-bold" onclick="alterarQtd(<?= $produto['id'] ?>, 1)">+</button>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="d-flex justify-content-between mt-4">
          <button type="submit" class="btn btn-success btn-lg px-4">Vincular</button>
          <a href="index.php?c=ingresso&a=listar" class="btn btn-secondary btn-lg px-4">Voltar</a>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
const html5QrCode = new Html5Qrcode("qr-reader");
let scanning = false;
function abrirLeitor() {
    const qrReader = document.getElementById('qr-reader');
    const btnFecharQr = document.getElementById('btn-fechar-qr');
    if (!scanning) {
        qrReader.style.display = 'block';
        btnFecharQr.style.display = 'inline-block';
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 200, height: 200 }, aspectRatio: 1.0 },
            (decodedText) => {
                stopScanning();
                document.getElementById('codigo').value = decodedText;
                document.getElementById('novo_codigo').value = decodedText;
            },
            (error) => { /* Ignorar erros de leitura */ }
        ).catch((err) => {
            alert("Erro ao acessar a câmera. Verifique as permissões do navegador.");
            qrReader.style.display = 'none';
            btnFecharQr.style.display = 'none';
        });
        scanning = true;
    } else {
        stopScanning();
    }
}
function stopScanning() {
    const qrReader = document.getElementById('qr-reader');
    const btnFecharQr = document.getElementById('btn-fechar-qr');
    if (scanning) {
        html5QrCode.stop().then(() => {
            qrReader.style.display = 'none';
            btnFecharQr.style.display = 'none';
            scanning = false;
        });
    }
}
window.addEventListener('beforeunload', stopScanning);
function alterarQtd(id, delta) {
    var input = document.getElementById('qtd-produto-' + id);
    var val = parseInt(input.value) || 0;
    val += delta;
    if (val < 0) val = 0;
    input.value = val;
    // Destacar card se selecionado
    var card = document.querySelector('.produto-card[data-produto-id="' + id + '"]');
    if (val > 0) {
        card.classList.add('border-primary', 'shadow-lg');
    } else {
        card.classList.remove('border-primary', 'shadow-lg');
    }
}
// Torna o card inteiro clicável para incrementar a quantidade
window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.produto-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            // Evita conflito com botões - e +
            if (e.target.tagName === 'BUTTON') return;
            var id = card.getAttribute('data-produto-id');
            alterarQtd(id, 1);
        });
    });
});
</script>
<style>
.produto-card {
  transition: box-shadow 0.2s, border-color 0.2s;
  cursor: pointer;
  box-shadow: 0 4px 24px 0 rgba(0,0,0,0.18) !important;
  max-width: 320px;
  margin-left: auto;
  margin-right: auto;
}
.produto-card.border-primary {
  border-width: 2px !important;
  box-shadow: 0 8px 32px 0 rgba(13,110,253,0.25) !important;
}
.produto-card:active {
  box-shadow: 0 0 0 0.2rem #0d6efd33 !important;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($sucesso)): ?>
<script>
Swal.fire({
  icon: 'success',
  title: 'Sucesso!',
  text: 'Produtos vinculados ao ingresso com sucesso.',
  confirmButtonText: 'OK'
});
</script>
<?php endif; ?>
</body>
</html> 