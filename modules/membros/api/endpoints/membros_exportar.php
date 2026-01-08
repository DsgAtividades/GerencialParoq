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
            m.cpf,
            m.status,
            m.paroquiano,
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
    
    error_log("Exportar: Query executada - " . count($membros) . " membros encontrados");
    if (count($membros) > 0) {
        error_log("Exportar: Primeiro membro - " . json_encode($membros[0]));
    }
    
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
        'CPF',
        'Data de Nascimento',
        'Sexo',
        'Status',
        'Paroquiano',
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
        $sheet->setCellValue('E' . $row, $membro['cpf'] ?? '');
        $sheet->setCellValue('F' . $row, $membro['data_nascimento'] ?? '');
        $sheet->setCellValue('G' . $row, $membro['sexo'] ?? '');
        $sheet->setCellValue('H' . $row, traduzirStatus($membro['status'] ?? ''));
        $sheet->setCellValue('I' . $row, $membro['paroquiano'] ? 'Sim' : 'Não');
        $sheet->setCellValue('J' . $row, $membro['data_entrada'] ?? '');
        $sheet->setCellValue('K' . $row, $membro['pastorais'] ?? '');
        $row++;
    }
    
    // Ajustar largura das colunas
    $sheet->getColumnDimension('A')->setWidth(30);
    $sheet->getColumnDimension('B')->setWidth(20);
    $sheet->getColumnDimension('C')->setWidth(30);
    $sheet->getColumnDimension('D')->setWidth(20);
    $sheet->getColumnDimension('E')->setWidth(15);
    $sheet->getColumnDimension('F')->setWidth(18);
    $sheet->getColumnDimension('G')->setWidth(10);
    $sheet->getColumnDimension('H')->setWidth(15);
    $sheet->getColumnDimension('I')->setWidth(12);
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
        'CPF',
        'Data de Nascimento',
        'Sexo',
        'Status',
        'Paroquiano',
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
            $membro['cpf'] ?? '',
            $membro['data_nascimento'] ?? '',
            $membro['sexo'] ?? '',
            traduzirStatus($membro['status'] ?? ''),
            $membro['paroquiano'] ? 'Sim' : 'Não',
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
        'CPF',
        'Data de Nascimento',
        'Sexo',
        'Status',
        'Paroquiano',
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
            $membro['cpf'] ?? '',
            $membro['data_nascimento'] ?? '',
            $membro['sexo'] ?? '',
            traduzirStatus($membro['status'] ?? ''),
            $membro['paroquiano'] ? 'Sim' : 'Não',
            $membro['data_entrada'] ?? '',
            $membro['pastorais'] ?? ''
        ];
        
        fputcsv($output, $linha);
    }
    
    fclose($output);
    exit;
}

/**
 * Exporta membros em formato PDF - Implementação própria sem dependências
 */
function exportarPDF($membros, $busca = '', $status = '', $pastoral = '') {
    // Limpar buffer antes de gerar arquivo binário
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    error_log("PDF Export: Iniciando exportação PDF");
    error_log("PDF Export: Total de membros recebidos: " . count($membros));
    
    if (empty($membros)) {
        error_log("PDF Export: AVISO - Nenhum membro para exportar!");
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Nenhum membro encontrado para exportar.';
        exit;
    }
    
    $nomeArquivo = 'membros_' . date('Y-m-d_His') . '.pdf';
    
    // Usar implementação própria de PDF (sem dependências de arquivos de fonte)
    gerarPDFCompleto($membros, $busca, $status, $pastoral, $nomeArquivo);
}

/**
 * Gera PDF completo com tabela de membros - Implementação própria
 */
function gerarPDFCompleto($membros, $busca, $status, $pastoral, $nomeArquivo) {
    // Headers PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
    // Inicializar variáveis para construção do PDF
    $objects = [];
    $offsets = [];
    $objNum = 1;
    
    // Objeto 1: Catalog
    $objects[$objNum++] = "<<\n/Type /Catalog\n/Pages 2 0 R\n>>";
    
    // Objeto 2: Pages (será atualizado depois)
    $pageObjStart = $objNum++;
    $objects[$pageObjStart] = ""; // Será preenchido depois
    
    // Preparar conteúdo das páginas
    $pageContents = [];
    $currentPage = 0;
    $pageHeight = 842; // A4 portrait height em pontos
    $pageWidth = 595; // A4 portrait width em pontos
    $margin = 40;
    $yPosFromTop = 50; // Posição Y do topo (em pontos do topo)
    
    // Função auxiliar para converter texto para ISO-8859-1 e escapar
    $escapeText = function($text) {
        $text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    };
    
    // Função para converter Y do topo para Y do PDF (PDF tem Y=0 na parte inferior)
    $pdfY = function($yFromTop) use ($pageHeight) {
        return $pageHeight - $yFromTop;
    };
    
    // Função para adicionar texto ao conteúdo
    $addText = function(&$content, $x, $yFromTop, $text, $size = 10, $align = 'L', $textColor = null) use ($escapeText, $pdfY) {
        $text = $escapeText($text);
        $y = $pdfY($yFromTop);
        $content .= "BT\n";
        $content .= "/F1 $size Tf\n";
        // Definir cor do texto (se especificada)
        if ($textColor !== null) {
            $content .= "$textColor[0] $textColor[1] $textColor[2] rg\n";
        } else {
            $content .= "0 0 0 rg\n"; // Cor preta padrão
        }
        if ($align == 'C') {
            // Centralizar (aproximado)
            $textWidth = strlen($text) * $size * 0.6;
            $x = $x - ($textWidth / 2);
        } elseif ($align == 'R') {
            $textWidth = strlen($text) * $size * 0.6;
            $x = $x - $textWidth;
        }
        $content .= "1 0 0 1 $x $y Tm\n";
        $content .= "($text) Tj\n";
        $content .= "ET\n";
    };
    
    // Função para adicionar retângulo (célula)
    $addRect = function(&$content, $x, $yFromTop, $w, $h, $fill = false, $stroke = true, $header = false) use ($pdfY) {
        $y = $pdfY($yFromTop + $h); // Converter para coordenada inferior do retângulo
        if ($fill) {
            if ($header) {
                $content .= "0.27 0.45 0.77 rg\n"; // Cor azul para cabeçalho
            } else {
                $content .= "0.94 0.95 0.96 rg\n"; // Cor cinza claro para linhas
            }
            $content .= "$x $y $w $h re f\n";
        }
        if ($stroke) {
            $content .= "0 0 0 RG\n"; // Cor preta
            $content .= "0.5 w\n"; // Largura da linha
            $content .= "$x $y $w $h re S\n";
        }
    };
    
    // Iniciar primeira página
    $currentPage++;
    $content = "q\n";
    
    // Título
    $addText($content, $pageWidth / 2, $yPosFromTop, 'Relatorio de Membros', 18, 'C');
    $yPosFromTop += 30;
    
    // Filtros (se houver)
    if (!empty($busca) || !empty($status) || !empty($pastoral)) {
        $filtros = [];
        if (!empty($busca)) $filtros[] = "Busca: " . $escapeText($busca);
        if (!empty($status)) $filtros[] = "Status: " . $escapeText(traduzirStatus($status));
        if (!empty($pastoral)) $filtros[] = "Pastoral: " . $escapeText($pastoral);
        $addText($content, $margin, $yPosFromTop, 'Filtros: ' . implode(' | ', $filtros), 9);
        $yPosFromTop += 20;
    }
    
    // Cabeçalhos da tabela
    $xStart = $margin;
    // Larguras ajustadas para caber na página (595 pontos - 80 de margens = 515 pontos disponíveis)
    // Ajustadas para garantir que todas as colunas caibam corretamente e os cabeçalhos sejam legíveis
    $colWidths = [95, 80, 65, 58, 58, 38, 55, 62]; // Total: 511 pontos (com margem de segurança)
    $cabecalhos = ['Nome', 'Email', 'Telefone', 'Nascimento', 'Entrada', 'Sexo', 'Status', 'Paroquiano'];
    $headerY = $yPosFromTop;
    
    // Desenhar cabeçalhos
    $x = $xStart;
    foreach ($cabecalhos as $i => $cabecalho) {
        $addRect($content, $x, $headerY, $colWidths[$i], 15, true, true, true);
        // Texto branco para cabeçalho - alinhado à esquerda
        $addText($content, $x + 2, $headerY + 12, $cabecalho, 9, 'L', [1, 1, 1]);
        $x += $colWidths[$i];
    }
    $yPosFromTop += 20;
    
    // Dados dos membros
    $rowNum = 0;
    foreach ($membros as $membro) {
        // Verificar se precisa de nova página
        if ($yPosFromTop > ($pageHeight - 50)) {
            // Finalizar página atual
            $content .= "Q\n";
            $pageContents[] = $content;
            
            // Nova página
            $currentPage++;
            $content = "q\n";
            $yPosFromTop = 50;
            
            // Reimprimir cabeçalhos
            $x = $xStart;
            foreach ($cabecalhos as $i => $cabecalho) {
                $addRect($content, $x, $yPosFromTop, $colWidths[$i], 15, true, true, true);
                // Texto branco para cabeçalho - alinhado à esquerda
                $addText($content, $x + 2, $yPosFromTop + 12, $cabecalho, 9, 'L', [1, 1, 1]);
                $x += $colWidths[$i];
            }
            $yPosFromTop += 20;
        }
        
        // Dados da linha
        $fill = ($rowNum % 2 == 0);
        $x = $xStart;
        $rowY = $yPosFromTop;
        
        // Nome
        $nome = substr($membro['nome_completo'] ?? '', 0, 30);
        $addRect($content, $x, $rowY, $colWidths[0], 12, $fill, true);
        $addText($content, $x + 2, $rowY + 9, $nome, 8);
        $x += $colWidths[0];
        
        // Email
        $email = substr($membro['email'] ?? '', 0, 25);
        $addRect($content, $x, $rowY, $colWidths[1], 12, $fill, true);
        $addText($content, $x + 2, $rowY + 9, $email, 8);
        $x += $colWidths[1];
        
        // Telefone
        $telefone = substr($membro['telefone'] ?? '', 0, 20);
        $addRect($content, $x, $rowY, $colWidths[2], 12, $fill, true);
        $addText($content, $x + 2, $rowY + 9, $telefone, 8);
        $x += $colWidths[2];
        
        // Data Nascimento - centralizar corretamente
        $dataNasc = $membro['data_nascimento'] ?? '';
        $addRect($content, $x, $rowY, $colWidths[3], 12, $fill, true);
        $addText($content, $x + ($colWidths[3] / 2), $rowY + 9, $dataNasc, 8, 'C');
        $x += $colWidths[3];
        
        // Data Entrada - centralizar corretamente
        $dataEntrada = $membro['data_entrada'] ?? '';
        $addRect($content, $x, $rowY, $colWidths[4], 12, $fill, true);
        $addText($content, $x + ($colWidths[4] / 2), $rowY + 9, $dataEntrada, 8, 'C');
        $x += $colWidths[4];
        
        // Sexo - centralizar corretamente
        $sexo = $membro['sexo'] ?? '';
        $addRect($content, $x, $rowY, $colWidths[5], 12, $fill, true);
        $addText($content, $x + ($colWidths[5] / 2), $rowY + 9, $sexo, 8, 'C');
        $x += $colWidths[5];
        
        // Status - centralizar corretamente
        $status_texto = traduzirStatus($membro['status'] ?? '');
        $addRect($content, $x, $rowY, $colWidths[6], 12, $fill, true);
        $addText($content, $x + ($colWidths[6] / 2), $rowY + 9, $status_texto, 8, 'C');
        $x += $colWidths[6];
        
        // Paroquiano - centralizar corretamente
        $paroquiano = ($membro['paroquiano'] ?? 0) ? 'Sim' : 'Nao';
        $addRect($content, $x, $rowY, $colWidths[7], 12, $fill, true);
        $addText($content, $x + ($colWidths[7] / 2), $rowY + 9, $paroquiano, 8, 'C');
        
        $yPosFromTop += 12;
        $rowNum++;
    }
    
    // Rodapé na última página
    $content .= "Q\n";
    $pageContents[] = $content;
    
    // Adicionar rodapé na última página
    $lastPageContent = $pageContents[count($pageContents) - 1];
    $lastPageContent = rtrim($lastPageContent, "Q\n");
    $rodape = "Total de membros: " . count($membros) . " | Gerado em " . date('d/m/Y H:i:s');
    $rodapeY = $pageHeight - 20; // 20 pontos do topo = rodapé (convertido para coordenada PDF)
    $rodapeText = $escapeText($rodape);
    $lastPageContent .= "BT\n/F1 8 Tf\n1 0 0 1 " . ($pageWidth / 2 - 100) . " $rodapeY Tm\n($rodapeText) Tj\nET\n";
    $lastPageContent .= "Q\n";
    $pageContents[count($pageContents) - 1] = $lastPageContent;
    
    // Criar objetos de página
    $pageKids = [];
    foreach ($pageContents as $pageIdx => $pageContent) {
        $contentObjNum = $objNum++;
        $pageObjNum = $objNum++;
        
        $objects[$contentObjNum] = "<<\n/Length " . strlen($pageContent) . "\n>>\nstream\n$pageContent\nendstream";
        $objects[$pageObjNum] = "<<\n/Type /Page\n/Parent $pageObjStart 0 R\n/MediaBox [0 0 $pageWidth $pageHeight]\n/Contents $contentObjNum 0 R\n/Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> /F2 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >>\n>>";
        
        $pageKids[] = "$pageObjNum 0 R";
    }
    
    // Atualizar objeto Pages
    $objects[$pageObjStart] = "<<\n/Type /Pages\n/Kids [" . implode(' ', $pageKids) . "]\n/Count " . count($pageContents) . "\n>>";
    
    // Construir PDF final
    $pdf = "%PDF-1.4\n";
    $maxObjNum = max(array_keys($objects));
    
    // Escrever objetos
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
    $pdf .= "xref\n0 " . ($maxObjNum + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    foreach (range(1, $maxObjNum) as $i) {
        if (isset($offsets[$i])) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
    }
    
    // Trailer
    $pdf .= "trailer\n";
    $pdf .= "<<\n/Size " . ($maxObjNum + 1) . "\n/Root 1 0 R\n>>\n";
    $pdf .= "startxref\n$xrefStart\n";
    $pdf .= "%%EOF\n";
    
    echo $pdf;
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
 * Classe SimplePDF removida - agora usando FPDF do módulo hamburger
 * Esta classe não está mais em uso
 */
/*
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
                        
                        // Converter UTF-8 para ISO-8859-1 (Latin-1) que é suportado pela fonte Helvetica
                        // Se a função iconv estiver disponível, usar ela, senão usar mb_convert_encoding
                        if (function_exists('iconv')) {
                            $text = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
                            if ($text === false) {
                                // Se falhar, tentar sem TRANSLIT
                                $text = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $element['text']);
                                if ($text === false) {
                                    $text = $element['text'];
                                }
                            }
                        } else {
                            $text = mb_convert_encoding($element['text'], 'ISO-8859-1', 'UTF-8');
                        }
                        
                        // Escapar caracteres especiais do PDF
                        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
                        
                        // Converter tamanho da fonte de mm para pontos
                        $fontSizePt = $element['fontSize'] * 2.83465 * 0.6; // Ajustar para melhor legibilidade
                        
                        // Posicionamento do texto
                        // No PDF, Y=0 está no canto inferior esquerdo
                        // A célula está em $y (que é o canto inferior esquerdo após inversão)
                        // $h já está em pontos (convertido acima)
                        // Para centralizar o texto verticalmente na célula:
                        // - Centro vertical da célula = Y + (altura / 2)
                        // - O texto é posicionado pela linha de base (baseline)
                        // - Ajustar para que a linha de base fique no centro
                        $textX = $x + 3;
                        // Centralizar verticalmente: centro da célula
                        // A linha de base do texto precisa estar no centro vertical da célula
                        // No PDF, o texto é posicionado pela linha de base
                        // Centro da célula em pontos: $y + ($h / 2)
                        // Ajustar para que a linha de base fique no centro menos uma fração do tamanho da fonte
                        // A linha de base fica aproximadamente 70% da altura da fonte acima do Y especificado
                        $textY = $y + ($h / 2) + ($fontSizePt * 0.25);
                        
                        if ($element['align'] == 'C') {
                            // Centralizar: estimar largura do texto
                            $textWidth = strlen($text) * $fontSizePt * 0.5; // Usar strlen para ISO-8859-1
                            $textX = $x + ($w / 2) - ($textWidth / 2);
                        } elseif ($element['align'] == 'R') {
                            // Alinhar à direita
                            $textWidth = strlen($text) * $fontSizePt * 0.5;
                            $textX = $x + $w - $textWidth - 3;
                        } else {
                            // Alinhar à esquerda (padrão)
                            $textX = $x + 3;
                        }
                        
                        // Garantir que o texto não saia da página (com margem de segurança)
                        if ($textY < 10 || $textY > ($pageHeightPt - 10)) {
                            error_log("PDF: Texto fora da página - textY: $textY, pageHeightPt: $pageHeightPt, texto: " . substr($element['text'], 0, 30));
                            continue; // Pular este elemento se estiver fora da página
                        }
                        
                        // Text color
                        $r = $element['textColor'][0] / 255;
                        $g = $element['textColor'][1] / 255;
                        $b = $element['textColor'][2] / 255;
                        
                        // Debug: logar primeiros elementos para verificar
                        static $debugCount = 0;
                        if ($debugCount < 5) {
                            error_log("PDF Render: Elemento $debugCount - textX: $textX, textY: $textY, texto: " . substr($text, 0, 30) . ", fontSizePt: $fontSizePt");
                            $debugCount++;
                        }
                        
                        // Renderizar texto
                        // IMPORTANTE: No PDF, a ordem dos operadores é crítica
                        // 1. BT (Begin Text)
                        // 2. Definir cor do texto (rg para RGB fill color)
                        // 3. Definir fonte e tamanho (Tf)
                        // 4. Posicionar texto (Tm)
                        // 5. Desenhar texto (Tj)
                        // 6. ET (End Text)
                        $content .= "BT\n"; // Begin Text
                        $content .= "$r $g $b rg\n"; // Cor do texto (RGB) - DEVE vir antes de Tf
                        $content .= "/F1 $fontSizePt Tf\n"; // Definir fonte e tamanho
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
*/
?>
