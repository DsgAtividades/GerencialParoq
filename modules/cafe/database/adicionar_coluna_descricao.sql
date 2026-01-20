-- Adicionar coluna descricao na tabela cafe_permissoes
-- Esta coluna permite uma descrição mais detalhada de cada permissão

ALTER TABLE `cafe_permissoes` 
ADD COLUMN `descricao` VARCHAR(255) NULL AFTER `nome`;

-- Atualizar permissões existentes com descrições
UPDATE `cafe_permissoes` SET `descricao` = 'Gerenciar Grupos de Usuários' WHERE `nome` = 'gerenciar_grupos';
UPDATE `cafe_permissoes` SET `descricao` = 'Gerenciar Permissões do Sistema' WHERE `nome` = 'gerenciar_permissoes';
UPDATE `cafe_permissoes` SET `descricao` = 'Gerenciar Usuários do Sistema' WHERE `nome` = 'gerenciar_usuarios';
UPDATE `cafe_permissoes` SET `descricao` = 'Gerenciar Pessoas/Clientes' WHERE `nome` = 'gerenciar_pessoas';
UPDATE `cafe_permissoes` SET `descricao` = 'Gerenciar Transações e Saldos' WHERE `nome` = 'gerenciar_transacoes';
UPDATE `cafe_permissoes` SET `descricao` = 'Gerenciar Produtos' WHERE `nome` = 'gerenciar_produtos';
UPDATE `cafe_permissoes` SET `descricao` = 'Gerenciar Vendas (Relatórios)' WHERE `nome` = 'gerenciar_vendas';
UPDATE `cafe_permissoes` SET `descricao` = 'Visualizar Dashboard' WHERE `nome` = 'visualizar_dashboard';
UPDATE `cafe_permissoes` SET `descricao` = 'Visualizar Relatórios' WHERE `nome` = 'visualizar_relatorios';
UPDATE `cafe_permissoes` SET `descricao` = 'Gerenciar Cartões' WHERE `nome` = 'gerenciar_cartoes';

