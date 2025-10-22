<?php
require_once 'config/database.php';

try {
    // Create tables
    $sql = file_get_contents('database/schema.sql');
    $pdo->exec($sql);
    
    // Create equipe_pastoral table
    $sql_equipe = file_get_contents('database/equipe_pastoral.sql');
    $pdo->exec($sql_equipe);
    
    echo "Database initialized successfully!";
} catch (PDOException $e) {
    die("Error initializing database: " . $e->getMessage());
}
