USE pastoral_social;

ALTER TABLE eventos 
ADD COLUMN status ENUM('pendente', 'realizado') NOT NULL DEFAULT 'pendente' AFTER descricao; 