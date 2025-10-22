<?php
try {
    $host = 'pastoralsocial.mysql.dbaas.com.br';
    $dbname = 'pastoralsocial';
    $username = 'pastoralsocial';
    $password = 'Dsg#1806';
    
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Verifica se o campo status existe na tabela eventos
    $stmt = $pdo->query("SHOW COLUMNS FROM eventos LIKE 'status'");
    if ($stmt->rowCount() == 0) {
        // Adiciona o campo status se não existir
        $pdo->exec("ALTER TABLE eventos ADD COLUMN status ENUM('pendente', 'realizado', 'cancelado') DEFAULT 'pendente'");
    } else {
        // Modifica o campo status existente para incluir a opção 'cancelado'
        $pdo->exec("ALTER TABLE eventos MODIFY COLUMN status ENUM('pendente', 'realizado', 'cancelado') DEFAULT 'pendente'");
    }
} catch(PDOException $e) {
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados']);
        exit;
    }
    die("Erro de conexão: " . $e->getMessage());
}
?>
