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
    <title>Setup Database</title>
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
        <h1>Setup do Banco de Dados</h1>
";

// SQL para recriar a tabela users
$sql_users = "
DROP TABLE IF EXISTS users;

CREATE TABLE obras_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(14),
    data_nascimento DATE,
    data_cadastro DATE NOT NULL,
    endereco VARCHAR(255),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    telefone VARCHAR(20),
    email VARCHAR(255),
    visitado_por VARCHAR(100),
    qtd_moram_casa INT,
    paga_aluguel ENUM('Sim', 'Não'),
    paroquia VARCHAR(100),
    situacao ENUM('Ativo', 'Inativo', 'Aguardando Documentação', 'Outros') NOT NULL DEFAULT 'Ativo',
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// SQL para recriar a tabela system_users (se não existir)
$sql_system_users = "
CREATE TABLE IF NOT EXISTS obras_system_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nome_completo VARCHAR(255) NOT NULL,
    tipo_acesso ENUM('Administrador', 'Operador') NOT NULL,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insere o usuário admin padrão se não existir
INSERT IGNORE INTO obras_system_users (username, password, nome_completo, tipo_acesso) 
VALUES ('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Administrador');
";

// SQL para recriar a tabela obras (se não existir)
$sql_obras = "
DROP TABLE IF EXISTS obras_obras;

CREATE TABLE obras_obras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    responsavel_tecnico VARCHAR(100) NOT NULL,
    status ENUM('Em Andamento','Concluída','Pendente','Cancelada') NOT NULL DEFAULT 'Pendente',
    total DECIMAL(10,2) DEFAULT NULL,
    valor_adiantado DECIMAL(10,2) NOT NULL DEFAULT '0.00',
    data_ordem_servico DATE DEFAULT NULL,
    data_conclusao DATE DEFAULT NULL,
    previsao_entrega DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// Executa as queries
echo executeQuery($conn, $sql_users, "Recriação da tabela users");
echo executeQuery($conn, $sql_system_users, "Verificação/criação da tabela system_users");
echo executeQuery($conn, $sql_obras, "Atualização da tabela obras");

// Insere obras de exemplo se não existirem
$check = $conn->query("SELECT COUNT(*) FROM obras_obras")->fetchColumn();
if ($check == 0) {
    $sql = "INSERT INTO obras_obras (descricao, responsavel_tecnico, status, total, valor_adiantado, data_ordem_servico, data_conclusao, previsao_entrega) VALUES 
            ('Reforma do Altar', 'João Silva', 'Em Andamento', 50000.00, 15000.00, '2025-03-01', NULL, '2025-06-01'),
            ('Pintura Externa', 'Maria Santos', 'Concluída', 25000.00, 25000.00, '2025-03-15', '2025-04-01', '2025-04-15'),
            ('Troca do Telhado', 'Pedro Oliveira', 'Pendente', 75000.00, 0.00, NULL, NULL, NULL)";
    $conn->exec($sql);
}

// Verifica a estrutura da tabela users
try {
    $stmt = $conn->query("DESCRIBE obras_users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='color: blue;'>ℹ Estrutura atual da tabela users:</div>";
    echo "<pre style='background-color: #f8f9fa; padding: 10px; border-radius: 3px;'>";
    foreach ($columns as $column) {
        echo htmlspecialchars(json_encode($column, JSON_PRETTY_PRINT)) . "\n";
    }
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>✗ Erro ao verificar estrutura: " . $e->getMessage() . "</div>";
}

echo "
    </div>
</body>
</html>
";
