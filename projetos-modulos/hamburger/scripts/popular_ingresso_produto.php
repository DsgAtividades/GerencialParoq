<?php
require_once __DIR__ . '/../core/Database.php';
use Core\Database;

// ID do produto hambúrguer (ajuste conforme o seu cadastro)
$produto_id_hamburguer = 1; // <-- Altere para o ID correto do produto hambúrguer

$db = Database::getInstance()->getConnection();

// Buscar todos os ingressos entregues que não têm produtos associados
$sql = "SELECT i.id, i.quantidade
        FROM ingressos i
        JOIN entregas e ON i.id = e.ingresso_id
        LEFT JOIN ingresso_produto ip ON i.id = ip.ingresso_id
        WHERE ip.id IS NULL";
$stmt = $db->query($sql);
$ingressos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = 0;
foreach ($ingressos as $ing) {
    $ingresso_id = $ing['id'];
    $qtd = $ing['quantidade'] > 0 ? $ing['quantidade'] : 1;
    $stmt2 = $db->prepare('INSERT INTO ingresso_produto (ingresso_id, produto_id, quantidade) VALUES (?, ?, ?)');
    if ($stmt2->execute([$ingresso_id, $produto_id_hamburguer, $qtd])) {
        $count++;
    }
}
echo "Foram populados $count registros na tabela ingresso_produto.\n"; 