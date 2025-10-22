<?php
require_once 'config/database.php';

try {
    echo "<h2>Verificação do Sistema</h2>";
    
    // Verificar se a tabela system_users existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'system_users'");
    $tableExists = $stmt->rowCount() > 0;
    echo "Tabela system_users existe: " . ($tableExists ? 'Sim' : 'Não') . "<br>";

    if ($tableExists) {
        // Verificar usuário admin
        $stmt = $pdo->prepare("SELECT id, username, password, ativo FROM obras_system_users WHERE username = ?");
        $stmt->execute(['admin']);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            echo "Usuário admin encontrado:<br>";
            echo "- ID: " . $admin['id'] . "<br>";
            echo "- Ativo: " . ($admin['ativo'] ? 'Sim' : 'Não') . "<br>";
            echo "- Tamanho do hash da senha: " . strlen($admin['password']) . " caracteres<br>";
            
            // Testar se a senha está correta
            $testPassword = 'admin123';
            $passwordValid = password_verify($testPassword, $admin['password']);
            echo "- Senha 'admin123' é válida: " . ($passwordValid ? 'Sim' : 'Não') . "<br>";
        } else {
            echo "Usuário admin não encontrado!<br>";
        }
    }

    echo "<br><a href='create_admin.php'>Criar/Atualizar usuário admin</a>";
    
} catch(PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>
