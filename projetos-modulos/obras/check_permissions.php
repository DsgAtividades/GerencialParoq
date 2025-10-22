<?php
$uploadDir = __DIR__ . '/uploads';
$testDir = $uploadDir . '/test_' . uniqid();
$testFile = $testDir . '/test.txt';

echo "Verificando permissões...\n\n";

// Verificar se o diretório uploads existe
if (file_exists($uploadDir)) {
    echo "✓ Diretório uploads existe\n";
    echo "Permissões: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "\n";
} else {
    echo "✗ Diretório uploads não existe\n";
    if (mkdir($uploadDir, 0777, true)) {
        echo "✓ Diretório uploads criado com sucesso\n";
    } else {
        echo "✗ Erro ao criar diretório uploads\n";
    }
}

// Tentar criar um diretório de teste
if (mkdir($testDir, 0777, true)) {
    echo "✓ Pode criar diretórios\n";
    
    // Tentar criar um arquivo de teste
    if (file_put_contents($testFile, 'test')) {
        echo "✓ Pode criar arquivos\n";
        
        // Tentar excluir o arquivo de teste
        if (unlink($testFile)) {
            echo "✓ Pode excluir arquivos\n";
        } else {
            echo "✗ Não pode excluir arquivos\n";
        }
    } else {
        echo "✗ Não pode criar arquivos\n";
    }
    
    // Tentar excluir o diretório de teste
    if (rmdir($testDir)) {
        echo "✓ Pode excluir diretórios\n";
    } else {
        echo "✗ Não pode excluir diretórios\n";
    }
} else {
    echo "✗ Não pode criar diretórios\n";
}
?>
