<?php
// Arquivo para verificar se os dados existem no banco
require_once '../../config/database.php';

echo "<h2>🔍 Verificação de Dados - Relatórios de Atividades</h2>";

try {
    $pdo = getConnection();
    
    if (!$pdo) {
        echo "<p style='color: red;'>❌ Erro: Não foi possível conectar ao banco de dados</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Conexão com banco estabelecida</p>";
    
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'relatorios_atividades'");
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        echo "<p style='color: red;'>❌ Tabela 'relatorios_atividades' não existe</p>";
        
        // Criar a tabela
        echo "<p style='color: orange;'>🔧 Criando tabela...</p>";
        
        $create_sql = "
        CREATE TABLE IF NOT EXISTS relatorios_atividades (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo_atividade VARCHAR(255) NOT NULL,
            setor VARCHAR(100) NOT NULL,
            responsavel VARCHAR(100) NOT NULL,
            data_inicio DATE NOT NULL,
            data_previsao DATE NOT NULL,
            data_termino DATE NULL,
            status ENUM('a_fazer', 'em_andamento', 'pausado', 'concluido', 'cancelado') DEFAULT 'a_fazer',
            observacao TEXT,
            user_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($create_sql);
        echo "<p style='color: green;'>✅ Tabela criada com sucesso</p>";
        
        // Inserir dados de exemplo
        $insert_sql = "
        INSERT INTO relatorios_atividades 
        (titulo_atividade, setor, responsavel, data_inicio, data_previsao, status, observacao) 
        VALUES 
        ('Preparação para Primeira Comunhão', 'Catequese', 'Maria Silva', '2024-01-15', '2024-06-15', 'em_andamento', 'Turma de 20 crianças'),
        ('Campanha de Arrecadação de Alimentos', 'Pastoral Social', 'João Santos', '2024-02-01', '2024-02-28', 'concluido', 'Arrecadados 500kg de alimentos'),
        ('Retiro de Jovens', 'Pastoral da Juventude', 'Ana Costa', '2024-03-10', '2024-03-12', 'a_fazer', 'Retiro de fim de semana')";
        
        $pdo->exec($insert_sql);
        echo "<p style='color: green;'>✅ Dados de exemplo inseridos</p>";
    } else {
        echo "<p style='color: green;'>✅ Tabela 'relatorios_atividades' existe</p>";
    }
    
    // Buscar dados
    $stmt = $pdo->query("SELECT * FROM relatorios_atividades ORDER BY created_at DESC");
    $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📊 Dados na Tabela (Total: " . count($relatorios) . " registros)</h3>";
    
    if (empty($relatorios)) {
        echo "<p style='color: red;'>❌ Nenhum registro encontrado</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #f0f0f0; font-weight: bold;'>";
        echo "<th>ID</th><th>Título</th><th>Setor</th><th>Responsável</th><th>Status</th><th>Data Criação</th>";
        echo "</tr>";
        
        foreach ($relatorios as $relatorio) {
            echo "<tr>";
            echo "<td>" . $relatorio['id'] . "</td>";
            echo "<td>" . htmlspecialchars($relatorio['titulo_atividade']) . "</td>";
            echo "<td>" . htmlspecialchars($relatorio['setor']) . "</td>";
            echo "<td>" . htmlspecialchars($relatorio['responsavel']) . "</td>";
            echo "<td>" . $relatorio['status'] . "</td>";
            echo "<td>" . $relatorio['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Testar a API
    echo "<h3>🧪 Teste da API buscar_relatorios.php</h3>";
    
    // Simular requisição
    $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/buscar_relatorios.php';
    echo "<p>URL da API: <a href='$url' target='_blank'>$url</a></p>";
    
    // Fazer requisição local
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "<p style='color: red;'>❌ Erro ao acessar a API</p>";
    } else {
        echo "<p style='color: green;'>✅ API respondeu</p>";
        $data = json_decode($response, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            echo "<p style='color: green;'>✅ API retornou sucesso com " . count($data['relatorios']) . " relatórios</p>";
        } else {
            echo "<p style='color: red;'>❌ API retornou erro: " . ($data['message'] ?? 'Erro desconhecido') . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
table { border-collapse: collapse; width: 100%; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>
