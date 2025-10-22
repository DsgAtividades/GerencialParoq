<?php
require_once 'config/database.php';

try {
    // Criar a tabela eventos
    $sql = "CREATE TABLE IF NOT EXISTS eventos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        data_evento DATE NOT NULL,
        hora TIME NOT NULL,
        descricao TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>";
    echo "<h2 style='color: #28a745;'>✅ Tabela 'eventos' criada com sucesso!</h2>";
    echo "<p>Agora você pode acessar o calendário clicando no link abaixo:</p>";
    echo "<a href='index.php?page=calendario' style='display: inline-block; background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Ir para o Calendário</a>";
    echo "</div>";
} catch(PDOException $e) {
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>";
    echo "<h2 style='color: #dc3545;'>❌ Erro ao criar a tabela:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?> 