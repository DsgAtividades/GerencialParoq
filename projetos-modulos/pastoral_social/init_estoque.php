<?php
require_once 'config/database.php';

try {
    // Create tables
    $sql = file_get_contents('database/estoque.sql');
    $conn->exec($sql);
    
    echo "Tabelas de estoque criadas com sucesso!";
} catch (PDOException $e) {
    die("Erro ao criar tabelas de estoque: " . $e->getMessage());
}
?> 