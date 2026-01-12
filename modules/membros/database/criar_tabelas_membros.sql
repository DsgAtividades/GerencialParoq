-- Active: 1760110745587@@127.0.0.1@3306@gerencialparoq
-- =====================================================
-- SCRIPT DE CRIAÇÃO DAS TABELAS DO MÓDULO MEMBROS
-- Sistema de Gestão Paroquial - GerencialParoq
-- =====================================================
-- 
-- Este script cria todas as tabelas necessárias para o módulo de Membros
-- Baseado na análise completa do módulo (ANALISE_COMPLETA_MODULO_MEMBROS.md)
--
<<<<<<< HEAD
-- IMPORTANTE: Este script cria todas as tabelas primeiro, depois adiciona
-- as foreign keys. Isso garante que não haverá problemas de ordem de criação.
--
-- =====================================================

-- Desabilitar verificação de foreign keys temporariamente
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- PARTE 1: CRIAR TODAS AS TABELAS (SEM FOREIGN KEYS)
-- =====================================================

=======
-- IMPORTANTE: Execute este script em ordem para garantir que as foreign keys
-- sejam criadas corretamente
--
-- =====================================================

>>>>>>> main
-- =====================================================
-- 1. TABELA PRINCIPAL: membros_membros
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_membros (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do membro',
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
<<<<<<< HEAD
    INDEX idx_membros_status_nome (status, nome_completo)
    -- NOTA: UNIQUE constraints removidas de campos NULL para evitar problemas
    -- Email e CPF devem ser validados na aplicação quando preenchidos
=======
    INDEX idx_membros_status_nome (status, nome_completo),
    -- Constraints
    UNIQUE KEY uk_membros_email (email),
    UNIQUE KEY uk_membros_cpf (cpf)
>>>>>>> main
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
<<<<<<< HEAD
    INDEX idx_pastorais_vice_coordenador (vice_coordenador_id)
=======
    INDEX idx_pastorais_vice_coordenador (vice_coordenador_id),
    -- Foreign Keys
    CONSTRAINT fk_pastorais_coordenador FOREIGN KEY (coordenador_id) 
        REFERENCES membros_membros(id) ON DELETE SET NULL,
    CONSTRAINT fk_pastorais_vice_coordenador FOREIGN KEY (vice_coordenador_id) 
        REFERENCES membros_membros(id) ON DELETE SET NULL
>>>>>>> main
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
<<<<<<< HEAD
=======
    -- Foreign Keys
    CONSTRAINT fk_membros_pastorais_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE,
    CONSTRAINT fk_membros_pastorais_pastoral FOREIGN KEY (pastoral_id) 
        REFERENCES membros_pastorais(id) ON DELETE CASCADE,
    CONSTRAINT fk_membros_pastorais_funcao FOREIGN KEY (funcao_id) 
        REFERENCES membros_funcoes(id) ON DELETE SET NULL,
>>>>>>> main
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
<<<<<<< HEAD
    -- Auditoria
=======
    -- Auitoria
>>>>>>> main
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    -- Índices
    INDEX idx_eventos_nome (nome),
    INDEX idx_eventos_tipo (tipo),
    INDEX idx_eventos_data (data_evento),
    INDEX idx_eventos_ativo (ativo),
    INDEX idx_eventos_data_ativo (data_evento, ativo),
<<<<<<< HEAD
    INDEX idx_eventos_responsavel (responsavel_id)
=======
    INDEX idx_eventos_responsavel (responsavel_id),
    -- Foreign Keys
    CONSTRAINT fk_eventos_responsavel FOREIGN KEY (responsavel_id) 
        REFERENCES membros_membros(id) ON DELETE SET NULL
>>>>>>> main
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
<<<<<<< HEAD
=======
        -- Foreign Keys
    CONSTRAINT fk_eventos_pastorais_evento FOREIGN KEY (evento_id) 
        REFERENCES membros_eventos(id) ON DELETE CASCADE,
    CONSTRAINT fk_eventos_pastorais_pastoral FOREIGN KEY (pastoral_id) 
        REFERENCES membros_pastorais(id) ON DELETE CASCADE,
>>>>>>> main
        -- Constraint: Um evento não pode estar vinculado à mesma pastoral duas vezes
    UNIQUE KEY uk_evento_pastoral (evento_id, pastoral_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relacionamento N:N entre eventos e pastorais';

-- =====================================================
-- 7. TABELA: membros_escalas_eventos
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_escalas_eventos (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID da escala',
<<<<<<< HEAD
    titulo VARCHAR(255) NOT NULL COMMENT 'Título/nome da escala',
    descricao TEXT DEFAULT NULL COMMENT 'Descrição da escala',
        -- Data e Hora
    data DATE NOT NULL COMMENT 'Data do evento da escala',
    hora TIME DEFAULT NULL COMMENT 'Hora do evento',
=======
    nome VARCHAR(255) NOT NULL COMMENT 'Nome/título da escala',
    descricao TEXT DEFAULT NULL COMMENT 'Descrição da escala',
        -- Data e Hora
    data_evento DATE NOT NULL COMMENT 'Data do evento da escala',
    hora_inicio TIME DEFAULT NULL COMMENT 'Hora de início',
    hora_fim TIME DEFAULT NULL COMMENT 'Hora de término',
>>>>>>> main
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
<<<<<<< HEAD
    INDEX idx_escalas_eventos_titulo (titulo),
    INDEX idx_escalas_eventos_data (data),
    INDEX idx_escalas_eventos_pastoral (pastoral_id),
    INDEX idx_escalas_eventos_pastoral_data (pastoral_id, data),
    INDEX idx_escalas_eventos_created_by (created_by)
=======
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
>>>>>>> main
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
<<<<<<< HEAD
    INDEX idx_escalas_funcoes_nome (nome_funcao)
=======
    INDEX idx_escalas_funcoes_nome (nome_funcao),
        -- Foreign Keys
    CONSTRAINT fk_escalas_funcoes_evento FOREIGN KEY (evento_id) 
        REFERENCES membros_escalas_eventos(id) ON DELETE CASCADE
>>>>>>> main
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
<<<<<<< HEAD
=======
        -- Foreign Keys
    CONSTRAINT fk_escalas_funcao_membros_funcao FOREIGN KEY (funcao_id) 
        REFERENCES membros_escalas_funcoes(id) ON DELETE CASCADE,
    CONSTRAINT fk_escalas_funcao_membros_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE,
>>>>>>> main
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
<<<<<<< HEAD
    INDEX idx_escalas_logs_created_at (created_at)
=======
    INDEX idx_escalas_logs_created_at (created_at),
        -- Foreign Keys
    CONSTRAINT fk_escalas_logs_evento FOREIGN KEY (evento_id) 
        REFERENCES membros_escalas_eventos(id) ON DELETE CASCADE
>>>>>>> main
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
<<<<<<< HEAD
    -- Índices
    INDEX idx_consentimentos_membro (membro_id),
    INDEX idx_consentimentos_finalidade (finalidade),
    INDEX idx_consentimentos_data (data_consentimento)
=======
        -- Ídices
    INDEX idx_consentimentos_membro (membro_id),
    INDEX idx_consentimentos_finalidade (finalidade),
    INDEX idx_consentimentos_data (data_consentimento),
        -- Foreign Keys
    CONSTRAINT fk_consentimentos_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE
>>>>>>> main
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
<<<<<<< HEAD
-- 13. TABELA: membros_enderecos_membro
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_enderecos_membro (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do endereço',
    membro_id VARCHAR(36) NOT NULL COMMENT 'ID do membro (FK membros_membros)',
    tipo VARCHAR(50) DEFAULT 'residencial' COMMENT 'Tipo: residencial, comercial, etc',
    rua VARCHAR(255) DEFAULT NULL COMMENT 'Rua/Logradouro',
    numero VARCHAR(20) DEFAULT NULL COMMENT 'Número',
    complemento VARCHAR(100) DEFAULT NULL COMMENT 'Complemento',
    bairro VARCHAR(100) DEFAULT NULL COMMENT 'Bairro',
    cidade VARCHAR(100) DEFAULT NULL COMMENT 'Cidade',
    uf CHAR(2) DEFAULT NULL COMMENT 'Estado (UF)',
    cep VARCHAR(10) DEFAULT NULL COMMENT 'CEP',
    principal TINYINT(1) DEFAULT 0 COMMENT '1 = Endereço principal, 0 = Não principal',
    data_inicio DATE DEFAULT NULL COMMENT 'Data de início do endereço',
    data_fim DATE DEFAULT NULL COMMENT 'Data de término (se houver)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    -- Índices
    INDEX idx_enderecos_membro (membro_id),
    INDEX idx_enderecos_principal (membro_id, principal),
    INDEX idx_enderecos_cidade (cidade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Endereços dos membros (permite múltiplos endereços)';

-- =====================================================
-- 14. TABELA: membros_contatos_membro
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_contatos_membro (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do contato',
    membro_id VARCHAR(36) NOT NULL COMMENT 'ID do membro (FK membros_membros)',
    tipo VARCHAR(50) NOT NULL COMMENT 'Tipo: email, telefone, celular, whatsapp, etc',
    valor VARCHAR(255) NOT NULL COMMENT 'Valor do contato',
    principal TINYINT(1) DEFAULT 0 COMMENT '1 = Contato principal, 0 = Não principal',
    observacoes TEXT DEFAULT NULL COMMENT 'Observações sobre o contato',
    data_inicio DATE DEFAULT NULL COMMENT 'Data de início do contato',
    data_fim DATE DEFAULT NULL COMMENT 'Data de término (se houver)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    -- Índices
    INDEX idx_contatos_membro (membro_id),
    INDEX idx_contatos_principal (membro_id, principal),
    INDEX idx_contatos_tipo (tipo),
    INDEX idx_contatos_valor (valor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contatos dos membros (permite múltiplos contatos)';

-- =====================================================
-- 15. TABELA: membros_documentos_membro
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_documentos_membro (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do documento',
    membro_id VARCHAR(36) NOT NULL COMMENT 'ID do membro (FK membros_membros)',
    tipo_documento VARCHAR(100) NOT NULL COMMENT 'Tipo: RG, CPF, CNH, Certidão, etc',
    numero VARCHAR(50) NOT NULL COMMENT 'Número do documento',
    orgao_emissor VARCHAR(100) DEFAULT NULL COMMENT 'Órgão emissor',
    data_emissao DATE DEFAULT NULL COMMENT 'Data de emissão',
    data_vencimento DATE DEFAULT NULL COMMENT 'Data de vencimento',
    arquivo_url VARCHAR(500) DEFAULT NULL COMMENT 'URL do arquivo do documento',
    observacoes TEXT DEFAULT NULL COMMENT 'Observações sobre o documento',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    -- Índices
    INDEX idx_documentos_membro (membro_id),
    INDEX idx_documentos_tipo (tipo_documento),
    INDEX idx_documentos_numero (numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Documentos dos membros (permite múltiplos documentos)';

-- =====================================================
-- 16. TABELA: membros_formacoes
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_formacoes (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID da formação',
    nome VARCHAR(255) NOT NULL COMMENT 'Nome da formação',
    descricao TEXT DEFAULT NULL COMMENT 'Descrição da formação',
    tipo VARCHAR(100) DEFAULT NULL COMMENT 'Tipo da formação',
    categoria VARCHAR(100) DEFAULT NULL COMMENT 'Categoria da formação',
    duracao_horas INT DEFAULT NULL COMMENT 'Duração em horas',
    ativo TINYINT(1) DEFAULT 1 COMMENT '1 = Ativa, 0 = Inativa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    -- Índices
    INDEX idx_formacoes_nome (nome),
    INDEX idx_formacoes_tipo (tipo),
    INDEX idx_formacoes_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de formações disponíveis';

-- =====================================================
-- 17. TABELA: membros_membros_formacoes (Relacionamento N:N)
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_membros_formacoes (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do relacionamento',
    membro_id VARCHAR(36) NOT NULL COMMENT 'ID do membro (FK membros_membros)',
    formacao_id VARCHAR(36) NOT NULL COMMENT 'ID da formação (FK membros_formacoes)',
    data_conclusao DATE DEFAULT NULL COMMENT 'Data de conclusão',
    data_validade DATE DEFAULT NULL COMMENT 'Data de validade (se houver)',
    instituicao VARCHAR(255) DEFAULT NULL COMMENT 'Instituição que emitiu',
    certificado_url VARCHAR(500) DEFAULT NULL COMMENT 'URL do certificado',
    observacoes TEXT DEFAULT NULL COMMENT 'Observações',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    -- Índices
    INDEX idx_membros_formacoes_membro (membro_id),
    INDEX idx_membros_formacoes_formacao (formacao_id),
    INDEX idx_membros_formacoes_data_conclusao (data_conclusao),
    -- Constraint: Um membro não pode ter a mesma formação duas vezes
    UNIQUE KEY uk_membro_formacao (membro_id, formacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relacionamento N:N entre membros e formações';

-- =====================================================
-- 18. TABELA: membros_checkins
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_checkins (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do check-in',
    membro_id VARCHAR(36) NOT NULL COMMENT 'ID do membro (FK membros_membros)',
    evento_id VARCHAR(36) DEFAULT NULL COMMENT 'ID do evento (FK membros_eventos ou membros_escalas_eventos)',
    tipo_evento VARCHAR(50) DEFAULT NULL COMMENT 'Tipo: evento, escala, etc',
    data_checkin DATETIME NOT NULL COMMENT 'Data e hora do check-in',
    local VARCHAR(255) DEFAULT NULL COMMENT 'Local do check-in',
    observacoes TEXT DEFAULT NULL COMMENT 'Observações',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    -- Índices
    INDEX idx_checkins_membro (membro_id),
    INDEX idx_checkins_evento (evento_id),
    INDEX idx_checkins_data (data_checkin),
    INDEX idx_checkins_membro_data (membro_id, data_checkin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Check-ins de membros em eventos';

-- =====================================================
-- 19. TABELA: membros_alocacoes
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_alocacoes (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID da alocação',
    membro_id VARCHAR(36) NOT NULL COMMENT 'ID do membro (FK membros_membros)',
    evento_id VARCHAR(36) DEFAULT NULL COMMENT 'ID do evento',
    funcao_id VARCHAR(36) DEFAULT NULL COMMENT 'ID da função (FK membros_escalas_funcoes)',
    data_alocacao DATE NOT NULL COMMENT 'Data da alocação',
    status VARCHAR(50) DEFAULT 'confirmado' COMMENT 'Status: confirmado, pendente, ausente, etc',
    observacoes TEXT DEFAULT NULL COMMENT 'Observações',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    -- Índices
    INDEX idx_alocacoes_membro (membro_id),
    INDEX idx_alocacoes_evento (evento_id),
    INDEX idx_alocacoes_funcao (funcao_id),
    INDEX idx_alocacoes_data (data_alocacao),
    INDEX idx_alocacoes_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Alocações de membros em eventos e funções';

-- =====================================================
-- 20. TABELA: membros_candidaturas
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_candidaturas (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID da candidatura',
    membro_id VARCHAR(36) NOT NULL COMMENT 'ID do membro (FK membros_membros)',
    pastoral_id VARCHAR(36) DEFAULT NULL COMMENT 'ID da pastoral (FK membros_pastorais)',
    funcao_id VARCHAR(36) DEFAULT NULL COMMENT 'ID da função (FK membros_funcoes)',
    data_candidatura DATE NOT NULL COMMENT 'Data da candidatura',
    status VARCHAR(50) DEFAULT 'pendente' COMMENT 'Status: pendente, aprovada, rejeitada, etc',
    observacoes TEXT DEFAULT NULL COMMENT 'Observações',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    -- Índices
    INDEX idx_candidaturas_membro (membro_id),
    INDEX idx_candidaturas_pastoral (pastoral_id),
    INDEX idx_candidaturas_funcao (funcao_id),
    INDEX idx_candidaturas_status (status),
    INDEX idx_candidaturas_data (data_candidatura)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Candidaturas de membros para pastorais/funções';

-- =====================================================
-- 21. TABELA: membros_anexos (ATUALIZADA)
-- =====================================================
CREATE TABLE IF NOT EXISTS membros_anexos (
    id VARCHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID do anexo',
    entidade_tipo VARCHAR(50) DEFAULT 'membro' COMMENT 'Tipo da entidade: membro, pastoral, evento, etc',
    entidade_id VARCHAR(36) DEFAULT NULL COMMENT 'ID da entidade relacionada',
    membro_id VARCHAR(36) DEFAULT NULL COMMENT 'ID do membro (FK membros_membros) - DEPRECATED, usar entidade_id',
    tipo ENUM('foto', 'documento', 'outro') DEFAULT 'outro' COMMENT 'Tipo do anexo',
    nome_arquivo VARCHAR(255) NOT NULL COMMENT 'Nome do arquivo',
    tipo_arquivo VARCHAR(100) DEFAULT NULL COMMENT 'Tipo MIME (image/jpeg, application/pdf, etc)',
    tamanho_bytes INT DEFAULT NULL COMMENT 'Tamanho em bytes',
    url_arquivo VARCHAR(500) NOT NULL COMMENT 'URL completa do arquivo',
    caminho_arquivo VARCHAR(500) DEFAULT NULL COMMENT 'Caminho completo do arquivo (DEPRECATED, usar url_arquivo)',
    descricao TEXT DEFAULT NULL COMMENT 'Descrição do anexo',
    created_by VARCHAR(36) DEFAULT NULL COMMENT 'ID do usuário que criou',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
        -- Índices
    INDEX idx_anexos_entidade (entidade_tipo, entidade_id),
    INDEX idx_anexos_membro (membro_id),
    INDEX idx_anexos_tipo (tipo),
    INDEX idx_anexos_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Anexos de membros e outras entidades (fotos, documentos, etc)';

-- =====================================================
-- PARTE 2: ADICIONAR FOREIGN KEYS
-- =====================================================
-- NOTA: Cada foreign key é adicionada em um ALTER TABLE separado
-- para evitar problemas de sintaxe e facilitar depuração

-- Foreign Keys para membros_pastorais
ALTER TABLE membros_pastorais
    ADD CONSTRAINT fk_pastorais_coordenador FOREIGN KEY (coordenador_id) 
        REFERENCES membros_membros(id) ON DELETE SET NULL;

ALTER TABLE membros_pastorais
    ADD CONSTRAINT fk_pastorais_vice_coordenador FOREIGN KEY (vice_coordenador_id) 
        REFERENCES membros_membros(id) ON DELETE SET NULL;

-- Foreign Keys para membros_membros_pastorais
ALTER TABLE membros_membros_pastorais
    ADD CONSTRAINT fk_membros_pastorais_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE;

ALTER TABLE membros_membros_pastorais
    ADD CONSTRAINT fk_membros_pastorais_pastoral FOREIGN KEY (pastoral_id) 
        REFERENCES membros_pastorais(id) ON DELETE CASCADE;

ALTER TABLE membros_membros_pastorais
    ADD CONSTRAINT fk_membros_pastorais_funcao FOREIGN KEY (funcao_id) 
        REFERENCES membros_funcoes(id) ON DELETE SET NULL;

-- Foreign Keys para membros_eventos
ALTER TABLE membros_eventos
    ADD CONSTRAINT fk_eventos_responsavel FOREIGN KEY (responsavel_id) 
        REFERENCES membros_membros(id) ON DELETE SET NULL;

-- Foreign Keys para membros_eventos_pastorais
ALTER TABLE membros_eventos_pastorais
    ADD CONSTRAINT fk_eventos_pastorais_evento FOREIGN KEY (evento_id) 
        REFERENCES membros_eventos(id) ON DELETE CASCADE;

ALTER TABLE membros_eventos_pastorais
    ADD CONSTRAINT fk_eventos_pastorais_pastoral FOREIGN KEY (pastoral_id) 
        REFERENCES membros_pastorais(id) ON DELETE CASCADE;

-- Foreign Keys para membros_escalas_eventos
ALTER TABLE membros_escalas_eventos
    ADD CONSTRAINT fk_escalas_eventos_pastoral FOREIGN KEY (pastoral_id) 
        REFERENCES membros_pastorais(id) ON DELETE CASCADE;

ALTER TABLE membros_escalas_eventos
    ADD CONSTRAINT fk_escalas_eventos_created_by FOREIGN KEY (created_by) 
        REFERENCES membros_membros(id) ON DELETE SET NULL;

-- Foreign Keys para membros_escalas_funcoes
ALTER TABLE membros_escalas_funcoes
    ADD CONSTRAINT fk_escalas_funcoes_evento FOREIGN KEY (evento_id) 
        REFERENCES membros_escalas_eventos(id) ON DELETE CASCADE;

-- Foreign Keys para membros_escalas_funcao_membros
ALTER TABLE membros_escalas_funcao_membros
    ADD CONSTRAINT fk_escalas_funcao_membros_funcao FOREIGN KEY (funcao_id) 
        REFERENCES membros_escalas_funcoes(id) ON DELETE CASCADE;

ALTER TABLE membros_escalas_funcao_membros
    ADD CONSTRAINT fk_escalas_funcao_membros_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE;

-- Foreign Keys para membros_escalas_logs
ALTER TABLE membros_escalas_logs
    ADD CONSTRAINT fk_escalas_logs_evento FOREIGN KEY (evento_id) 
        REFERENCES membros_escalas_eventos(id) ON DELETE CASCADE;

-- Foreign Keys para membros_consentimentos_lgpd
ALTER TABLE membros_consentimentos_lgpd
    ADD CONSTRAINT fk_consentimentos_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE;

-- Foreign Keys para membros_enderecos_membro
ALTER TABLE membros_enderecos_membro
    ADD CONSTRAINT fk_enderecos_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE;

-- Foreign Keys para membros_contatos_membro
ALTER TABLE membros_contatos_membro
    ADD CONSTRAINT fk_contatos_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE;

-- Foreign Keys para membros_documentos_membro
ALTER TABLE membros_documentos_membro
    ADD CONSTRAINT fk_documentos_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE;

-- Foreign Keys para membros_membros_formacoes
ALTER TABLE membros_membros_formacoes
    ADD CONSTRAINT fk_membros_formacoes_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE;

ALTER TABLE membros_membros_formacoes
    ADD CONSTRAINT fk_membros_formacoes_formacao FOREIGN KEY (formacao_id) 
        REFERENCES membros_formacoes(id) ON DELETE CASCADE;

-- Foreign Keys para membros_checkins
ALTER TABLE membros_checkins
    ADD CONSTRAINT fk_checkins_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE;

-- Foreign Keys para membros_alocacoes
ALTER TABLE membros_alocacoes
    ADD CONSTRAINT fk_alocacoes_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE;

ALTER TABLE membros_alocacoes
    ADD CONSTRAINT fk_alocacoes_funcao FOREIGN KEY (funcao_id) 
        REFERENCES membros_escalas_funcoes(id) ON DELETE SET NULL;

-- Foreign Keys para membros_candidaturas
ALTER TABLE membros_candidaturas
    ADD CONSTRAINT fk_candidaturas_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE;

ALTER TABLE membros_candidaturas
    ADD CONSTRAINT fk_candidaturas_pastoral FOREIGN KEY (pastoral_id) 
        REFERENCES membros_pastorais(id) ON DELETE SET NULL;

ALTER TABLE membros_candidaturas
    ADD CONSTRAINT fk_candidaturas_funcao FOREIGN KEY (funcao_id) 
        REFERENCES membros_funcoes(id) ON DELETE SET NULL;

-- Foreign Keys para membros_anexos
ALTER TABLE membros_anexos
    ADD CONSTRAINT fk_anexos_membro FOREIGN KEY (membro_id) 
        REFERENCES membros_membros(id) ON DELETE CASCADE;

-- Reabilitar verificação de foreign keys
SET FOREIGN_KEY_CHECKS = 1;
=======
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
>>>>>>> main

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
-- 
<<<<<<< HEAD
-- RESUMO DAS TABELAS CRIADAS (21 tabelas):
-- 
-- Tabelas Principais:
-- 1. membros_membros - Tabela principal de membros
-- 2. membros_funcoes - Funções/cargos
-- 3. membros_pastorais - Pastorais
-- 4. membros_eventos - Eventos gerais
-- 5. membros_formacoes - Catálogo de formações
-- 
-- Tabelas de Relacionamento:
-- 6. membros_membros_pastorais - N:N membros-pastorais
-- 7. membros_eventos_pastorais - N:N eventos-pastorais
-- 8. membros_membros_formacoes - N:N membros-formações
-- 
-- Tabelas de Dados Relacionados:
-- 9. membros_enderecos_membro - Endereços dos membros
-- 10. membros_contatos_membro - Contatos dos membros
-- 11. membros_documentos_membro - Documentos dos membros
-- 
-- Tabelas de Escalas:
-- 12. membros_escalas_eventos - Escalas de eventos
-- 13. membros_escalas_funcoes - Funções em escalas
-- 14. membros_escalas_funcao_membros - Membros em funções
-- 15. membros_escalas_logs - Logs de escalas
-- 
-- Tabelas de Operações:
-- 16. membros_checkins - Check-ins de membros
-- 17. membros_alocacoes - Alocações de membros
-- 18. membros_candidaturas - Candidaturas de membros
-- 
-- Tabelas de Sistema:
-- 19. membros_consentimentos_lgpd - Consentimentos LGPD
-- 20. membros_auditoria_logs - Logs de auditoria
-- 21. membros_anexos - Anexos (fotos, documentos)
-- 
=======
>>>>>>> main
-- Observações importantes:
-- 
-- 1. Todas as tabelas usam UUID (VARCHAR(36)) como chave primária
-- 2. Soft delete implementado via campo 'status' (status = 'bloqueado')
-- 3. Campos JSON para dados flexíveis (preferencias_contato, dias_turnos, habilidades)
-- 4. Auditoria completa com created_at, updated_at, created_by, updated_by
-- 5. Foreign keys configuradas com ON DELETE CASCADE ou ON DELETE SET NULL
-- 6. Índices criados para otimização de queries frequentes
<<<<<<< HEAD
-- 7. Tabelas de endereços, contatos e documentos permitem múltiplos registros por membro
-- 8. Tabela de anexos suporta múltiplas entidades (membros, pastorais, eventos)
=======
>>>>>>> main
-- 
-- Para aplicar os índices de performance, execute também:
-- performance_indices.sql
-- 
<<<<<<< HEAD
-- Verificar se todas as tabelas foram criadas:
-- Execute: SHOW TABLES LIKE 'membros_%';
-- Deve retornar 21 tabelas
-- 
-- =====================================================
=======
-- =====================================================

>>>>>>> main
