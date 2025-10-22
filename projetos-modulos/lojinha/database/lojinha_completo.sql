-- ============================================
-- SQL COMPLETO - MÓDULO LOJINHA
-- Sistema de Gestão Paroquial
-- ============================================
-- 
-- Este arquivo contém:
-- 1. Criação de todas as tabelas (com prefixo lojinha_)
-- 2. Dados padrão (categorias e fornecedores)
-- 
-- IMPORTANTE: 
-- - Todas as tabelas têm o prefixo "lojinha_"
-- - Pode ser executado em um banco existente
-- - Não afeta outras tabelas
-- ============================================

-- ============================================
-- 1. TABELA: lojinha_categorias
-- ============================================
CREATE TABLE IF NOT EXISTS `lojinha_categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados padrão para categorias
INSERT INTO `lojinha_categorias` (`nome`, `descricao`, `ativo`) VALUES
('Livros', 'Livros religiosos e de formação', 1),
('Imagens', 'Imagens de santos e Nossa Senhora', 1),
('Terços', 'Terços de diversos materiais', 1),
('Artigos Litúrgicos', 'Objetos para uso litúrgico', 1),
('Velas', 'Velas votivas e decorativas', 1),
('Vestuário', 'Camisetas e artigos de vestuário religioso', 1),
('Decoração', 'Itens de decoração religiosa', 1),
('Músicas', 'CDs e DVDs de música católica', 1);

-- ============================================
-- 2. TABELA: lojinha_fornecedores
-- ============================================
CREATE TABLE IF NOT EXISTS `lojinha_fornecedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  `contato` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `cnpj` varchar(18) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados padrão para fornecedores
INSERT INTO `lojinha_fornecedores` (`nome`, `contato`, `telefone`, `email`, `ativo`) VALUES
('Editora Ave Maria', 'João Silva', '11987654321', 'contato@avemaria.com', 1),
('Artigos Religiosos Divina Luz', 'Maria Souza', '21998765432', 'vendas@divinaluz.com', 1),
('Livraria Paulus', 'Pedro Santos', '31987651234', 'paulus@paulus.com.br', 1);

-- ============================================
-- 3. TABELA: lojinha_produtos
-- ============================================
CREATE TABLE IF NOT EXISTS `lojinha_produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `fornecedor` varchar(255) DEFAULT NULL,
  `preco_compra` decimal(10,2) DEFAULT 0.00,
  `preco_venda` decimal(10,2) NOT NULL,
  `estoque_atual` int(11) DEFAULT 0,
  `estoque_minimo` int(11) DEFAULT 0,
  `tipo_liturgico` varchar(50) DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_categoria` (`categoria_id`),
  KEY `idx_ativo` (`ativo`),
  KEY `idx_estoque` (`estoque_atual`),
  CONSTRAINT `lojinha_produtos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `lojinha_categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. TABELA: lojinha_estoque_movimentacoes
-- ============================================
CREATE TABLE IF NOT EXISTS `lojinha_estoque_movimentacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) NOT NULL,
  `tipo` enum('entrada','saida','ajuste') NOT NULL,
  `quantidade` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_movimentacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_produto` (`produto_id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_data` (`data_movimentacao`),
  CONSTRAINT `lojinha_estoque_movimentacoes_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `lojinha_produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. TABELA: lojinha_vendas
-- ============================================
CREATE TABLE IF NOT EXISTS `lojinha_vendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_venda` varchar(20) NOT NULL,
  `cliente_nome` varchar(200) DEFAULT NULL,
  `cliente_telefone` varchar(20) DEFAULT NULL,
  `forma_pagamento` enum('dinheiro','pix','cartao_debito','cartao_credito') NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `desconto` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `observacoes` text DEFAULT NULL,
  `status` enum('pendente','finalizada','cancelada') DEFAULT 'pendente',
  `usuario_id` int(11) DEFAULT NULL,
  `data_venda` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_venda` (`numero_venda`),
  KEY `idx_status` (`status`),
  KEY `idx_data` (`data_venda`),
  KEY `idx_forma_pagamento` (`forma_pagamento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. TABELA: lojinha_vendas_itens
-- ============================================
CREATE TABLE IF NOT EXISTS `lojinha_vendas_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_venda` (`venda_id`),
  KEY `idx_produto` (`produto_id`),
  CONSTRAINT `lojinha_vendas_itens_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `lojinha_vendas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lojinha_vendas_itens_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `lojinha_produtos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. TABELA: lojinha_caixa
-- ============================================
CREATE TABLE IF NOT EXISTS `lojinha_caixa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `saldo_inicial` decimal(10,2) NOT NULL,
  `saldo_atual` decimal(10,2) NOT NULL,
  `saldo_final` decimal(10,2) DEFAULT NULL,
  `status` enum('aberto','fechado') DEFAULT 'aberto',
  `usuario_id` int(11) DEFAULT NULL,
  `data_abertura` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_fechamento` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_data_abertura` (`data_abertura`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- FIM DO SCRIPT
-- ============================================
-- 
-- INSTRUÇÕES DE USO:
-- 
-- 1. Faça backup do seu banco de dados antes de executar
-- 2. Execute este script no seu banco existente
-- 3. Todas as tabelas serão criadas com o prefixo "lojinha_"
-- 4. Dados padrão serão inseridos (categorias e fornecedores)
-- 
-- VERIFICAÇÃO:
-- Execute: SELECT table_name FROM information_schema.tables 
--          WHERE table_schema = 'seu_banco' AND table_name LIKE 'lojinha_%';
-- 
-- Deve retornar 7 tabelas:
-- - lojinha_categorias
-- - lojinha_fornecedores
-- - lojinha_produtos
-- - lojinha_estoque_movimentacoes
-- - lojinha_vendas
-- - lojinha_vendas_itens
-- - lojinha_caixa
-- 
-- ============================================

