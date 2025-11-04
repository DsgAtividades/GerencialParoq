<?php
require_once __DIR__ . '/../config/database.php';

if (!class_exists('PDO')) {
    die("PDO não está disponível neste ambiente\n");
}

try {
    $db = new MembrosDatabase();
    $conn = $db->getConnection();
} catch (Exception $e) {
    die("Erro ao conectar no banco: " . $e->getMessage() . "\n");
}

echo "=== VERIFICANDO TIPOS DAS COLUNAS ===\n\n";

$tables = [
    'membros_membros',
    'membros_enderecos_membro',
    'membros_contatos_membro',
    'membros_documentos_membro',
    'membros_membros_pastorais',
    'membros_pastorais'
];

foreach ($tables as $table) {
    echo "Tabela: {$table}\n";
    try {
        $stmt = $conn->query("DESCRIBE {$table}");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            printf("  %-30s | %-20s | Null: %-3s | Key: %-3s | Default: %s\n",
                $col['Field'],
                $col['Type'],
                $col['Null'],
                $col['Key'],
                $col['Default'] === null ? 'NULL' : $col['Default']
            );
        }
    } catch (Exception $e) {
        echo "  Erro ao descrever tabela: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

?>

