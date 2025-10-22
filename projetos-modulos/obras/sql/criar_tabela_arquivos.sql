CREATE TABLE IF NOT EXISTS obras_servicos_arquivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    servico_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    caminho_arquivo VARCHAR(255) NOT NULL,
    data_upload DATETIME NOT NULL,
    FOREIGN KEY (servico_id) REFERENCES obras_servicos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
