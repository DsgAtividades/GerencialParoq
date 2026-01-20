-- ============================================================
-- Script de Limpeza e Reset do Banco de Dados - Módulo Café
-- ============================================================
-- Descrição: Limpa dados e reseta AUTO_INCREMENT das tabelas
-- Criado em: 2026-01-13
-- 
-- ⚠️ ATENÇÃO: Este script é DESTRUTIVO!
-- Sempre faça backup antes de executar!
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
SET AUTOCOMMIT = 0;

START TRANSACTION;

-- ============================================================
-- 1. LIMPAR TABELAS DEPENDENTES (Ordem: filhas primeiro)
-- ============================================================

-- Limpar itens de venda (depende de vendas e produtos)
TRUNCATE TABLE `cafe_itens_venda`;

-- Limpar vendas (depende de pessoas)
TRUNCATE TABLE `cafe_vendas`;

-- Limpar históricos (dependem de pessoas e produtos)
TRUNCATE TABLE `cafe_historico_saldo`;
TRUNCATE TABLE `cafe_historico_estoque`;
TRUNCATE TABLE `cafe_historico_transacoes_sistema`;

-- Limpar saldos de cartão (depende de pessoas)
TRUNCATE TABLE `cafe_saldos_cartao`;

-- Limpar pessoas (depende de cartões)
TRUNCATE TABLE `cafe_pessoas`;

-- Limpar cartões (independente agora)
TRUNCATE TABLE `cafe_cartoes`;

-- Limpar produtos (independente)
TRUNCATE TABLE `cafe_produtos`;

-- Limpar categorias (independente)
TRUNCATE TABLE `cafe_categorias`;

-- ============================================================
-- 2. LIMPAR TABELAS COM REGRAS ESPECIAIS
-- ============================================================

-- Limpar grupos_permissoes (mantém apenas grupo_id = 1)
-- Nota: A instrução original mencionava grupo_id <> 0, mas corrigido para <> 1
DELETE FROM `cafe_grupos_permissoes` WHERE `grupo_id` <> 1;

-- Limpar grupos (mantém apenas id = 1 - Administrador)
DELETE FROM `cafe_grupos` WHERE `id` <> 1;

-- Limpar usuarios (move id 12 para 2, mantém apenas 1 e 2)
-- Primeiro, verificar se existe usuário com id 12
UPDATE `cafe_usuarios` SET `id` = 2 WHERE `id` = 12;
-- Depois, deletar todos com id > 2
DELETE FROM `cafe_usuarios` WHERE `id` > 2;

-- ============================================================
-- 3. RESETAR AUTO_INCREMENT DE TODAS AS TABELAS
-- ============================================================

ALTER TABLE `cafe_cartoes` AUTO_INCREMENT = 1;
ALTER TABLE `cafe_categorias` AUTO_INCREMENT = 1;
ALTER TABLE `cafe_grupos` AUTO_INCREMENT = 2; -- Próximo será 2 (1 já existe)
ALTER TABLE `cafe_historico_estoque` AUTO_INCREMENT = 1;
ALTER TABLE `cafe_historico_saldo` AUTO_INCREMENT = 1;
ALTER TABLE `cafe_historico_transacoes_sistema` AUTO_INCREMENT = 1;
ALTER TABLE `cafe_itens_venda` AUTO_INCREMENT = 1;
ALTER TABLE `cafe_pessoas` AUTO_INCREMENT = 1;
ALTER TABLE `cafe_produtos` AUTO_INCREMENT = 1;
ALTER TABLE `cafe_saldos_cartao` AUTO_INCREMENT = 1;
ALTER TABLE `cafe_usuarios` AUTO_INCREMENT = 3; -- Próximo será 3 (1 e 2 já existem)
ALTER TABLE `cafe_vendas` AUTO_INCREMENT = 1;

-- ============================================================
-- 4. VERIFICAÇÕES E VALIDAÇÕES
-- ============================================================

-- Verificar se grupo Administrador (id=1) ainda existe
-- Se não existir, criar
INSERT IGNORE INTO `cafe_grupos` (`id`, `nome`, `created_at`) 
VALUES (1, 'Administrador', NOW());

-- Verificar se existem permissões para o grupo 1
-- Se não houver, manter como está (não criar permissões aqui)

-- ============================================================
-- FINALIZAÇÃO
-- ============================================================

COMMIT;

-- Reabilitar verificações de foreign keys
SET FOREIGN_KEY_CHECKS = 1;
SET AUTOCOMMIT = 1;

-- ============================================================
-- RESUMO
-- ============================================================
-- Tabelas completamente limpas (TRUNCATE):
--   - cafe_cartoes
--   - cafe_categorias
--   - cafe_historico_estoque
--   - cafe_historico_saldo
--   - cafe_historico_transacoes_sistema
--   - cafe_itens_venda
--   - cafe_pessoas
--   - cafe_produtos
--   - cafe_saldos_cartao
--   - cafe_vendas
--
-- Tabelas com regras especiais:
--   - cafe_grupos: Mantém apenas id=1 (Administrador)
--   - cafe_grupos_permissoes: Mantém apenas grupo_id=1
--   - cafe_usuarios: Mantém apenas id=1 e 2 (move 12 para 2)
--
-- AUTO_INCREMENT resetado para todas as tabelas
-- ============================================================

