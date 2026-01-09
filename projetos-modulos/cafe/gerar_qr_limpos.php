<?php
require_once 'config/database.php';
require_once 'fpdf.php';

$database = new Database();
$conn = $database->getConnection();

try {
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
            $codigo = 'pspa' .implode('', $numeros);
            $hashCode = 'pspa' .md5($codigo);
            $stmt = $conn->prepare("SELECT COUNT(*) FROM cafe_cartoes WHERE codigo = ?");
            $stmt->execute([$hashCode]);
            $existe = $stmt->fetchColumn() > 0;
        } while ($existe);

        try {
            $stmt = $conn->prepare("INSERT INTO cafe_cartoes (codigo) VALUES (?)");
            $stmt->execute([$hashCode]);
            return $hashCode;
        } catch (PDOException $e) {
            return gerarCartaoUnico($conn);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar_pdf'])) {
        $quantidade_cartoes = intval($_POST['quantidade_cartoes'] ?? 12);
        if ($quantidade_cartoes < 1) $quantidade_cartoes = 1;
        if ($quantidade_cartoes > 100) $quantidade_cartoes = 100;
        
        $cartoes = [];
        for ($i = 0; $i < $quantidade_cartoes; $i++) {
            $cartoes[] = gerarCartaoUnico($conn);
        }
        
        gerarPDFQRLimpos($cartoes);
        exit;
    }

} catch (PDOException $e) {
    // Silenciar erros na versão minimalista
}

function baixarQRCode($texto) {
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&format=png&data=" . urlencode($texto);
    $context = stream_context_create([
        'http' => [
            'timeout' => 5, // Aumentar timeout para 15 segundos
            'user_agent' => 'Mozilla/5.0 (compatible; FPDF QR Generator)',
            'method' => 'GET',
            'header' => [
                'Connection: close',
                'Accept: image/png'
            ]
        ]
    ]);
    
    // Tentar baixar com retry em caso de falha
    $tentativas = 3;
    for ($i = 0; $i < $tentativas; $i++) {
        $qr_image = @file_get_contents($qr_url, false, $context);
        if ($qr_image !== false) {
            return $qr_image;
        }
        // Aguardar um pouco antes de tentar novamente
        usleep(500000); // 0.5 segundos
    }
    
    return false;
}

function gerarPDFQRLimpos($cartoes) {
    // Aumentar tempo limite de execução para muitos QR codes
    set_time_limit(300); // 5 minutos
    ini_set('memory_limit', '256M'); // Aumentar memória disponível
    
    // Configurar PDF com página personalizada 4x4 cm
    $pdf = new FPDF('P', 'mm', array(40, 40)); // 4x4 cm em modo retrato
    $pdf->SetMargins(2, 2, 2);
    $pdf->SetAutoPageBreak(false);
    
    // Dimensões da página personalizada 4x4 cm
    $pageWidth = 40;  // 4 cm
    $pageHeight = 40; // 4 cm
    
    // Tamanho do QR code: 3x3 cm (30mm)
    $qrSize = 30; // 3cm
    
    // Mostrar progresso para evitar timeouts em navegadores
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    $totalCartoes = count($cartoes);
    $contador = 0;
    
    foreach ($cartoes as $cartao) {
        $contador++;
        
        // Flush output periodicamente para evitar timeout do navegador
        if ($contador % 10 == 0) {
            if (connection_status() != CONNECTION_NORMAL) {
                break; // Parar se a conexão foi perdida
            }
        }
        $pdf->AddPage();
        // Centralizar o QR code na página
        $qrX = ($pageWidth - $qrSize) / 2;
        $qrY = ($pageHeight - $qrSize) / 2;
        
        $qrImage = baixarQRCode($cartao);
        
        if ($qrImage) {
            $tempFile = tempnam(sys_get_temp_dir(), 'qr') . '.png';
            file_put_contents($tempFile, $qrImage);
            try {
                $pdf->Image($tempFile, $qrX, $qrY, $qrSize, $qrSize, 'PNG');
                @unlink($tempFile);
            } catch (Exception $e) {
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->SetLineWidth(0.5);
                $pdf->Rect($qrX, $qrY, $qrSize, $qrSize);
                $pdf->Line($qrX, $qrY, $qrX + $qrSize, $qrY + $qrSize);
                $pdf->Line($qrX + $qrSize, $qrY, $qrX, $qrY + $qrSize);
                @unlink($tempFile);
            }
        } else {
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetLineWidth(0.5);
            $pdf->Rect($qrX, $qrY, $qrSize, $qrSize);
            $pdf->Line($qrX, $qrY, $qrX + $qrSize, $qrY + $qrSize);
            $pdf->Line($qrX + $qrSize, $qrY, $qrX, $qrY + $qrSize);
        }
    }
    
    // Gerar número sequencial incremental
    $contadorFile = 'contador_pdf.txt';
    
    // Ler o último número usado
    if (file_exists($contadorFile)) {
        $ultimoNumero = intval(file_get_contents($contadorFile));
    } else {
        $ultimoNumero = 0;
    }
    
    // Incrementar e salvar o novo número
    $proximoNumero = $ultimoNumero + 1;
    file_put_contents($contadorFile, $proximoNumero);
    
    // Formatar o nome do arquivo
    $numeroSequencial = str_pad($proximoNumero, 2, '0', STR_PAD_LEFT);
    $filename = 'arq_' . $numeroSequencial . '.pdf';
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    $pdf->Output('D', $filename);
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar QR Codes Limpos - Paróquia São José</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            padding: 12px 30px;
            font-size: 1.1rem;
        }
        .form-control {
            padding: 12px;
            font-size: 1.1rem;
        }
        .info-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="info-box">
                    <h3 class="text-center mb-3">
                        <i class="bi bi-qr-code-scan text-primary me-2"></i>
                        QR Codes Limpos
                    </h3>
                    <div class="row text-center">
                        <div class="col-md-3">
                            <i class="bi bi-grid-3x3-gap-fill text-success fs-1"></i>
                            <p class="mt-2"><strong>Layout</strong><br>3×4 por página (12 QR codes)</p>
                        </div>
                        <div class="col-md-3">
                            <i class="bi bi-file-earmark-pdf text-danger fs-1"></i>
                            <p class="mt-2"><strong>Página</strong><br>3x3cm</p>
                        </div>
                        <div class="col-md-3">
                            <i class="bi bi-qr-code text-primary fs-1"></i>
                            <p class="mt-2"><strong>QR Code</strong><br>3×3 cm cada</p>
                        </div>
                        <div class="col-md-3">
                            <i class="bi bi-printer text-info fs-1"></i>
                            <p class="mt-2"><strong>Impressão</strong><br>Direta 10×15</p>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header text-center">
                        <h4 class="mb-0">
                            <i class="bi bi-qr-code text-primary me-2"></i>
                            Gerar QR Codes (Apenas Códigos)
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="post">
                            <div class="mb-4">
                                <label for="quantidade_cartoes" class="form-label">Quantidade de QR Codes</label>
                                <input type="number" 
                                       class="form-control text-center" 
                                       id="quantidade_cartoes"
                                       name="quantidade_cartoes" 
                                       min="1" 
                                       max="100" 
                                       value="12" 
                                       required>
                                <div class="form-text text-center">Entre 1 e 100 QR codes</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" name="gerar_pdf" class="btn btn-primary">
                                    <i class="bi bi-download me-2"></i>
                                    Baixar PDF com QR Codes Limpos
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="gerar_cartoes.php" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-2"></i>
                        Menu Principal
                    </a>
                    <a href="gerar_cartoes_pdf_final.php" class="btn btn-outline-info">
                        <i class="bi bi-card-text me-2"></i>
                        Cartões com Informações
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn = null; ?> 