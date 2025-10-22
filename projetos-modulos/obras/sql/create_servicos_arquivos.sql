USE obras;

CREATE TABLE IF NOT EXISTS obras_servicos_arquivos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    servico_id INT,
    tipo ENUM('comprovante_pagamento', 'nota_fiscal', 'ordem_servico'),
    nome_arquivo VARCHAR(255),
    caminho_arquivo VARCHAR(255),
    data_upload DATETIME,
    FOREIGN KEY (servico_id) REFERENCES obras_servicos(id) ON DELETE CASCADE
);
