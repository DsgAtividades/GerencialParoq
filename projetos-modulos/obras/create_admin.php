<?php
require_once 'config/database.php';

try {
    // Criar hash da senha admin123
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Verificar se o usuário admin já existe
    $stmt = $pdo->prepare("SELECT id FROM obras_system_users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        // Atualizar a senha do admin existente
        $stmt = $pdo->prepare("UPDATE obras_system_users SET password = ?, ativo = 1 WHERE username = 'admin'");
        $stmt->execute([$password]);
        echo "Senha do usuário admin atualizada com sucesso!<br>";
    } else {
        // Criar novo usuário admin
        $stmt = $pdo->prepare("INSERT INTO obras_system_users (username, password, nome_completo, tipo_acesso, ativo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $password, 'Administrador', 'Administrador', 1]);
        echo "Usuário admin criado com sucesso!<br>";
    }
    
    echo "<br>Agora você pode fazer login com:<br>";
    echo "Usuário: admin<br>";
    echo "Senha: admin123<br><br>";
    echo "<a href='login.php'>Ir para a página de login</a>";
    
} catch(PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>
