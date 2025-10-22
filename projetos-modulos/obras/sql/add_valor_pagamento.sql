ALTER TABLE obras_servicos_arquivos
ADD COLUMN valor_pagamento DECIMAL(10,2) DEFAULT 0.00
COMMENT 'Valor do pagamento registrado no comprovante';
