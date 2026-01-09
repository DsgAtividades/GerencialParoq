-- =====================================================
-- SCRIPT DE VERIFICAÇÃO DE TABELAS - MÓDULO MEMBROS
-- Execute este script para verificar se todas as tabelas foram criadas
-- =====================================================

-- Verificar todas as tabelas do módulo
SELECT 
    TABLE_NAME as 'Tabela',
    TABLE_ROWS as 'Registros',
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS 'Tamanho (MB)'
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME LIKE 'membros_%'
ORDER BY TABLE_NAME;

-- Verificar estrutura da tabela principal
DESCRIBE membros_membros;

-- Verificar índices
SHOW INDEX FROM membros_membros;

-- Contar registros por tabela
SELECT 'membros_membros' as tabela, COUNT(*) as total FROM membros_membros
UNION ALL
SELECT 'membros_funcoes', COUNT(*) FROM membros_funcoes
UNION ALL
SELECT 'membros_pastorais', COUNT(*) FROM membros_pastorais
UNION ALL
SELECT 'membros_membros_pastorais', COUNT(*) FROM membros_membros_pastorais
UNION ALL
SELECT 'membros_eventos', COUNT(*) FROM membros_eventos
UNION ALL
SELECT 'membros_eventos_pastorais', COUNT(*) FROM membros_eventos_pastorais
UNION ALL
SELECT 'membros_escalas_eventos', COUNT(*) FROM membros_escalas_eventos
UNION ALL
SELECT 'membros_escalas_funcoes', COUNT(*) FROM membros_escalas_funcoes
UNION ALL
SELECT 'membros_escalas_funcao_membros', COUNT(*) FROM membros_escalas_funcao_membros
UNION ALL
SELECT 'membros_escalas_logs', COUNT(*) FROM membros_escalas_logs
UNION ALL
SELECT 'membros_consentimentos_lgpd', COUNT(*) FROM membros_consentimentos_lgpd
UNION ALL
SELECT 'membros_auditoria_logs', COUNT(*) FROM membros_auditoria_logs
UNION ALL
SELECT 'membros_anexos', COUNT(*) FROM membros_anexos;

