<?php
/**
 * Script para atualizar todos os arquivos AJAX
 * Substitui a conexão PDO direta pela classe Database
 */

$ajaxDir = __DIR__ . '/ajax/';
$files = glob($ajaxDir . '*.php');

$search = <<<'EOD'
    // Conexão direta com PDO
    $pdo = new PDO(
        "mysql:host=localhost;dbname=gerencialparoq;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
EOD;

$replace = "    require_once '../config/config.php';\n    \$pdo = getConnection();";

$count = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Pular arquivos de teste
    if (strpos($file, 'teste_') !== false) {
        continue;
    }
    
    // Verificar se precisa atualizar
    if (strpos($content, 'new PDO(') !== false && strpos($content, 'require_once') === false) {
        // Adicionar require no início, após os headers
        $lines = explode("\n", $content);
        $newLines = [];
        $headerAdded = false;
        
        foreach ($lines as $line) {
            $newLines[] = $line;
            
            // Adicionar require após o header JSON
            if (!$headerAdded && strpos($line, "header('Content-Type: application/json')") !== false) {
                $newLines[] = "";
                $newLines[] = "require_once '../config/config.php';";
                $headerAdded = true;
            }
        }
        
        $content = implode("\n", $newLines);
        
        // Substituir a conexão PDO
        $content = preg_replace(
            '/\$pdo = new PDO\(\s*"mysql:host=localhost;dbname=gerencialparoq;charset=utf8mb4",\s*"root",\s*"",\s*\[\s*PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\s*PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC\s*\]\s*\);/s',
            '$pdo = getConnection();',
            $content
        );
        
        // Remover comentário "Conexão direta com PDO"
        $content = str_replace("    // Conexão direta com PDO\n", "", $content);
        
        file_put_contents($file, $content);
        echo "✓ Atualizado: " . basename($file) . "\n";
        $count++;
    }
}

echo "\n" . $count . " arquivos atualizados com sucesso!\n";
?>

