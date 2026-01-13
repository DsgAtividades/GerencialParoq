-- Adicionar coluna eventos_url na tabela membros_eventos
-- Esta coluna armazenará URLs relacionadas aos eventos

ALTER TABLE membros_eventos 
ADD COLUMN eventos_url VARCHAR(500) DEFAULT NULL COMMENT 'URL relacionada ao evento (link externo, página, etc.)' 
AFTER descricao;

-- Verificar se a coluna foi adicionada
DESCRIBE membros_eventos;


