USE bancoobras;

-- Adicionar campos para os arquivos na tabela servicos
ALTER TABLE obras_servicos
ADD COLUMN comprovante_pagamento VARCHAR(255) DEFAULT NULL,
ADD COLUMN nota_fiscal VARCHAR(255) DEFAULT NULL,
ADD COLUMN ordem_servico VARCHAR(255) DEFAULT NULL;
