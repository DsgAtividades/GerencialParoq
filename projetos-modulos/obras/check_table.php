<?php
require_once __DIR__ . '/config/database.php';

try {
    $stmt = $pdo->query("DESCRIBE servicos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Estrutura da tabela servicos:\n\n";
    print_r($columns);
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
