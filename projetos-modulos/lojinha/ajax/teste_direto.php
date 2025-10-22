<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=gerencialparoq.mysql.dbaas.com.br;dbname=gerencialparoq;charset=utf8mb4",
        "gerencialparoq",
        "Dsg#1806",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $pdo->query("SELECT id, nome FROM lojinha_categorias WHERE ativo = 1 ORDER BY nome ASC");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'total' => count($categorias),
        'categorias' => $categorias
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
