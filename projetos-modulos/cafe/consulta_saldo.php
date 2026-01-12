<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('gerenciar_transacoes');

// Inicializar variáveis
$historico = [];
$qrcode = isset($_GET['qrcode']) ? $_GET['qrcode'] : '';
$participante = null;

// Se houver um QR code, buscar o histórico
if ($qrcode) {
    // Buscar participante e saldo
    $query = "
        SELECT 
            p.id_pessoa,
            p.nome,
            p.cpf,
            COALESCE(sc.saldo, 0) as saldo_atual
        FROM cafe_pessoas p
        LEFT JOIN cafe_cartoes c ON p.id_pessoa = c.id_pessoa
        LEFT JOIN cafe_saldos_cartao sc ON p.id_pessoa = sc.id_pessoa
        WHERE c.codigo = :qrcode AND c.usado = 1
        LIMIT 1
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['qrcode' => $qrcode]);
    $participante = $stmt->fetch();

    // Buscar histórico
    if ($participante) {
        $query = "
            SELECT h.*, p.nome, p.cpf
            FROM cafe_historico_saldo h
            JOIN cafe_pessoas p ON h.id_pessoa = p.id_pessoa
            JOIN cafe_cartoes c ON p.id_pessoa = c.id_pessoa
            WHERE c.codigo = :qrcode AND c.usado = 1
            ORDER BY h.data_operacao DESC
            LIMIT 50
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute(['qrcode' => $qrcode]);
        $historico = $stmt->fetchAll();
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Consulta Saldos</h1>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6" style="display: none;">
                    <form method="get" id="searchForm">
                        <input type="text" 
                               id="qrcode" 
                               name="qrcode" 
                               value="<?php echo htmlspecialchars($qrcode); ?>"
                               required>
                    </form>
                </div>
                <div class="col-md-12 text-center">
                    <?php if ($participante): ?>
                        <a href="consulta_saldo.php" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-x-circle"></i> Limpar
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-primary" type="button" id="btnLerQRCode" style="font-size: 0.9rem; padding: 0.375rem 0.75rem; width: 150px;">
                        <i class="bi bi-qr-code-scan"></i> Ler QR Code
                    </button>
                    <div id="qr-reader" style="display: none; max-width: 250px; margin: 0 auto;" class="mt-3"></div>
                </div>
            </div>

            <?php if ($qrcode): ?>
                <div class="text-center mt-4">
                    <?php if ($participante): ?>
                        <h3 class="mb-3"><?= htmlspecialchars($participante['nome']) ?></h3>
                        <?php if ($participante['cpf']): ?>
                            <p class="text-muted mb-3">CPF: <?= htmlspecialchars($participante['cpf']) ?></p>
                        <?php endif; ?>
                        <div class="h2 mb-4 <?= $participante['saldo_atual'] > 0 ? 'text-success' : 'text-danger' ?>">
                            R$ <?= number_format($participante['saldo_atual'], 2, ',', '.') ?>
                        </div>

                        <?php if (!empty($historico)): ?>
                            <div class="table-responsive mt-4">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Tipo</th>
                                            <th>Valor</th>
                                            <th>Motivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historico as $h): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i', strtotime($h['data_operacao'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $h['tipo_operacao'] === 'credito' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($h['tipo_operacao']); ?>
                                                    </span>
                                                </td>
                                                <td class="<?=$h['tipo_operacao'] === 'credito' ? 'text-success' : 'text-danger'; ?>">
                                                    R$ <?php echo number_format(abs($h['valor']), 2, ',', '.') ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($h['motivo']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mt-4">
                                <i class="bi bi-info-circle"></i> Nenhum histórico encontrado para este participante.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning mt-4">
                            <i class="bi bi-exclamation-triangle-fill"></i> Nenhum participante encontrado com o código informado.
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-qr-code-scan" style="font-size: 4rem; color: #6c757d;"></i>
                    <p class="mt-3 text-muted">Escaneie o QR Code do cartão para consultar o saldo</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const html5QrCode = new Html5Qrcode("qr-reader");
    let scanning = false;

    const btnLerQRCode = document.getElementById('btnLerQRCode');
    const qrReader = document.getElementById('qr-reader');
    
    if (btnLerQRCode) {
        btnLerQRCode.addEventListener('click', function() {
            if (!scanning) {
                qrReader.style.display = 'block';
                this.innerHTML = '<i class="bi bi-x-circle"></i> Cancelar Leitura';
                
                html5QrCode.start(
                    { facingMode: "environment" },
                    {
                        fps: 10,
                        qrbox: { width: 200, height: 200 },
                        aspectRatio: 1.0
                    },
                    (decodedText) => {
                        stopScanning();
                        document.getElementById('qrcode').value = decodedText;
                        document.getElementById('searchForm').submit();
                    },
                    (error) => {
                        // Ignorar erros de leitura
                    }
                ).catch((err) => {
                    console.error("Erro ao iniciar scanner:", err);
                    alert("Erro ao acessar a câmera. Verifique as permissões do navegador.");
                    stopScanning();
                });
                
                scanning = true;
            } else {
                stopScanning();
            }
        });
    }

    function stopScanning() {
        if (scanning) {
            html5QrCode.stop().then(() => {
                qrReader.style.display = 'none';
                if (btnLerQRCode) {
                    btnLerQRCode.innerHTML = '<i class="bi bi-qr-code-scan"></i> Ler QR Code';
                }
                scanning = false;
            }).catch((err) => {
                console.error("Erro ao parar scanner:", err);
                scanning = false;
            });
        }
    }

    window.addEventListener('beforeunload', stopScanning);
});
</script>

<?php include 'includes/footer.php'; ?>
