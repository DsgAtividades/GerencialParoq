USE festa;

-- Remover a foreign key existente
ALTER TABLE pessoas DROP FOREIGN KEY fk_pessoas_cartao;

-- Adicionar a coluna id_pessoa na tabela cartoes
ALTER TABLE cartoes 
ADD COLUMN id_pessoa INT NULL,
ADD CONSTRAINT fk_cartoes_pessoa FOREIGN KEY (id_pessoa) REFERENCES pessoas(id_pessoa) ON DELETE RESTRICT;

-- Recriar a foreign key na tabela pessoas
ALTER TABLE pessoas
ADD CONSTRAINT fk_pessoas_cartao FOREIGN KEY (qrcode) REFERENCES cartoes(codigo) ON DELETE RESTRICT;
