<?php
$uploadsDir = __DIR__ . '/uploads';

function checkDirectory($dir) {
    echo "Verificando diretório: $dir\n";
    
    if (!file_exists($dir)) {
        echo "✗ Diretório não existe. Criando...\n";
        if (mkdir($dir, 0777, true)) {
            echo "✓ Diretório criado com sucesso\n";
        } else {
            echo "✗ Erro ao criar diretório\n";
            return;
        }
    } else {
        echo "✓ Diretório existe\n";
    }
    
    // Verificar permissões
    $perms = substr(sprintf('%o', fileperms($dir)), -4);
    echo "Permissões atuais: $perms\n";
    
    // Tentar criar um arquivo de teste
    $testFile = $dir . '/test.txt';
    if (file_put_contents($testFile, 'test')) {
        echo "✓ Pode criar arquivos\n";
        unlink($testFile);
        echo "✓ Pode excluir arquivos\n";
    } else {
        echo "✗ Não pode criar/excluir arquivos\n";
    }
    
    // Verificar se é gravável
    if (is_writable($dir)) {
        echo "✓ Diretório é gravável\n";
    } else {
        echo "✗ Diretório não é gravável\n";
    }
}

checkDirectory($uploadsDir);
?>
