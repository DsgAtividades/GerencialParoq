-- =====================================================
-- MÓDULO DE CADASTRO DE MEMBROS - DADOS INICIAIS MYSQL CORRIGIDO
-- Sistema de Gestão Paroquial
-- =====================================================

-- Inserir habilidades/carismas padrão
INSERT INTO membros_habilidades_tags (id, nome, categoria, descricao) VALUES
(UUID(), 'Música', 'Liturgia', 'Canto, instrumentos musicais'),
(UUID(), 'Catequese', 'Formação', 'Ensino da doutrina católica'),
(UUID(), 'Liturgia', 'Liturgia', 'Coordenação de celebrações'),
(UUID(), 'Informática', 'Técnico', 'Suporte técnico e digital'),
(UUID(), 'Acolhida', 'Pastoral', 'Recepção e acolhimento'),
(UUID(), 'Comunicação', 'Pastoral', 'Marketing e comunicação'),
(UUID(), 'Elétrica', 'Técnico', 'Instalações elétricas'),
(UUID(), 'Manutenção', 'Técnico', 'Reparos e manutenção geral'),
(UUID(), 'Primeiros Socorros', 'Saúde', 'Atendimento de emergência'),
(UUID(), 'Cozinha', 'Eventos', 'Preparação de alimentos'),
(UUID(), 'Limpeza', 'Manutenção', 'Limpeza e organização'),
(UUID(), 'Jardim', 'Manutenção', 'Cuidados com jardim e plantas'),
(UUID(), 'Secretaria', 'Administrativo', 'Trabalhos administrativos'),
(UUID(), 'Tesouraria', 'Administrativo', 'Controle financeiro'),
(UUID(), 'Eventos', 'Pastoral', 'Organização de eventos'),
(UUID(), 'Crianças', 'Pastoral', 'Trabalho com crianças'),
(UUID(), 'Jovens', 'Pastoral', 'Trabalho com jovens'),
(UUID(), 'Idosos', 'Pastoral', 'Trabalho com idosos'),
(UUID(), 'Família', 'Pastoral', 'Pastoral familiar'),
(UUID(), 'Comunicação Social', 'Pastoral', 'Mídias sociais e comunicação');

-- Inserir formações/certificações padrão
INSERT INTO membros_formacoes (id, nome, tipo, descricao, duracao_meses, validade_meses) VALUES
(UUID(), 'Iniciação Cristã', 'iniciacao_crista', 'Formação básica da fé católica', 12, NULL),
(UUID(), 'Curso de Ministros da Eucaristia', 'curso_ministros', 'Formação para ministros extraordinários', 6, 24),
(UUID(), 'NR10 - Segurança em Instalações Elétricas', 'nr10', 'Curso obrigatório para trabalhos elétricos', 1, 12),
(UUID(), 'Brigada de Incêndio', 'brigada_incendio', 'Formação para brigada de emergência', 1, 12),
(UUID(), 'Primeiros Socorros', 'primeiros_socorros', 'Atendimento de emergência médica', 1, 12),
(UUID(), 'Catequese - Formação Básica', 'catequese_basica', 'Formação para catequistas', 6, 36),
(UUID(), 'Liturgia - Formação Avançada', 'liturgia_avancada', 'Formação aprofundada em liturgia', 3, 24),
(UUID(), 'Comunicação Pastoral', 'comunicacao_pastoral', 'Formação em comunicação e mídias', 2, 18),
(UUID(), 'Pastoral Familiar', 'pastoral_familiar', 'Formação específica para pastoral familiar', 4, 24),
(UUID(), 'Acolhida e Recepção', 'acolhida', 'Formação para acolhida paroquial', 1, 12);

-- Inserir funções/roles padrão
INSERT INTO membros_funcoes (id, nome, descricao, categoria) VALUES
(UUID(), 'Coordenador', 'Liderança e coordenação geral', 'Liderança'),
(UUID(), 'Vice-Coordenador', 'Apoio à coordenação', 'Liderança'),
(UUID(), 'Secretário', 'Trabalhos administrativos e documentação', 'Administrativo'),
(UUID(), 'Tesoureiro', 'Controle financeiro e recursos', 'Administrativo'),
(UUID(), 'Catequista', 'Ensino da doutrina católica', 'Formação'),
(UUID(), 'Ministro da Eucaristia', 'Distribuição da Eucaristia', 'Liturgia'),
(UUID(), 'Leitor', 'Leitura nas celebrações', 'Liturgia'),
(UUID(), 'Salmista', 'Canto dos salmos', 'Liturgia'),
(UUID(), 'Coroinha', 'Auxílio nas celebrações', 'Liturgia'),
(UUID(), 'Acolhida', 'Recepção e acolhimento', 'Pastoral'),
(UUID(), 'Música', 'Ministério musical', 'Liturgia'),
(UUID(), 'Comunicação', 'Marketing e comunicação', 'Pastoral'),
(UUID(), 'Manutenção', 'Reparos e manutenção', 'Técnico'),
(UUID(), 'Eventos', 'Organização de eventos', 'Pastoral'),
(UUID(), 'Lojinha', 'Atendimento na lojinha', 'Comercial'),
(UUID(), 'Obra Social', 'Trabalho social', 'Social'),
(UUID(), 'Limpeza', 'Limpeza e organização', 'Manutenção'),
(UUID(), 'Cozinha', 'Preparação de alimentos', 'Eventos'),
(UUID(), 'Jardim', 'Cuidados com jardim', 'Manutenção'),
(UUID(), 'Segurança', 'Segurança dos eventos', 'Segurança');

-- Inserir pastorais/movimentos padrão
INSERT INTO membros_pastorais (id, nome, tipo, comunidade_capelania, dia_semana, horario, local_reuniao, finalidade_descricao, whatsapp_grupo_link, email_grupo) VALUES
(UUID(), 'Catequese', 'pastoral', 'Matriz', 'Sábado', '14:00', 'Salão Paroquial', 'Formação catequética de crianças e jovens', 'https://chat.whatsapp.com/catequese', 'catequese@paroquia.com'),
(UUID(), 'Pastoral Social', 'pastoral', 'Matriz', 'Terça', '19:30', 'Centro Social', 'Ação social e caridade', 'https://chat.whatsapp.com/pastoralsocial', 'social@paroquia.com'),
(UUID(), 'Liturgia', 'ministerio_liturgico', 'Matriz', 'Quinta', '20:00', 'Igreja', 'Coordenação das celebrações litúrgicas', 'https://chat.whatsapp.com/liturgia', 'liturgia@paroquia.com'),
(UUID(), 'Juventude', 'movimento', 'Matriz', 'Sexta', '19:00', 'Salão da Juventude', 'Formação e atividades para jovens', 'https://chat.whatsapp.com/juventude', 'juventude@paroquia.com'),
(UUID(), 'Coral', 'ministerio_liturgico', 'Matriz', 'Quarta', '19:30', 'Igreja', 'Ministério musical', 'https://chat.whatsapp.com/coral', 'coral@paroquia.com'),
(UUID(), 'Acolhida', 'servico', 'Matriz', 'Domingo', '07:00', 'Igreja', 'Recepção e acolhimento dos fiéis', 'https://chat.whatsapp.com/acolhida', 'acolhida@paroquia.com'),
(UUID(), 'Comunicação', 'pastoral', 'Matriz', 'Segunda', '20:00', 'Escritório Paroquial', 'Marketing e comunicação paroquial', 'https://chat.whatsapp.com/comunicacao', 'comunicacao@paroquia.com'),
(UUID(), 'Pastoral Familiar', 'pastoral', 'Matriz', 'Sábado', '16:00', 'Salão Paroquial', 'Acompanhamento das famílias', 'https://chat.whatsapp.com/familia', 'familia@paroquia.com');

-- Inserir membros de exemplo
INSERT INTO membros_membros (
    id, nome_completo, apelido, data_nascimento, sexo, celular_whatsapp, email, 
    rua, numero, bairro, cidade, uf, cep, cpf, rg,
    paroquiano, comunidade_ou_capelania, data_entrada,
    preferencias_contato, dias_turnos, frequencia, periodo, habilidades,
    status, created_by
) VALUES
(UUID(), 'Maria da Silva Santos', 'Maria', '1985-03-15', 'F', '(11) 99999-1111', 'maria.santos@email.com',
 'Rua das Flores', '123', 'Centro', 'São Paulo', 'SP', '01234-567', '123.456.789-00', '12.345.678-9',
 true, 'Matriz', '2020-01-15',
 '["whatsapp", "email"]', '["sabado_tarde", "domingo_manha"]', 'semanal', 'tarde', '["catequese", "musica"]',
 'ativo', 'admin'),

(UUID(), 'João Pedro Oliveira', 'João', '1978-07-22', 'M', '(11) 99999-2222', 'joao.oliveira@email.com',
 'Av. Principal', '456', 'Jardim', 'São Paulo', 'SP', '01234-890', '987.654.321-00', '98.765.432-1',
 true, 'Matriz', '2019-06-10',
 '["whatsapp", "sms"]', '["quinta_noite", "sexta_noite"]', 'semanal', 'noite', '["liturgia", "acolhida"]',
 'ativo', 'admin'),

(UUID(), 'Ana Carolina Costa', 'Ana', '1992-11-08', 'F', '(11) 99999-3333', 'ana.costa@email.com',
 'Rua da Paz', '789', 'Vila Nova', 'São Paulo', 'SP', '01234-123', '456.789.123-00', '45.678.912-3',
 true, 'Matriz', '2021-03-20',
 '["whatsapp", "email"]', '["sabado_manha", "domingo_tarde"]', 'semanal', 'manha', '["comunicacao", "eventos"]',
 'ativo', 'admin'),

(UUID(), 'Carlos Eduardo Lima', 'Carlos', '1980-12-03', 'M', '(11) 99999-4444', 'carlos.lima@email.com',
 'Rua da Esperança', '321', 'Centro', 'São Paulo', 'SP', '01234-456', '789.123.456-00', '78.912.345-6',
 true, 'Matriz', '2018-09-05',
 '["whatsapp"]', '["segunda_manha", "terca_manha"]', 'semanal', 'manha', '["manutencao", "eletrica"]',
 'ativo', 'admin'),

(UUID(), 'Fernanda Rodrigues', 'Fernanda', '1987-05-18', 'F', '(11) 99999-5555', 'fernanda.rodrigues@email.com',
 'Av. da Fé', '654', 'Jardim', 'São Paulo', 'SP', '01234-789', '321.654.987-00', '32.165.498-7',
 true, 'Matriz', '2020-11-12',
 '["whatsapp", "email"]', '["quarta_tarde", "quinta_tarde"]', 'semanal', 'tarde', '["coral", "musica"]',
 'ativo', 'admin'),

(UUID(), 'Roberto Almeida', 'Roberto', '1975-09-25', 'M', '(11) 99999-6666', 'roberto.almeida@email.com',
 'Rua da Caridade', '987', 'Vila Nova', 'São Paulo', 'SP', '01234-012', '654.321.987-00', '65.432.198-7',
 true, 'Matriz', '2017-04-08',
 '["whatsapp", "sms"]', '["sabado_noite", "domingo_noite"]', 'semanal', 'noite', '["pastoral_social", "acolhida"]',
 'ativo', 'admin'),

(UUID(), 'Patrícia Mendes', 'Patrícia', '1990-01-30', 'F', '(11) 99999-7777', 'patricia.mendes@email.com',
 'Rua da Esperança', '147', 'Centro', 'São Paulo', 'SP', '01234-345', '147.258.369-00', '14.725.836-9',
 true, 'Matriz', '2022-02-14',
 '["whatsapp", "email"]', '["segunda_tarde", "quarta_tarde"]', 'semanal', 'tarde', '["catequese", "criancas"]',
 'ativo', 'admin'),

(UUID(), 'Antônio Silva', 'Antônio', '1983-08-12', 'M', '(11) 99999-8888', 'antonio.silva@email.com',
 'Av. da Esperança', '258', 'Jardim', 'São Paulo', 'SP', '01234-678', '258.369.147-00', '25.836.914-7',
 true, 'Matriz', '2019-10-20',
 '["whatsapp"]', '["terca_noite", "quinta_noite"]', 'semanal', 'noite', '["tesouraria", "administrativo"]',
 'ativo', 'admin'),

(UUID(), 'Lucia Helena', 'Lucia', '1972-04-05', 'F', '(11) 99999-9999', 'lucia.helena@email.com',
 'Rua da Fé', '369', 'Vila Nova', 'São Paulo', 'SP', '01234-901', '369.147.258-00', '36.914.725-8',
 true, 'Matriz', '2016-12-01',
 '["whatsapp", "email"]', '["sabado_manha", "domingo_manha"]', 'semanal', 'manha', '["pastoral_familiar", "acolhida"]',
 'ativo', 'admin'),

(UUID(), 'Pedro Henrique', 'Pedro', '1995-06-28', 'M', '(11) 99999-0000', 'pedro.henrique@email.com',
 'Rua da Paz', '741', 'Centro', 'São Paulo', 'SP', '01234-234', '741.852.963-00', '74.185.296-3',
 true, 'Matriz', '2023-01-10',
 '["whatsapp", "email"]', '["sexta_noite", "sabado_noite"]', 'semanal', 'noite', '["juventude", "comunicacao"]',
 'ativo', 'admin');
