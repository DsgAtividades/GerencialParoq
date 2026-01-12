-- Backup dos dados existentes
CREATE TABLE cafe_cartoes_backup LIKE cafe_cartoes;
INSERT INTO cafe_cartoes_backup SELECT * FROM cafe_cartoes;

-- Drop foreign key da tabela pessoas
ALTER TABLE cafe_pessoas DROP FOREIGN KEY fk_pessoas_cartao;

-- Drop index único do qrcode
ALTER TABLE cafe_pessoas DROP INDEX qrcode;

-- Recriar tabela cartoes com collation correta
DROP TABLE IF EXISTS cafe_cartoes;
CREATE TABLE cafe_cartoes (
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
INSERT INTO cafe_cartoes SELECT * FROM cafe_cartoes_backup;

-- Recriar foreign key e índice único
ALTER TABLE cafe_pessoas ADD CONSTRAINT uk_pessoas_qrcode UNIQUE (qrcode);
ALTER TABLE cafe_pessoas
ADD CONSTRAINT fk_pessoas_cartao
FOREIGN KEY (qrcode)
REFERENCES cafe_cartoes(codigo)
ON DELETE RESTRICT;

-- Remover tabela de backup
DROP TABLE cafe_cartoes_backup;
