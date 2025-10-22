<?php
require_once 'config/database.php';

try {
    // Create tables
    $sql = file_get_contents('database/schema.sql');
    $conn->exec($sql);
    
    // Create admin user with proper password hash
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE obras_system_users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$admin_password]);
    
    echo "Setup completed successfully! You can now login with:<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<a href='index.php'>Click here to go to login page</a>";
    
} catch (PDOException $e) {
    die("Error during setup: " . $e->getMessage());
}
