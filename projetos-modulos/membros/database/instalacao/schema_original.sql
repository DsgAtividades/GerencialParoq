-- =====================================================
-- INSTALAÇÃO DAS TABELAS DO MÓDULO DE MEMBROS
-- Banco: gerencialparoq
-- Sistema: GerencialParoq
-- Data: 2024-01-23
-- =====================================================

-- Verificar se o banco existe
USE gerencialparoq;

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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de endereços específicos do membro
CREATE TABLE IF NOT EXISTS membros_enderecos_membro (
    id VARCHAR(36) PRIMARY KEY,
    membro_id VARCHAR(36) NOT NULL,
    tipo ENUM('residencial', 'comercial', 'correspondencia') DEFAULT 'residencial',
    rua VARCHAR(255) NOT NULL,
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    uf VARCHAR(2),
    cep VARCHAR(10),
    principal BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE
);

-- Tabela de contatos específicos do membro
CREATE TABLE IF NOT EXISTS membros_contatos_membro (
    id VARCHAR(36) PRIMARY KEY,
    membro_id VARCHAR(36) NOT NULL,
    tipo ENUM('celular', 'telefone_fixo', 'whatsapp', 'email', 'outro') NOT NULL,
    valor VARCHAR(255) NOT NULL,
    principal BOOLEAN DEFAULT false,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE
);

-- Tabela de documentos do membro
CREATE TABLE IF NOT EXISTS membros_documentos_membro (
    id VARCHAR(36) PRIMARY KEY,
    membro_id VARCHAR(36) NOT NULL,
    tipo ENUM('cpf', 'rg', 'cnh', 'passaporte', 'certidao_nascimento', 'certidao_casamento', 'outro') NOT NULL,
    numero VARCHAR(100) NOT NULL,
    orgao_emissor VARCHAR(100),
    data_emissao DATE,
    data_validade DATE,
    arquivo_url TEXT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE
);

-- Tabela de consentimentos LGPD
CREATE TABLE IF NOT EXISTS membros_consentimentos_lgpd (
    id VARCHAR(36) PRIMARY KEY,
    membro_id VARCHAR(36) NOT NULL,
    finalidade VARCHAR(255) NOT NULL,
    consentimento BOOLEAN NOT NULL,
    data_consentimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    observacoes TEXT,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE
);

-- Tabela de habilidades/carismas
CREATE TABLE IF NOT EXISTS membros_habilidades_tags (
    id VARCHAR(36) PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    categoria VARCHAR(50),
    descricao TEXT,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de formações/certificações
CREATE TABLE IF NOT EXISTS membros_formacoes (
    id VARCHAR(36) PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('curso', 'certificacao', 'workshop', 'seminario', 'outro') NOT NULL,
    descricao TEXT,
    carga_horaria INT,
    instituicao VARCHAR(255),
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
    status ENUM('designado', 'confirmado', 'presente', 'ausente', 'justificado') DEFAULT 'designado',
    data_designacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_confirmacao TIMESTAMP NULL,
    observacoes TEXT,
    FOREIGN KEY (item_escala_id) REFERENCES membros_itens_escala(id) ON DELETE CASCADE,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE
);

-- Tabela de check-ins
CREATE TABLE IF NOT EXISTS membros_checkins (
    id VARCHAR(36) PRIMARY KEY,
    membro_id VARCHAR(36) NOT NULL,
    evento_id VARCHAR(36),
    data_checkin TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('entrada', 'saida', 'pausa', 'retorno') DEFAULT 'entrada',
    observacoes TEXT,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE,
    FOREIGN KEY (evento_id) REFERENCES membros_eventos(id) ON DELETE SET NULL
);

-- =====================================================
-- 4. SISTEMA DE VAGAS E CANDIDATURAS
-- =====================================================

-- Tabela de vagas
CREATE TABLE IF NOT EXISTS membros_vagas (
    id VARCHAR(36) PRIMARY KEY,
    pastoral_id VARCHAR(36) NOT NULL,
    funcao_id VARCHAR(36) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    requisitos TEXT,
    carga_horaria_semana INT,
    data_abertura DATE NOT NULL,
    data_fechamento DATE,
    quantidade_vagas INT DEFAULT 1,
    status ENUM('aberta', 'pausada', 'fechada', 'preenchida') DEFAULT 'aberta',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pastoral_id) REFERENCES membros_pastorais(id) ON DELETE CASCADE,
    FOREIGN KEY (funcao_id) REFERENCES membros_funcoes(id)
);

-- Tabela de candidaturas
CREATE TABLE IF NOT EXISTS membros_candidaturas (
    id VARCHAR(36) PRIMARY KEY,
    vaga_id VARCHAR(36) NOT NULL,
    membro_id VARCHAR(36) NOT NULL,
    status ENUM('pendente', 'aprovada', 'rejeitada', 'cancelada') DEFAULT 'pendente',
    data_candidatura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_avaliacao TIMESTAMP NULL,
    avaliador_id VARCHAR(36),
    observacoes TEXT,
    FOREIGN KEY (vaga_id) REFERENCES membros_vagas(id) ON DELETE CASCADE,
    FOREIGN KEY (membro_id) REFERENCES membros_membros(id) ON DELETE CASCADE,
    FOREIGN KEY (avaliador_id) REFERENCES membros_membros(id)
);

-- =====================================================
-- 5. COMUNICAÇÃO E NOTIFICAÇÕES
-- =====================================================

-- Tabela de comunicados
CREATE TABLE IF NOT EXISTS membros_comunicados (
    id VARCHAR(36) PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    tipo ENUM('geral', 'pastoral', 'evento', 'urgente') DEFAULT 'geral',
    pastoral_id VARCHAR(36),
    evento_id VARCHAR(36),
    destinatarios JSON,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('rascunho', 'enviado', 'cancelado') DEFAULT 'rascunho',
    created_by VARCHAR(36),
    FOREIGN KEY (pastoral_id) REFERENCES membros_pastorais(id) ON DELETE CASCADE,
    FOREIGN KEY (evento_id) REFERENCES membros_eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES membros_membros(id)
);

-- Tabela de anexos
CREATE TABLE IF NOT EXISTS membros_anexos (
    id VARCHAR(36) PRIMARY KEY,
    tabela_referencia VARCHAR(50) NOT NULL,
    id_referencia VARCHAR(36) NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    caminho_arquivo TEXT NOT NULL,
    tipo_mime VARCHAR(100),
    tamanho_bytes BIGINT,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(36),
    FOREIGN KEY (created_by) REFERENCES membros_membros(id)
);

-- =====================================================
-- 6. AUDITORIA E LOGS
-- =====================================================

-- Tabela de logs de auditoria
CREATE TABLE IF NOT EXISTS membros_auditoria_logs (
    id VARCHAR(36) PRIMARY KEY,
    tabela VARCHAR(50) NOT NULL,
    registro_id VARCHAR(36) NOT NULL,
    acao ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    dados_anteriores JSON,
    dados_novos JSON,
    usuario_id VARCHAR(36),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES membros_membros(id)
);

-- =====================================================
-- 7. ÍNDICES DE PERFORMANCE
-- =====================================================

-- Índices para membros
CREATE INDEX idx_membros_nome ON membros_membros(nome_completo);
CREATE INDEX idx_membros_cpf ON membros_membros(cpf);
CREATE INDEX idx_membros_email ON membros_membros(email);
CREATE INDEX idx_membros_status ON membros_membros(status);
CREATE INDEX idx_membros_paroquiano ON membros_membros(paroquiano);

-- Índices para relacionamentos
CREATE INDEX idx_membros_pastorais_membro ON membros_membros_pastorais(membro_id);
CREATE INDEX idx_membros_pastorais_pastoral ON membros_membros_pastorais(pastoral_id);
CREATE INDEX idx_membros_pastorais_funcao ON membros_membros_pastorais(funcao_id);

-- Índices para eventos
CREATE INDEX idx_eventos_data ON membros_eventos(data_evento);
CREATE INDEX idx_eventos_tipo ON membros_eventos(tipo);
CREATE INDEX idx_eventos_ativo ON membros_eventos(ativo);

-- Índices para check-ins
CREATE INDEX idx_checkins_evento ON membros_checkins(evento_id);
CREATE INDEX idx_checkins_membro ON membros_checkins(membro_id);
CREATE INDEX idx_checkins_data ON membros_checkins(data_checkin);

-- Índices para auditoria
CREATE INDEX idx_auditoria_tabela ON membros_auditoria_logs(tabela);
CREATE INDEX idx_auditoria_registro ON membros_auditoria_logs(registro_id);
CREATE INDEX idx_auditoria_data ON membros_auditoria_logs(created_at);

-- =====================================================
-- 8. TRIGGERS PARA AUDITORIA
-- =====================================================

-- Trigger para auditoria de membros
DELIMITER $$
CREATE TRIGGER tr_membros_audit_insert
    AFTER INSERT ON membros_membros
    FOR EACH ROW
BEGIN
    INSERT INTO membros_auditoria_logs (id, tabela, registro_id, acao, dados_novos, created_at)
    VALUES (UUID(), 'membros_membros', NEW.id, 'INSERT', JSON_OBJECT(
        'nome_completo', NEW.nome_completo,
        'email', NEW.email,
        'status', NEW.status
    ), NOW());
END$$

CREATE TRIGGER tr_membros_audit_update
    AFTER UPDATE ON membros_membros
    FOR EACH ROW
BEGIN
    INSERT INTO membros_auditoria_logs (id, tabela, registro_id, acao, dados_anteriores, dados_novos, created_at)
    VALUES (UUID(), 'membros_membros', NEW.id, 'UPDATE', 
        JSON_OBJECT('nome_completo', OLD.nome_completo, 'email', OLD.email, 'status', OLD.status),
        JSON_OBJECT('nome_completo', NEW.nome_completo, 'email', NEW.email, 'status', NEW.status),
        NOW());
END$$

CREATE TRIGGER tr_membros_audit_delete
    AFTER DELETE ON membros_membros
    FOR EACH ROW
BEGIN
    INSERT INTO membros_auditoria_logs (id, tabela, registro_id, acao, dados_anteriores, created_at)
    VALUES (UUID(), 'membros_membros', OLD.id, 'DELETE', 
        JSON_OBJECT('nome_completo', OLD.nome_completo, 'email', OLD.email, 'status', OLD.status),
        NOW());
END$$
DELIMITER ;

-- =====================================================
-- 9. DADOS INICIAIS
-- =====================================================

-- Inserir habilidades/carismas básicos
INSERT IGNORE INTO membros_habilidades_tags (id, nome, categoria, descricao) VALUES
(UUID(), 'Canto', 'Litúrgico', 'Habilidade para cantar em celebrações'),
(UUID(), 'Instrumento Musical', 'Litúrgico', 'Tocar instrumentos musicais'),
(UUID(), 'Acolhida', 'Pastoral', 'Receber e acolher pessoas'),
(UUID(), 'Catequese', 'Formação', 'Ministrar catequese'),
(UUID(), 'Liturgia', 'Litúrgico', 'Participar da liturgia'),
(UUID(), 'Pastoral Social', 'Social', 'Trabalho social e caritativo'),
(UUID(), 'Jovens', 'Pastoral', 'Trabalho com jovens'),
(UUID(), 'Família', 'Pastoral', 'Pastoral familiar'),
(UUID(), 'Comunicação', 'Técnico', 'Habilidades de comunicação'),
(UUID(), 'Organização', 'Administrativo', 'Organização de eventos'),
(UUID(), 'Tecnologia', 'Técnico', 'Conhecimento em tecnologia'),
(UUID(), 'Liderança', 'Pastoral', 'Liderança de grupos'),
(UUID(), 'Oração', 'Espiritual', 'Ministério de oração'),
(UUID(), 'Evangelização', 'Pastoral', 'Evangelização e missão'),
(UUID(), 'Aconselhamento', 'Pastoral', 'Aconselhamento pastoral'),
(UUID(), 'Música', 'Litúrgico', 'Ministério musical'),
(UUID(), 'Arte', 'Criativo', 'Arte e criatividade'),
(UUID(), 'Esporte', 'Recreativo', 'Atividades esportivas'),
(UUID(), 'Cozinha', 'Serviço', 'Preparação de alimentos'),
(UUID(), 'Limpeza', 'Serviço', 'Serviços de limpeza');

-- Inserir formações básicas
INSERT IGNORE INTO membros_formacoes (id, nome, tipo, descricao, carga_horaria, instituicao) VALUES
(UUID(), 'Curso de Catequese', 'curso', 'Formação básica para catequistas', 40, 'Paróquia'),
(UUID(), 'Ministério Litúrgico', 'curso', 'Formação para ministros', 20, 'Diocese'),
(UUID(), 'Pastoral Social', 'workshop', 'Formação em pastoral social', 16, 'CNBB'),
(UUID(), 'Música Litúrgica', 'curso', 'Formação musical para liturgia', 30, 'Instituto de Música'),
(UUID(), 'Liderança Cristã', 'seminario', 'Desenvolvimento de liderança', 24, 'Movimento'),
(UUID(), 'Primeiros Socorros', 'certificacao', 'Certificação em primeiros socorros', 8, 'Cruz Vermelha'),
(UUID(), 'Gestão de Projetos', 'curso', 'Gestão de projetos sociais', 32, 'ONG'),
(UUID(), 'Comunicação Social', 'workshop', 'Comunicação e mídia', 12, 'Universidade'),
(UUID(), 'Psicologia Pastoral', 'curso', 'Aconselhamento pastoral', 60, 'Instituto Teológico'),
(UUID(), 'Administração Paroquial', 'curso', 'Gestão administrativa', 40, 'Diocese');

-- Inserir funções básicas
INSERT IGNORE INTO membros_funcoes (id, nome, descricao, categoria) VALUES
(UUID(), 'Coordenador', 'Coordenador geral da pastoral', 'Liderança'),
(UUID(), 'Vice-Coordenador', 'Vice-coordenador da pastoral', 'Liderança'),
(UUID(), 'Secretário', 'Secretário da pastoral', 'Administrativo'),
(UUID(), 'Tesoureiro', 'Responsável financeiro', 'Administrativo'),
(UUID(), 'Catequista', 'Ministra catequese', 'Formação'),
(UUID(), 'Ministro da Palavra', 'Proclama a Palavra', 'Litúrgico'),
(UUID(), 'Ministro da Eucaristia', 'Distribui a Eucaristia', 'Litúrgico'),
(UUID(), 'Acólito', 'Auxilia na liturgia', 'Litúrgico'),
(UUID(), 'Cantor', 'Canta nas celebrações', 'Litúrgico'),
(UUID(), 'Músico', 'Toca instrumentos', 'Litúrgico'),
(UUID(), 'Acolhedor', 'Recebe os fiéis', 'Serviço'),
(UUID(), 'Limpeza', 'Limpeza da igreja', 'Serviço'),
(UUID(), 'Segurança', 'Segurança dos eventos', 'Serviço'),
(UUID(), 'Comunicação', 'Responsável pela comunicação', 'Comunicação'),
(UUID(), 'Eventos', 'Organiza eventos', 'Organização'),
(UUID(), 'Pastoral Social', 'Trabalho social', 'Social'),
(UUID(), 'Jovens', 'Trabalho com jovens', 'Pastoral'),
(UUID(), 'Família', 'Pastoral familiar', 'Pastoral'),
(UUID(), 'Idosos', 'Trabalho com idosos', 'Pastoral'),
(UUID(), 'Crianças', 'Trabalho com crianças', 'Pastoral');

-- Inserir pastorais básicas
INSERT IGNORE INTO membros_pastorais (id, nome, tipo, finalidade_descricao, ativo) VALUES
(UUID(), 'Acolhida', 'servico', 'Serviço de acolhida aos fiéis', true),
(UUID(), 'Catequese', 'pastoral', 'Formação catequética', true),
(UUID(), 'Liturgia', 'ministerio_liturgico', 'Ministério litúrgico', true),
(UUID(), 'Pastoral Social', 'pastoral', 'Ação social e caritativa', true),
(UUID(), 'Pastoral da Juventude', 'pastoral', 'Trabalho com jovens', true),
(UUID(), 'Pastoral Familiar', 'pastoral', 'Acompanhamento das famílias', true),
(UUID(), 'Ministério de Música', 'ministerio_liturgico', 'Música litúrgica', true),
(UUID(), 'Comunicação', 'servico', 'Comunicação paroquial', true);

-- =====================================================
-- 10. VERIFICAÇÃO FINAL
-- =====================================================

-- Verificar se todas as tabelas foram criadas
SELECT 
    'Tabelas criadas com sucesso!' as status,
    COUNT(*) as total_tabelas
FROM information_schema.tables 
WHERE table_schema = 'gerencialparoq' 
AND table_name LIKE 'membros_%';

-- Verificar dados iniciais
SELECT 'Habilidades inseridas:' as item, COUNT(*) as total FROM membros_habilidades_tags
UNION ALL
SELECT 'Formações inseridas:', COUNT(*) FROM membros_formacoes
UNION ALL
SELECT 'Funções inseridas:', COUNT(*) FROM membros_funcoes
UNION ALL
SELECT 'Pastorais inseridas:', COUNT(*) FROM membros_pastorais;

-- =====================================================
-- FIM DA INSTALAÇÃO
-- =====================================================

SELECT 'Módulo de Membros instalado com sucesso no banco gerencialparoq!' as resultado;
