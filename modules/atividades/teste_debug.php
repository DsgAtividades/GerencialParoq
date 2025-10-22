<?php
// Arquivo de debug para testar a conexão e dados
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Debug - Teste de Conexão e Dados</h2>";

try {
    // Testar conexão com banco
    require_once '../../config/database.php';
    $pdo = getConnection();
    
    if (!$pdo) {
        echo "<p style='color: red;'>❌ Erro: Não foi possível conectar ao banco de dados</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Conexão com banco estabelecida com sucesso</p>";
    
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'relatorios_atividades'");
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        echo "<p style='color: red;'>❌ Erro: Tabela 'relatorios_atividades' não existe</p>";
        
        // Criar a tabela
        echo "<p style='color: orange;'>🔧 Tentando criar a tabela...</p>";
        
        $create_table = "
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
        
        $pdo->exec($create_table);
        echo "<p style='color: green;'>✅ Tabela criada com sucesso</p>";
        
        // Inserir dados de exemplo
        $insert_data = "
        INSERT INTO relatorios_atividades 
        (titulo_atividade, setor, responsavel, data_inicio, data_previsao, status, observacao) 
        VALUES 
        ('Preparação para Primeira Comunhão', 'Catequese', 'Maria Silva', '2024-01-15', '2024-06-15', 'em_andamento', 'Turma de 20 crianças'),
        ('Campanha de Arrecadação de Alimentos', 'Pastoral Social', 'João Santos', '2024-02-01', '2024-02-28', 'concluido', 'Arrecadados 500kg de alimentos'),
        ('Retiro de Jovens', 'Pastoral da Juventude', 'Ana Costa', '2024-03-10', '2024-03-12', 'a_fazer', 'Retiro de fim de semana')";
        
        $pdo->exec($insert_data);
        echo "<p style='color: green;'>✅ Dados de exemplo inseridos</p>";
    } else {
        echo "<p style='color: green;'>✅ Tabela 'relatorios_atividades' existe</p>";
    }
    
    // Buscar todos os dados da tabela
    $stmt = $pdo->query("SELECT * FROM relatorios_atividades ORDER BY created_at DESC");
    $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📊 Dados na Tabela (Total: " . count($relatorios) . " registros)</h3>";
    
    if (empty($relatorios)) {
        echo "<p style='color: red;'>❌ Nenhum registro encontrado na tabela</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
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
    
    // Testar a função buscar_relatorios.php
    echo "<h3>🧪 Teste do arquivo buscar_relatorios.php</h3>";
    
    // Simular sessão
    session_start();
    $_SESSION['module_logged_in'] = true;
    $_SESSION['module_user_id'] = 1;
    
    // Capturar output do arquivo
    ob_start();
    include 'buscar_relatorios.php';
    $output = ob_get_clean();
    
    echo "<h4>Resposta do buscar_relatorios.php:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars($output);
    echo "</pre>";
    
    // Decodificar JSON para verificar
    $json_data = json_decode($output, true);
    if ($json_data) {
        echo "<h4>Dados decodificados:</h4>";
        echo "<pre style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
        print_r($json_data);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . "</p>";
    echo "<p>Linha: " . $e->getLine() . "</p>";
}
?>

