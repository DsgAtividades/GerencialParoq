<?php
require_once __DIR__ . '/config/database.php';

try {
    $id = 5; // ID da obra "Pintura do Salão"
    
    // Primeiro, buscar o caminho do arquivo atual
    $stmt = $pdo->prepare("SELECT comprovante_pagamento FROM obras_servicos WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && !empty($result['comprovante_pagamento'])) {
        $arquivo = __DIR__ . '/' . $result['comprovante_pagamento'];
        
        // Deletar o arquivo físico se ele existir
        if (file_exists($arquivo)) {
            if (unlink($arquivo)) {
                echo "Arquivo físico excluído com sucesso.\n";
            } else {
                echo "Erro ao excluir arquivo físico.\n";
            }
        }
        
        // Atualizar o banco de dados
        $stmt = $pdo->prepare("UPDATE obras_servicos SET comprovante_pagamento = NULL WHERE id = ?");
        $stmt->execute([$id]);
        
        echo "Registro do comprovante removido do banco de dados com sucesso!";
    } else {
        echo "Nenhum comprovante encontrado para esta obra.";
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
