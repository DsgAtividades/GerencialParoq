<?php
require_once '../config/database.php';

// Função para executar queries SQL e retornar mensagens de status
function executeQuery($conn, $sql, $description) {
    try {
        $conn->exec($sql);
        return "<div style='color: green;'>✓ $description: Sucesso</div>";
    } catch (PDOException $e) {
        return "<div style='color: red;'>✗ $description: Erro - " . $e->getMessage() . "</div>";
    }
}

// Adiciona estilo para melhor visualização
echo "
<!DOCTYPE html>
<html>
<head>
    <title>Setup Database - Módulo Lojinha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        div {
            margin: 10px 0;
            padding: 10px;
            border-radius: 3px;
        }
        div[style*='color: green'] {
            background-color: #e8f5e9;
        }
        div[style*='color: red'] {
            background-color: #ffebee;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Setup do Banco de Dados - Módulo Lojinha</h1>
";

try {
    $conn = getConnection();
    
    // SQL para criar tabela de categorias
    $sql_categorias = "
    CREATE TABLE IF NOT EXISTS lojinha_categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        descricao TEXT,
        ativo BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL para criar tabela de fornecedores
    $sql_fornecedores = "
    CREATE TABLE IF NOT EXISTS lojinha_fornecedores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        contato VARCHAR(100),
        telefone VARCHAR(20),
        email VARCHAR(100),
        endereco TEXT,
        ativo BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL para criar tabela de produtos
    $sql_produtos = "
    CREATE TABLE IF NOT EXISTS lojinha_produtos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(50) UNIQUE NOT NULL,
        nome VARCHAR(200) NOT NULL,
        descricao TEXT,
        categoria_id INT,
        fornecedor_id INT,
        preco_compra DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        preco_venda DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        estoque_atual INT DEFAULT 0,
        estoque_minimo INT DEFAULT 0,
        foto VARCHAR(255),
        tipo_liturgico VARCHAR(50),
        ativo BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (categoria_id) REFERENCES lojinha_categorias(id),
        FOREIGN KEY (fornecedor_id) REFERENCES lojinha_fornecedores(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL para criar tabela de vendas
    $sql_vendas = "
    CREATE TABLE IF NOT EXISTS lojinha_vendas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        numero_venda VARCHAR(20) UNIQUE NOT NULL,
        data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        vendedor_id INT,
        cliente_nome VARCHAR(200),
        cliente_telefone VARCHAR(20),
        subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        desconto DECIMAL(10,2) DEFAULT 0.00,
        total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        forma_pagamento ENUM('dinheiro', 'pix', 'cartao_debito', 'cartao_credito') NOT NULL,
        status ENUM('pendente', 'finalizada', 'cancelada') DEFAULT 'pendente',
        observacoes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (vendedor_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL para criar tabela de itens da venda
    $sql_venda_itens = "
    CREATE TABLE IF NOT EXISTS lojinha_venda_itens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        venda_id INT NOT NULL,
        produto_id INT NOT NULL,
        quantidade INT NOT NULL,
        preco_unitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (venda_id) REFERENCES lojinha_vendas(id) ON DELETE CASCADE,
        FOREIGN KEY (produto_id) REFERENCES lojinha_produtos(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL para criar tabela de movimentações de estoque
    $sql_estoque_movimentacoes = "
    CREATE TABLE IF NOT EXISTS lojinha_estoque_movimentacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        produto_id INT NOT NULL,
        tipo ENUM('entrada', 'saida', 'ajuste') NOT NULL,
        quantidade INT NOT NULL,
        motivo VARCHAR(200),
        usuario_id INT,
        venda_id INT NULL,
        data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (produto_id) REFERENCES lojinha_produtos(id),
        FOREIGN KEY (usuario_id) REFERENCES users(id),
        FOREIGN KEY (venda_id) REFERENCES lojinha_vendas(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL para criar tabela de controle de caixa
    $sql_caixa = "
    CREATE TABLE IF NOT EXISTS lojinha_caixa (
        id INT AUTO_INCREMENT PRIMARY KEY,
        saldo_inicial DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        saldo_atual DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        saldo_final DECIMAL(10,2) NULL DEFAULT NULL,
        status ENUM('aberto', 'fechado') DEFAULT 'aberto',
        usuario_id INT DEFAULT NULL,
        data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_fechamento TIMESTAMP NULL DEFAULT NULL,
        observacoes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL para criar tabela de movimentações de caixa
    $sql_caixa_movimentacoes = "
    CREATE TABLE IF NOT EXISTS lojinha_caixa_movimentacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        caixa_id INT NOT NULL,
        tipo ENUM('entrada', 'saida') NOT NULL,
        valor DECIMAL(10,2) NOT NULL,
        descricao VARCHAR(200) NOT NULL,
        categoria VARCHAR(100),
        usuario_id INT,
        data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (caixa_id) REFERENCES lojinha_caixa(id),
        FOREIGN KEY (usuario_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Executa as queries
    echo executeQuery($conn, $sql_categorias, "Criação da tabela lojinha_categorias");
    echo executeQuery($conn, $sql_fornecedores, "Criação da tabela lojinha_fornecedores");
    echo executeQuery($conn, $sql_produtos, "Criação da tabela lojinha_produtos");
    echo executeQuery($conn, $sql_vendas, "Criação da tabela lojinha_vendas");
    echo executeQuery($conn, $sql_venda_itens, "Criação da tabela lojinha_venda_itens");
    echo executeQuery($conn, $sql_estoque_movimentacoes, "Criação da tabela lojinha_estoque_movimentacoes");
    echo executeQuery($conn, $sql_caixa, "Criação da tabela lojinha_caixa");
    echo executeQuery($conn, $sql_caixa_movimentacoes, "Criação da tabela lojinha_caixa_movimentacoes");

    // Inserir dados padrão
    $sql_insert_categorias = "
    INSERT IGNORE INTO lojinha_categorias (nome, descricao) VALUES
    ('Livros', 'Livros religiosos, bíblias, catecismos'),
    ('Imagens', 'Imagens de santos, quadros religiosos'),
    ('Terços', 'Terços, rosários e objetos de devoção'),
    ('Velas', 'Velas comuns e velas especiais'),
    ('Roupas Litúrgicas', 'Vestimentas para celebrações'),
    ('Decoração', 'Itens decorativos para igreja'),
    ('Catequese', 'Material para catequese'),
    ('Outros', 'Outros produtos diversos');
    ";

    $sql_insert_fornecedor = "
    INSERT IGNORE INTO lojinha_fornecedores (nome, contato, telefone, email) VALUES
    ('Fornecedor Padrão', 'Contato Principal', '(11) 99999-9999', 'contato@fornecedor.com');
    ";

    echo executeQuery($conn, $sql_insert_categorias, "Inserção de categorias padrão");
    echo executeQuery($conn, $sql_insert_fornecedor, "Inserção de fornecedor padrão");

    // Criar índices para melhor performance
    $sql_indices = "
    CREATE INDEX IF NOT EXISTS idx_lojinha_produtos_categoria ON lojinha_produtos(categoria_id);
    CREATE INDEX IF NOT EXISTS idx_lojinha_produtos_ativo ON lojinha_produtos(ativo);
    CREATE INDEX IF NOT EXISTS idx_lojinha_vendas_data ON lojinha_vendas(data_venda);
    CREATE INDEX IF NOT EXISTS idx_lojinha_vendas_vendedor ON lojinha_vendas(vendedor_id);
    CREATE INDEX IF NOT EXISTS idx_lojinha_venda_itens_venda ON lojinha_venda_itens(venda_id);
    CREATE INDEX IF NOT EXISTS idx_lojinha_estoque_produto ON lojinha_estoque_movimentacoes(produto_id);
    CREATE INDEX IF NOT EXISTS idx_lojinha_caixa_data ON lojinha_caixa(data_abertura);
    CREATE INDEX IF NOT EXISTS idx_lojinha_caixa_usuario ON lojinha_caixa(usuario_id);
    ";

    echo executeQuery($conn, $sql_indices, "Criação de índices para performance");

    echo "<div style='color: blue;'>ℹ Setup do módulo Lojinha concluído com sucesso!</div>";

} catch(Exception $e) {
    echo "<div style='color: red;'>✗ Erro de conexão: " . $e->getMessage() . "</div>";
}

echo "
    </div>
</body>
</html>
";
?>
