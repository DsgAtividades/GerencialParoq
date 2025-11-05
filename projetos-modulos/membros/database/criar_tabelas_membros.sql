-- =====================================================
-- SCRIPT DE CRIAÇÃO DAS TABELAS DO MÓDULO MEMBROS
-- Sistema de Gestão Paroquial - GerencialParoq
-- =====================================================
-- 
-- Este script cria todas as tabelas necessárias para o módulo de Membros
-- Baseado na análise completa do módulo (ANALISE_COMPLETA_MODULO_MEMBROS.md)
--
-- IMPORTANTE: Execute este script em ordem para garantir que as foreign keys
-- sejam criadas corretamente
--
-- =====================================================

-- =====================================================
-- 1. TABELA PRINCIPAL: membros_membros
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_membros (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do membro',
    
    -- Dados Pessoais
    nome_completo VARCHAR(255) NOT NULL COMMENT 'Nome completo do membro',
    apelido VARCHAR(100) DEFAULT NULL COMMENT 'Apelido ou nome preferido',
    data_nascimento DATE DEFAULT NULL COMMENT 'Data de nascimento',
    sexo CHAR(1) DEFAULT NULL COMMENT 'M = Masculino, F = Feminino',
    
    -- Contato
    email VARCHAR(255) DEFAULT NULL COMMENT 'Email do membro',
    celular_whatsapp VARCHAR(20) DEFAULT NULL COMMENT 'Celular com WhatsApp',
    telefone_fixo VARCHAR(20) DEFAULT NULL COMMENT 'Telefone fixo',
    
    -- Endereço
    rua VARCHAR(255) DEFAULT NULL COMMENT 'Rua/Logradouro',
    numero VARCHAR(20) DEFAULT NULL COMMENT 'Número',
    bairro VARCHAR(100) DEFAULT NULL COMMENT 'Bairro',
    cidade VARCHAR(100) DEFAULT NULL COMMENT 'Cidade',
    uf CHAR(2) DEFAULT NULL COMMENT 'Estado (UF)',
    cep VARCHAR(10) DEFAULT NULL COMMENT 'CEP',
    
    -- Documentos
    cpf VARCHAR(14) DEFAULT NULL COMMENT 'CPF',
    rg VARCHAR(20) DEFAULT NULL COMMENT 'RG',
    
    -- Status e Informações Paroquiais
    status VARCHAR(50) DEFAULT 'ativo' COMMENT 'Status: ativo, afastado, bloqueado, etc',
    motivo_bloqueio TEXT DEFAULT NULL COMMENT 'Motivo do bloqueio (soft delete)',
    paroquiano TINYINT(1) DEFAULT 1 COMMENT '1 = Paroquiano, 0 = Não paroquiano',
    comunidade_ou_capelania VARCHAR(100) DEFAULT NULL COMMENT 'Comunidade ou capelania',
    data_entrada DATE DEFAULT NULL COMMENT 'Data de entrada na paróquia',
    
    -- Foto e Observações
    foto_url VARCHAR(500) DEFAULT NULL COMMENT 'URL da foto do membro',
    observacoes_pastorais TEXT DEFAULT NULL COMMENT 'Observações pastorais',
    
    -- Preferências (Campos JSON)
    preferencias_contato JSON DEFAULT NULL COMMENT 'Preferências de contato (JSON)',
    dias_turnos JSON DEFAULT NULL COMMENT 'Dias e turnos de disponibilidade (JSON)',
    frequencia VARCHAR(50) DEFAULT NULL COMMENT 'Frequência de participação',
    periodo VARCHAR(50) DEFAULT NULL COMMENT 'Período de participação',
    habilidades JSON DEFAULT NULL COMMENT 'Habilidades e talentos (JSON)',
    
    -- LGPD
    lgpd_consentimento_data DATETIME DEFAULT NULL COMMENT 'Data do consentimento LGPD',
    lgpd_consentimento_finalidade TEXT DEFAULT NULL COMMENT 'Finalidade do consentimento',
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    created_by VARCHAR(36) DEFAULT NULL COMMENT 'ID do usuário que criou',
    updated_by VARCHAR(36) DEFAULT NULL COMMENT 'ID do usuário que atualizou',
    
    -- Índices
    INDEX idx_membros_nome (nome_completo),
    INDEX idx_membros_email (email),
    INDEX idx_membros_cpf (cpf),
    INDEX idx_membros_status (status),
    INDEX idx_membros_celular (celular_whatsapp),
    INDEX idx_membros_data_entrada (data_entrada),
    INDEX idx_membros_created_at (created_at),
    INDEX idx_membros_status_nome (status, nome_completo),
    
    -- Constraints
    UNIQUE KEY uk_membros_email (email),
    UNIQUE KEY uk_membros_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela principal de membros paroquiais';

-- =====================================================
-- 2. TABELA: membros_funcoes
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_funcoes (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID da função',
    nome VARCHAR(100) NOT NULL COMMENT 'Nome da função',
    descricao TEXT DEFAULT NULL COMMENT 'Descrição da função',
    tipo VARCHAR(50) DEFAULT NULL COMMENT 'Tipo da função',
    ordem INT DEFAULT 0 COMMENT 'Ordem de exibição',
    ativo TINYINT(1) DEFAULT 1 COMMENT '1 = Ativo, 0 = Inativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    
    -- Índices
    INDEX idx_funcoes_nome (nome),
    INDEX idx_funcoes_tipo (tipo),
    INDEX idx_funcoes_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Funções/cargos dentro das pastorais';

-- =====================================================
-- 3. TABELA: membros_pastorais
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_pastorais (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID da pastoral',
    nome VARCHAR(255) NOT NULL COMMENT 'Nome da pastoral',
    tipo VARCHAR(100) DEFAULT NULL COMMENT 'Tipo da pastoral',
    finalidade_descricao TEXT DEFAULT NULL COMMENT 'Finalidade e descrição',
    
    -- Coordenadores
    coordenador_id VARCHAR(36) DEFAULT NULL COMMENT 'ID do coordenador (FK membros_membros)',
    vice_coordenador_id VARCHAR(36) DEFAULT NULL COMMENT 'ID do vice-coordenador (FK membros_membros)',
    
    -- Informações de Reunião
    comunidade_ou_capelania VARCHAR(100) DEFAULT NULL COMMENT 'Comunidade ou capelania',
    dia_semana VARCHAR(50) DEFAULT NULL COMMENT 'Dia da semana da reunião',
    horario TIME DEFAULT NULL COMMENT 'Horário da reunião',
    local_reuniao VARCHAR(255) DEFAULT NULL COMMENT 'Local da reunião',
    
    -- Comunicação
    whatsapp_grupo_link VARCHAR(500) DEFAULT NULL COMMENT 'Link do grupo WhatsApp',
    email_grupo VARCHAR(255) DEFAULT NULL COMMENT 'Email do grupo',
    
    -- Status
    ativo TINYINT(1) DEFAULT 1 COMMENT '1 = Ativa, 0 = Inativa',
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    
    -- Índices
    INDEX idx_pastorais_nome (nome),
    INDEX idx_pastorais_tipo (tipo),
    INDEX idx_pastorais_ativo (ativo),
    INDEX idx_pastorais_coordenador (coordenador_id),
    INDEX idx_pastorais_vice_coordenador (vice_coordenador_id),
    
    -- Foreign Keys
    CONSTRAINT fk_pastorais_coordenador FOREIGN KEY (coordenador_id) 
        REFERENCES membros_membros(id) ON DELETE SET NULL,
    CONSTRAINT fk_pastorais_vice_coordenador FOREIGN KEY (vice_coordenador_id) 
        REFERENCES membros_membros(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pastorais da paróquia';

-- =====================================================
-- 4. TABELA: membros_membros_pastorais (Relacionamento N:N)
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_membros_pastorais (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do relacionamento',
    membro_id VARCHAR(36) NOT NULL COMMENT 'ID do membro (FK membros_membros)',
    pastoral_id VARCHAR(36) NOT NULL COMMENT 'ID da pastoral (FK membros_pastorais)',
    funcao_id VARCHAR(36) DEFAULT NULL COMMENT 'ID da função (FK membros_funcoes)',
    
    -- Datas
    data_inicio DATE DEFAULT NULL COMMENT 'Data de início na pastoral',
    data_fim DATE DEFAULT NULL COMMENT 'Data de término (se houver)',
    
    -- Status
    status VARCHAR(50) DEFAULT 'ativo' COMMENT 'Status na pastoral',
    situacao_pastoral VARCHAR(100) DEFAULT NULL COMMENT 'Situação na pastoral',
    
    -- Detalhes
    prioridade INT DEFAULT 0 COMMENT 'Prioridade do membro na pastoral',
    carga_horaria_semana INT DEFAULT NULL COMMENT 'Carga horária semanal em horas',
    preferencias JSON DEFAULT NULL COMMENT 'Preferências específicas (JSON)',
    observacoes TEXT DEFAULT NULL COMMENT 'Observações sobre o membro na pastoral',
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    
    -- Índices
    INDEX idx_membros_pastorais_membro (membro_id),
    INDEX idx_membros_pastorais_pastoral (pastoral_id),
    INDEX idx_membros_pastorais_funcao (funcao_id),
    INDEX idx_membros_pastorais_situacao (situacao_pastoral),
    INDEX idx_membros_pastorais_pastoral_membro (pastoral_id, membro_id),
    
    -- Foreign Keys
    CONSTRAINT fk_membros_pastorais_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE,
    CONSTRAINT fk_membros_pastorais_pastoral FOREIGN KEY (pastoral_id) 
        REFERENCES membros_pastorais(id) ON DELETE CASCADE,
    CONSTRAINT fk_membros_pastorais_funcao FOREIGN KEY (funcao_id) 
        REFERENCES membros_funcoes(id) ON DELETE SET NULL,
    
    -- Constraint: Um membro não pode estar na mesma pastoral duas vezes
    UNIQUE KEY uk_membro_pastoral (membro_id, pastoral_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relacionamento N:N entre membros e pastorais';

-- =====================================================
-- 5. TABELA: membros_eventos
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_eventos (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do evento',
    nome VARCHAR(255) NOT NULL COMMENT 'Nome do evento',
    descricao TEXT DEFAULT NULL COMMENT 'Descrição do evento',
    tipo VARCHAR(100) DEFAULT NULL COMMENT 'Tipo do evento',
    
    -- Data e Hora
    data_evento DATE NOT NULL COMMENT 'Data do evento',
    hora_inicio TIME DEFAULT NULL COMMENT 'Hora de início',
    hora_fim TIME DEFAULT NULL COMMENT 'Hora de término',
    
    -- Local
    local VARCHAR(255) DEFAULT NULL COMMENT 'Local do evento',
    endereco TEXT DEFAULT NULL COMMENT 'Endereço completo',
    
    -- Responsável
    responsavel_id VARCHAR(36) DEFAULT NULL COMMENT 'ID do responsável (FK membros_membros)',
    
    -- Status
    ativo TINYINT(1) DEFAULT 1 COMMENT '1 = Ativo, 0 = Inativo',
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    
    -- Índices
    INDEX idx_eventos_nome (nome),
    INDEX idx_eventos_tipo (tipo),
    INDEX idx_eventos_data (data_evento),
    INDEX idx_eventos_ativo (ativo),
    INDEX idx_eventos_data_ativo (data_evento, ativo),
    INDEX idx_eventos_responsavel (responsavel_id),
    
    -- Foreign Keys
    CONSTRAINT fk_eventos_responsavel FOREIGN KEY (responsavel_id) 
        REFERENCES membros_membros(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Eventos gerais da paróquia';

-- =====================================================
-- 6. TABELA: membros_eventos_pastorais (Relacionamento N:N)
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_eventos_pastorais (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do relacionamento',
    evento_id VARCHAR(36) NOT NULL COMMENT 'ID do evento (FK membros_eventos)',
    pastoral_id VARCHAR(36) NOT NULL COMMENT 'ID da pastoral (FK membros_pastorais)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    
    -- Índices
    INDEX idx_eventos_pastorais_evento (evento_id),
    INDEX idx_eventos_pastorais_pastoral (pastoral_id),
    
    -- Foreign Keys
    CONSTRAINT fk_eventos_pastorais_evento FOREIGN KEY (evento_id) 
        REFERENCES membros_eventos(id) ON DELETE CASCADE,
    CONSTRAINT fk_eventos_pastorais_pastoral FOREIGN KEY (pastoral_id) 
        REFERENCES membros_pastorais(id) ON DELETE CASCADE,
    
    -- Constraint: Um evento não pode estar vinculado à mesma pastoral duas vezes
    UNIQUE KEY uk_evento_pastoral (evento_id, pastoral_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relacionamento N:N entre eventos e pastorais';

-- =====================================================
-- 7. TABELA: membros_escalas_eventos
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_escalas_eventos (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID da escala',
    nome VARCHAR(255) NOT NULL COMMENT 'Nome/título da escala',
    descricao TEXT DEFAULT NULL COMMENT 'Descrição da escala',
    
    -- Data e Hora
    data_evento DATE NOT NULL COMMENT 'Data do evento da escala',
    hora_inicio TIME DEFAULT NULL COMMENT 'Hora de início',
    hora_fim TIME DEFAULT NULL COMMENT 'Hora de término',
    
    -- Pastoral e Local
    pastoral_id VARCHAR(36) NOT NULL COMMENT 'ID da pastoral (FK membros_pastorais)',
    local VARCHAR(255) DEFAULT NULL COMMENT 'Local do evento',
    observacoes TEXT DEFAULT NULL COMMENT 'Observações sobre a escala',
    
    -- Criador
    created_by VARCHAR(36) DEFAULT NULL COMMENT 'ID do usuário que criou (FK membros_membros)',
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    
    -- Índices
    INDEX idx_escalas_eventos_nome (nome),
    INDEX idx_escalas_eventos_data (data_evento),
    INDEX idx_escalas_eventos_pastoral (pastoral_id),
    INDEX idx_escalas_eventos_pastoral_data (pastoral_id, data_evento),
    INDEX idx_escalas_eventos_created_by (created_by),
    
    -- Foreign Keys
    CONSTRAINT fk_escalas_eventos_pastoral FOREIGN KEY (pastoral_id) 
        REFERENCES membros_pastorais(id) ON DELETE CASCADE,
    CONSTRAINT fk_escalas_eventos_created_by FOREIGN KEY (created_by) 
        REFERENCES membros_membros(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Escalas de eventos com funções e membros atribuídos';

-- =====================================================
-- 8. TABELA: membros_escalas_funcoes
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_escalas_funcoes (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID da função na escala',
    evento_id VARCHAR(36) NOT NULL COMMENT 'ID do evento da escala (FK membros_escalas_eventos)',
    nome_funcao VARCHAR(100) NOT NULL COMMENT 'Nome da função',
    descricao TEXT DEFAULT NULL COMMENT 'Descrição da função',
    quantidade_necessaria INT DEFAULT 1 COMMENT 'Quantidade necessária de pessoas',
    ordem INT DEFAULT 0 COMMENT 'Ordem de exibição',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    
    -- Índices
    INDEX idx_escalas_funcoes_evento (evento_id),
    INDEX idx_escalas_funcoes_nome (nome_funcao),
    
    -- Foreign Keys
    CONSTRAINT fk_escalas_funcoes_evento FOREIGN KEY (evento_id) 
        REFERENCES membros_escalas_eventos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Funções dentro de uma escala de evento';

-- =====================================================
-- 9. TABELA: membros_escalas_funcao_membros
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_escalas_funcao_membros (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID da atribuição',
    funcao_id VARCHAR(36) NOT NULL COMMENT 'ID da função (FK membros_escalas_funcoes)',
    membro_id VARCHAR(36) NOT NULL COMMENT 'ID do membro (FK membros_membros)',
    status VARCHAR(50) DEFAULT 'confirmado' COMMENT 'Status: confirmado, pendente, ausente, etc',
    observacoes TEXT DEFAULT NULL COMMENT 'Observações sobre a atribuição',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    
    -- Índices
    INDEX idx_escalas_funcao_membros_funcao (funcao_id),
    INDEX idx_escalas_funcao_membros_membro (membro_id),
    INDEX idx_escalas_funcao_membros_status (status),
    
    -- Foreign Keys
    CONSTRAINT fk_escalas_funcao_membros_funcao FOREIGN KEY (funcao_id) 
        REFERENCES membros_escalas_funcoes(id) ON DELETE CASCADE,
    CONSTRAINT fk_escalas_funcao_membros_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE,
    
    -- Constraint: Um membro não pode ter a mesma função duas vezes
    UNIQUE KEY uk_funcao_membro (funcao_id, membro_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Membros atribuídos a funções em escalas';

-- =====================================================
-- 10. TABELA: membros_escalas_logs
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_escalas_logs (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do log',
    evento_id VARCHAR(36) DEFAULT NULL COMMENT 'ID do evento (FK membros_escalas_eventos)',
    usuario_id VARCHAR(36) DEFAULT NULL COMMENT 'ID do usuário que executou a ação',
    acao VARCHAR(60) NOT NULL COMMENT 'Ação realizada',
    detalhes JSON DEFAULT NULL COMMENT 'Detalhes da ação (JSON)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    
    -- Índices
    INDEX idx_escalas_logs_evento (evento_id),
    INDEX idx_escalas_logs_usuario (usuario_id),
    INDEX idx_escalas_logs_acao (acao),
    INDEX idx_escalas_logs_created_at (created_at),
    
    -- Foreign Keys
    CONSTRAINT fk_escalas_logs_evento FOREIGN KEY (evento_id) 
        REFERENCES membros_escalas_eventos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Logs de ações nas escalas';

-- =====================================================
-- 11. TABELA: membros_consentimentos_lgpd
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_consentimentos_lgpd (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do consentimento',
    membro_id VARCHAR(36) NOT NULL COMMENT 'ID do membro (FK membros_membros)',
    finalidade VARCHAR(255) NOT NULL COMMENT 'Finalidade do consentimento',
    consentimento TINYINT(1) NOT NULL COMMENT '1 = Consentiu, 0 = Não consentiu',
    data_consentimento DATETIME NOT NULL COMMENT 'Data e hora do consentimento',
    ip_consentimento VARCHAR(45) DEFAULT NULL COMMENT 'IP de origem do consentimento',
    user_agent TEXT DEFAULT NULL COMMENT 'User agent do navegador',
    versao_termo VARCHAR(50) DEFAULT NULL COMMENT 'Versão do termo de consentimento',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    
    -- Índices
    INDEX idx_consentimentos_membro (membro_id),
    INDEX idx_consentimentos_finalidade (finalidade),
    INDEX idx_consentimentos_data (data_consentimento),
    
    -- Foreign Keys
    CONSTRAINT fk_consentimentos_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Consentimentos LGPD dos membros';

-- =====================================================
-- 12. TABELA: membros_auditoria_logs
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_auditoria_logs (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do log',
    entidade_tipo VARCHAR(50) NOT NULL COMMENT 'Tipo da entidade (membro, pastoral, evento, etc)',
    entidade_id VARCHAR(36) NOT NULL COMMENT 'ID da entidade',
    acao VARCHAR(50) NOT NULL COMMENT 'Ação realizada (create, update, delete)',
    campo_alterado VARCHAR(100) DEFAULT NULL COMMENT 'Campo que foi alterado',
    valor_anterior TEXT DEFAULT NULL COMMENT 'Valor anterior',
    valor_novo TEXT DEFAULT NULL COMMENT 'Valor novo',
    usuario_id VARCHAR(36) DEFAULT NULL COMMENT 'ID do usuário que executou a ação',
    ip_address VARCHAR(45) DEFAULT NULL COMMENT 'IP de origem',
    user_agent TEXT DEFAULT NULL COMMENT 'User agent do navegador',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    
    -- Índices
    INDEX idx_auditoria_entidade (entidade_tipo, entidade_id),
    INDEX idx_auditoria_acao (acao),
    INDEX idx_auditoria_usuario (usuario_id),
    INDEX idx_auditoria_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Logs de auditoria de todas as ações do sistema';

-- =====================================================
-- 13. TABELA: membros_anexos
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_anexos (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do anexo',
    membro_id VARCHAR(36) DEFAULT NULL COMMENT 'ID do membro (FK membros_membros)',
    tipo ENUM('foto', 'documento', 'outro') DEFAULT 'outro' COMMENT 'Tipo do anexo',
    nome_arquivo VARCHAR(255) NOT NULL COMMENT 'Nome do arquivo',
    caminho_arquivo VARCHAR(500) NOT NULL COMMENT 'Caminho completo do arquivo',
    tamanho INT DEFAULT NULL COMMENT 'Tamanho em bytes',
    mime_type VARCHAR(100) DEFAULT NULL COMMENT 'Tipo MIME (image/jpeg, application/pdf, etc)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    
    -- Índices
    INDEX idx_anexos_membro (membro_id),
    INDEX idx_anexos_tipo (tipo),
    
    -- Foreign Keys
    CONSTRAINT fk_anexos_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Anexos de membros (fotos, documentos, etc)';

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
-- 
-- Observações importantes:
-- 
-- 1. Todas as tabelas usam UUID (VARCHAR(36)) como chave primária
-- 2. Soft delete implementado via campo 'status' (status = 'bloqueado')
-- 3. Campos JSON para dados flexíveis (preferencias_contato, dias_turnos, habilidades)
-- 4. Auditoria completa com created_at, updated_at, created_by, updated_by
-- 5. Foreign keys configuradas com ON DELETE CASCADE ou ON DELETE SET NULL
-- 6. Índices criados para otimização de queries frequentes
-- 
-- Para aplicar os índices de performance, execute também:
-- performance_indices.sql
-- 
-- =====================================================

