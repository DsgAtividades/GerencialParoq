-- Script para adicionar coluna comunidade_ou_capelania na tabela membros_pastorais
-- Execute este script se a coluna não existir

-- Verificar e adicionar coluna se não existir
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'membros_pastorais'
    AND COLUMN_NAME = 'comunidade_ou_capelania'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE membros_pastorais ADD COLUMN comunidade_ou_capelania VARCHAR(100) DEFAULT NULL COMMENT ''Comunidade ou capelania'' AFTER tipo',
    'SELECT ''Coluna comunidade_ou_capelania já existe'' AS resultado'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

