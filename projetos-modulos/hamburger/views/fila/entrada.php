<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrada na Fila</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>body { overflow-x: hidden; }</style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col-auto p-0">
      <?php include __DIR__ . '/../_menu.php'; ?>
    </div>
    <div class="col mt-5">
      <h2>Adicionar à Fila de Espera</h2>
      <?php if (isset($erro)): ?>
          <div class="alert alert-danger"> <?= htmlspecialchars($erro) ?> </div>
      <?php endif; ?>
      <form method="post">
          <div class="mb-3">
              <label for="codigo" class="form-label">Código do Ingresso (ou leia o QR Code)</label>
              <div class="input-group mb-3">
                  <input type="text" class="form-control" id="codigo" name="codigo" required autofocus>
                  <button type="button" class="btn btn-outline-secondary" onclick="abrirLeitor()">Ler QR Code</button>
              </div>
          </div>
          <div id="qr-reader" style="display: none; max-width: 320px; margin: 0 auto;" class="mb-3"></div>
          <button type="button" class="btn btn-outline-danger mb-3" id="btn-fechar-qr" style="display:none;" onclick="stopScanning()">Fechar Leitor</button>
          <button type="submit" class="btn btn-success">Adicionar à Fila</button>
          <a href="index.php" class="btn btn-secondary">Voltar</a>
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
</script>
</body>
</html> 