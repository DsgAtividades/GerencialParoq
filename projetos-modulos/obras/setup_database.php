<?php
// Configurações do banco de dados
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'obras';

try {
    // Conectar ao MySQL sem selecionar um banco de dados
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Criar o banco de dados se não existir
    // Criar o banco de dados
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Banco de dados '$dbname' criado com sucesso!<br>";

    // Reconectar selecionando o banco de dados
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Dropar tabelas existentes
    $pdo->exec("DROP TABLE IF EXISTS servicos");
    $pdo->exec("DROP TABLE IF EXISTS obras");
    $pdo->exec("DROP TABLE IF EXISTS system_users");
    $pdo->exec("DROP TABLE IF EXISTS users");
    echo "Tabelas antigas removidas com sucesso!<br>";

    // Criar as tabelas
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $pdo->exec($sql);
    echo "Tabelas criadas com sucesso!<br>";

    echo "<br>Configuração concluída! Agora você pode <a href='login.php'>fazer login</a> com:<br>";
    echo "Usuário: admin<br>";
    echo "Senha: admin123";

} catch(PDOException $e) {
    die("Erro na configuração: " . $e->getMessage());
}
?>
