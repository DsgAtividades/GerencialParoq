<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Gera o hash da senha corretamente
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("
        INSERT INTO obras_system_users (username, password, nome_completo, tipo_acesso) 
        VALUES (:username, :password, :nome, :tipo)
        ON DUPLICATE KEY UPDATE password = VALUES(password)
    ");
    
    $stmt->execute([
        ':username' => 'denys',
        ':password' => $hash,
        ':nome' => 'Denys',
        ':tipo' => 'Administrador'
    ]);
    
    echo "<div class='alert alert-success'>
            <h4>Usuário administrador criado com sucesso!</h4>
            <p><strong>Username:</strong> denys</p>
            <p><strong>Senha:</strong> admin123</p>
            <p>Você já pode fazer login com essas credenciais.</p>
            <p><a href='../login.php' class='btn btn-primary'>Ir para o Login</a></p>
          </div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>
            <h4>Erro ao criar usuário</h4>
            <p>" . $e->getMessage() . "</p>
          </div>";
}
?>
