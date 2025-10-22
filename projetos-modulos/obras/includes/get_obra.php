<?php
function getObra($pdo, $id) {
    // Forçar nova consulta ao banco sem cache
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    
    $stmt = $pdo->prepare("SELECT * FROM obras_servicos WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $obra = $stmt->fetch();
    
    // Resetar configurações do PDO
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    if ($obra) {
        // Garantir que campos vazios sejam NULL
        $campos = ['comprovante_pagamento', 'nota_fiscal', 'ordem_servico'];
        foreach ($campos as $campo) {
            if (empty($obra[$campo]) || $obra[$campo] === '') {
                $obra[$campo] = null;
            }
        }
        
        // Verificar se os arquivos existem fisicamente
        foreach ($campos as $campo) {
            if (!empty($obra[$campo])) {
                $arquivo = str_replace('\\', '/', dirname(__DIR__) . '/' . $obra[$campo]);
                if (!file_exists($arquivo)) {
                    $obra[$campo] = null;
                    
                    // Atualizar o banco se o arquivo não existe
                    $updateStmt = $pdo->prepare("UPDATE obras_servicos SET $campo = NULL WHERE id = ?");
                    $updateStmt->execute([$id]);
                }
            }
        }
    }
    
    return $obra;
}
?>
