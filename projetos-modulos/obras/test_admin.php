<?php
require_once 'config/database.php';

try {
    // Verificar se o banco existe
    $stmt = $pdo->query("SELECT DATABASE()");
    $db = $stmt->fetchColumn();
    echo "Banco de dados atual: " . $db . "<br>";

    // Listar todas as tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tabelas encontradas: <br>";
    foreach ($tables as $table) {
        echo "- " . $table . "<br>";
    }

    // Verificar usuário admin
    $stmt = $pdo->query("SELECT id, username, password, nome_completo, tipo_acesso, ativo FROM obras_system_users WHERE username = 'admin'");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<br>Dados do usuário admin:<br>";
    if ($admin) {
        echo "ID: " . $admin['id'] . "<br>";
        echo "Username: " . $admin['username'] . "<br>";
        echo "Hash da senha: " . $admin['password'] . "<br>";
        echo "Nome completo: " . $admin['nome_completo'] . "<br>";
        echo "Tipo de acesso: " . $admin['tipo_acesso'] . "<br>";
        echo "Ativo: " . ($admin['ativo'] ? 'Sim' : 'Não') . "<br>";
        
        // Testar a senha
        $senha_teste = 'admin123';
        echo "<br>Testando senha 'admin123': " . (password_verify($senha_teste, $admin['password']) ? 'Senha correta' : 'Senha incorreta');
    } else {
        echo "Usuário admin não encontrado!";
    }

} catch(PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>
