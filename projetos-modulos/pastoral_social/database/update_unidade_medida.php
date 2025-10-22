<?php
require_once '../config/database.php';

try {
    // Atualizar na tabela estoque
    $stmt = $conn->prepare("UPDATE estoque SET unidade_medida = 'L' WHERE unidade_medida = 'l'");
    $stmt->execute();
    
    // Atualizar no histórico de movimentações (caso exista o campo unidade_medida)
    $stmt = $conn->prepare("UPDATE historico_estoque h 
                           JOIN estoque e ON h.alimento_id = e.id 
                           SET e.unidade_medida = 'L' 
                           WHERE e.unidade_medida = 'l'");
    $stmt->execute();
    
    echo "Unidades de medida atualizadas com sucesso!";
    
    // Redirecionar de volta para a página de estoque
    header("Location: ../index.php?page=estoque");
    exit;
    
} catch (PDOException $e) {
    echo "Erro ao atualizar unidades de medida: " . $e->getMessage();
} 