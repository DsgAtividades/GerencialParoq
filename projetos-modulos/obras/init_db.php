<?php
require_once 'config/database.php';

try {
    // Create tables
    $sql = file_get_contents('database/schema.sql');
    $conn->exec($sql);
    
    echo "Database initialized successfully!";
} catch (PDOException $e) {
    die("Error initializing database: " . $e->getMessage());
}
