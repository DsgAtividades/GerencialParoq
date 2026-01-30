-- ==================================================
-- BANCO DE DADOS COMPLETO - MÓDULO CAFÉ
-- ==================================================
-- Versão: 1.0.0
-- Data: 21/01/2026
-- Descrição: Estrutura completa do banco de dados do módulo Café
--            Inclui todas as funcionalidades: vendas, estoque, cartões,
--            permissões, caixa e históricos
-- ==================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ==================================================
-- 1. TABELAS DE AUTENTICAÇÃO E PERMISSÕES
-- ==================================================

-- Tabela: cafe_grupos
CREATE TABLE IF NOT EXISTS `cafe_grupos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: cafe_permissoes
CREATE TABLE IF NOT EXISTS `cafe_permissoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL COMMENT 'Descrição detalhada da permissão',
  `pagina` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: cafe_grupos_permissoes
CREATE TABLE IF NOT EXISTS `cafe_grupos_permissoes` (
  `grupo_id` int(11) NOT NULL,
  `permissao_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`grupo_id`,`permissao_id`),
  KEY `permissao_id` (`permissao_id`),
  CONSTRAINT `fk_grupos_permissoes_grupo` FOREIGN KEY (`grupo_id`) REFERENCES `cafe_grupos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_grupos_permissoes_permissao` FOREIGN KEY (`permissao_id`) REFERENCES `cafe_permissoes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: cafe_usuarios
CREATE TABLE IF NOT EXISTS `cafe_usuarios` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `grupo_id` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `grupo_id` (`grupo_id`),
  CONSTRAINT `fk_usuarios_grupo` FOREIGN KEY (`grupo_id`) REFERENCES `cafe_grupos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==================================================
-- 2. TABELAS DE CADASTROS
-- ==================================================

-- Tabela: cafe_categorias
CREATE TABLE IF NOT EXISTS `cafe_categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `icone` varchar(50) NOT NULL,
  `ordem` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: cafe_produtos
CREATE TABLE IF NOT EXISTS `cafe_produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_produto` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `descricao` text CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `estoque` int(11) NOT NULL DEFAULT 0,
  `categoria_id` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `bloqueado` tinyint(1) DEFAULT 0 COMMENT '1=bloqueado para venda',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `fk_produtos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `cafe_categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: cafe_pessoas
CREATE TABLE IF NOT EXISTS `cafe_pessoas` (
  `id_pessoa` int(10) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_pessoa`),
  UNIQUE KEY `uk_pessoas_cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: cafe_cartoes
CREATE TABLE IF NOT EXISTS `cafe_cartoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(255) NOT NULL,
  `data_geracao` timestamp NULL DEFAULT current_timestamp(),
  `usado` tinyint(1) DEFAULT 0,
  `id_pessoa` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cartoes_codigo` (`codigo`),
  KEY `fk_cartoes_pessoa` (`id_pessoa`),
  CONSTRAINT `fk_cartoes_pessoa` FOREIGN KEY (`id_pessoa`) REFERENCES `cafe_pessoas` (`id_pessoa`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- 3. TABELAS DE VENDAS
-- ==================================================

-- Tabela: cafe_vendas (sem FK para caixa ainda, será adicionada depois)
CREATE TABLE IF NOT EXISTS `cafe_vendas` (
  `id_venda` int(10) NOT NULL AUTO_INCREMENT,
  `caixa_id` int(11) DEFAULT NULL COMMENT 'ID do caixa onde a venda foi realizada',
  `id_pessoa` int(11) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `Tipo_venda` varchar(50) DEFAULT NULL COMMENT 'dinheiro, credito, debito',
  `Atendente` varchar(255) DEFAULT NULL COMMENT 'Nome do usuário que realizou a venda',
  `estornada` tinyint(1) DEFAULT NULL COMMENT '1=estornada, NULL/0=não estornada',
  `data_venda` datetime NOT NULL,
  PRIMARY KEY (`id_venda`),
  KEY `fk_vendas_pessoa` (`id_pessoa`),
  KEY `idx_caixa_id` (`caixa_id`),
  KEY `idx_vendas_data` (`data_venda`),
  CONSTRAINT `fk_vendas_pessoa` FOREIGN KEY (`id_pessoa`) REFERENCES `cafe_pessoas` (`id_pessoa`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: cafe_itens_venda
CREATE TABLE IF NOT EXISTS `cafe_itens_venda` (
  `id_item` int(10) NOT NULL AUTO_INCREMENT,
  `id_venda` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `valor_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_item`),
  KEY `idx_itens_venda` (`id_venda`),
  KEY `idx_itens_produto` (`id_produto`),
  CONSTRAINT `fk_itens_venda` FOREIGN KEY (`id_venda`) REFERENCES `cafe_vendas` (`id_venda`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_itens_produto` FOREIGN KEY (`id_produto`) REFERENCES `cafe_produtos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- 4. TABELAS FINANCEIRAS
-- ==================================================

-- Tabela: cafe_saldos_cartao
CREATE TABLE IF NOT EXISTS `cafe_saldos_cartao` (
  `id_saldo` int(11) NOT NULL AUTO_INCREMENT,
  `id_pessoa` int(11) NOT NULL,
  `saldo` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_saldo`),
  KEY `fk_saldo_pessoa` (`id_pessoa`),
  CONSTRAINT `fk_saldo_pessoa` FOREIGN KEY (`id_pessoa`) REFERENCES `cafe_pessoas` (`id_pessoa`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: cafe_caixas
CREATE TABLE IF NOT EXISTS `cafe_caixas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_abertura` datetime NOT NULL DEFAULT current_timestamp(),
  `data_fechamento` datetime DEFAULT NULL,
  `valor_troco_inicial` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor de troco disponível na abertura (preservado)',
  `total_trocos_dados` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total de trocos dados durante o período do caixa',
  `valor_troco_final` decimal(10,2) DEFAULT NULL COMMENT 'Valor de troco que sobrou no fechamento (calculado)',
  `usuario_abertura_id` int(11) NOT NULL COMMENT 'ID do usuário que abriu o caixa',
  `usuario_abertura_nome` varchar(255) NOT NULL COMMENT 'Nome do usuário que abriu (auditoria)',
  `usuario_fechamento_id` int(11) DEFAULT NULL COMMENT 'ID do usuário que fechou o caixa',
  `usuario_fechamento_nome` varchar(255) DEFAULT NULL COMMENT 'Nome do usuário que fechou (auditoria)',
  `status` enum('aberto','fechado') NOT NULL DEFAULT 'aberto',
  `observacao_abertura` text DEFAULT NULL,
  `observacao_fechamento` text DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_data_abertura` (`data_abertura`),
  KEY `idx_usuario_abertura` (`usuario_abertura_id`),
  CONSTRAINT `fk_caixas_usuario_abertura` FOREIGN KEY (`usuario_abertura_id`) REFERENCES `cafe_usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_caixas_usuario_fechamento` FOREIGN KEY (`usuario_fechamento_id`) REFERENCES `cafe_usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Controle de abertura e fechamento de caixa';

-- ==================================================
-- 5. TABELAS DE HISTÓRICO
-- ==================================================

-- Tabela: cafe_historico_saldo
CREATE TABLE IF NOT EXISTS `cafe_historico_saldo` (
  `id_historico` int(11) NOT NULL AUTO_INCREMENT,
  `id_pessoa` int(11) NOT NULL,
  `tipo_operacao` enum('credito','debito','custo cartao','dinheiro','bonus') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `saldo_anterior` decimal(10,2) NOT NULL,
  `saldo_novo` decimal(10,2) NOT NULL,
  `motivo` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `data_operacao` datetime NOT NULL,
  PRIMARY KEY (`id_historico`),
  KEY `idx_historico_pessoa` (`id_pessoa`),
  CONSTRAINT `fk_historico_saldo_pessoa` FOREIGN KEY (`id_pessoa`) REFERENCES `cafe_pessoas` (`id_pessoa`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: cafe_historico_estoque
CREATE TABLE IF NOT EXISTS `cafe_historico_estoque` (
  `id_historico` int(11) NOT NULL AUTO_INCREMENT,
  `id_produto` int(11) NOT NULL,
  `tipo_operacao` enum('entrada','saida') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `quantidade` int(11) NOT NULL,
  `quantidade_anterior` int(11) NOT NULL,
  `motivo` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `data_operacao` datetime NOT NULL,
  PRIMARY KEY (`id_historico`),
  KEY `fk_historico_produto` (`id_produto`),
  CONSTRAINT `fk_historico_produto` FOREIGN KEY (`id_produto`) REFERENCES `cafe_produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: cafe_historico_transacoes_sistema
CREATE TABLE IF NOT EXISTS `cafe_historico_transacoes_sistema` (
  `id_transacao` int(11) NOT NULL AUTO_INCREMENT,
  `nome_usuario` varchar(255) DEFAULT NULL,
  `grupo_usuario` varchar(255) DEFAULT NULL,
  `tipo` varchar(255) DEFAULT NULL,
  `tipo_transacao` varchar(255) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `id_pessoa` int(11) DEFAULT NULL,
  `cartao` varchar(255) DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_transacao`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ==================================================
-- 6. FOREIGN KEYS ADICIONAIS
-- ==================================================

-- Adicionar FK de cafe_vendas para cafe_caixas (após criar cafe_caixas)
ALTER TABLE `cafe_vendas`
ADD CONSTRAINT `fk_vendas_caixa` FOREIGN KEY (`caixa_id`) REFERENCES `cafe_caixas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ==================================================
-- 7. VIEWS
-- ==================================================

-- View: vw_cafe_caixas_resumo
DROP VIEW IF EXISTS `vw_cafe_caixas_resumo`;

CREATE VIEW `vw_cafe_caixas_resumo` AS
SELECT
    c.id,
    c.data_abertura,
    c.data_fechamento,
    c.valor_troco_inicial,
    c.total_trocos_dados,
    c.valor_troco_final,
    c.observacao_abertura,
    c.observacao_fechamento,
    c.usuario_abertura_nome,
    c.usuario_fechamento_nome,
    c.usuario_abertura_id,
    c.usuario_fechamento_id,
    c.status,
    -- Troco atual (para caixas abertos) ou final (para fechados)
    CASE 
        WHEN c.status = 'aberto' THEN c.valor_troco_inicial - c.total_trocos_dados
        ELSE c.valor_troco_final
    END AS troco_atual,
    TIMESTAMPDIFF(HOUR, c.data_abertura, COALESCE(c.data_fechamento, NOW())) AS horas_abertas,
    COALESCE((SELECT SUM(cv.valor_total) FROM cafe_vendas cv WHERE cv.caixa_id = c.id AND (cv.estornada IS NULL OR cv.estornada = 0) AND cv.Tipo_venda = 'dinheiro'), 0) AS total_dinheiro,
    COALESCE((SELECT SUM(cv.valor_total) FROM cafe_vendas cv WHERE cv.caixa_id = c.id AND (cv.estornada IS NULL OR cv.estornada = 0) AND cv.Tipo_venda = 'credito'), 0) AS total_credito,
    COALESCE((SELECT SUM(cv.valor_total) FROM cafe_vendas cv WHERE cv.caixa_id = c.id AND (cv.estornada IS NULL OR cv.estornada = 0) AND cv.Tipo_venda = 'debito'), 0) AS total_debito,
    COALESCE((SELECT SUM(cv.valor_total) FROM cafe_vendas cv WHERE cv.caixa_id = c.id AND (cv.estornada IS NULL OR cv.estornada = 0) AND cv.Tipo_venda = 'pix'), 0) AS total_pix,
    COALESCE((SELECT SUM(cv.valor_total) FROM cafe_vendas cv WHERE cv.caixa_id = c.id AND (cv.estornada IS NULL OR cv.estornada = 0) AND LOWER(TRIM(cv.Tipo_venda)) = 'cortesia'), 0) AS total_cortesia,
    COALESCE((SELECT COUNT(cv.id_venda) FROM cafe_vendas cv WHERE cv.caixa_id = c.id AND (cv.estornada IS NULL OR cv.estornada = 0)), 0) AS total_vendas,
    COALESCE((SELECT SUM(cv.valor_total) FROM cafe_vendas cv WHERE cv.caixa_id = c.id AND (cv.estornada IS NULL OR cv.estornada = 0)), 0) AS total_geral
FROM
    cafe_caixas c;

-- ==================================================
-- 8. PERMISSÕES PADRÃO
-- ==================================================

-- Inserir permissões básicas (se não existirem)
INSERT IGNORE INTO `cafe_permissoes` (`nome`, `descricao`, `pagina`) VALUES
('gerenciar_grupos', 'Gerenciar Grupos de Usuários', 'grupos.php'),
('gerenciar_permissoes', 'Gerenciar Permissões do Sistema', 'permissoes.php'),
('gerenciar_usuarios', 'Gerenciar Usuários do Sistema', 'usuarios.php'),
('gerenciar_pessoas', 'Gerenciar Pessoas/Clientes', 'pessoas.php'),
('gerenciar_transacoes', 'Gerenciar Transações e Saldos', 'transacoes_lista.php'),
('gerenciar_produtos', 'Gerenciar Produtos', 'produtos_lista.php'),
('gerenciar_categorias', 'Gerenciar Categorias', 'categorias.php'),
('gerenciar_vendas', 'Gerenciar Vendas (Relatórios)', 'vendas.php'),
('vendas_mobile', 'Realizar Vendas Mobile', 'vendas_mobile.php'),
('api_finalizar_venda', 'API: Finalizar Venda', 'api/finalizar_venda.php'),
('visualizar_dashboard', 'Visualizar Dashboard', 'dashboard_vendas.php'),
('visualizar_relatorios', 'Visualizar Relatórios', 'relatorio_vendas.php'),
('gerenciar_cartoes', 'Gerenciar Cartões', 'cartoes.php'),
('gerenciar_dashboard', 'Gerenciar Dashboard', 'gerenciar_dashboard.php'),
('abrir_caixa', 'Abrir Caixa', 'caixa.php'),
('fechar_caixa', 'Fechar Caixa', 'caixa.php'),
('visualizar_caixa', 'Visualizar Caixa', 'caixa.php'),
('gerenciar_caixas', 'Gerenciar Todos os Caixas', 'caixa.php');

-- ==================================================
-- FIM DO SCRIPT
-- ==================================================

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

