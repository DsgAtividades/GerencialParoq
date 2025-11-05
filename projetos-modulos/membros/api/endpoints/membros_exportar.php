<?php
/**
 * Endpoint: Exportar Membros
 * Método: GET
 * URL: /api/membros/exportar
 * Parâmetros: formato (pdf|xlsx), busca, status, pastoral
 */

// Limpar qualquer output anterior e iniciar buffer
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Desabilitar exibição de erros durante a exportação (mas manter log)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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
    // Limpar buffer antes de gerar arquivo binário
    while (ob_get_level()) {
        ob_end_clean();
    }
    
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
    
    // Headers - enviar antes de qualquer output
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
    // Gerar arquivo diretamente para output
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    
    // Salvar para output
    $writer->save('php://output');
    exit;
}

/**
 * Exporta membros em formato CSV otimizado para Excel
 */
function exportarCSVExcel($membros, $busca = '', $status = '', $pastoral = '') {
    // Limpar buffer antes de gerar arquivo
    while (ob_get_level()) {
        ob_end_clean();
    }
    
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
    // Limpar buffer antes de gerar arquivo
    while (ob_get_level()) {
        ob_end_clean();
    }
    
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
    // Limpar buffer antes de gerar arquivo binário
    while (ob_get_level()) {
        ob_end_clean();
    }
    
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
    
    // Debug: verificar se há páginas e elementos
    $reflection = new ReflectionClass($pdf);
    $pagesProperty = $reflection->getProperty('pages');
    $pagesProperty->setAccessible(true);
    $pages = $pagesProperty->getValue($pdf);
    
    error_log("PDF Debug: Total de páginas: " . count($pages));
    foreach ($pages as $pageNum => $pageData) {
        error_log("PDF Debug: Página $pageNum tem " . count($pageData['elements']) . " elementos");
    }
    
    // Headers PDF - DEVE ser enviado ANTES de qualquer output
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
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
        
        // Se width for 0, calcular baseado na largura da página
        if ($w == 0) {
            $w = $this->w - $this->marginLeft - $this->marginRight;
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
        echo $pdfContent;
    }
    
    private function generatePDF() {
        $objects = [];
        $offsets = [];
        
        // Objeto 1: Catalog
        $catalog = "<<\n/Type /Catalog\n/Pages 2 0 R\n>>";
        $objects[1] = $catalog;
        
        // Objeto 2: Pages
        $pageCount = count($this->pages);
        
        // Se não houver páginas, criar uma página vazia
        if ($pageCount == 0) {
            $this->AddPage('L', 'A4');
            $pageCount = 1;
        }
        
        $pageRefs = [];
        $objNum = 3;
        foreach ($this->pages as $pageNum => $pageData) {
            $pageRefs[] = "$objNum 0 R";
            $objNum += 2; // Cada página tem um objeto de conteúdo e um objeto de página
        }
        $pages = "<<\n/Type /Pages\n/Kids [" . implode(' ', $pageRefs) . "]\n/Count $pageCount\n>>";
        $objects[2] = $pages;
        
        // Objetos de páginas
        $objNum = 3;
        foreach ($this->pages as $pageNum => $pageData) {
            $orientation = $pageData['orientation'];
            $elements = $pageData['elements'];
            $pageHeight = ($orientation == 'L') ? 210 : 297; // altura em mm
            $pageWidth = ($orientation == 'L') ? 297 : 210; // largura em mm
            
            // Conteúdo da página
            $content = "q\n";
            
            foreach ($elements as $element) {
                if ($element['type'] == 'cell') {
                    // Converter mm para pontos (1mm = 2.83465 points)
                    $mmToPt = 2.83465;
                    $pageHeightPt = $pageHeight * $mmToPt;
                    
                    // Coordenadas X (não precisa inverter)
                    $x = $element['x'] * $mmToPt;
                    $w = $element['w'] * $mmToPt;
                    $h = $element['h'] * $mmToPt;
                    
                    // Inverter Y: PDF tem origem no canto inferior esquerdo (Y=0 está embaixo)
                    // Nossa coordenada Y está do topo (Y=0 está em cima)
                    // element['y'] é a posição do topo da célula em nosso sistema
                    // Converter para PDF: Y_pdf = altura_página - Y_elemento
                    // Mas precisamos do canto inferior esquerdo da célula: Y_pdf = altura_página - (Y_elemento + altura_elemento)
                    $y = $pageHeightPt - (($element['y'] + $element['h']) * $mmToPt);
                    
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
                        $content .= "0.5 w\n"; // Line width
                        $content .= "$x $y $w 0 re S\n"; // Top
                        $content .= "$x $y 0 $h re S\n"; // Left
                        $content .= ($x + $w) . " $y 0 $h re S\n"; // Right
                        $content .= "$x " . ($y + $h) . " $w 0 re S\n"; // Bottom
                    }
                    
                    // Text
                    if (!empty($element['text'])) {
                        $text = $element['text'];
                        // Escapar caracteres especiais do PDF
                        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
                        
                        // Converter tamanho da fonte de mm para pontos
                        $fontSizePt = $element['fontSize'] * 2.83465 * 0.6; // Ajustar para melhor legibilidade
                        
                        // Posicionamento do texto
                        // No PDF, Y=0 está no canto inferior esquerdo
                        // A célula está em $y (que é o canto inferior esquerdo após inversão)
                        // Para centralizar o texto verticalmente na célula:
                        // - Centro vertical da célula = Y + (altura / 2)
                        // - O texto é posicionado pela linha de base (baseline)
                        // - Ajustar para que a linha de base fique no centro menos metade do tamanho da fonte
                        $textX = $x + 3;
                        // Centralizar verticalmente: centro da célula menos metade da altura do texto
                        // A linha de base fica ~70% da altura da fonte acima do Y especificado
                        $textY = $y + ($h / 2) - ($fontSizePt * 0.7); // 0.7 é aproximadamente a altura da linha de base
                        
                        if ($element['align'] == 'C') {
                            // Centralizar: estimar largura do texto
                            $textWidth = mb_strlen($text, 'UTF-8') * $fontSizePt * 0.5;
                            $textX = $x + ($w / 2) - ($textWidth / 2);
                        } elseif ($element['align'] == 'R') {
                            // Alinhar à direita
                            $textWidth = mb_strlen($text, 'UTF-8') * $fontSizePt * 0.5;
                            $textX = $x + $w - $textWidth - 3;
                        } else {
                            // Alinhar à esquerda (padrão)
                            $textX = $x + 3;
                        }
                        
                        // Text color
                        $r = $element['textColor'][0] / 255;
                        $g = $element['textColor'][1] / 255;
                        $b = $element['textColor'][2] / 255;
                        
                        // Renderizar texto
                        // IMPORTANTE: No PDF, Td é relativo, então precisamos resetar o cursor primeiro
                        // Usar matriz de transformação de texto (Tm) para posicionamento absoluto
                        // Tm: [a b c d e f] onde e,f são a posição X,Y
                        $content .= "BT\n"; // Begin Text
                        $content .= "/F1 $fontSizePt Tf\n"; // Definir fonte e tamanho
                        $content .= "$r $g $b rg\n"; // Cor do texto (RGB)
                        // Resetar matriz de texto e posicionar absolutamente
                        // Matriz: [1 0 0 1 x y] = identidade com translação
                        $content .= "1 0 0 1 $textX $textY Tm\n"; // Matriz de transformação (posição absoluta)
                        $content .= "($text) Tj\n"; // Desenhar texto
                        $content .= "ET\n"; // End Text
                    }
                }
            }
            
            $content .= "Q\n";
            
            // Objeto de conteúdo
            $contentObj = $objNum++;
            $objects[$contentObj] = "<<\n/Length " . strlen($content) . "\n>>\nstream\n$content\nendstream";
            
            // Objeto de página
            $mediaBox = $orientation == 'L' ? "[0 0 841.89 595.28]" : "[0 0 595.28 841.89]";
            $pageObj = $objNum++;
            $objects[$pageObj] = "<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox $mediaBox\n/Contents $contentObj 0 R\n/Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >>\n>>";
        }
        
        // Construir PDF
        $pdf = "%PDF-1.4\n";
        
        // Escrever objetos e calcular offsets simultaneamente
        if (empty($objects)) {
            // Se não houver objetos, retornar PDF mínimo válido
            return "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids []\n/Count 0\n>>\nendobj\nxref\n0 3\n0000000000 65535 f \ntrailer\n<<\n/Size 3\n/Root 1 0 R\n>>\nstartxref\n0\n%%EOF\n";
        }
        
        $maxObjNum = max(array_keys($objects));
        
        foreach (range(1, $maxObjNum) as $i) {
            if (isset($objects[$i])) {
                $offsets[$i] = strlen($pdf);
                $pdf .= "$i 0 obj\n";
                $pdf .= $objects[$i];
                $pdf .= "\nendobj\n";
            }
        }
        
        // Xref table
        $xrefStart = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= "0 " . ($maxObjNum + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        
        foreach (range(1, $maxObjNum) as $i) {
            if (isset($offsets[$i])) {
                $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
            }
        }
        
        // Trailer
        $pdf .= "trailer\n";
        $pdf .= "<<\n/Size " . ($maxObjNum + 1) . "\n/Root 1 0 R\n>>\n";
        $pdf .= "startxref\n";
        $pdf .= $xrefStart . "\n";
        $pdf .= "%%EOF\n";
        
        return $pdf;
    }
}
?>
