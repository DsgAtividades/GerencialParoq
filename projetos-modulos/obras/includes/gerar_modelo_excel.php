<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

// Criar uma nova planilha
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Importação de Serviços');

// Definir cabeçalhos
$headers = [
    'A1' => 'Descrição do Serviço *',
    'B1' => 'Responsável *',
    'C1' => 'Valor Total *',
    'D1' => 'Status *',
    'E1' => 'Observações (opcional)'
];

// Adicionar nota sobre campos obrigatórios
$sheet->setCellValue('A2', '* Campos obrigatórios');
$sheet->getStyle('A2')->getFont()->setItalic(true);
$sheet->getStyle('A2')->getFont()->setSize(10);
$sheet->getStyle('A2')->getFont()->getColor()->setRGB('FF0000');

// Mesclar células para a nota
$sheet->mergeCells('A2:E2');

// Aplicar cabeçalhos
foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
}

// Estilizar cabeçalhos
$sheet->getStyle('A1:E1')->getFont()->setBold(true);
$sheet->getStyle('A1:E1')->getFont()->getColor()->setRGB('FFFFFF');
$sheet->getStyle('A1:E1')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('0066CC');

// Destacar campos obrigatórios com fundo mais escuro
$obrigatorios = ['A1', 'B1', 'C1', 'D1'];
foreach ($obrigatorios as $cell) {
    $sheet->getStyle($cell)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('004C99');
}
$sheet->getStyle('A1:H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Ajustar largura das colunas
$columnWidths = [
    'A' => 40, // Descrição
    'B' => 20, // Responsável
    'C' => 15, // Valor
    'D' => 15, // Status
    'E' => 40  // Observações
];

foreach ($columnWidths as $column => $width) {
    $sheet->getColumnDimension($column)->setWidth($width);
}

// Configurar validação para a coluna Status
$statusOptions = ['Em Andamento', 'Concluído', 'Pendente', 'Cancelado'];

// Aplicar validação para todas as células da coluna E (3-1000)
for ($row = 3; $row <= 1000; $row++) {
    $validation = $sheet->getCell("E{$row}")->getDataValidation();
    $validation->setType(DataValidation::TYPE_LIST)
        ->setErrorStyle(DataValidation::STYLE_INFORMATION)
        ->setAllowBlank(false)
        ->setShowInputMessage(true)
        ->setShowErrorMessage(true)
        ->setShowDropDown(true)
        ->setFormula1('"'.implode(',', $statusOptions).'"');
}

// Configurar formato de moeda para coluna C
$sheet->getStyle('C3:C1000')
    ->getNumberFormat()
    ->setFormatCode('R$ #,##0.00');

// Salvar o arquivo
$writer = new Xlsx($spreadsheet);
$filename = 'modelo_importacao_servicos.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$writer->save('php://output');
exit;
