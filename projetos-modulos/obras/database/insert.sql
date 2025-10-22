-- Modelo de inserção em massa de usuários
INSERT INTO obras_users (
    nome,
    cpf,
    data_nascimento,
    endereco,
    bairro,
    cidade,
    estado,
    cep,
    telefone,
    email,
    situacao,
    observacoes
) VALUES 
(
    'João da Silva',
    '123.456.789-00',
    '1980-05-15',
    'Rua das Flores, 123',
    'Centro',
    'São Paulo',
    'SP',
    '01234-567',
    '(11) 98765-4321',
    'joao.silva@email.com',
    'Ativo',
    'Exemplo de observação'
),
(
    'Maria Santos',
    '987.654.321-00',
    '1992-08-20',
    'Av. Principal, 456',
    'Jardim América',
    'São Paulo',
    'SP',
    '04567-890',
    '(11) 91234-5678',
    'maria.santos@email.com',
    'Ativo',
    NULL
),
(
    'José Oliveira',
    '456.789.123-00',
    '1975-03-10',
    'Rua do Comércio, 789',
    'Vila Nova',
    'Campinas',
    'SP',
    '13015-000',
    '(19) 98877-6655',
    'jose.oliveira@email.com',
    'Ativo',
    'Voluntário ativo'
);

-- Para adicionar mais registros, copie o bloco entre parênteses e ajuste os valores
-- Lembre-se de adicionar uma vírgula após cada bloco, exceto no último
-- Exemplo de estrutura para copiar:
/*
(
    'Nome Completo',
    'CPF (com pontos e traço)',
    'AAAA-MM-DD',
    'Endereço completo',
    'Bairro',
    'Cidade',
    'UF',
    'CEP (com traço)',
    'Telefone (com parênteses, espaço e traço)',
    'email@dominio.com',
    'Ativo',
    'Observações ou NULL'
),
*/
