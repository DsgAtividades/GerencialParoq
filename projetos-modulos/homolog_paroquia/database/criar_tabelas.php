<?php
require_once '../includes/conexao.php';

try {
    // Criar tabela de grupos
    $pdo->exec("CREATE TABLE IF NOT EXISTS grupos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Criar tabela de permissões
    $pdo->exec("CREATE TABLE IF NOT EXISTS permissoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        pagina VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Criar tabela de relacionamento grupos_permissoes
    $pdo->exec("CREATE TABLE IF NOT EXISTS grupos_permissoes (
        grupo_id INT,
        permissao_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (grupo_id, permissao_id),
        FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE CASCADE,
        FOREIGN KEY (permissao_id) REFERENCES permissoes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // Criar tabela de usuários
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        grupo_id INT,
        ativo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE SET NULL
    ) ENGINE=InnoDB");

    // Criar tabela de pessoas
    $pdo->exec("CREATE TABLE IF NOT EXISTS pessoas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        cpf VARCHAR(14) UNIQUE,
        telefone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Criar tabela de categorias
    $pdo->exec("CREATE TABLE IF NOT EXISTS categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Criar tabela de produtos
    $pdo->exec("CREATE TABLE IF NOT EXISTS produtos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        descricao TEXT,
        preco DECIMAL(10,2) NOT NULL,
        estoque INT NOT NULL DEFAULT 0,
        categoria_id INT,
        ativo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
    ) ENGINE=InnoDB");

    // Criar tabela de vendas
    $pdo->exec("CREATE TABLE IF NOT EXISTS vendas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pessoa_id INT,
        data_venda DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        valor_total DECIMAL(10,2) NOT NULL DEFAULT 0,
        status ENUM('pendente', 'concluida', 'cancelada') NOT NULL DEFAULT 'pendente',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pessoa_id) REFERENCES pessoas(id) ON DELETE SET NULL
    ) ENGINE=InnoDB");

    // Criar tabela de itens da venda
    $pdo->exec("CREATE TABLE IF NOT EXISTS itens_venda (
        id INT AUTO_INCREMENT PRIMARY KEY,
        venda_id INT NOT NULL,
        produto_id INT,
        quantidade INT NOT NULL,
        valor_unitario DECIMAL(10,2) NOT NULL,
        valor_total DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (venda_id) REFERENCES vendas(id) ON DELETE CASCADE,
        FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL
    ) ENGINE=InnoDB");

    // Inserir grupo Administrador
    $stmt = $pdo->prepare("INSERT IGNORE INTO grupos (nome) VALUES ('Administrador')");
    $stmt->execute();
    $grupoAdminId = $pdo->lastInsertId();

    // Inserir permissões padrão
    $permissoes = [
        ['gerenciar_grupos', 'grupos.php'],
        ['gerenciar_permissoes', 'permissoes.php'],
        ['gerenciar_usuarios', 'usuarios.php'],
        ['gerenciar_pessoas', 'pessoas.php'],
        ['gerenciar_transacoes', 'transacoes_lista.php'],
        ['gerenciar_produtos', 'produtos_lista.php'],
        ['gerenciar_vendas', 'vendas.php'],
        ['visualizar_dashboard', 'dashboard_vendas.php'],
        ['visualizar_relatorios', 'relatorio_vendas.php'],
        ['gerenciar_cartoes', 'cartoes.php']
    ];

    foreach ($permissoes as $p) {
        $sql = "INSERT INTO permissoes (nome, pagina) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($p);

        // Associar ao grupo Administrador
        $sql = "INSERT INTO grupos_permissoes (grupo_id, permissao_id) 
                SELECT g.id, p.id 
                FROM grupos g, permissoes p 
                WHERE g.nome = 'Administrador' 
                AND p.nome = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$p[0]]);
    }

    echo "Tabelas criadas com sucesso e dados iniciais inseridos!";

} catch (PDOException $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage();
}
