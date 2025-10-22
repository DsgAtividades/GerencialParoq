CREATE TABLE IF NOT EXISTS obras_servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao TEXT NOT NULL,
    responsavel VARCHAR(100),
    data_ordem_servico DATE,
    total DECIMAL(10,2),
    status ENUM('Em Andamento', 'Concluído', 'Pendente', 'Cancelado') DEFAULT 'Pendente',
    cidade VARCHAR(100),
    endereco TEXT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
