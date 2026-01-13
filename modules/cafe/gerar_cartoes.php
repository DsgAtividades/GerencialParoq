<?php
require_once 'config/database.php';

// Inicializar conexão
$database = new Database();
$conn = $database->getConnection();

try {
    // Criar tabela se não existir
    $sql = "CREATE TABLE IF NOT EXISTS cafe_cartoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(11) UNIQUE NOT NULL,
        data_geracao DATETIME DEFAULT CURRENT_TIMESTAMP,
        usado BOOLEAN DEFAULT FALSE
    )";
    $conn->exec($sql);

    function gerarNumeroAleatorio() {
        $numeros = range(0, 9);
        shuffle($numeros);
        return array_slice($numeros, 0, 8);
    }

    function gerarCartaoUnico($conn) {
        do {
            $numeros = gerarNumeroAleatorio();
            $codigo =  'pspa' .implode('', $numeros);
            $hashCode = 'pspa' .md5($codigo);
            // Verificar se o código já existe
            $stmt = $conn->prepare("SELECT COUNT(*) FROM cafe_cartoes WHERE codigo = ?");
            $stmt->execute([$hashCode]);
            $existe = $stmt->fetchColumn() > 0;
        } while ($existe);

        try {
            // Inserir novo código
            $stmt = $conn->prepare("INSERT INTO cafe_cartoes (codigo) VALUES (?)");
            $stmt->execute([$hashCode]);
            return $hashCode;
        } catch (PDOException $e) {
            // Se houver erro na inserção, tenta gerar outro código
            return gerarCartaoUnico($conn);
        }
    }

    $cartoes = [];
    $erros = [];
    $mensagem = '';
    $tipo = '';

    // Gera cartões só se o botão for clicado (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar_cartoes'])) {
        try {
            for ($i = 0; $i < 20; $i++) {
                $cartoes[] = gerarCartaoUnico($conn);
            }
            $mensagem = "Cartões gerados com sucesso!";
            $tipo = "success";
        } catch (Exception $e) {
            $erros[] = "Erro ao gerar cartões: " . $e->getMessage();
            $tipo = "danger";
        }
    }

    // Sempre busca o histórico para exibir
    $stmt = $conn->query("SELECT codigo, data_geracao, usado FROM cafe_cartoes ORDER BY data_geracao DESC LIMIT 100");
    $todosCartoes = $stmt->fetchAll();

} catch (PDOException $e) {
    $erros[] = "Erro no banco de dados: " . $e->getMessage();
    $todosCartoes = [];
    $tipo = "danger";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Cartões - Festa Junina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <!-- QR Code library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .cartao-numero {
            font-family: monospace;
            font-size: 1.2rem;
            letter-spacing: 1px;
        }
        .data-geracao {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .cartao-usado {
            opacity: 0.6;
            background-color: #f8f9fa;
        }
        .qr-code {
            margin: 10px auto;
            width: 128px;
            height: 128px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .card {
                break-inside: avoid;
                margin: 10px !important;
                border: 1px solid #ddd !important;
                page-break-inside: avoid;
            }
            .container {
                width: 100% !important;
                max-width: none !important;
                padding: 0 !important;
            }
            .cartao-usado {
                display: none !important;
            }
            .qr-code {
                width: 200px;
                height: 200px;
            }
            .cartao-numero {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gerar Cartões</h1>
            <div class="no-print">
                <a href="gerar_cartoes_impressao.php" class="btn btn-primary me-2">
                    <i class="bi bi-printer"></i> Imprimir Cartões
                </a>
                <form method="post" style="display:inline;">
                    <button class="btn btn-success" type="submit" name="gerar_cartoes">
                        <i class="bi bi-arrow-clockwise"></i> Gerar Novos
                    </button>
                </form>
            </div>
        </div>

        <?php if (!empty($erros)): ?>
            <?php foreach ($erros as $erro): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-<?= $tipo ?>" role="alert">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($cartoes)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Cartões Recém Gerados</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($cartoes as $cartao): ?>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <!--<h6 class="card-subtitle mb-2 text-muted">Cartão de Acesso</h6>-->
                                    <div class="qr-code" id="qr-<?= htmlspecialchars($cartao) ?>"></div>
                                    <!--<p class="cartao-numero mb-2">?= htmlspecialchars($cartao) ?></p>
                                    <div class="data-geracao">
                                        Gerado em: <= date('d/m/Y H:i') ?> 
                                    </div>-->
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card mt-4 no-print">
            <div class="card-header">
                <h5 class="card-title mb-0">Histórico de Cartões</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Data de Geração</th>
                                <th>Status</th>
                                <th>Visualizar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todosCartoes as $cartao): ?>
                            <tr class="<?= $cartao['usado'] ? 'cartao-usado' : '' ?>">
                                <td class="cartao-numero"><?= htmlspecialchars($cartao['codigo']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($cartao['data_geracao'])) ?></td>
                                <td>
                                    <?php if ($cartao['usado']): ?>
                                        <span class="badge bg-secondary">Usado</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Disponível</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-outline-primary btn-sm visualizar-qr" 
                                        data-codigo="<?= htmlspecialchars($cartao['codigo']) ?>" 
                                        title="Visualizar QR Code">
                                        <i class="bi bi-qr-code"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="back"></div> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gerar QR Codes para cada cartão
        function gerarQRCode(texto, elementId) {
            var qr = qrcode(0, 'M');
            qr.addData(texto);
            qr.make();
            document.getElementById(elementId).innerHTML = qr.createImgTag(4);
        }

        // Gerar QR Codes para todos os cartões recém gerados
        <?php if (!empty($cartoes)): ?>
        <?php foreach ($cartoes as $cartao): ?>
        gerarQRCode('<?= $cartao ?>', 'qr-<?= $cartao ?>');
        <?php endforeach; ?>
        <?php endif; ?>

        document.addEventListener('DOMContentLoaded', function() {
            // Evento para abrir o modal e gerar o QR code
            document.querySelectorAll('.visualizar-qr').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var codigo = this.getAttribute('data-codigo');
                    var qrDiv = document.getElementById('qrCodeModalImg');
                    qrDiv.innerHTML = '';
                    var qr = qrcode(0, 'M');
                    qr.addData(codigo);
                    qr.make();
                    qrDiv.innerHTML = qr.createImgTag(4);
                    document.getElementById('qrCodeModalCodigo').textContent = codigo;
                    var modal = new bootstrap.Modal(document.getElementById('qrCodeModal'));
                    modal.show();
                });
            });
        });
    </script>

    <!-- Modal para exibir QR Code -->
    <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="qrCodeModalLabel">QR Code do Cartão</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <div class="modal-body text-center">
            <div id="qrCodeModalImg"></div>
            <div id="qrCodeModalCodigo" class="mt-2 text-muted"></div>
          </div>
        </div>
      </div>
    </div>
</body>
</html>
<?php $conn = null; ?>
