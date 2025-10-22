-- Create users table
CREATE TABLE IF NOT EXISTS obras_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255),
    cpf VARCHAR(14),
    data_nascimento DATE,
    endereco VARCHAR(255),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    telefone VARCHAR(20),
    email VARCHAR(255),
    situacao VARCHAR(20) DEFAULT 'Ativo',
    observacoes TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create system users table for authentication
CREATE TABLE IF NOT EXISTS obras_system_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nome_completo VARCHAR(255) NOT NULL,
    tipo_acesso ENUM('Administrador', 'Operador') NOT NULL,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
DELETE FROM obras_system_users WHERE username = 'admin';
INSERT INTO obras_system_users (username, password, nome_completo, tipo_acesso, ativo) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Administrador', 1);

-- Create obras table
CREATE TABLE IF NOT EXISTS obras_obras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao TEXT NOT NULL,
    responsavel VARCHAR(255) NOT NULL,
    responsavel_autorizacao VARCHAR(255),
    adiantamento_1 DECIMAL(10,2),
    data_adiant_1 DATE,
    adiantamento_2 DECIMAL(10,2),
    data_adiant_2 DATE,
    adiantamento_3 DECIMAL(10,2),
    data_adiant_3 DATE,
    valor_antecipado DECIMAL(10,2),
    total DECIMAL(10,2),
    falta_pagar DECIMAL(10,2),
    status ENUM('Em Andamento', 'Concluído', 'Pendente', 'Cancelado') NOT NULL,
    previsao_entrega DATE,
    data_ordem_servico DATE,
    data_previsao_entrega DATE,
    data_entrega_final DATE,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
