<?php
require_once __DIR__ . '/config/database.php';

try {
    $stmt = $pdo->query("SELECT id, descricao, comprovante_pagamento FROM obras_servicos WHERE descricao LIKE '%Pintura do Salão%'");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($result);
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
