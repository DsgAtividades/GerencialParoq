<?php
require_once __DIR__ . '/../config/database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS servicos_arquivos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        servico_id INT NOT NULL,
        tipo VARCHAR(50) NOT NULL,
        nome_arquivo VARCHAR(255) NOT NULL,
        caminho_arquivo VARCHAR(255) NOT NULL,
        data_upload DATETIME NOT NULL,
        FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Tabela servicos_arquivos criada com sucesso!\n";

} catch (PDOException $e) {
    echo "Erro ao criar tabela: " . $e->getMessage() . "\n";
}
?>
