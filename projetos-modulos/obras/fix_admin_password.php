<?php
require_once 'config/database.php';

try {
    // Gerar novo hash para a senha admin123
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Atualizar a senha do admin
    $stmt = $pdo->prepare("UPDATE obras_system_users SET password = ? WHERE username = 'admin'");
    if ($stmt->execute([$password])) {
        echo "Senha do admin atualizada com sucesso!<br>";
        echo "Novo hash: " . $password . "<br><br>";
        
        // Verificar se a senha está correta
        $stmt = $pdo->query("SELECT password FROM obras_system_users WHERE username = 'admin'");
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify('admin123', $admin['password'])) {
            echo "Teste de verificação: OK - A senha está correta!<br>";
            echo "<br><a href='login.php'>Ir para página de login</a>";
        } else {
            echo "Erro: A senha ainda não está verificando corretamente.";
        }
    } else {
        echo "Erro ao atualizar a senha.";
    }
} catch(PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>
