<?php
/**
 * Endpoint: Exportar Membros
 * Método: GET
 * URL: /api/membros/exportar
 * Parâmetros: formato (pdf|xlsx), busca, status, pastoral
 */

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new MembrosDatabase();
    
    // Parâmetros de filtro
    $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $pastoral = isset($_GET['pastoral']) ? trim($_GET['pastoral']) : '';
    $formato = isset($_GET['formato']) ? strtolower(trim($_GET['formato'])) : 'xlsx';
    
    // Validar formato
    if (!in_array($formato, ['pdf', 'xlsx', 'excel', 'csv'])) {
        $formato = 'xlsx';
    }
    
    // Query para buscar membros (similar à listar)
    $query = "
        SELECT 
            m.id,
            m.nome_completo,
            m.apelido,
            m.email,
            COALESCE(m.celular_whatsapp, m.telefone_fixo) as telefone,
            m.status,
            m.paroquiano,
            m.comunidade_ou_capelania,
            m.data_entrada,
            m.data_nascimento,
            m.sexo,
            GROUP_CONCAT(DISTINCT p.nome SEPARATOR ', ') as pastorais
        FROM membros_membros m
        LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id
        LEFT JOIN membros_pastorais p ON mp.pastoral_id = p.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Adicionar filtros
    if (!empty($busca)) {
        $query .= " AND (
            m.nome_completo LIKE ? OR 
            m.apelido LIKE ? OR 
            m.email LIKE ? OR 
            m.celular_whatsapp LIKE ? OR 
            m.telefone_fixo LIKE ?
        )";
        $buscaParam = "%{$busca}%";
        $params[] = $buscaParam;
        $params[] = $buscaParam;
        $params[] = $buscaParam;
        $params[] = $buscaParam;
        $params[] = $buscaParam;
    }
    
    if (!empty($status)) {
        $query .= " AND m.status = ?";
        $params[] = $status;
    }
    
    if (!empty($pastoral)) {
        $query .= " AND m.id IN (SELECT membro_id FROM membros_membros_pastorais WHERE pastoral_id = ?)";
        $params[] = $pastoral;
    }
    
    // Agrupar por membro
    $query .= " GROUP BY m.id ORDER BY m.nome_completo";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Exportar conforme formato solicitado
    if ($formato === 'pdf') {
        exportarPDF($membros, $busca, $status, $pastoral);
    } elseif ($formato === 'xlsx' || $formato === 'excel') {
        // Tentar usar PhpSpreadsheet se disponível
        $phpspreadsheetPath = __DIR__ . '/../../../obras/vendor/autoload.php';
        if (file_exists($phpspreadsheetPath)) {
            try {
                exportarXLSXPhpSpreadsheet($membros);
            } catch (Exception $e) {
                error_log("Erro ao usar PhpSpreadsheet: " . $e->getMessage());
                // Fallback: CSV otimizado para Excel
                exportarCSVExcel($membros, $busca, $status, $pastoral);
            }
        } else {
            // Fallback: CSV otimizado para Excel
            exportarCSVExcel($membros, $busca, $status, $pastoral);
        }
    } elseif ($formato === 'csv') {
        exportarCSV($membros, $busca, $status, $pastoral);
    } else {
        exportarCSVExcel($membros, $busca, $status, $pastoral);
    }
    
} catch (Exception $e) {
    error_log("Erro ao exportar membros: " . $e->getMessage());
    
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Erro ao exportar membros: ' . $e->getMessage();
    exit;
}

/**
 * Exporta membros em formato XLSX usando PhpSpreadsheet
 */
function exportarXLSXPhpSpreadsheet($membros) {
    $phpspreadsheetPath = __DIR__ . '/../../../obras/vendor/autoload.php';
    if (!file_exists($phpspreadsheetPath)) {
        // Fallback para CSV se PhpSpreadsheet não estiver disponível
        exportarCSVExcel($membros, '', '', '');
        return;
    }
    
    require_once $phpspreadsheetPath;
    
    // Usar namespaces completos
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Membros');
    
    // Cabeçalhos
    $cabecalhos = [
        'Nome Completo',
        'Apelido',
        'Email',
        'Telefone',
        'Data de Nascimento',
        'Sexo',
        'Status',
        'Paroquiano',
        'Comunidade/Capelania',
        'Data de Entrada',
        'Pastorais'
    ];
    
    // Adicionar cabeçalhos
    $col = 'A';
    foreach ($cabecalhos as $cabecalho) {
        $sheet->setCellValue($col . '1', $cabecalho);
        $col++;
    }
    
    // Estilizar cabeçalhos
    $headerRange = 'A1:' . chr(ord('A') + count($cabecalhos) - 1) . '1';
    $sheet->getStyle($headerRange)->getFont()->setBold(true);
    $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
    $sheet->getStyle($headerRange)->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('4472C4');
    $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
    // Adicionar dados
    $row = 2;
    foreach ($membros as $membro) {
        $sheet->setCellValue('A' . $row, $membro['nome_completo'] ?? '');
        $sheet->setCellValue('B' . $row, $membro['apelido'] ?? '');
        $sheet->setCellValue('C' . $row, $membro['email'] ?? '');
        $sheet->setCellValue('D' . $row, $membro['telefone'] ?? '');
        $sheet->setCellValue('E' . $row, $membro['data_nascimento'] ?? '');
        $sheet->setCellValue('F' . $row, $membro['sexo'] ?? '');
        $sheet->setCellValue('G' . $row, traduzirStatus($membro['status'] ?? ''));
        $sheet->setCellValue('H' . $row, $membro['paroquiano'] ? 'Sim' : 'Não');
        $sheet->setCellValue('I' . $row, $membro['comunidade_ou_capelania'] ?? '');
        $sheet->setCellValue('J' . $row, $membro['data_entrada'] ?? '');
        $sheet->setCellValue('K' . $row, $membro['pastorais'] ?? '');
        $row++;
    }
    
    // Ajustar largura das colunas
    $sheet->getColumnDimension('A')->setWidth(30);
    $sheet->getColumnDimension('B')->setWidth(20);
    $sheet->getColumnDimension('C')->setWidth(30);
    $sheet->getColumnDimension('D')->setWidth(20);
    $sheet->getColumnDimension('E')->setWidth(18);
    $sheet->getColumnDimension('F')->setWidth(10);
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getColumnDimension('H')->setWidth(12);
    $sheet->getColumnDimension('I')->setWidth(25);
    $sheet->getColumnDimension('J')->setWidth(18);
    $sheet->getColumnDimension('K')->setWidth(30);
    
    // Nome do arquivo
    $nomeArquivo = 'membros_' . date('Y-m-d_His') . '.xlsx';
    
    // Headers
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
    header('Cache-Control: max-age=0');
    
    // Gerar arquivo
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

/**
 * Exporta membros em formato CSV otimizado para Excel
 */
function exportarCSVExcel($membros, $busca = '', $status = '', $pastoral = '') {
    $nomeArquivo = 'membros_' . date('Y-m-d_His') . '.csv';
    
    // Headers para CSV (Excel abre CSV nativamente)
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    
    $output = fopen('php://output', 'w');
    
    // BOM UTF-8 para Excel reconhecer corretamente
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalhos
    $cabecalhos = [
        'Nome Completo',
        'Apelido',
        'Email',
        'Telefone',
        'Data de Nascimento',
        'Sexo',
        'Status',
        'Paroquiano',
        'Comunidade/Capelania',
        'Data de Entrada',
        'Pastorais'
    ];
    
    fputcsv($output, $cabecalhos, ';'); // Ponto e vírgula para Excel brasileiro
    
    // Dados
    foreach ($membros as $membro) {
        $linha = [
            $membro['nome_completo'] ?? '',
            $membro['apelido'] ?? '',
            $membro['email'] ?? '',
            $membro['telefone'] ?? '',
            $membro['data_nascimento'] ?? '',
            $membro['sexo'] ?? '',
            traduzirStatus($membro['status'] ?? ''),
            $membro['paroquiano'] ? 'Sim' : 'Não',
            $membro['comunidade_ou_capelania'] ?? '',
            $membro['data_entrada'] ?? '',
            $membro['pastorais'] ?? ''
        ];
        
        fputcsv($output, $linha, ';');
    }
    
    fclose($output);
    exit;
}

/**
 * Exporta membros em formato CSV padrão
 */
function exportarCSV($membros, $busca = '', $status = '', $pastoral = '') {
    $nomeArquivo = 'membros_' . date('Y-m-d_His') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // BOM UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    $cabecalhos = [
        'Nome Completo',
        'Apelido',
        'Email',
        'Telefone',
        'Data de Nascimento',
        'Sexo',
        'Status',
        'Paroquiano',
        'Comunidade/Capelania',
        'Data de Entrada',
        'Pastorais'
    ];
    
    fputcsv($output, $cabecalhos);
    
    foreach ($membros as $membro) {
        $linha = [
            $membro['nome_completo'] ?? '',
            $membro['apelido'] ?? '',
            $membro['email'] ?? '',
            $membro['telefone'] ?? '',
            $membro['data_nascimento'] ?? '',
            $membro['sexo'] ?? '',
            traduzirStatus($membro['status'] ?? ''),
            $membro['paroquiano'] ? 'Sim' : 'Não',
            $membro['comunidade_ou_capelania'] ?? '',
            $membro['data_entrada'] ?? '',
            $membro['pastorais'] ?? ''
        ];
        
        fputcsv($output, $linha);
    }
    
    fclose($output);
    exit;
}

/**
 * Exporta membros em formato PDF
 */
function exportarPDF($membros, $busca = '', $status = '', $pastoral = '') {
    $nomeArquivo = 'membros_' . date('Y-m-d_His') . '.pdf';
    
    // Usar SimplePDF diretamente (não depende de arquivos de fonte externos)
    $pdf = new SimplePDF();
    $pdf->AddPage('L', 'A4'); // Landscape para mais colunas
    
    // Título
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Relatorio de Membros', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Informações de filtro (se houver)
    if (!empty($busca) || !empty($status) || !empty($pastoral)) {
        $pdf->SetFont('Arial', 'I', 10);
        $filtros = [];
        if (!empty($busca)) $filtros[] = "Busca: {$busca}";
        if (!empty($status)) $filtros[] = "Status: " . traduzirStatus($status);
        if (!empty($pastoral)) $filtros[] = "Pastoral: {$pastoral}";
        $pdf->Cell(0, 5, 'Filtros aplicados: ' . implode(' | ', $filtros), 0, 1);
        $pdf->Ln(3);
    }
    
    // Cabeçalhos da tabela
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(68, 114, 196);
    $pdf->SetTextColor(255, 255, 255);
    
    $larguras = [40, 30, 35, 25, 25, 15, 20, 20];
    $cabecalhos = ['Nome', 'Email', 'Telefone', 'Nascimento', 'Entrada', 'Sexo', 'Status', 'Paroquiano'];
    
    foreach ($cabecalhos as $i => $cabecalho) {
        $pdf->Cell($larguras[$i], 7, $cabecalho, 1, 0, 'C', true);
    }
    $pdf->Ln();
    
    // Dados
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(240, 240, 240);
    
    $fill = false;
    foreach ($membros as $membro) {
        // Verificar se precisa de nova página (A4 Landscape: 297mm altura)
        if ($pdf->GetY() > 280) {
            $pdf->AddPage('L', 'A4');
            // Reimprimir cabeçalhos
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(68, 114, 196);
            $pdf->SetTextColor(255, 255, 255);
            foreach ($cabecalhos as $i => $cabecalho) {
                $pdf->Cell($larguras[$i], 7, $cabecalho, 1, 0, 'C', true);
            }
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(240, 240, 240);
            $fill = false;
        }
        
        $pdf->Cell($larguras[0], 6, mb_substr($membro['nome_completo'] ?? '', 0, 25), 1, 0, 'L', $fill);
        $pdf->Cell($larguras[1], 6, mb_substr($membro['email'] ?? '', 0, 20), 1, 0, 'L', $fill);
        $pdf->Cell($larguras[2], 6, mb_substr($membro['telefone'] ?? '', 0, 15), 1, 0, 'L', $fill);
        $pdf->Cell($larguras[3], 6, $membro['data_nascimento'] ?? '', 1, 0, 'C', $fill);
        $pdf->Cell($larguras[4], 6, $membro['data_entrada'] ?? '', 1, 0, 'C', $fill);
        $pdf->Cell($larguras[5], 6, $membro['sexo'] ?? '', 1, 0, 'C', $fill);
        $pdf->Cell($larguras[6], 6, mb_substr(traduzirStatus($membro['status'] ?? ''), 0, 12), 1, 0, 'C', $fill);
        $pdf->Cell($larguras[7], 6, $membro['paroquiano'] ? 'Sim' : 'Nao', 1, 1, 'C', $fill);
        
        $fill = !$fill;
    }
    
    // Rodapé
    $pdf->SetY(-15);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 10, 'Total de membros: ' . count($membros) . ' | Gerado em ' . date('d/m/Y H:i:s'), 0, 0, 'C');
    
    // Output
    $pdf->Output('D', $nomeArquivo);
    exit;
}

/**
 * Traduz status para português
 */
function traduzirStatus($status) {
    $statusMap = [
        'ativo' => 'Ativo',
        'afastado' => 'Afastado',
        'em_discernimento' => 'Em Discernimento',
        'bloqueado' => 'Bloqueado'
    ];
    
    return $statusMap[$status] ?? $status;
}

/**
 * Classe PDF simples para gerar PDFs básicos
 */
class SimplePDF {
    private $pages = [];
    private $currentPage = 0;
    private $x = 20;
    private $y = 20;
    private $w = 210; // A4 portrait width
    private $h = 297; // A4 portrait height
    private $orientation = 'P'; // P = Portrait, L = Landscape
    private $marginLeft = 10;
    private $marginTop = 10;
    private $marginRight = 10;
    private $marginBottom = 10;
    private $fontSize = 12;
    private $fontFamily = 'Arial';
    private $fontStyle = '';
    private $fillColor = [255, 255, 255];
    private $textColor = [0, 0, 0];
    
    public function AddPage($orientation = 'P', $size = 'A4') {
        $this->currentPage++;
        $this->orientation = $orientation;
        $this->pages[$this->currentPage] = [
            'orientation' => $orientation,
            'elements' => []
        ];
        
        // Ajustar dimensões conforme orientação
        if ($orientation == 'L') {
            // Landscape: largura > altura
            $this->w = 297; // A4 landscape width
            $this->h = 210; // A4 landscape height
        } else {
            // Portrait: altura > largura
            $this->w = 210; // A4 portrait width
            $this->h = 297; // A4 portrait height
        }
        
        $this->x = $this->marginLeft;
        $this->y = $this->marginTop;
    }
    
    public function SetFont($family, $style = '', $size = 0) {
        $this->fontFamily = $family;
        $this->fontStyle = $style;
        if ($size > 0) {
            $this->fontSize = $size;
        }
    }
    
    public function SetFillColor($r, $g = null, $b = null) {
        if ($g === null) {
            $this->fillColor = [$r, $r, $r];
        } else {
            $this->fillColor = [$r, $g, $b];
        }
    }
    
    public function SetTextColor($r, $g = null, $b = null) {
        if ($g === null) {
            $this->textColor = [$r, $r, $r];
        } else {
            $this->textColor = [$r, $g, $b];
        }
    }
    
    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        if ($h == 0) {
            $h = $this->fontSize * 1.2;
        }
        
        $this->pages[$this->currentPage]['elements'][] = [
            'type' => 'cell',
            'x' => $this->x,
            'y' => $this->y,
            'w' => $w,
            'h' => $h,
            'text' => $txt,
            'border' => $border,
            'align' => $align,
            'fill' => $fill,
            'fontSize' => $this->fontSize,
            'fontFamily' => $this->fontFamily,
            'fontStyle' => $this->fontStyle,
            'fillColor' => $this->fillColor,
            'textColor' => $this->textColor
        ];
        
        if ($align == 'C') {
            // Center alignment handled in output
        } elseif ($align == 'R') {
            // Right alignment handled in output
        }
        
        $this->x += $w;
        if ($ln == 1) {
            $this->x = $this->marginLeft;
            $this->y += $h;
        }
    }
    
    public function Ln($h = 0) {
        if ($h == 0) {
            $h = $this->fontSize * 1.2;
        }
        $this->y += $h;
        $this->x = $this->marginLeft;
    }
    
    public function GetX() {
        return $this->x;
    }
    
    public function GetY() {
        return $this->y;
    }
    
    public function SetY($y) {
        $this->y = $y;
    }
    
    public function Output($dest = 'I', $name = '') {
        $pdfContent = $this->generatePDF();
        
        if ($dest == 'D' || $dest == 'F') {
            echo $pdfContent;
        } else {
            echo $pdfContent;
        }
    }
    
    private function generatePDF() {
        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n";
        
        $pageCount = count($this->pages);
        $pdf .= "2 0 obj\n<<\n/Type /Pages\n/Kids [";
        for ($i = 3; $i < 3 + $pageCount; $i++) {
            $pdf .= "$i 0 R ";
        }
        $pdf .= "]\n/Count $pageCount\n>>\nendobj\n";
        
        $objNum = 3;
        foreach ($this->pages as $pageNum => $pageData) {
            $orientation = $pageData['orientation'];
            $elements = $pageData['elements'];
            $pageHeight = ($orientation == 'L') ? 210 : 297; // altura em mm
            
            $content = "q\n";
            $content .= "BT\n";
            $content .= "/F1 12 Tf\n";
            
            foreach ($elements as $element) {
                if ($element['type'] == 'cell') {
                    // Converter mm para pontos (1mm = 2.83465 points)
                    $mmToPt = 2.83465;
                    $x = $element['x'] * $mmToPt;
                    $y = ($pageHeight - $element['y']) * $mmToPt; // Inverter Y (PDF tem origem no canto inferior esquerdo)
                    $w = $element['w'] * $mmToPt;
                    $h = $element['h'] * $mmToPt;
                    
                    // Fill
                    if ($element['fill']) {
                        $r = $element['fillColor'][0] / 255;
                        $g = $element['fillColor'][1] / 255;
                        $b = $element['fillColor'][2] / 255;
                        $content .= "$r $g $b rg\n";
                        $content .= "$x $y $w $h re f\n";
                    }
                    
                    // Border
                    if ($element['border']) {
                        $content .= "0 0 0 RG\n";
                        $content .= "1 w\n"; // Line width
                        $content .= "$x $y $w $h re S\n";
                    }
                    
                    // Text
                    $text = $element['text'];
                    // Escapar caracteres especiais do PDF
                    $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
                    
                    $fontSizePt = $element['fontSize'] * $mmToPt * 0.35; // Ajustar tamanho da fonte
                    $textX = $x + 2;
                    $textY = $y - $h + ($element['fontSize'] * $mmToPt * 0.4);
                    
                    if ($element['align'] == 'C') {
                        $textWidth = strlen($text) * $fontSizePt * 0.5;
                        $textX = $x + ($w / 2) - ($textWidth / 2);
                    } elseif ($element['align'] == 'R') {
                        $textWidth = strlen($text) * $fontSizePt * 0.5;
                        $textX = $x + $w - $textWidth - 2;
                    }
                    
                    $content .= "0 0 0 rg\n";
                    $content .= "BT\n";
                    $content .= "/F1 " . $fontSizePt . " Tf\n";
                    $content .= "$textX $textY Td\n";
                    $content .= "($text) Tj\n";
                    $content .= "ET\n";
                }
            }
            
            $content .= "ET\n";
            $content .= "Q\n";
            
            $contentObj = $objNum++;
            $pdf .= "$contentObj 0 obj\n<<\n/Length " . strlen($content) . "\n>>\nstream\n$content\nendstream\nendobj\n";
            
            // MediaBox para A4 (em pontos: 595.28 x 841.89 para portrait, invertido para landscape)
            $mediaBox = $orientation == 'L' ? "[0 0 841.89 595.28]" : "[0 0 595.28 841.89]";
            $pdf .= "$objNum 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox $mediaBox\n/Contents $contentObj 0 R\n/Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >>\n>>\nendobj\n";
            $objNum++;
        }
        
        $xref = strlen($pdf);
        $pdf .= "xref\n0 $objNum\n0000000000 65535 f \n";
        for ($i = 1; $i < $objNum; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $xref);
            $xref += 50; // Aproximação
        }
        
        $pdf .= "trailer\n<<\n/Size $objNum\n/Root 1 0 R\n>>\nstartxref\n$xref\n%%EOF\n";
        
        return $pdf;
    }
}
?>
