<?php
require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $conn->prepare("
        INSERT INTO system_users (username, password, nome_completo, tipo_acesso) 
        VALUES (:username, :password, :nome, :tipo)
        ON DUPLICATE KEY UPDATE password = VALUES(password)
    ");
    
    $stmt->execute([
        ':username' => 'denys',
        ':password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ':nome' => 'Denys',
        ':tipo' => 'Administrador'
    ]);
    
    echo "Usuário administrador 'Denys' criado com sucesso!\n";
    echo "Username: denys\n";
    echo "Senha: admin123\n";
    
} catch (PDOException $e) {
    echo "Erro ao criar usuário: " . $e->getMessage() . "\n";
}
?>
