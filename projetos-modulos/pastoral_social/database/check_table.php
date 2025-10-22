<?php
require_once '../config/database.php';

try {
    // Verificar a estrutura da tabela
    $stmt = $conn->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Estrutura da tabela users:\n";
    foreach ($columns as $column) {
        echo json_encode($column, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
