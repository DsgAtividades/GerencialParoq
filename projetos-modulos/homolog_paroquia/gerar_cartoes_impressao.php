<?php
require_once 'config/database.php';

// Inicializar conexão
$database = new Database();
$conn = $database->getConnection();

try {
    // Criar tabela se não existir
    $sql = "CREATE TABLE IF NOT EXISTS cartoes (
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
            $codigo = 'pspa' .implode('', $numeros);
            $hashCode = 'pspa' .md5($codigo);
            // Verificar se o código já existe
            $stmt = $conn->prepare("SELECT COUNT(*) FROM cartoes WHERE codigo = ?");
            $stmt->execute([$hashCode]);
            $existe = $stmt->fetchColumn() > 0;
        } while ($existe);

        try {
            // Inserir novo código
            $stmt = $conn->prepare("INSERT INTO cartoes (codigo) VALUES (?)");
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
    $stmt = $conn->query("SELECT codigo, data_geracao, usado FROM cartoes ORDER BY data_geracao DESC LIMIT 100");
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
            margin: 0 auto;
            width: 100px;
            height: 100px;
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
 

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
        
            <div class="no-print">
                <button class="btn btn-primary me-2" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir Cartões
                </button>
                <a href="gerar_cartoes.php" class="btn btn-secondary">
                            <i class="bi bi-back"></i> voltar
                </a>
            </div>
        </div>

           
        <?php 
        $controla_coluna = 0;
        ?>
        <div class="container">
                    <?php foreach ($todosCartoes as $cartao): ?>
                        <?php if($controla_coluna == 0): ?>
                            <div class="row">
                        <?php endif; ?>
                    
                        <div class="col-md-3">
                                <div class="text-center">
                                    <div class="qr-code" id="qr-<?= htmlspecialchars($cartao['codigo']) ?>"></div>
                                </div>
                        </div>
                        <?php 
                        $controla_coluna++;
                        if ($controla_coluna == 4){
                            $controla_coluna = 0;
                            echo "</div>";
                        }
                        endforeach; ?>
                    
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
            document.getElementById(elementId).innerHTML = qr.createImgTag(3);
        }

        // Gerar QR Codes para todos os cartões recém gerados
     
        <?php foreach ($todosCartoes as $cartao): ?>
        gerarQRCode('<?= $cartao['codigo'] ?>', 'qr-<?= $cartao['codigo'] ?>');
        <?php endforeach; ?>


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
                    qrDiv.innerHTML = qr.createImgTag(3);
                    document.getElementById('qrCodeModalCodigo').textContent = codigo;
                    var modal = new bootstrap.Modal(document.getElementById('qrCodeModal'));
                    modal.show();
                });
            });
        });
    </script>

   
</body>
</html>
<?php $conn = null; ?>
