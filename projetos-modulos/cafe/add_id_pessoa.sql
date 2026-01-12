USE festa;

-- Remover a foreign key existente
ALTER TABLE cafe_pessoas DROP FOREIGN KEY fk_pessoas_cartao;

-- Adicionar a coluna id_pessoa na tabela cartoes
ALTER TABLE cafe_cartoes 
ADD COLUMN id_pessoa INT NULL,
ADD CONSTRAINT fk_cartoes_pessoa FOREIGN KEY (id_pessoa) REFERENCES cafe_pessoas(id_pessoa) ON DELETE RESTRICT;

-- Recriar a foreign key na tabela pessoas
ALTER TABLE cafe_pessoas
ADD CONSTRAINT fk_pessoas_cartao FOREIGN KEY (qrcode) REFERENCES cafe_cartoes(codigo) ON DELETE RESTRICT;
