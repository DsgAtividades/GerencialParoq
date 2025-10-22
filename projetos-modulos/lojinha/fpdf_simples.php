<?php
/**
 * FPDF Simplificado - Sem dependências de arquivos de fonte externos
 * Versão simplificada para o módulo Lojinha
 */

class FPDF {
    private $page;
    private $pages;
    private $x;
    private $y;
    private $w;
    private $h;
    private $fontSize;
    private $fontStyle;
    private $fontFamily;
    private $lineHeight;
    private $marginLeft;
    private $marginTop;
    private $marginRight;
    private $marginBottom;
    private $currentFont;
    
    public function __construct() {
        $this->page = 0;
        $this->pages = array();
        $this->x = 0;
        $this->y = 0;
        $this->w = 210; // A4 width in mm
        $this->h = 297; // A4 height in mm
        $this->fontSize = 12;
        $this->fontStyle = 'normal';
        $this->fontFamily = 'Arial';
        $this->lineHeight = 1.2;
        $this->marginLeft = 20;
        $this->marginTop = 20;
        $this->marginRight = 20;
        $this->marginBottom = 20;
        $this->currentFont = 'Arial';
    }
    
    public function AddPage() {
        $this->page++;
        $this->pages[$this->page] = array();
        $this->x = $this->marginLeft;
        $this->y = $this->marginTop;
    }
    
    public function SetFont($family, $style = '', $size = 0) {
        $this->fontFamily = $family;
        $this->fontStyle = $style;
        if ($size > 0) {
            $this->fontSize = $size;
        }
        $this->currentFont = $family;
    }
    
    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        if ($h == 0) {
            $h = $this->fontSize * $this->lineHeight;
        }
        
        $this->pages[$this->page][] = array(
            'type' => 'cell',
            'x' => $this->x,
            'y' => $this->y,
            'w' => $w,
            'h' => $h,
            'text' => $txt,
            'border' => $border,
            'align' => $align,
            'font' => $this->currentFont,
            'fontSize' => $this->fontSize,
            'fontStyle' => $this->fontStyle
        );
        
        $this->x += $w;
        if ($ln == 1) {
            $this->x = $this->marginLeft;
            $this->y += $h;
        }
    }
    
    public function Ln($h = 0) {
        if ($h == 0) {
            $h = $this->fontSize * $this->lineHeight;
        }
        $this->y += $h;
        $this->x = $this->marginLeft;
    }
    
    public function Output($dest = '', $name = '', $isUTF8 = false) {
        if ($dest == 'F' && $name != '') {
            $this->saveToFile($name);
        } else {
            $this->outputToBrowser();
        }
    }
    
    private function saveToFile($filename) {
        $content = $this->generateHTML();
        file_put_contents($filename, $content);
    }
    
    private function outputToBrowser() {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="relatorio.pdf"');
        echo $this->generatePDF();
    }
    
    private function generateHTML() {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .page { page-break-after: always; min-height: 297mm; }
        .cell { position: absolute; }
        .border { border: 1px solid #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>';
        
        foreach ($this->pages as $pageNum => $page) {
            $html .= '<div class="page">';
            foreach ($page as $element) {
                if ($element['type'] == 'cell') {
                    $style = 'left: ' . $element['x'] . 'mm; top: ' . $element['y'] . 'mm; width: ' . $element['w'] . 'mm; height: ' . $element['h'] . 'mm; font-size: ' . $element['fontSize'] . 'pt;';
                    
                    if ($element['border']) {
                        $style .= ' border: 1px solid #000;';
                    }
                    
                    $class = '';
                    if ($element['align'] == 'C') {
                        $class .= ' text-center';
                    } elseif ($element['align'] == 'R') {
                        $class .= ' text-right';
                    }
                    
                    if ($element['fontStyle'] == 'B') {
                        $class .= ' bold';
                    }
                    
                    $html .= '<div class="cell ' . $class . '" style="' . $style . '">' . htmlspecialchars($element['text']) . '</div>';
                }
            }
            $html .= '</div>';
        }
        
        $html .= '</body></html>';
        return $html;
    }
    
    private function generatePDF() {
        // Para uma versão mais simples, vamos gerar HTML que pode ser convertido para PDF
        // ou usar uma biblioteca mais simples
        return $this->generateSimplePDF();
    }
    
    private function generateSimplePDF() {
        // Gerar um PDF muito simples usando apenas texto
        $content = "%PDF-1.4\n";
        $content .= "1 0 obj\n";
        $content .= "<<\n";
        $content .= "/Type /Catalog\n";
        $content .= "/Pages 2 0 R\n";
        $content .= ">>\n";
        $content .= "endobj\n";
        
        $content .= "2 0 obj\n";
        $content .= "<<\n";
        $content .= "/Type /Pages\n";
        $content .= "/Kids [3 0 R]\n";
        $content .= "/Count 1\n";
        $content .= ">>\n";
        $content .= "endobj\n";
        
        $content .= "3 0 obj\n";
        $content .= "<<\n";
        $content .= "/Type /Page\n";
        $content .= "/Parent 2 0 R\n";
        $content .= "/MediaBox [0 0 612 792]\n";
        $content .= "/Contents 4 0 R\n";
        $content .= ">>\n";
        $content .= "endobj\n";
        
        // Conteúdo da página
        $pageContent = "BT\n";
        $pageContent .= "/F1 12 Tf\n";
        $pageContent .= "50 750 Td\n";
        $pageContent .= "(Relatório Gerado) Tj\n";
        $pageContent .= "ET\n";
        
        $content .= "4 0 obj\n";
        $content .= "<<\n";
        $content .= "/Length " . strlen($pageContent) . "\n";
        $content .= ">>\n";
        $content .= "stream\n";
        $content .= $pageContent;
        $content .= "endstream\n";
        $content .= "endobj\n";
        
        $content .= "xref\n";
        $content .= "0 5\n";
        $content .= "0000000000 65535 f \n";
        $content .= "0000000009 00000 n \n";
        $content .= "0000000058 00000 n \n";
        $content .= "0000000115 00000 n \n";
        $content .= "0000000204 00000 n \n";
        $content .= "trailer\n";
        $content .= "<<\n";
        $content .= "/Size 5\n";
        $content .= "/Root 1 0 R\n";
        $content .= ">>\n";
        $content .= "startxref\n";
        $content .= "300\n";
        $content .= "%%EOF\n";
        
        return $content;
    }
}
?>
