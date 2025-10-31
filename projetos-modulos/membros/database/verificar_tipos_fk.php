<?php
require_once __DIR__ . '/../config/database.php';

$db = new MembrosDatabase();
$conn = $db->getConnection();

echo "=== VERIFICANDO TIPOS DAS COLUNAS ===\n\n";

// Verificar membros_pastorais
$stmt = $conn->query("DESCRIBE membros_pastorais");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "membros_pastorais.id:\n";
foreach($cols as $col) {
    if ($col['Field'] === 'id') {
        echo "  Tipo: {$col['Type']}\n";
        echo "  Null: {$col['Null']}\n";
        echo "  Key: {$col['Key']}\n";
    }
}

// Verificar membros_membros
echo "\nmembros_membros.id:\n";
$stmt = $conn->query("DESCRIBE membros_membros");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $col) {
    if ($col['Field'] === 'id') {
        echo "  Tipo: {$col['Type']}\n";
        echo "  Null: {$col['Null']}\n";
        echo "  Key: {$col['Key']}\n";
    }
}
?>

