-- =====================================================
-- ÍNDICES PARA OTIMIZAÇÃO DE PERFORMANCE
-- Módulo de Membros - Sistema de Gestão Paroquial
-- =====================================================

-- =====================================================
-- TABELA: membros_membros
-- =====================================================

-- Índice para busca por nome (utilizado em filtros e pesquisas)
CREATE INDEX IF NOT EXISTS idx_membros_nome 
ON membros_membros(nome_completo);

-- Índice para busca por status (filtro frequente)
CREATE INDEX IF NOT EXISTS idx_membros_status 
ON membros_membros(status);

-- Índice para busca por email (validação de duplicatas e login)
CREATE INDEX IF NOT EXISTS idx_membros_email 
ON membros_membros(email);

-- Índice para busca por CPF (validação de duplicatas)
CREATE INDEX IF NOT EXISTS idx_membros_cpf 
ON membros_membros(cpf);

-- Índice para busca por celular (contato)
CREATE INDEX IF NOT EXISTS idx_membros_celular 
ON membros_membros(celular_whatsapp);

-- Índice para ordenação por data de entrada
CREATE INDEX IF NOT EXISTS idx_membros_data_entrada 
ON membros_membros(data_entrada);

-- Índice para ordenação por data de cadastro
CREATE INDEX IF NOT EXISTS idx_membros_created_at 
ON membros_membros(created_at);

-- Índice composto para filtros comuns (status + nome)
CREATE INDEX IF NOT EXISTS idx_membros_status_nome 
ON membros_membros(status, nome_completo);

-- =====================================================
-- TABELA: membros_membros_pastorais
-- =====================================================

-- Índice para buscar membros por pastoral
CREATE INDEX IF NOT EXISTS idx_membros_pastorais_pastoral 
ON membros_membros_pastorais(pastoral_id);

-- Índice para buscar pastorais de um membro
CREATE INDEX IF NOT EXISTS idx_membros_pastorais_membro 
ON membros_membros_pastorais(membro_id);

-- Índice para buscar por função
CREATE INDEX IF NOT EXISTS idx_membros_pastorais_funcao 
ON membros_membros_pastorais(funcao_id);

-- Índice composto para queries comuns (pastoral + membro)
CREATE INDEX IF NOT EXISTS idx_membros_pastorais_pastoral_membro 
ON membros_membros_pastorais(pastoral_id, membro_id);

-- Índice para filtrar por situação pastoral
CREATE INDEX IF NOT EXISTS idx_membros_pastorais_situacao 
ON membros_membros_pastorais(situacao_pastoral);

-- =====================================================
-- TABELA: membros_pastorais
-- =====================================================

-- Índice para buscar pastorais ativas
CREATE INDEX IF NOT EXISTS idx_pastorais_ativo 
ON membros_pastorais(ativo);

-- Índice para buscar por nome
CREATE INDEX IF NOT EXISTS idx_pastorais_nome 
ON membros_pastorais(nome);

-- Índice para buscar por tipo
CREATE INDEX IF NOT EXISTS idx_pastorais_tipo 
ON membros_pastorais(tipo);

-- Índice para buscar coordenador
CREATE INDEX IF NOT EXISTS idx_pastorais_coordenador 
ON membros_pastorais(coordenador_id);

-- Índice para buscar vice-coordenador
CREATE INDEX IF NOT EXISTS idx_pastorais_vice_coordenador 
ON membros_pastorais(vice_coordenador_id);

-- =====================================================
-- TABELA: membros_eventos
-- =====================================================

-- Índice para buscar eventos por data
CREATE INDEX IF NOT EXISTS idx_eventos_data 
ON membros_eventos(data_evento);

-- Índice para buscar eventos por tipo
CREATE INDEX IF NOT EXISTS idx_eventos_tipo 
ON membros_eventos(tipo);

-- Índice para buscar eventos ativos
CREATE INDEX IF NOT EXISTS idx_eventos_ativo 
ON membros_eventos(ativo);

-- Índice para buscar por responsável
CREATE INDEX IF NOT EXISTS idx_eventos_responsavel 
ON membros_eventos(responsavel_id);

-- Índice composto para listagem de eventos futuros
CREATE INDEX IF NOT EXISTS idx_eventos_data_ativo 
ON membros_eventos(data_evento, ativo);

-- =====================================================
-- TABELA: membros_eventos_pastorais
-- =====================================================

-- Índice para buscar eventos de uma pastoral
CREATE INDEX IF NOT EXISTS idx_eventos_pastorais_pastoral 
ON membros_eventos_pastorais(pastoral_id);

-- Índice para buscar pastorais de um evento
CREATE INDEX IF NOT EXISTS idx_eventos_pastorais_evento 
ON membros_eventos_pastorais(evento_id);

-- Índice composto
CREATE INDEX IF NOT EXISTS idx_eventos_pastorais_evento_pastoral 
ON membros_eventos_pastorais(evento_id, pastoral_id);

-- =====================================================
-- TABELA: membros_escalas_eventos
-- =====================================================

-- Índice composto já existe (idx_pastoral_data)
-- Apenas adicionando mais se necessário

-- Índice para buscar por criador
CREATE INDEX IF NOT EXISTS idx_escalas_eventos_created_by 
ON membros_escalas_eventos(created_by);

-- =====================================================
-- TABELA: membros_escalas_funcoes
-- =====================================================

-- Índice já existe (idx_evento)

-- Índice para ordenar por ordem
CREATE INDEX IF NOT EXISTS idx_escalas_funcoes_ordem 
ON membros_escalas_funcoes(evento_id, ordem);

-- =====================================================
-- TABELA: membros_escalas_funcao_membros
-- =====================================================

-- Índice para buscar membros de uma função
CREATE INDEX IF NOT EXISTS idx_escalas_funcao_membros_funcao 
ON membros_escalas_funcao_membros(funcao_id);

-- Índice para buscar funções de um membro
CREATE INDEX IF NOT EXISTS idx_escalas_funcao_membros_membro 
ON membros_escalas_funcao_membros(membro_id);

-- =====================================================
-- TABELA: membros_enderecos_membro
-- =====================================================

-- Índice para buscar endereços por membro
CREATE INDEX IF NOT EXISTS idx_enderecos_membro 
ON membros_enderecos_membro(membro_id);

-- Índice para buscar endereço principal
CREATE INDEX IF NOT EXISTS idx_enderecos_principal 
ON membros_enderecos_membro(membro_id, principal);

-- Índice para buscar por cidade
CREATE INDEX IF NOT EXISTS idx_enderecos_cidade 
ON membros_enderecos_membro(cidade);

-- =====================================================
-- TABELA: membros_contatos_membro
-- =====================================================

-- Índice para buscar contatos por membro
CREATE INDEX IF NOT EXISTS idx_contatos_membro 
ON membros_contatos_membro(membro_id);

-- Índice para buscar contato principal
CREATE INDEX IF NOT EXISTS idx_contatos_principal 
ON membros_contatos_membro(membro_id, principal);

-- Índice para buscar por tipo
CREATE INDEX IF NOT EXISTS idx_contatos_tipo 
ON membros_contatos_membro(tipo);

-- =====================================================
-- TABELA: membros_documentos_membro
-- =====================================================

-- Índice para buscar documentos por membro
CREATE INDEX IF NOT EXISTS idx_documentos_membro 
ON membros_documentos_membro(membro_id);

-- Índice para buscar por tipo
CREATE INDEX IF NOT EXISTS idx_documentos_tipo 
ON membros_documentos_membro(tipo);

-- =====================================================
-- TABELA: membros_consentimentos_lgpd
-- =====================================================

-- Índice para buscar consentimentos por membro
CREATE INDEX IF NOT EXISTS idx_lgpd_membro 
ON membros_consentimentos_lgpd(membro_id);

-- Índice para buscar por finalidade
CREATE INDEX IF NOT EXISTS idx_lgpd_finalidade 
ON membros_consentimentos_lgpd(finalidade);

-- Índice para buscar consentimentos ativos
CREATE INDEX IF NOT EXISTS idx_lgpd_consentimento 
ON membros_consentimentos_lgpd(consentimento);

-- =====================================================
-- VERIFICAR ÍNDICES CRIADOS
-- =====================================================

-- Query para listar todos os índices das tabelas do módulo membros
-- SELECT 
--     TABLE_NAME,
--     INDEX_NAME,
--     COLUMN_NAME,
--     SEQ_IN_INDEX,
--     NON_UNIQUE
-- FROM information_schema.STATISTICS
-- WHERE TABLE_SCHEMA = 'gerencialparoq'
--   AND TABLE_NAME LIKE 'membros_%'
-- ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

-- =====================================================
-- ANÁLISE DE PERFORMANCE
-- =====================================================

-- Query para verificar índices não utilizados
-- SELECT * FROM sys.schema_unused_indexes WHERE object_schema = 'gerencialparoq';

-- Query para verificar índices duplicados
-- SELECT * FROM sys.schema_redundant_indexes WHERE table_schema = 'gerencialparoq';

-- =====================================================
-- NOTAS
-- =====================================================

-- 1. Executar este script em ambiente de desenvolvimento primeiro
-- 2. Monitorar impacto na performance após aplicação
-- 3. Índices consomem espaço e podem impactar INSERTs/UPDATEs
-- 4. Revisar periodicamente índices não utilizados
-- 5. Considerar índices FULLTEXT para busca textual avançada

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================

