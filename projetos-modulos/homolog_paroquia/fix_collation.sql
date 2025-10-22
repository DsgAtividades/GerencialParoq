-- Backup dos dados existentes
CREATE TABLE cartoes_backup LIKE cartoes;
INSERT INTO cartoes_backup SELECT * FROM cartoes;

-- Drop foreign key da tabela pessoas
ALTER TABLE pessoas DROP FOREIGN KEY fk_pessoas_cartao;

-- Drop index único do qrcode
ALTER TABLE pessoas DROP INDEX qrcode;

-- Recriar tabela cartoes com collation correta
DROP TABLE IF EXISTS cartoes;
CREATE TABLE cartoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(255) NOT NULL,
    data_geracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usado BOOLEAN DEFAULT FALSE,
    CONSTRAINT uk_cartoes_codigo UNIQUE (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recriar índices
CREATE INDEX idx_cartoes_codigo ON cartoes(codigo);
CREATE INDEX idx_cartoes_usado ON cartoes(usado);

-- Restaurar dados
INSERT INTO cartoes SELECT * FROM cartoes_backup;

-- Recriar foreign key e índice único
ALTER TABLE pessoas ADD CONSTRAINT uk_pessoas_qrcode UNIQUE (qrcode);
ALTER TABLE pessoas 
ADD CONSTRAINT fk_pessoas_cartao 
FOREIGN KEY (qrcode) 
REFERENCES cartoes(codigo) 
ON DELETE RESTRICT;

-- Remover tabela de backup
DROP TABLE cartoes_backup;
