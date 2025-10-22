<?php
try {
    
    $host = 'gerencialparoq.mysql.dbaas.com.br';
    $dbname = 'gerencialparoq';
    $username = 'gerencialparoq';
    $password = 'Dsg#1806';
    /*
    $host = '177.153.63.28';
    $dbname = 'bancoobras';
    $username = 'bancoobras';
    $password = 'Dsg#1806';
    */
    
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Para manter compatibilidade com código existente
    $conn = $pdo;
} catch(PDOException $e) {
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados']);
        exit;
    }
    die("Erro de conexão: " . $e->getMessage());
}
?>
