ALTER TABLE obras_obras
ADD COLUMN comprovante_pagamento VARCHAR(255) DEFAULT NULL COMMENT 'Caminho do arquivo de comprovante de pagamento',
ADD COLUMN nota_fiscal VARCHAR(255) DEFAULT NULL COMMENT 'Caminho do arquivo da nota fiscal';

-- Criar índices para melhor performance nas buscas
CREATE INDEX idx_comprovante_pagamento ON obras_obras(comprovante_pagamento);
CREATE INDEX idx_nota_fiscal ON obras_obras(nota_fiscal);
