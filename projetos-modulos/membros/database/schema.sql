-- =====================================================
-- MÓDULO DE CADASTRO DE MEMBROS - SCHEMA MYSQL CORRIGIDO
-- Sistema de Gestão Paroquial
-- =====================================================

-- =====================================================
-- 1. CADASTROS BASE
-- =====================================================

-- Tabela principal de membros
CREATE TABLE IF NOT EXISTS membros_membros (
    id VARCHAR(36) PRIMARY KEY,
    nome_completo VARCHAR(255) NOT NULL,
    apelido VARCHAR(100),
    data_nascimento DATE,
    sexo ENUM('M', 'F', 'Outro'),
    
    -- Contatos
    celular_whatsapp VARCHAR(20),
    email VARCHAR(255),
    telefone_fixo VARCHAR(20),
    
    -- Endereço
    rua VARCHAR(255),
    numero VARCHAR(20),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    uf VARCHAR(2),
    cep VARCHAR(10),
    
    -- Documentos
    cpf VARCHAR(14) UNIQUE,
    rg VARCHAR(20),
    lgpd_consentimento_data TIMESTAMP NULL,
    lgpd_consentimento_finalidade TEXT,
    
    -- Situação pastoral
    paroquiano BOOLEAN DEFAULT false,
    comunidade_ou_capelania VARCHAR(100),
    data_entrada DATE,
    
    -- Outros dados
    foto_url TEXT,
    observacoes_pastorais TEXT,
    
    -- Preferências de contato (JSON)
    preferencias_contato JSON,
    
    -- Disponibilidade
    dias_turnos JSON,
    frequencia ENUM('semanal', 'mensal', 'eventual'),
    periodo ENUM('manha', 'tarde', 'noite'),
    
    -- Habilidades/Carismas (JSON)
    habilidades JSON,
    
    -- Situação
    status ENUM('ativo', 'afastado', 'em_discernimento', 'bloqueado') DEFAULT 'ativo',
    motivo_bloqueio TEXT,
    
    -- Auditoria
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100),
    updated_by VARCHAR(100)
);

-- Tabela de endereços (histórico)
CREATE TABLE IF NOT EXISTS membros_enderecos_membro (
    id VARCHAR(36) PRIMARY KEY,
    membro_id VARCHAR(36) NOT NULL,
    rua VARCHAR(255),
    numero VARCHAR(20),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    uf VARCHAR(2),
    cep VARCHAR(10),
    principal BOOLEAN DEFAULT false,
    data_inicio DATE,
    data_fim DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE
);

-- Tabela de contatos (histórico)
CREATE TABLE IF NOT EXISTS membros_contatos_membro (
    id VARCHAR(36) PRIMARY KEY,
    membro_id VARCHAR(36) NOT NULL,
    tipo ENUM('celular', 'whatsapp', 'email', 'telefone_fixo') NOT NULL,
    valor VARCHAR(255) NOT NULL,
    principal BOOLEAN DEFAULT false,
    data_inicio DATE,
    data_fim DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE
);

-- Tabela de documentos
CREATE TABLE IF NOT EXISTS membros_documentos_membro (
    id VARCHAR(36) PRIMARY KEY,
    membro_id VARCHAR(36) NOT NULL,
    tipo_documento VARCHAR(50) NOT NULL,
    numero VARCHAR(100),
    orgao_emissor VARCHAR(100),
    data_emissao DATE,
    data_vencimento DATE,
    arquivo_url TEXT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE
);

-- Tabela de consentimentos LGPD
CREATE TABLE IF NOT EXISTS membros_consentimentos_lgpd (
    id VARCHAR(36) PRIMARY KEY,
    membro_id VARCHAR(36) NOT NULL,
    finalidade VARCHAR(100) NOT NULL,
    consentimento BOOLEAN NOT NULL,
    data_consentimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_consentimento VARCHAR(45),
    user_agent TEXT,
    versao_termo VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE
);

-- Tabela de habilidades/carismas (normalizada)
CREATE TABLE IF NOT EXISTS membros_habilidades_tags (
    id VARCHAR(36) PRIMARY KEY,
    nome VARCHAR(100) UNIQUE NOT NULL,
    categoria VARCHAR(50),
    descricao TEXT,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de formações/certificações
CREATE TABLE IF NOT EXISTS membros_formacoes (
    id VARCHAR(36) PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    descricao TEXT,
    duracao_meses INT,
    validade_meses INT,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de formações dos membros
CREATE TABLE IF NOT EXISTS membros_membros_formacoes (
    id VARCHAR(36) PRIMARY KEY,
    membro_id VARCHAR(36) NOT NULL,
    formacao_id VARCHAR(36) NOT NULL,
    data_conclusao DATE NOT NULL,
    data_validade DATE,
    instituicao VARCHAR(255),
    certificado_url TEXT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE,
    FOREIGN KEY (formacao_id) REFERENCES membros_formacoes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_membro_formacao (membro_id, formacao_id, data_conclusao)
);

-- =====================================================
-- 2. PASTORAIS E MOVIMENTOS
-- =====================================================

-- Tabela de pastorais/movimentos/serviços
CREATE TABLE IF NOT EXISTS membros_pastorais (
    id VARCHAR(36) PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('pastoral', 'movimento', 'ministerio_liturgico', 'servico') NOT NULL,
    coordenador_id VARCHAR(36),
    vice_coordenador_id VARCHAR(36),
    comunidade_capelania VARCHAR(100),
    
    -- Reunião fixa
    dia_semana VARCHAR(20),
    horario TIME,
    local_reuniao VARCHAR(255),
    
    -- Descrição e comunicação
    finalidade_descricao TEXT,
    whatsapp_grupo_link TEXT,
    email_grupo VARCHAR(255),
    
    -- Status
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (coordenador_id) REFERENCES membros_membros(id),
    FOREIGN KEY (vice_coordenador_id) REFERENCES membros_membros(id)
);

-- Tabela de funções/roles
CREATE TABLE IF NOT EXISTS membros_funcoes (
    id VARCHAR(36) PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    categoria VARCHAR(50),
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de requisitos por função
CREATE TABLE IF NOT EXISTS membros_requisitos_funcao (
    id VARCHAR(36) PRIMARY KEY,
    funcao_id VARCHAR(36) NOT NULL,
    requisito VARCHAR(255) NOT NULL,
    obrigatorio BOOLEAN DEFAULT true,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (funcao_id) REFERENCES membros_funcoes(id) ON DELETE CASCADE
);

-- =====================================================
-- 3. RELACIONAMENTOS E PARTICIPAÇÕES
-- =====================================================

-- Tabela de vínculos membro-pastoral
CREATE TABLE IF NOT EXISTS membros_membros_pastorais (
    id VARCHAR(36) PRIMARY KEY,
    membro_id VARCHAR(36) NOT NULL,
    pastoral_id VARCHAR(36) NOT NULL,
    funcao_id VARCHAR(36),
    data_inicio DATE NOT NULL,
    data_fim DATE,
    status ENUM('ativo', 'pausado', 'finalizado') DEFAULT 'ativo',
    prioridade ENUM('principal', 'secundaria') DEFAULT 'secundaria',
    carga_horaria_semana INT,
    preferencias TEXT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE,
    FOREIGN KEY (pastoral_id) REFERENCES membros_pastorais(id) ON DELETE CASCADE,
    FOREIGN KEY (funcao_id) REFERENCES membros_funcoes(id),
    UNIQUE KEY unique_membro_pastoral_funcao (membro_id, pastoral_id, funcao_id)
);

-- Tabela de eventos
CREATE TABLE IF NOT EXISTS membros_eventos (
    id VARCHAR(36) PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('missa', 'reuniao', 'formacao', 'acao_social', 'feira', 'festa_patronal', 'outro') NOT NULL,
    data_evento DATE NOT NULL,
    horario TIME,
    local VARCHAR(255),
    responsavel_id VARCHAR(36),
    descricao TEXT,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (responsavel_id) REFERENCES membros_membros(id)
);

-- Tabela de itens de escala
CREATE TABLE IF NOT EXISTS membros_itens_escala (
    id VARCHAR(36) PRIMARY KEY,
    evento_id VARCHAR(36) NOT NULL,
    funcao_id VARCHAR(36) NOT NULL,
    quantidade_necessaria INT NOT NULL DEFAULT 1,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES membros_eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (funcao_id) REFERENCES membros_funcoes(id)
);

-- Tabela de alocações (designações)
CREATE TABLE IF NOT EXISTS membros_alocacoes (
    id VARCHAR(36) PRIMARY KEY,
    item_escala_id VARCHAR(36) NOT NULL,
    membro_id VARCHAR(36) NOT NULL,
    status ENUM('convite', 'confirmado', 'faltou', 'substituido') DEFAULT 'convite',
    data_convite TIMESTAMP,
    data_confirmacao TIMESTAMP,
    observacoes TEXT,
    substituto_id VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_escala_id) REFERENCES membros_itens_escala(id) ON DELETE CASCADE,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE,
    FOREIGN KEY (substituto_id) REFERENCES membros_membros(id)
);

-- Tabela de participação e frequência
CREATE TABLE IF NOT EXISTS membros_checkins (
    id VARCHAR(36) PRIMARY KEY,
    evento_id VARCHAR(36) NOT NULL,
    membro_id VARCHAR(36) NOT NULL,
    alocacao_id VARCHAR(36),
    status ENUM('presente', 'ausente_justificado', 'ausente', 'substituido') NOT NULL,
    data_checkin TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    justificativa TEXT,
    creditos_servico DECIMAL(5,2) DEFAULT 0,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES membros_eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE,
    FOREIGN KEY (alocacao_id) REFERENCES membros_alocacoes(id)
);

-- =====================================================
-- 4. COMUNICAÇÃO E PROCESSOS
-- =====================================================

-- Tabela de vagas/solicitações
CREATE TABLE IF NOT EXISTS membros_vagas (
    id VARCHAR(36) PRIMARY KEY,
    pastoral_id VARCHAR(36) NOT NULL,
    funcao_id VARCHAR(36) NOT NULL,
    evento_id VARCHAR(36),
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    quantidade_vagas INT NOT NULL DEFAULT 1,
    data_limite_candidatura TIMESTAMP,
    status ENUM('aberta', 'fechada', 'preenchida') DEFAULT 'aberta',
    created_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pastoral_id) REFERENCES membros_pastorais(id) ON DELETE CASCADE,
    FOREIGN KEY (funcao_id) REFERENCES membros_funcoes(id),
    FOREIGN KEY (evento_id) REFERENCES membros_eventos(id),
    FOREIGN KEY (created_by) REFERENCES membros_membros(id)
);

-- Tabela de candidaturas
CREATE TABLE IF NOT EXISTS membros_candidaturas (
    id VARCHAR(36) PRIMARY KEY,
    vaga_id VARCHAR(36) NOT NULL,
    membro_id VARCHAR(36) NOT NULL,
    status ENUM('pendente', 'aprovada', 'rejeitada', 'cancelada') DEFAULT 'pendente',
    data_candidatura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao TIMESTAMP,
    aprovado_por VARCHAR(36),
    observacoes TEXT,
    FOREIGN KEY (vaga_id) REFERENCES membros_vagas(id) ON DELETE CASCADE,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE,
    FOREIGN KEY (aprovado_por) REFERENCES membros_membros(id),
    UNIQUE KEY unique_vaga_membro (vaga_id, membro_id)
);

-- Tabela de comunicados
CREATE TABLE IF NOT EXISTS membros_comunicados (
    id VARCHAR(36) PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    tipo ENUM('escala', 'formacao', 'evento_especial', 'mudanca_horario', 'geral') NOT NULL,
    destinatarios JSON,
    enviado_por VARCHAR(36),
    data_envio TIMESTAMP,
    status ENUM('rascunho', 'enviado', 'cancelado') DEFAULT 'rascunho',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enviado_por) REFERENCES membros_membros(id)
);

-- Tabela de anexos
CREATE TABLE IF NOT EXISTS membros_anexos (
    id VARCHAR(36) PRIMARY KEY,
    entidade_tipo VARCHAR(50) NOT NULL,
    entidade_id VARCHAR(36) NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    tipo_arquivo VARCHAR(100),
    tamanho_bytes BIGINT,
    url_arquivo TEXT NOT NULL,
    descricao TEXT,
    created_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES membros_membros(id)
);

-- =====================================================
-- 5. AUDITORIA E LOGS
-- =====================================================

-- Tabela de logs de auditoria
CREATE TABLE IF NOT EXISTS membros_auditoria_logs (
    id VARCHAR(36) PRIMARY KEY,
    entidade_tipo VARCHAR(50) NOT NULL,
    entidade_id VARCHAR(36) NOT NULL,
    acao ENUM('create', 'update', 'delete', 'view') NOT NULL,
    campo_alterado VARCHAR(100),
    valor_anterior TEXT,
    valor_novo TEXT,
    usuario_id VARCHAR(36),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES membros_membros(id)
);

-- =====================================================
-- 6. ÍNDICES PARA PERFORMANCE
-- =====================================================

-- Índices para membros
CREATE INDEX idx_membros_nome ON membros_membros(nome_completo);
CREATE INDEX idx_membros_cpf ON membros_membros(cpf);
CREATE INDEX idx_membros_email ON membros_membros(email);
CREATE INDEX idx_membros_status ON membros_membros(status);
CREATE INDEX idx_membros_data_entrada ON membros_membros(data_entrada);
CREATE INDEX idx_membros_paroquiano ON membros_membros(paroquiano);

-- Índices para relacionamentos
CREATE INDEX idx_membros_pastorais_membro ON membros_membros_pastorais(membro_id);
CREATE INDEX idx_membros_pastorais_pastoral ON membros_membros_pastorais(pastoral_id);
CREATE INDEX idx_membros_pastorais_funcao ON membros_membros_pastorais(funcao_id);
CREATE INDEX idx_membros_pastorais_status ON membros_membros_pastorais(status);

-- Índices para eventos e escalas
CREATE INDEX idx_eventos_data ON membros_eventos(data_evento);
CREATE INDEX idx_eventos_tipo ON membros_eventos(tipo);
CREATE INDEX idx_alocacoes_evento ON membros_alocacoes(item_escala_id);
CREATE INDEX idx_alocacoes_membro ON membros_alocacoes(membro_id);
CREATE INDEX idx_checkins_evento ON membros_checkins(evento_id);
CREATE INDEX idx_checkins_membro ON membros_checkins(membro_id);

-- Índices para comunicação
CREATE INDEX idx_vagas_pastoral ON membros_vagas(pastoral_id);
CREATE INDEX idx_vagas_status ON membros_vagas(status);
CREATE INDEX idx_candidaturas_vaga ON membros_candidaturas(vaga_id);
CREATE INDEX idx_candidaturas_membro ON membros_candidaturas(membro_id);

-- Índices para auditoria
CREATE INDEX idx_auditoria_entidade ON membros_auditoria_logs(entidade_tipo, entidade_id);
CREATE INDEX idx_auditoria_usuario ON membros_auditoria_logs(usuario_id);
CREATE INDEX idx_auditoria_data ON membros_auditoria_logs(created_at);
