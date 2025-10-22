<?php
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar o banco de dados
    $pdo->exec("DROP DATABASE IF EXISTS festa");
    $pdo->exec("CREATE DATABASE festa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE festa");
    
    // Criar tabela pessoas
    $pdo->exec("CREATE TABLE pessoas (
        id_pessoa INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        cpf VARCHAR(14) NOT NULL,
        telefone VARCHAR(15),
        qrcode VARCHAR(255) NOT NULL,
        CONSTRAINT uk_pessoas_cpf UNIQUE (cpf),
        CONSTRAINT uk_pessoas_qrcode UNIQUE (qrcode)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Criar tabela produtos
    $pdo->exec("CREATE TABLE produtos (
        id_produto INT AUTO_INCREMENT PRIMARY KEY,
        nome_produto VARCHAR(255) NOT NULL,
        preco DECIMAL(10, 2) NOT NULL,
        quantidade_estoque INT NOT NULL CHECK (quantidade_estoque >= 0)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Criar tabela vendas
    $pdo->exec("CREATE TABLE vendas (
        id_venda INT AUTO_INCREMENT PRIMARY KEY,
        id_pessoa INT NOT NULL,
        id_produto INT NOT NULL,
        quantidade INT NOT NULL CHECK (quantidade > 0),
        valor_total DECIMAL(10, 2) NOT NULL CHECK (valor_total >= 0),
        data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_vendas_pessoa FOREIGN KEY (id_pessoa) REFERENCES pessoas(id_pessoa) ON DELETE RESTRICT,
        CONSTRAINT fk_vendas_produto FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Criar tabela saldos_cartao
    $pdo->exec("CREATE TABLE saldos_cartao (
        id_saldo INT AUTO_INCREMENT PRIMARY KEY,
        id_pessoa INT NOT NULL,
        saldo DECIMAL(10, 2) NOT NULL DEFAULT 0.00 CHECK (saldo >= 0),
        CONSTRAINT fk_saldo_pessoa FOREIGN KEY (id_pessoa) REFERENCES pessoas(id_pessoa) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Criar índices
    $pdo->exec("CREATE INDEX idx_pessoas_cpf ON pessoas(cpf)");
    $pdo->exec("CREATE INDEX idx_pessoas_qrcode ON pessoas(qrcode)");
    $pdo->exec("CREATE INDEX idx_vendas_data ON vendas(data_venda)");
    
    echo "Banco de dados e tabelas criados com sucesso!";
    
} catch(PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>
