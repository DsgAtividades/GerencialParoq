<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

try {
    // Conexão PDO direta usando configuração da Locaweb
    $pdo = new PDO(
        "mysql:host=gerencialparoq.mysql.dbaas.com.br;dbname=gerencialparoq;charset=utf8mb4",
        "gerencialparoq",
        "Dsg#1806",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            c.nome as categoria_nome
        FROM lojinha_produtos p
        LEFT JOIN lojinha_categorias c ON p.categoria_id = c.id
        ORDER BY p.nome ASC
    ");
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'produtos' => $produtos
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
