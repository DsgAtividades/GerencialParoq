<?php
session_start();
require_once __DIR__ . '/config/database.php';

$id = 5; // ID da obra
$tipo = 'comprovante';
$coluna = 'comprovante_pagamento';

echo "=== Verificando arquivo ===\n";

// Buscar informações do arquivo
$stmt = $pdo->prepare("SELECT $coluna FROM obras_servicos WHERE id = ?");
$stmt->execute([$id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result && !empty($result[$coluna])) {
    echo "Arquivo no banco: " . $result[$coluna] . "\n";
    
    $arquivo = dirname(__DIR__) . '/obras/' . $result[$coluna];
    echo "Caminho completo: " . $arquivo . "\n";
    echo "Arquivo existe? " . (file_exists($arquivo) ? "Sim" : "Não") . "\n";
    
    if (file_exists($arquivo)) {
        echo "Permissões: " . substr(sprintf('%o', fileperms($arquivo)), -4) . "\n";
        echo "É gravável? " . (is_writable($arquivo) ? "Sim" : "Não") . "\n";
        echo "Dono: " . posix_getpwuid(fileowner($arquivo))['name'] . "\n";
    }
    
    // Tentar excluir
    if (file_exists($arquivo)) {
        echo "\nTentando excluir...\n";
        if (unlink($arquivo)) {
            echo "Arquivo excluído com sucesso!\n";
            
            // Atualizar banco
            $stmt = $pdo->prepare("UPDATE obras_servicos SET $coluna = NULL WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo "Banco de dados atualizado com sucesso!\n";
            } else {
                echo "Erro ao atualizar banco de dados.\n";
            }
        } else {
            echo "Erro ao excluir arquivo.\n";
            echo "Último erro: " . error_get_last()['message'] . "\n";
        }
    }
} else {
    echo "Nenhum arquivo encontrado no banco de dados.\n";
}
?>
