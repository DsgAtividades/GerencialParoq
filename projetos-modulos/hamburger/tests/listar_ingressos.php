<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Ingresso.php';
$model = new Ingresso();
$ingressos = $model->listarTodos();
echo "<h2>Lista de Ingressos</h2>";
echo "<table border='1' cellpadding='5' style='background:#fff;color:#000;'>";
echo "<tr><th>ID</th><th>Código</th><th>Nome</th><th>Telefone</th><th>Quantidade</th><th>Status</th></tr>";
foreach ($ingressos as $ing) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($ing['id'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($ing['codigo'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($ing['nome'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($ing['telefone'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($ing['quantidade'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($ing['status'] ?? '') . "</td>";
    echo "</tr>";
}
echo "</table>";
?> 