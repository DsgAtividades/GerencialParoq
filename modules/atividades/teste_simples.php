<?php
echo "Teste PHP funcionando!<br>";

// Testar conexão
try {
    require_once '../../config/database.php';
    $pdo = getConnection();
    
    if ($pdo) {
        echo "✅ Conexão OK<br>";
        
        // Testar query simples
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM relatorios_atividades");
        $result = $stmt->fetch();
        echo "Total de registros: " . $result['total'] . "<br>";
        
        // Buscar dados
        $stmt = $pdo->query("SELECT * FROM relatorios_atividades LIMIT 3");
        $relatorios = $stmt->fetchAll();
        
        echo "Dados encontrados:<br>";
        foreach ($relatorios as $rel) {
            echo "- " . $rel['titulo_atividade'] . "<br>";
        }
        
    } else {
        echo "❌ Erro de conexão";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>

