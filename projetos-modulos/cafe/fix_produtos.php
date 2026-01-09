<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

try {
    // Verificar se a coluna bloqueado existe
    $stmt = $db->query("SHOW COLUMNS FROM cafe_produtos LIKE 'bloqueado'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE cafe_produtos ADD COLUMN bloqueado TINYINT(1) NOT NULL DEFAULT 0");
        echo "Coluna bloqueado adicionada com sucesso!<br>";
    } else {
        echo "Coluna bloqueado já existe!<br>";
    }

    // Verificar se a coluna categoria_id existe
    $stmt = $db->query("SHOW COLUMNS FROM cafe_produtos LIKE 'categoria_id'");
    if ($stmt->rowCount() == 0) {
        // Primeiro criar a tabela categorias se não existir
        $db->exec("CREATE TABLE IF NOT EXISTS cafe_categorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(50) NOT NULL,
            icone VARCHAR(30),
            ordem INT DEFAULT 0,
            UNIQUE KEY uk_categorias_nome (nome)
        )");

        // Inserir categorias padrão
        $db->exec("INSERT IGNORE INTO cafe_categorias (nome, icone, ordem) VALUES 
            ('Comidas', 'bi-egg-fried', 1),
            ('Bebidas', 'bi-cup-straw', 2),
            ('Doces', 'bi-cookie', 3),
            ('Diversos', 'bi-box', 99)
        ");

        // Adicionar coluna categoria_id
        $db->exec("ALTER TABLE cafe_produtos ADD COLUMN categoria_id INT");
        $db->exec("ALTER TABLE cafe_produtos ADD FOREIGN KEY (categoria_id) REFERENCES cafe_categorias(id)");
        echo "Coluna categoria_id adicionada com sucesso!<br>";
    } else {
        echo "Coluna categoria_id já existe!<br>";
    }

    echo "Tabela produtos atualizada com sucesso!";
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
