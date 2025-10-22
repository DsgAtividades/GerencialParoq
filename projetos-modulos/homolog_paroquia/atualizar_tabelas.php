<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Atualizando tabelas do banco de dados...</h2>";
    
    // Criar tabela historico_estoque
    $sql = "CREATE TABLE IF NOT EXISTS `historico_estoque` (
        `id_historico` int(11) NOT NULL AUTO_INCREMENT,
        `id_produto` int(11) NOT NULL,
        `tipo_operacao` enum('entrada','saida') NOT NULL,
        `quantidade` int(11) NOT NULL,
        `quantidade_anterior` int(11) NOT NULL,
        `motivo` varchar(100) NOT NULL,
        `data_operacao` datetime NOT NULL,
        PRIMARY KEY (`id_historico`),
        KEY `fk_historico_produto` (`id_produto`),
        CONSTRAINT `fk_historico_produto` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id_produto`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $db->exec($sql);
    echo "<p style='color: green;'>✓ Tabela historico_estoque verificada/criada!</p>";

    // Criar tabela historico_saldo se não existir
    $sql = "CREATE TABLE IF NOT EXISTS `historico_saldo` (
        `id_historico` int(11) NOT NULL AUTO_INCREMENT,
        `id_pessoa` int(11) NOT NULL,
        `tipo_operacao` enum('credito','debito') NOT NULL,
        `valor` decimal(10,2) NOT NULL,
        `saldo_anterior` decimal(10,2) NOT NULL,
        `saldo_novo` decimal(10,2) NOT NULL,
        `motivo` varchar(50) NOT NULL,
        `data_operacao` datetime NOT NULL,
        PRIMARY KEY (`id_historico`),
        KEY `fk_historico_pessoa` (`id_pessoa`),
        CONSTRAINT `fk_historico_pessoa` FOREIGN KEY (`id_pessoa`) REFERENCES `pessoas` (`id_pessoa`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $db->exec($sql);
    echo "<p style='color: green;'>✓ Tabela historico_saldo verificada/criada!</p>";

    // Criar tabela saldos_cartao se não existir
    $sql = "CREATE TABLE IF NOT EXISTS `saldos_cartao` (
        `id_saldo` int(11) NOT NULL AUTO_INCREMENT,
        `id_pessoa` int(11) NOT NULL,
        `saldo` decimal(10,2) NOT NULL DEFAULT 0.00,
        PRIMARY KEY (`id_saldo`),
        CONSTRAINT `fk_saldo_pessoa` FOREIGN KEY (`id_pessoa`) REFERENCES `pessoas` (`id_pessoa`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $db->exec($sql);
    echo "<p style='color: green;'>✓ Tabela saldos_cartao verificada/criada!</p>";

    echo "<p style='color: green; font-weight: bold; margin-top: 20px;'>✓ Todas as tabelas foram verificadas/criadas com sucesso!</p>";

} catch(PDOException $e) {
    echo "<p style='color: red; font-weight: bold;'>Erro: " . $e->getMessage() . "</p>";
}

// Adicionar estilo e botão de retorno
echo "
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        line-height: 1.6;
        background-color: #f8f9fa;
    }
    h2 {
        color: #0d6efd;
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 10px;
    }
    p {
        margin: 10px 0;
        padding: 8px;
        border-radius: 4px;
        background-color: white;
    }
    p[style*='color: green'] {
        border-left: 4px solid #198754;
    }
    p[style*='color: red'] {
        border-left: 4px solid #dc3545;
    }
    .btn {
        display: inline-block;
        padding: 10px 20px;
        background-color: #0d6efd;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin-top: 20px;
    }
    .btn:hover {
        background-color: #0b5ed7;
    }
</style>

<div style='margin-top: 20px;'>
    <a href='produtos.php' class='btn'>Voltar para Produtos</a>
</div>";
?>
