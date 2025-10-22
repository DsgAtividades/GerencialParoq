<?php
require_once 'config/database.php';

try {
    // Atualizar na tabela estoque
    $stmt = $conn->prepare("UPDATE estoque SET unidade_medida = 'L' WHERE unidade_medida = 'l'");
    $result1 = $stmt->execute();
    $count1 = $stmt->rowCount();
    
    // Atualizar no histórico de movimentações (caso exista o campo unidade_medida)
    $stmt = $conn->prepare("UPDATE historico_estoque h 
                           JOIN estoque e ON h.alimento_id = e.id 
                           SET e.unidade_medida = 'L' 
                           WHERE e.unidade_medida = 'l'");
    $result2 = $stmt->execute();
    $count2 = $stmt->rowCount();
    
    echo "Atualização concluída!<br>";
    echo "Registros atualizados na tabela estoque: " . $count1 . "<br>";
    echo "Registros atualizados no histórico: " . $count2 . "<br>";
    echo "<br><a href='index.php?page=estoque'>Voltar para o estoque</a>";
    
} catch (PDOException $e) {
    echo "Erro ao atualizar unidades de medida: " . $e->getMessage();
    echo "<br><a href='index.php?page=estoque'>Voltar para o estoque</a>";
} 