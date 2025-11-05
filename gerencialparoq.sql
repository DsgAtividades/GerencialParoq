-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: 186.202.152.114
-- Generation Time: 22-Out-2025 às 16:15
-- Versão do servidor: 5.7.32-35-log
-- PHP Version: 5.6.40-0+deb8u12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gerencialparoq`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `access_logs`
--

CREATE TABLE `access_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `lojinha_caixa`
--

CREATE TABLE `lojinha_caixa` (
  `id` int(11) NOT NULL,
  `data_abertura` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_fechamento` timestamp NULL DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `saldo_inicial` decimal(10,2) NOT NULL DEFAULT '0.00',
  `saldo_final` decimal(10,2) DEFAULT NULL,
  `total_vendas` decimal(10,2) DEFAULT '0.00',
  `total_entradas` decimal(10,2) DEFAULT '0.00',
  `total_saidas` decimal(10,2) DEFAULT '0.00',
  `status` enum('aberto','fechado') DEFAULT 'aberto',
  `observacoes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `lojinha_caixa_movimentacoes`
--

CREATE TABLE `lojinha_caixa_movimentacoes` (
  `id` int(11) NOT NULL,
  `caixa_id` int(11) NOT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `descricao` varchar(200) NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_movimentacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `lojinha_categorias`
--

CREATE TABLE `lojinha_categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text,
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `lojinha_categorias`
--

INSERT INTO `lojinha_categorias` (`id`, `nome`, `descricao`, `ativo`, `created_at`, `updated_at`) VALUES
(41, 'Livros', NULL, 1, '2025-10-13 19:44:51', '2025-10-13 19:44:51'),
(42, 'Imagens', NULL, 1, '2025-10-13 19:44:51', '2025-10-13 19:44:51'),
(43, 'Terços', NULL, 1, '2025-10-13 19:44:51', '2025-10-13 19:44:51'),
(44, 'Velas', NULL, 1, '2025-10-13 19:44:51', '2025-10-13 19:44:51'),
(45, 'Roupas Litúrgicas', NULL, 1, '2025-10-13 19:44:51', '2025-10-13 19:44:51'),
(46, 'Decoração', NULL, 1, '2025-10-13 19:44:51', '2025-10-13 19:44:51'),
(47, 'Catequese', NULL, 1, '2025-10-13 19:44:51', '2025-10-13 19:44:51'),
(48, 'Outros', NULL, 1, '2025-10-13 19:44:51', '2025-10-13 19:44:51');

-- --------------------------------------------------------

--
-- Estrutura da tabela `lojinha_estoque_movimentacoes`
--

CREATE TABLE `lojinha_estoque_movimentacoes` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `tipo` enum('entrada','saida','ajuste') NOT NULL,
  `quantidade` int(11) NOT NULL,
  `motivo` varchar(200) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `venda_id` int(11) DEFAULT NULL,
  `data_movimentacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `lojinha_estoque_movimentacoes`
--

INSERT INTO `lojinha_estoque_movimentacoes` (`id`, `produto_id`, `tipo`, `quantidade`, `motivo`, `usuario_id`, `venda_id`, `data_movimentacao`) VALUES
(1, 1, 'entrada', 100, 'Estoque inicial', 1, NULL, '2025-10-13 19:58:23'),
(2, 2, 'entrada', 20, 'Estoque inicial', 1, NULL, '2025-10-13 20:50:58'),
(3, 3, 'entrada', 100, 'Estoque inicial', 1, NULL, '2025-10-16 00:56:11'),
(4, 5, 'entrada', 1, 'Estoque inicial', 1, NULL, '2025-10-16 19:00:06'),
(5, 6, 'entrada', 2, 'Estoque inicial', 1, NULL, '2025-10-16 19:00:53'),
(6, 7, 'entrada', 33, 'Estoque inicial', 1, NULL, '2025-10-16 19:01:18'),
(7, 8, 'entrada', 99, 'Estoque inicial', 1, NULL, '2025-10-16 19:01:52'),
(8, 1, 'saida', 1, 'Venda #V000001', NULL, NULL, '2025-10-16 19:01:57'),
(9, 9, 'entrada', 8, 'Estoque inicial', 1, NULL, '2025-10-16 19:02:10'),
(10, 10, 'entrada', 33, 'Estoque inicial', 1, NULL, '2025-10-16 19:02:37'),
(11, 11, 'entrada', 1, 'Estoque inicial', 1, NULL, '2025-10-16 19:03:03'),
(12, 12, 'entrada', 11, 'Estoque inicial', 1, NULL, '2025-10-16 19:03:38'),
(13, 13, 'entrada', 22, 'Estoque inicial', 1, NULL, '2025-10-16 19:04:00'),
(14, 14, 'entrada', 1, 'Estoque inicial', 1, NULL, '2025-10-16 19:04:27'),
(15, 15, 'entrada', 1, 'Estoque inicial', 1, NULL, '2025-10-16 19:05:01'),
(16, 1, 'saida', 1, 'Venda #V000002', NULL, NULL, '2025-10-16 19:05:09'),
(17, 16, 'entrada', 21, 'Estoque inicial', 1, NULL, '2025-10-16 19:05:25'),
(18, 17, 'entrada', 2, 'Estoque inicial', 1, NULL, '2025-10-16 19:06:29'),
(19, 18, 'entrada', 34, 'Estoque inicial', 1, NULL, '2025-10-16 19:06:46'),
(20, 19, 'entrada', 5, 'Estoque inicial', 1, NULL, '2025-10-16 19:07:10'),
(21, 20, 'entrada', 2, 'Estoque inicial', 1, NULL, '2025-10-16 19:07:30'),
(22, 22, 'entrada', 9, 'Estoque inicial', 1, NULL, '2025-10-16 19:08:38'),
(23, 1, 'entrada', 100, 'Aumento estoque (Produto: Biblia)', 1, NULL, '2025-10-16 19:14:53'),
(24, 2, 'entrada', 100, 'entrada (Produto: Terço)', 1, NULL, '2025-10-16 19:16:49'),
(25, 25, 'entrada', 2, 'Estoque inicial', 1, NULL, '2025-10-16 19:19:28'),
(26, 26, 'entrada', 123, 'Estoque inicial', 1, NULL, '2025-10-16 19:27:52'),
(27, 27, 'entrada', 42, 'Estoque inicial', 1, NULL, '2025-10-16 19:28:22'),
(28, 27, 'saida', 1, 'Venda #V000003', NULL, NULL, '2025-10-16 19:40:20'),
(29, 8, 'saida', 1, 'Venda #V000004', NULL, NULL, '2025-10-16 19:45:20'),
(30, 8, 'saida', 1, 'Venda #V000005', NULL, NULL, '2025-10-16 19:45:31'),
(31, 8, 'saida', 1, 'Venda #V000006', NULL, NULL, '2025-10-16 19:45:31'),
(32, 3, 'saida', 1, 'Venda #V000008', NULL, NULL, '2025-10-16 19:48:31'),
(33, 2, 'saida', 1, 'Venda #V000008', NULL, NULL, '2025-10-16 19:48:31'),
(34, 1, 'saida', 1, 'Venda #V000009', NULL, NULL, '2025-10-16 19:50:44'),
(35, 3, 'saida', 1, 'Venda #V000009', NULL, NULL, '2025-10-16 19:50:44'),
(36, 26, 'saida', 1, 'Venda #V000009', NULL, NULL, '2025-10-16 19:50:44'),
(37, 12, 'saida', 1, 'Venda #V000009', NULL, NULL, '2025-10-16 19:50:44'),
(38, 1, 'saida', 2, 'Venda #V000010', NULL, NULL, '2025-10-16 19:52:53'),
(39, 26, 'saida', 1, 'Venda #V000010', NULL, NULL, '2025-10-16 19:52:53'),
(40, 12, 'saida', 1, 'Venda #V000010', NULL, NULL, '2025-10-16 19:52:53'),
(41, 27, 'saida', 1, 'Venda #V000010', NULL, NULL, '2025-10-16 19:52:53'),
(42, 26, 'saida', 2, 'Venda #V000011', NULL, NULL, '2025-10-16 19:57:05'),
(43, 28, 'entrada', 24, 'Estoque inicial', 1, NULL, '2025-10-16 19:57:49'),
(44, 29, 'entrada', 2, 'Estoque inicial', 1, NULL, '2025-10-16 19:58:13'),
(45, 27, 'saida', 1, 'Venda #V000012', NULL, NULL, '2025-10-17 19:15:20'),
(46, 27, 'entrada', 38, 'recebimento de mercadoria (Produto: Bíblia)', 1, NULL, '2025-10-17 19:15:26'),
(47, 27, 'saida', 1, 'Venda #V000013', NULL, NULL, '2025-10-17 19:18:51'),
(48, 1, 'saida', 1, 'Venda #V000014', NULL, NULL, '2025-10-17 19:18:55'),
(49, 27, 'ajuste', 100, 'Recebimento (Produto: Bíblia)', 1, NULL, '2025-10-17 19:45:00'),
(50, 27, 'entrada', 1, 'Recimento (Produto: Bíblia)', 1, NULL, '2025-10-17 22:15:55'),
(51, 27, 'entrada', 1, 'Recebimento (Produto: Bíblia)', 1, NULL, '2025-10-17 22:24:19'),
(52, 27, 'saida', 38, 'Retirado (Produto: Bíblia)', 1, NULL, '2025-10-18 00:03:49'),
(53, 1, 'saida', 3, 'Venda #V000015', NULL, NULL, '2025-10-18 01:27:06'),
(54, 27, 'entrada', 4, 'recebido (Produto: Bíblia)', 1, NULL, '2025-10-18 02:58:46'),
(55, 1, 'saida', 1, 'Venda #V000016', NULL, NULL, '2025-10-18 13:51:56'),
(56, 3, 'saida', 1, 'Venda #V000016', NULL, NULL, '2025-10-18 13:51:57'),
(57, 2, 'saida', 1, 'Venda #V000016', NULL, NULL, '2025-10-18 13:51:57'),
(58, 14, 'saida', 1, 'Venda #V000017', NULL, NULL, '2025-10-18 13:55:00'),
(59, 20, 'saida', 1, 'Venda #V000017', NULL, NULL, '2025-10-18 13:55:00'),
(60, 27, 'saida', 4, 'xss (Produto: Bíblia)', 1, NULL, '2025-10-18 16:23:44'),
(61, 27, 'entrada', 24, 'chegada (Produto: Bíblia)', 1, NULL, '2025-10-18 16:25:50'),
(62, 8, 'saida', 2, 'Venda #V000018', NULL, NULL, '2025-10-20 20:26:37');

-- --------------------------------------------------------

--
-- Estrutura da tabela `lojinha_fornecedores`
--

CREATE TABLE `lojinha_fornecedores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `contato` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `endereco` text,
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `lojinha_fornecedores`
--

INSERT INTO `lojinha_fornecedores` (`id`, `nome`, `contato`, `telefone`, `email`, `endereco`, `ativo`, `created_at`, `updated_at`) VALUES
(18, 'Editora Ave Maria', NULL, NULL, NULL, NULL, 1, '2025-10-13 19:44:51', '2025-10-13 19:44:51'),
(19, 'Artigos Religiosos Divina Luz', NULL, NULL, NULL, NULL, 1, '2025-10-13 19:44:51', '2025-10-13 19:44:51'),
(20, 'Fornecedor Padrão', NULL, NULL, NULL, NULL, 1, '2025-10-13 19:44:51', '2025-10-13 19:44:51'),
(21, 'Distribuidora Católica', NULL, NULL, NULL, NULL, 1, '2025-10-13 19:44:51', '2025-10-13 19:44:51'),
(22, 'Livraria Paulinas', NULL, NULL, NULL, NULL, 1, '2025-10-13 19:44:51', '2025-10-13 19:44:51');

-- --------------------------------------------------------

--
-- Estrutura da tabela `lojinha_produtos`
--

CREATE TABLE `lojinha_produtos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `descricao` text,
  `categoria_id` int(11) DEFAULT NULL,
  `fornecedor` varchar(255) DEFAULT NULL,
  `preco_compra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `preco_venda` decimal(10,2) NOT NULL DEFAULT '0.00',
  `estoque_atual` int(11) DEFAULT '0',
  `estoque_minimo` int(11) DEFAULT '0',
  `foto` varchar(255) DEFAULT NULL,
  `tipo_liturgico` varchar(50) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `lojinha_produtos`
--

INSERT INTO `lojinha_produtos` (`id`, `codigo`, `nome`, `descricao`, `categoria_id`, `fornecedor`, `preco_compra`, `preco_venda`, `estoque_atual`, `estoque_minimo`, `foto`, `tipo_liturgico`, `ativo`, `created_at`, `updated_at`) VALUES
(1, '432', 'Biblia', 'Biblia Grande Vermelha', 41, 'Ave Maria', 50.00, 110.00, 190, 20, NULL, 'devoção', 1, '2025-10-13 19:58:23', '2025-10-18 13:51:56'),
(2, '1114', 'Terço', 'Terço de São Carlos Acutis', 43, 'SJO', 45.00, 79.00, 118, 10, NULL, 'devoção', 0, '2025-10-13 20:50:58', '2025-10-18 13:51:57'),
(3, '001', 'Pipoca', 'Pipoca Doce e Salgada', 48, 'PopCorn', 2.00, 5.00, 97, 10, NULL, 'outros', 1, '2025-10-16 00:56:11', '2025-10-18 13:51:57'),
(5, 'PROD-016', 'guardanapo', 'oi', 42, 'leo', 2.00, 2.00, 1, 1, NULL, 'sacramental', 1, '2025-10-16 19:00:06', '2025-10-16 20:11:31'),
(6, 'PROD-017', 'gel', 'oi', 46, 'leo', 22.00, 22.00, 2, 2, NULL, 'outros', 1, '2025-10-16 19:00:53', '2025-10-16 20:11:42'),
(7, 'PROD-018', 'gillete', 'oi', 47, 'leo', 33.00, 33.00, 33, 33, NULL, 'sacramental', 1, '2025-10-16 19:01:18', '2025-10-16 20:11:55'),
(8, 'PROD-003', 'ANEL', '9', 45, 'leo', 99.00, 99.00, 94, 99, NULL, 'liturgia', 1, '2025-10-16 19:01:52', '2025-10-20 20:26:37'),
(9, 'PROD-019', 'helicoptero', '8', 47, 'leo', 8.00, 8.00, 8, 8, NULL, NULL, 1, '2025-10-16 19:02:09', '2025-10-16 20:12:08'),
(10, 'PROD-026', 'livro', '3', 47, 'leo', 88.00, 21.00, 33, 33, NULL, 'liturgia', 1, '2025-10-16 19:02:37', '2025-10-16 20:15:13'),
(11, 'PROD-020', 'ima', '1', 47, 'leo', 1.00, 1.00, 1, 1, NULL, 'sacramental', 1, '2025-10-16 19:03:03', '2025-10-16 20:13:25'),
(12, 'PROD-005', 'BONE', '11', 47, 'leo', 11.00, 11.00, 9, 11, NULL, 'liturgia', 1, '2025-10-16 19:03:38', '2025-10-16 20:09:05'),
(13, 'PROD-006', 'bola', 'aaaa', 47, 'leo', 22.00, 11.00, 22, 22, NULL, 'sacramental', 1, '2025-10-16 19:04:00', '2025-10-16 20:09:15'),
(14, 'PROD-007', 'bicicleta', 'dsdas', 47, 'leo', 111.00, 33.00, 0, 1, NULL, 'sacramental', 1, '2025-10-16 19:04:27', '2025-10-18 13:55:00'),
(15, 'PROD-008', 'cadeado', 'dsadwef', 46, 'leo', 113.00, 1.00, 1, 2, NULL, 'sacramental', 1, '2025-10-16 19:05:01', '2025-10-16 20:09:41'),
(16, 'PROD-009', 'dado', 'qwsq', 47, 'leo', 31.00, 12.00, 21, 1, NULL, 'sacramental', 1, '2025-10-16 19:05:25', '2025-10-16 20:09:56'),
(17, 'PROD-010', 'espada', 'e2er2r2er2', 47, 'leo', 12.00, 12.00, 2, 2, NULL, 'sacramental', 1, '2025-10-16 19:06:29', '2025-10-16 20:10:06'),
(18, 'PROD-021', 'isqueiro', 'hretuyhrtujrkj54', 46, 'leo', 43.00, 43.00, 34, 34, NULL, 'devoção', 1, '2025-10-16 19:06:45', '2025-10-16 20:13:47'),
(19, 'PROD-022', 'jarro', '34t3y35y', 47, 'leo', 35.00, 6.00, 5, 4, NULL, 'outros', 1, '2025-10-16 19:07:10', '2025-10-16 20:13:55'),
(20, 'PROD-011', 'escavadeira', '13tr123', 43, 'leo', 21.00, 44.00, 1, 3, NULL, 'sacramental', 1, '2025-10-16 19:07:30', '2025-10-18 13:55:00'),
(21, 'PROD-012', 'faca', 'gdwgwertg3ryh3eh', 46, 'leo', 23.00, 23.00, 0, 0, NULL, 'sacramental', 1, '2025-10-16 19:07:57', '2025-10-16 20:10:28'),
(22, 'PROD-013', 'foto', '3r234t24', 48, 'leo', 99.00, 90.00, 9, 9, NULL, 'liturgia', 1, '2025-10-16 19:08:38', '2025-10-16 20:10:36'),
(23, 'PROD-014', 'farofa', 'f2e4t3wrtg4t', 47, 'leo', 12.00, 12.00, 0, 0, NULL, 'sacramental', 1, '2025-10-16 19:09:27', '2025-10-16 20:10:59'),
(24, 'PROD-023', 'joia', 'E12E123EE13E13E', 47, NULL, 2.00, 22.00, 0, 0, NULL, 'sacramental', 1, '2025-10-16 19:12:48', '2025-10-16 20:14:05'),
(25, 'PROD-024', 'jornal', 'eeee', 41, 'leo', 11.00, 10.01, 2, 1, NULL, 'catequese', 1, '2025-10-16 19:19:28', '2025-10-16 20:14:23'),
(26, 'PROD-001', 'Bíblia', '31231313', 47, 'leo', 123.00, 21.00, 119, 13, NULL, 'liturgia', 1, '2025-10-16 19:27:52', '2025-10-16 20:03:38'),
(27, 'PROD-002', 'Bíblia', '24t34t', 47, 'leo', 4.00, 24.00, 88, 42, NULL, 'catequese', 1, '2025-10-16 19:28:22', '2025-10-18 16:25:50'),
(28, 'PROD-015', 'gol', '2t2t24tt24t', 46, 'leo', 42424.00, 2424.00, 24, 24, NULL, 'sacramental', 1, '2025-10-16 19:57:49', '2025-10-16 20:11:17'),
(29, 'PROD-025', 'lapis', '2E32', 47, 'EF', 2.00, 2.00, 2, 2, NULL, 'devoção', 1, '2025-10-16 19:58:13', '2025-10-16 20:14:57');

-- --------------------------------------------------------

--
-- Estrutura da tabela `lojinha_vendas`
--

CREATE TABLE `lojinha_vendas` (
  `id` int(11) NOT NULL,
  `numero_venda` varchar(20) NOT NULL,
  `data_venda` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vendedor_id` int(11) DEFAULT NULL,
  `cliente_nome` varchar(200) DEFAULT NULL,
  `cliente_telefone` varchar(20) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `desconto` decimal(10,2) DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `forma_pagamento` enum('dinheiro','pix','cartao_debito','cartao_credito') NOT NULL,
  `status` enum('pendente','finalizada','cancelada') DEFAULT 'pendente',
  `observacoes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `lojinha_vendas`
--

INSERT INTO `lojinha_vendas` (`id`, `numero_venda`, `data_venda`, `vendedor_id`, `cliente_nome`, `cliente_telefone`, `subtotal`, `desconto`, `total`, `forma_pagamento`, `status`, `observacoes`, `created_at`, `updated_at`) VALUES
(1, 'V000001', '2025-10-16 19:01:57', NULL, 'Julio', '11987651234', 110.00, 0.00, 110.00, 'pix', 'finalizada', 'Teste final', '2025-10-16 19:01:57', '2025-10-16 19:01:57'),
(2, 'V000002', '2025-10-16 19:05:09', NULL, 'Juliao', '1', 110.00, 0.00, 110.00, 'dinheiro', 'finalizada', '1', '2025-10-16 19:05:09', '2025-10-16 19:05:09'),
(3, 'V000003', '2025-10-16 19:40:20', NULL, 'x', 'x', 24.00, 12.00, 12.00, 'dinheiro', 'finalizada', 'wdw', '2025-10-16 19:40:20', '2025-10-16 19:40:20'),
(4, 'V000004', '2025-10-16 19:45:20', NULL, 'W', 'W', 99.00, 0.00, 99.00, 'dinheiro', 'finalizada', 'WW', '2025-10-16 19:45:20', '2025-10-16 19:45:20'),
(5, 'V000005', '2025-10-16 19:45:31', NULL, 'DW', 'DW', 99.00, 0.00, 99.00, 'dinheiro', 'finalizada', 'DWWD', '2025-10-16 19:45:31', '2025-10-16 19:45:31'),
(7, 'V000006', '2025-10-16 19:45:31', NULL, 'DW', 'DW', 99.00, 0.00, 99.00, 'dinheiro', 'finalizada', 'DWWD', '2025-10-16 19:45:31', '2025-10-16 19:45:31'),
(8, 'V000008', '2025-10-16 19:48:31', NULL, 'Raissa Goncalves', '38987898787', 84.00, 5.00, 79.00, 'pix', 'finalizada', 'teste', '2025-10-16 19:48:31', '2025-10-16 19:48:31'),
(9, 'V000009', '2025-10-16 19:50:44', NULL, 'Jessika Lopes', '61987657253', 147.00, 0.00, 147.00, 'pix', 'finalizada', NULL, '2025-10-16 19:50:44', '2025-10-16 19:50:44'),
(10, 'V000010', '2025-10-16 19:52:53', NULL, 'Roberto Carlos', '42940498394', 276.00, 8.00, 268.00, 'cartao_credito', 'finalizada', NULL, '2025-10-16 19:52:53', '2025-10-16 19:52:53'),
(11, 'V000011', '2025-10-16 19:57:05', NULL, 'adaada', 'adaadada', 42.00, 0.00, 42.00, 'dinheiro', 'finalizada', NULL, '2025-10-16 19:57:05', '2025-10-16 19:57:05'),
(12, 'V000012', '2025-10-17 19:15:20', NULL, 'dwaw', 'adwas', 24.00, 0.00, 24.00, 'pix', 'finalizada', NULL, '2025-10-17 19:15:20', '2025-10-17 19:15:20'),
(13, 'V000013', '2025-10-17 19:18:51', NULL, NULL, NULL, 24.00, 0.00, 24.00, 'cartao_credito', 'finalizada', NULL, '2025-10-17 19:18:51', '2025-10-17 19:18:51'),
(14, 'V000014', '2025-10-17 19:18:55', NULL, NULL, NULL, 110.00, 0.00, 110.00, 'cartao_credito', 'finalizada', NULL, '2025-10-17 19:18:55', '2025-10-17 19:18:55'),
(15, 'V000015', '2025-10-18 01:27:06', NULL, 'jó', NULL, 330.00, 0.00, 330.00, 'dinheiro', 'finalizada', NULL, '2025-10-18 01:27:06', '2025-10-18 01:27:06'),
(16, 'V000016', '2025-10-18 13:51:56', NULL, NULL, NULL, 194.00, 0.00, 194.00, 'dinheiro', 'finalizada', NULL, '2025-10-18 13:51:56', '2025-10-18 13:51:56'),
(17, 'V000017', '2025-10-18 13:55:00', NULL, NULL, NULL, 77.00, 0.00, 77.00, 'pix', 'finalizada', NULL, '2025-10-18 13:55:00', '2025-10-18 13:55:00'),
(18, 'V000018', '2025-10-20 20:26:37', NULL, 'zapzin', NULL, 198.00, 0.00, 198.00, 'cartao_credito', 'finalizada', 'so de sacanagem', '2025-10-20 20:26:37', '2025-10-20 20:26:37');

-- --------------------------------------------------------

--
-- Estrutura da tabela `lojinha_vendas_itens`
--

CREATE TABLE `lojinha_vendas_itens` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `lojinha_vendas_itens`
--

INSERT INTO `lojinha_vendas_itens` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`, `subtotal`) VALUES
(1, 1, 1, 1, 110.00, 110.00),
(2, 2, 1, 1, 110.00, 110.00),
(3, 3, 27, 1, 24.00, 24.00),
(4, 4, 8, 1, 99.00, 99.00),
(5, 5, 8, 1, 99.00, 99.00),
(6, 7, 8, 1, 99.00, 99.00),
(7, 8, 3, 1, 5.00, 5.00),
(8, 8, 2, 1, 79.00, 79.00),
(9, 9, 1, 1, 110.00, 110.00),
(10, 9, 3, 1, 5.00, 5.00),
(11, 9, 26, 1, 21.00, 21.00),
(12, 9, 12, 1, 11.00, 11.00),
(13, 10, 1, 2, 110.00, 220.00),
(14, 10, 26, 1, 21.00, 21.00),
(15, 10, 12, 1, 11.00, 11.00),
(16, 10, 27, 1, 24.00, 24.00),
(17, 11, 26, 2, 21.00, 42.00),
(18, 12, 27, 1, 24.00, 24.00),
(19, 13, 27, 1, 24.00, 24.00),
(20, 14, 1, 1, 110.00, 110.00),
(21, 15, 1, 3, 110.00, 330.00),
(22, 16, 1, 1, 110.00, 110.00),
(23, 16, 3, 1, 5.00, 5.00),
(24, 16, 2, 1, 79.00, 79.00),
(25, 17, 14, 1, 33.00, 33.00),
(26, 17, 20, 1, 44.00, 44.00),
(27, 18, 8, 2, 99.00, 198.00);

-- --------------------------------------------------------

--
-- Estrutura da tabela `lojinha_venda_itens`
--

CREATE TABLE `lojinha_venda_itens` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `obras_obras`
--

CREATE TABLE `obras_obras` (
  `id` int(11) NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsavel_tecnico` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Em Andamento','Concluída','Pendente','Cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendente',
  `total` decimal(10,2) DEFAULT NULL,
  `valor_adiantado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `data_ordem_servico` date DEFAULT NULL,
  `data_conclusao` date DEFAULT NULL,
  `previsao_entrega` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `obras_servicos`
--

CREATE TABLE `obras_servicos` (
  `id` int(11) NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsavel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsavel_autorizacao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adiantamento_1` decimal(10,2) DEFAULT NULL,
  `data_adiant_1` date DEFAULT NULL,
  `adiantamento_2` decimal(10,2) DEFAULT NULL,
  `data_adiant_2` date DEFAULT NULL,
  `adiantamento_3` decimal(10,2) DEFAULT NULL,
  `data_adiant_3` date DEFAULT NULL,
  `valor_antecipado` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `falta_pagar` decimal(10,2) DEFAULT NULL,
  `status` enum('Em Andamento','Concluído','Pendente','Cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `previsao_entrega` date DEFAULT NULL,
  `data_ordem_servico` date DEFAULT NULL,
  `data_previsao_entrega` date DEFAULT NULL,
  `data_entrega_final` date DEFAULT NULL,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `obras_servicos`
--

INSERT INTO `obras_servicos` (`id`, `descricao`, `responsavel`, `responsavel_autorizacao`, `adiantamento_1`, `data_adiant_1`, `adiantamento_2`, `data_adiant_2`, `adiantamento_3`, `data_adiant_3`, `valor_antecipado`, `total`, `falta_pagar`, `status`, `previsao_entrega`, `data_ordem_servico`, `data_previsao_entrega`, `data_entrega_final`, `observacoes`, `created_at`, `updated_at`) VALUES
(9, 'Forro Sala 1,  Sala 2, Corredor escada e Divisória do porão', 'Dinga', 'Pe Regis', 3000.00, '2024-10-01', 2000.00, '2024-10-11', 6900.00, '2024-10-30', 11900.00, 11900.00, NULL, 'Concluído', '2024-10-30', '2024-09-26', NULL, '2024-10-30', 'FINALIZADO E PAGO\r\npAGAMENTO FINAL REALIZADO EM 29/10/2025 NO VALOR DE R$ 6.900,00', '2025-04-19 14:30:31', '2025-04-21 02:51:14'),
(10, 'Arquib. lateral + Duto da cozinha + Estrutura metalica escada', 'Valmir', 'Pe Regis', NULL, NULL, NULL, NULL, 7500.00, '2024-11-27', 7500.00, 7500.00, NULL, 'Concluído', '2024-11-27', '2024-10-25', NULL, '2024-11-27', 'FINALIZADO E PAGO\r\nENTREGA REALIZADA EM 27/11/2024 E PAGO', '2025-04-19 14:30:31', '2025-04-21 02:52:23'),
(11, 'Construção Quadra', 'Valmir', 'Pe Regis', NULL, NULL, NULL, NULL, 32000.00, '2024-12-27', 32000.00, 32000.00, NULL, 'Concluído', '2024-12-27', '2024-12-03', NULL, '2024-12-27', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:50:10'),
(12, 'Pintura corredores, secretaria, bazar + rufo quadra', 'Adalberto', '', 2000.00, '2025-03-07', NULL, NULL, 2700.00, '2025-03-20', 4700.00, 4700.00, NULL, 'Concluído', '2025-03-06', '2025-03-06', NULL, '2025-03-20', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:43:22'),
(13, 'Porta e parede São Pedro Café - Salão', 'Dinga', 'Pe Regis', NULL, NULL, NULL, NULL, 2350.00, '2025-04-01', 2350.00, 2350.00, NULL, 'Concluído', '2025-04-01', '2025-03-25', NULL, '2025-04-01', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:49:02'),
(14, 'Construção São Pedro Café', 'Fabiano', 'Rener e Flavia', NULL, NULL, NULL, NULL, 2100.00, '2025-04-01', 2100.00, 2100.00, NULL, 'Concluído', '2025-03-29', '2025-03-25', NULL, '2025-04-01', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:54:29'),
(15, 'Parede e Porta de correr no Corredor cozinha + Duto fiação Café', 'Dinga', 'Rener e Flavia', NULL, NULL, NULL, NULL, 2500.00, '2025-04-02', 2500.00, 2500.00, NULL, 'Concluído', '2025-04-02', '2025-03-26', NULL, '2025-04-02', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 03:08:51'),
(16, 'Armarios (Catequese, Musicos)', 'Edson', 'Pe Regis', 3000.00, '2025-03-14', 2000.00, '2025-03-27', NULL, NULL, 5000.00, 5000.00, NULL, 'Concluído', '2025-04-01', '2025-04-14', NULL, '2025-04-01', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:42:19'),
(17, 'Instalação de Exaustor + Redução duto + Manutenção motor', 'Valmir', 'Pe Regis', NULL, NULL, NULL, NULL, 4800.00, '2025-04-22', 4800.00, 4800.00, NULL, 'Concluído', '2025-04-22', '2025-03-20', NULL, '2025-04-22', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:53:28'),
(18, 'Armarios (Coroinhas)', 'Edson', 'Pe Regis', 2000.00, '2025-04-10', NULL, NULL, 1000.00, '2025-03-27', 3000.00, 3000.00, NULL, 'Concluído', '2025-03-27', '2025-03-10', NULL, '2025-03-27', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 03:09:34'),
(19, 'Fechar dutos do ar condicionado + Teto bazar + placas das maq. ar condicionado', 'Dinga', 'Pe Regis', NULL, NULL, NULL, NULL, 1700.00, '2025-04-09', 1700.00, 1700.00, NULL, 'Concluído', '2025-04-01', '2025-04-01', NULL, '2025-04-09', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:47:03'),
(20, 'Instalação de 4 ar condicionado', 'Wagner', 'Pe Regis', 3400.00, NULL, NULL, NULL, 3400.00, '2025-04-10', 6800.00, 6800.00, NULL, 'Concluído', '2025-04-10', '2025-03-27', NULL, '2025-04-10', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:48:07'),
(21, 'Instalação de suporte da TV Tenda', 'Valmir', 'Pe Regis', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.01, NULL, 'Concluído', '2025-04-17', '2025-04-01', NULL, '2025-04-19', 'Concluido - Brinde do Valmir para a paróquia', '2025-04-19 14:30:32', '2025-04-21 01:04:55'),
(22, 'Construção (Obras Salão)', 'Valmir', 'Rener e Flavia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7500.00, NULL, 'Concluído', '2025-05-20', '2025-05-20', NULL, '2025-04-26', 'FINALIZADO A PAGAR \r\nPAGAMENTO A SER REALIZADO NO DIA 26/04/2025', '2025-04-19 14:30:32', '2025-10-12 03:34:04'),
(23, 'Elétrica do Salão e café', 'Gervacio', 'Pe Regis', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1850.00, NULL, 'Concluído', '2025-05-20', '2025-05-20', NULL, '2025-04-20', 'FINALIZADO A PAGAR', '2025-04-19 14:30:32', '2025-05-01 01:50:15'),
(24, 'Revestimento Corredor + Pintura corredor + Pintura Pastoral Social', 'Valmir', 'Rener e Flavia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4000.00, NULL, 'Em Andamento', '2025-05-20', '2025-05-20', NULL, '2025-04-13', 'FINALIZADO A PAGAR', '2025-04-19 14:30:32', '2025-10-13 20:05:11'),
(25, 'Construção reforma quadra 1', 'Valmir', 'Pe Regis', 10000.00, '2025-04-01', NULL, NULL, NULL, NULL, 10000.00, 16200.00, NULL, 'Concluído', '2025-05-20', '2025-05-20', NULL, '2025-04-26', 'EM ANDAMENTO', '2025-04-19 14:30:32', '2025-10-13 02:50:09'),
(26, 'Instalação porta armario na parte baixa dos músicos', 'Edson', 'Pe Regis', NULL, NULL, NULL, NULL, 2000.00, '2025-04-16', 2000.00, 2000.00, NULL, 'Concluído', '2025-04-08', '2025-04-08', NULL, '2025-04-16', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:46:14'),
(27, 'Troca das portas do Salão São Pedro café', 'TBD', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, 'Pendente', NULL, NULL, NULL, NULL, 'A INICIAR', '2025-04-19 14:30:32', '2025-05-01 03:25:49'),
(28, 'Instalação de depósito de cadeiras e mesas (container)', 'TBD', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Pendente', NULL, NULL, NULL, NULL, 'A INICIAR', '2025-04-19 14:30:32', '2025-04-21 13:04:57'),
(29, 'Instalação de Telão na entrada do Salão', 'TBD', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, 'Pendente', NULL, NULL, NULL, NULL, 'A INICIAR', '2025-04-19 14:30:32', '2025-04-21 13:04:59'),
(31, 'Instalação de cameras de transmissoes para a PASCOM', 'Denys', 'Denys', 600.00, '2025-10-13', 100.00, '2025-10-15', NULL, NULL, 700.00, 2600.01, 3000.00, 'Cancelado', '2025-05-20', '2025-05-20', '2025-09-05', NULL, '', '2025-04-22 15:34:36', '2025-10-13 20:07:41'),
(32, 'Container lateral para cadeiras e local para festa junina movel', 'Denys', 'Rener', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 13500.00, 13500.00, 'Pendente', NULL, '1969-12-31', '1969-12-31', NULL, 'Aguardando autorização da OS', '2025-09-15 20:48:58', '2025-09-15 20:48:58'),
(33, 'da', 'da', 'da', 1111.00, '1969-12-31', NULL, NULL, NULL, NULL, 1111.00, NULL, -1111.00, 'Pendente', NULL, '1969-12-31', '1969-12-31', '1969-12-31', 'dda', '2025-10-13 19:48:55', '2025-10-13 19:48:55');

-- --------------------------------------------------------

--
-- Estrutura da tabela `obras_servicos_arquivos`
--

CREATE TABLE `obras_servicos_arquivos` (
  `id` int(11) NOT NULL,
  `servico_id` int(11) DEFAULT NULL,
  `tipo` enum('comprovante_pagamento','nota_fiscal','ordem_servico') COLLATE latin1_general_ci DEFAULT NULL,
  `nome_arquivo` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `caminho_arquivo` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `data_upload` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Extraindo dados da tabela `obras_servicos_arquivos`
--

INSERT INTO `obras_servicos_arquivos` (`id`, `servico_id`, `tipo`, `nome_arquivo`, `caminho_arquivo`, `data_upload`) VALUES
(64, 9, 'nota_fiscal', 'NOTA DE PRESTAÇÃO DE SERVIÇO.pdf', 'uploads/9/nota_fiscal_6805629d62082_20250420_180949.pdf', '2025-04-20 18:09:49'),
(65, 9, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 18.13.23.jpeg', 'uploads/9/comprovante_pagamento_68056386a6fc1_20250420_181342.jpeg', '2025-04-20 18:13:42'),
(66, 9, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 18.10.28.jpeg', 'uploads/9/comprovante_pagamento_68056386a8a0d_20250420_181342.jpeg', '2025-04-20 18:13:42'),
(67, 9, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 18.11.41.jpeg', 'uploads/9/comprovante_pagamento_68056386a9a7e_20250420_181342.jpeg', '2025-04-20 18:13:42'),
(68, 10, 'ordem_servico', 'DOC-20241018-WA0019._20241025_122201_0000.pdf', 'uploads/10/ordem_servico_6805644c6aeb0_20250420_181700.pdf', '2025-04-20 18:17:00'),
(69, 11, 'ordem_servico', 'DOC-20241126-WA0054._20241203_220027_0000.pdf', 'uploads/11/ordem_servico_6805667e4e7b9_20250420_182622.pdf', '2025-04-20 18:26:22'),
(70, 12, 'ordem_servico', 'Obras na paroquia - Adalberto.pdf', 'uploads/12/ordem_servico_680568e3b2854_20250420_183635.pdf', '2025-04-20 18:36:35'),
(71, 22, 'ordem_servico', 'DOC-20250330-WA0091..pdf', 'uploads/22/ordem_servico_6805a47554b0f_20250420_225045.pdf', '2025-04-20 22:50:45'),
(72, 25, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 22.51.55.jpeg', 'uploads/25/comprovante_pagamento_6805a5cee8137_20250420_225630.jpeg', '2025-04-20 22:56:30'),
(73, 25, 'ordem_servico', '172_20250312_224129_0000.pdf', 'uploads/25/ordem_servico_6805a5cee9508_20250420_225630.pdf', '2025-04-20 22:56:30'),
(74, 14, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 22.59.23.jpeg', 'uploads/14/comprovante_pagamento_6805a75f4f51e_20250420_230311.jpeg', '2025-04-20 23:03:11'),
(75, 14, 'nota_fiscal', '35503082220030270000152000000000000625032469748022 (2).pdf', 'uploads/14/nota_fiscal_6805a75f503dc_20250420_230311.pdf', '2025-04-20 23:03:11'),
(76, 14, 'ordem_servico', 'WhatsApp Image 2025-04-20 at 23.00.12.jpeg', 'uploads/14/ordem_servico_6805a75f5120a_20250420_230311.pdf', '2025-04-20 23:03:11'),
(77, 20, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 23.07.43.jpeg', 'uploads/20/comprovante_pagamento_6805a8e55a0e2_20250420_230941.jpeg', '2025-04-20 23:09:41'),
(78, 20, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 23.04.32.jpeg', 'uploads/20/comprovante_pagamento_6805a8e55b3a1_20250420_230941.jpeg', '2025-04-20 23:09:41'),
(79, 20, 'nota_fiscal', 'nota Paroquia São Pedro Apostolo.salas.pdf', 'uploads/20/nota_fiscal_6805a8e55c567_20250420_230941.pdf', '2025-04-20 23:09:41'),
(80, 20, 'nota_fiscal', 'nota São Pedro 50% restante.pdf', 'uploads/20/nota_fiscal_6805a8e55d7d0_20250420_230941.pdf', '2025-04-20 23:09:41'),
(81, 20, 'ordem_servico', 'orçamento paroquia São pedro Apostolo 2025.pdf', 'uploads/20/ordem_servico_6805a8e55f09f_20250420_230941.pdf', '2025-04-20 23:09:41'),
(82, 19, 'nota_fiscal', 'NOTA MITRA DIOCESANA.pdf', 'uploads/19/nota_fiscal_6805aa388c6b5_20250420_231520.pdf', '2025-04-20 23:15:20'),
(87, 19, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 23.18.45.jpeg', 'uploads/19/comprovante_pagamento_6805ab175458d_20250420_231903.jpeg', '2025-04-20 23:19:03'),
(88, 15, 'nota_fiscal', '35503082219944996000111000000000006025040395834491.pdf', 'uploads/15/nota_fiscal_6805ab925f037_20250420_232106.pdf', '2025-04-20 23:21:06'),
(89, 15, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 23.22.47.jpeg', 'uploads/15/comprovante_pagamento_6805ac1417b9f_20250420_232316.jpeg', '2025-04-20 23:23:16'),
(90, 15, 'ordem_servico', 'WhatsApp Image 2025-04-20 at 23.21.50.jpeg', 'uploads/15/ordem_servico_6805ac1418b69_20250420_232316.jpeg', '2025-04-20 23:23:16'),
(97, 26, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 23.28.55.jpeg', 'uploads/26/comprovante_pagamento_6805ad9de66f6_20250420_232949.jpeg', '2025-04-20 23:29:49'),
(98, 26, 'nota_fiscal', '35138012248553388000184000000000000725049260185262.pdf', 'uploads/26/nota_fiscal_6805ad9de7b8c_20250420_232949.pdf', '2025-04-20 23:29:49'),
(99, 26, 'ordem_servico', 'WhatsApp Image 2025-04-20 at 23.26.58 (1).jpeg', 'uploads/26/ordem_servico_6805ad9de943c_20250420_232949.jpeg', '2025-04-20 23:29:49'),
(100, 18, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 23.31.52.jpeg', 'uploads/18/comprovante_pagamento_6805aea0a6039_20250420_233408.jpeg', '2025-04-20 23:34:08'),
(101, 18, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 23.31.53.jpeg', 'uploads/18/comprovante_pagamento_6805aea0a736d_20250420_233408.jpeg', '2025-04-20 23:34:08'),
(103, 18, 'nota_fiscal', '35138012248553388000184000000000000625040795357940 (1).pdf', 'uploads/18/nota_fiscal_6805aef984bf6_20250420_233537.pdf', '2025-04-20 23:35:37'),
(106, 18, 'ordem_servico', 'WhatsApp Image 2025-04-20 at 23.37.24.jpeg', 'uploads/18/ordem_servico_6805af74ebdd6_20250420_233740.jpeg', '2025-04-20 23:37:40'),
(107, 16, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 23.41.04 (1).jpeg', 'uploads/16/comprovante_pagamento_6805b08b7cdaf_20250420_234219.jpeg', '2025-04-20 23:42:19'),
(108, 16, 'comprovante_pagamento', 'WhatsApp Image 2025-04-20 at 23.41.04.jpeg', 'uploads/16/comprovante_pagamento_6805b08b7eebb_20250420_234219.jpeg', '2025-04-20 23:42:19'),
(109, 16, 'nota_fiscal', 'NOTA DE SERVIÇO PRESTADO.pdf', 'uploads/16/nota_fiscal_6805b08b8131b_20250420_234219.pdf', '2025-04-20 23:42:19'),
(110, 16, 'ordem_servico', 'WhatsApp Image 2025-04-20 at 23.37.24 (1).jpeg', 'uploads/16/ordem_servico_6805b08b83036_20250420_234219.jpeg', '2025-04-20 23:42:19');

-- --------------------------------------------------------

--
-- Estrutura da tabela `obras_system_users`
--

CREATE TABLE `obras_system_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_completo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_acesso` enum('Administrador','Operador') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `obras_system_users`
--

INSERT INTO `obras_system_users` (`id`, `username`, `password`, `nome_completo`, `tipo_acesso`, `ativo`, `created_at`) VALUES
(1, 'admin', '$2y$10$2a/3.Kg8yWLyV9sUyxc5bum5K45uSFepKSoEjumxpBQrE1fsNq9Py', 'Administrador', 'Administrador', 1, '2025-04-07 02:56:49');

-- --------------------------------------------------------

--
-- Estrutura da tabela `obras_users`
--

CREATE TABLE `obras_users` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpf` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `data_cadastro` date NOT NULL,
  `endereco` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visitado_por` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qtd_moram_casa` int(11) DEFAULT NULL,
  `paga_aluguel` enum('Sim','Não') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paroquia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `situacao` enum('Ativo','Inativo','Aguardando Documentação','Outros') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Ativo',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `relatorios_atividades`
--

CREATE TABLE `relatorios_atividades` (
  `id` int(11) NOT NULL,
  `titulo_atividade` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `setor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsavel` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_inicio` date NOT NULL,
  `data_previsao` date NOT NULL,
  `data_termino` date DEFAULT NULL,
  `status` enum('em_andamento','concluido','a_fazer','pausado','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'a_fazer',
  `observacao` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `relatorios_atividades`
--

INSERT INTO `relatorios_atividades` (`id`, `titulo_atividade`, `setor`, `responsavel`, `data_inicio`, `data_previsao`, `data_termino`, `status`, `observacao`, `created_at`, `updated_at`, `user_id`) VALUES
(6, 'Manutenção e instalação de Cameras de segurança da paroquia', 'Infra de TI', 'Denys', '2025-09-11', '2025-09-28', NULL, 'em_andamento', 'Foram chamados dois fornecedores para avaliar ao serviço e solicitado o orçamento para analise.\r\nGiba veio na quinta-feira(11)\r\nEdu veio na sexta-feira(12)\r\nAguardando orçamento para dar andamento nas mudanças\r\n\r\nFoi solicitado a instalação de 2 cameras PTZ de alta qualidade\r\ninstalação de 2 cameras nas quadras\r\ninstalação de 2 cameras nas salas de jogos\r\ninstalação de 2 cameras nas dentro da igreja', '2025-09-12 18:28:47', '2025-09-12 18:28:47', 17),
(7, 'Instalação do container para as cadeiras', 'Manutenção paroquial', 'Denys', '2025-09-11', '2025-09-21', NULL, 'em_andamento', 'Foi enviado as informações para o Valmir realizar o orçamento para a construção do coitainer que será colocado na lateral da cozinha, onde será colocado as cadeiras da paroquia.', '2025-09-12 18:31:14', '2025-09-12 18:31:14', 17),
(8, 'Instalação das antenas (AccessPoint) para internet no salão, sala de jogos e quadra de esportes', 'Infra de TI', 'Denys', '2025-09-09', '2025-09-28', NULL, 'em_andamento', 'Compra de 2 antenas para aumento de sinal da internet para toda a paroquia. \r\nSerá colocado uma antena no salão e outra na sala de jogos(ping-pong).', '2025-09-12 18:33:45', '2025-09-12 18:33:45', 17),
(9, 'Manutenção das geladeiras e freezer', 'Cozinha', 'Denys', '2025-09-15', '2025-09-28', NULL, 'a_fazer', 'Chamar profissional para fazer manutenção dos equipamentos.', '2025-09-12 19:27:29', '2025-09-12 19:27:29', 17);

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `module_access` varchar(50) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_access` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `module_access`, `role`, `is_active`, `created_at`, `last_access`, `updated_at`) VALUES
(1, 'badmin', '$2b$10$peRkNU3bQtIQeZ5avqb93.3yRnDEAH/TVbknStAmxh8VjO1e79F4i', 'Administrador do Bazar', 'admin.bazar@paroquia.com', 'bazar', 'admin', 1, '2025-09-09 18:31:08', '2025-10-13 18:52:41', '2025-10-13 18:52:41'),
(2, 'bazar', '$2b$10$4HJwfCJiEC.wwgcGnfB5FerCNeTzM0eyca.HNUT0GqDFoAs39TVpe', 'Usuário do Bazar', 'user.bazar@paroquia.com', 'bazar', 'user', 1, '2025-09-09 18:31:08', '2025-10-13 18:50:31', '2025-10-13 18:50:31'),
(3, 'ladmin', '$2b$10$peRkNU3bQtIQeZ5avqb93.3yRnDEAH/TVbknStAmxh8VjO1e79F4i', 'Administrador da Lojinha', 'admin.lojinha@paroquia.com', 'lojinha', 'admin', 1, '2025-09-09 18:31:08', '2025-10-22 18:57:28', '2025-10-22 18:57:28'),
(4, 'lojinha', '$2b$10$4HJwfCJiEC.wwgcGnfB5FerCNeTzM0eyca.HNUT0GqDFoAs39TVpe', 'Usuário da Lojinha', 'user.lojinha@paroquia.com', 'lojinha', 'user', 1, '2025-09-09 18:31:08', '2025-10-13 18:59:15', '2025-10-13 18:59:15'),
(5, 'cfadmin', '$2b$10$peRkNU3bQtIQeZ5avqb93.3yRnDEAH/TVbknStAmxh8VjO1e79F4i', 'Administrador do Café', 'admin.cafe@paroquia.com', 'cafe', 'admin', 1, '2025-09-09 18:31:08', '2025-10-13 19:00:19', '2025-10-13 19:00:19'),
(6, 'cafe', '$2b$10$4HJwfCJiEC.wwgcGnfB5FerCNeTzM0eyca.HNUT0GqDFoAs39TVpe', 'Usuário do Café', 'user.cafe@paroquia.com', 'cafe', 'user', 1, '2025-09-09 18:31:08', '2025-10-13 19:01:57', '2025-10-13 19:01:57'),
(7, 'psadmin', '$2b$10$peRkNU3bQtIQeZ5avqb93.3yRnDEAH/TVbknStAmxh8VjO1e79F4i', 'Administrador Pastoral Social', 'admin.pastoral@paroquia.com', 'pastoral-social', 'admin', 1, '2025-09-09 18:31:08', '2025-10-13 19:03:41', '2025-10-13 19:03:41'),
(8, 'pastoral-social', '$2b$10$4HJwfCJiEC.wwgcGnfB5FerCNeTzM0eyca.HNUT0GqDFoAs39TVpe', 'Usuário Pastoral Social', 'user.pastoral@paroquia.com', 'pastoral-social', 'user', 1, '2025-09-09 18:31:08', '2025-10-13 19:04:50', '2025-10-13 19:04:50'),
(9, 'oadmin', '$2b$10$peRkNU3bQtIQeZ5avqb93.3yRnDEAH/TVbknStAmxh8VjO1e79F4i', 'Administrador de Obras', 'admin.obras@paroquia.com', 'obras', 'admin', 1, '2025-09-09 18:31:08', '2025-10-14 19:21:38', '2025-10-14 19:21:38'),
(10, 'obras', '$2b$10$4HJwfCJiEC.wwgcGnfB5FerCNeTzM0eyca.HNUT0GqDFoAs39TVpe', 'Usuário de Obras', 'user.obras@paroquia.com', 'obras', 'user', 1, '2025-09-09 18:31:08', NULL, '2025-09-12 20:07:46'),
(11, 'cpadmin', '$2b$10$peRkNU3bQtIQeZ5avqb93.3yRnDEAH/TVbknStAmxh8VjO1e79F4i', 'Administrador de Contas', 'admin.contas@paroquia.com', 'contas-pagas', 'admin', 1, '2025-09-09 18:31:08', '2025-10-13 17:21:30', '2025-10-13 17:21:30'),
(12, 'contas-pagas', '$2b$10$4HJwfCJiEC.wwgcGnfB5FerCNeTzM0eyca.HNUT0GqDFoAs39TVpe', 'Usuário de Contas', 'user.contas@paroquia.com', 'contas-pagas', 'user', 1, '2025-09-09 18:31:08', NULL, '2025-09-12 20:08:39'),
(13, 'madmin', '$2b$10$peRkNU3bQtIQeZ5avqb93.3yRnDEAH/TVbknStAmxh8VjO1e79F4i', 'Administrador de Membros', 'admin.membros@paroquia.com', 'membros', 'admin', 1, '2025-09-09 18:31:08', '2025-10-13 02:56:57', '2025-10-13 02:56:57'),
(14, 'membros', '$2b$10$4HJwfCJiEC.wwgcGnfB5FerCNeTzM0eyca.HNUT0GqDFoAs39TVpe', 'Usuário de Membros', 'user.membros@paroquia.com', 'membros', 'user', 1, '2025-09-09 18:31:08', NULL, '2025-09-12 20:08:44'),
(15, 'ctadmin', '$2b$10$peRkNU3bQtIQeZ5avqb93.3yRnDEAH/TVbknStAmxh8VjO1e79F4i', 'Administrador de Catequese', 'admin.catequese@paroquia.com', 'catequese', 'admin', 1, '2025-09-09 18:31:08', NULL, '2025-09-12 20:09:55'),
(16, 'catequese', '$2b$10$4HJwfCJiEC.wwgcGnfB5FerCNeTzM0eyca.HNUT0GqDFoAs39TVpe', 'Usuário de Catequese', 'user.catequese@paroquia.com', 'catequese', 'user', 1, '2025-09-09 18:31:08', NULL, '2025-09-12 20:09:05'),
(17, 'aadmin', '$2b$10$peRkNU3bQtIQeZ5avqb93.3yRnDEAH/TVbknStAmxh8VjO1e79F4i', 'Administrador de Atividades', 'admin.atividades@paroquia.com', 'atividades', 'admin', 1, '2025-09-09 18:31:08', '2025-09-15 20:46:50', '2025-09-15 20:46:50'),
(18, 'atividades', '$2b$10$4HJwfCJiEC.wwgcGnfB5FerCNeTzM0eyca.HNUT0GqDFoAs39TVpe', 'Usuário de Atividades', 'user.atividades@paroquia.com', 'atividades', 'user', 1, '2025-09-09 18:31:08', '2025-09-12 20:17:12', '2025-09-12 20:17:12'),
(19, 'sadmin', '$2b$10$peRkNU3bQtIQeZ5avqb93.3yRnDEAH/TVbknStAmxh8VjO1e79F4i', 'Administrador da Secretaria', 'admin.secretaria@paroquia.com', 'secretaria', 'admin', 1, '2025-09-09 18:31:08', '2025-09-10 17:54:50', '2025-09-12 20:09:27'),
(20, 'secretaria', '$2b$10$4HJwfCJiEC.wwgcGnfB5FerCNeTzM0eyca.HNUT0GqDFoAs39TVpe', 'Usuário da Secretaria', 'user.secretaria@paroquia.com', 'secretaria', 'user', 1, '2025-09-09 18:31:08', NULL, '2025-09-12 20:09:30'),
(21, 'cadmin', '$2b$10$peRkNU3bQtIQeZ5avqb93.3yRnDEAH/TVbknStAmxh8VjO1e79F4i', 'Administrador de Compras', 'admin.compras@paroquia.com', 'compras', 'admin', 1, '2025-09-09 18:31:08', NULL, '2025-09-12 20:10:42'),
(22, 'compras', '$2b$10$4HJwfCJiEC.wwgcGnfB5FerCNeTzM0eyca.HNUT0GqDFoAs39TVpe', 'Usuário de Compras', 'user.compras@paroquia.com', 'compras', 'user', 1, '2025-09-09 18:31:08', NULL, '2025-09-12 20:10:05'),
(23, 'eadmin', '$2b$10$peRkNU3bQtIQeZ5avqb93.3yRnDEAH/TVbknStAmxh8VjO1e79F4i', 'Administrador de Eventos', 'admin.eventos@paroquia.com', 'eventos', 'admin', 1, '2025-09-09 18:31:08', '2025-09-16 21:08:35', '2025-09-16 21:08:35'),
(24, 'eventos', '$2b$10$4HJwfCJiEC.wwgcGnfB5FerCNeTzM0eyca.HNUT0GqDFoAs39TVpe', 'Usuário de Eventos', 'user.eventos@paroquia.com', 'eventos', 'user', 1, '2025-09-09 18:31:08', NULL, '2025-09-12 20:10:11'),
(25, 'admin_sistema', '$2y$10$7GgHvePcGUFTgX...64Xtu7M.U1Oda5zo3ezGGTGqGYKOVyqMs3Tm', '', NULL, 'sistema', 'user', 1, '2025-09-10 17:57:29', '2025-09-10 18:43:51', '2025-09-10 18:43:51'),
(27, 'admin', '$2y$10$qA8DGJ0UPsNk3vu0SYod3.zLIjXQOL4rZJJv741zrZFJbUG7olQ3.', '', NULL, 'sistema', 'user', 1, '2025-09-10 18:56:46', '2025-10-22 18:57:21', '2025-10-22 18:57:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_access_logs_user` (`user_id`),
  ADD KEY `idx_access_logs_module` (`module`),
  ADD KEY `idx_access_logs_created` (`created_at`);

--
-- Indexes for table `lojinha_caixa`
--
ALTER TABLE `lojinha_caixa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_caixa_data` (`data_abertura`),
  ADD KEY `idx_caixa_usuario` (`usuario_id`);

--
-- Indexes for table `lojinha_caixa_movimentacoes`
--
ALTER TABLE `lojinha_caixa_movimentacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `caixa_id` (`caixa_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `lojinha_categorias`
--
ALTER TABLE `lojinha_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lojinha_estoque_movimentacoes`
--
ALTER TABLE `lojinha_estoque_movimentacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `venda_id` (`venda_id`),
  ADD KEY `idx_estoque_produto` (`produto_id`);

--
-- Indexes for table `lojinha_fornecedores`
--
ALTER TABLE `lojinha_fornecedores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lojinha_produtos`
--
ALTER TABLE `lojinha_produtos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_produtos_categoria` (`categoria_id`),
  ADD KEY `idx_produtos_ativo` (`ativo`);

--
-- Indexes for table `lojinha_vendas`
--
ALTER TABLE `lojinha_vendas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_venda` (`numero_venda`),
  ADD KEY `idx_vendas_data` (`data_venda`),
  ADD KEY `idx_vendas_vendedor` (`vendedor_id`);

--
-- Indexes for table `lojinha_vendas_itens`
--
ALTER TABLE `lojinha_vendas_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venda` (`venda_id`),
  ADD KEY `idx_produto` (`produto_id`);

--
-- Indexes for table `lojinha_venda_itens`
--
ALTER TABLE `lojinha_venda_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`),
  ADD KEY `idx_venda_itens_venda` (`venda_id`);

--
-- Indexes for table `obras_obras`
--
ALTER TABLE `obras_obras`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `obras_servicos`
--
ALTER TABLE `obras_servicos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `obras_servicos_arquivos`
--
ALTER TABLE `obras_servicos_arquivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `servico_id` (`servico_id`);

--
-- Indexes for table `obras_system_users`
--
ALTER TABLE `obras_system_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `obras_users`
--
ALTER TABLE `obras_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_cpf` (`cpf`);

--
-- Indexes for table `relatorios_atividades`
--
ALTER TABLE `relatorios_atividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data_inicio` (`data_inicio`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `idx_users_module` (`module_access`),
  ADD KEY `idx_users_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_logs`
--
ALTER TABLE `access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lojinha_caixa`
--
ALTER TABLE `lojinha_caixa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lojinha_caixa_movimentacoes`
--
ALTER TABLE `lojinha_caixa_movimentacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lojinha_categorias`
--
ALTER TABLE `lojinha_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `lojinha_estoque_movimentacoes`
--
ALTER TABLE `lojinha_estoque_movimentacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `lojinha_fornecedores`
--
ALTER TABLE `lojinha_fornecedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `lojinha_produtos`
--
ALTER TABLE `lojinha_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `lojinha_vendas`
--
ALTER TABLE `lojinha_vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `lojinha_vendas_itens`
--
ALTER TABLE `lojinha_vendas_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `lojinha_venda_itens`
--
ALTER TABLE `lojinha_venda_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `obras_obras`
--
ALTER TABLE `obras_obras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `obras_servicos`
--
ALTER TABLE `obras_servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `obras_servicos_arquivos`
--
ALTER TABLE `obras_servicos_arquivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `obras_system_users`
--
ALTER TABLE `obras_system_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `obras_users`
--
ALTER TABLE `obras_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `relatorios_atividades`
--
ALTER TABLE `relatorios_atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `access_logs`
--
ALTER TABLE `access_logs`
  ADD CONSTRAINT `access_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `lojinha_caixa`
--
ALTER TABLE `lojinha_caixa`
  ADD CONSTRAINT `lojinha_caixa_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `lojinha_caixa_movimentacoes`
--
ALTER TABLE `lojinha_caixa_movimentacoes`
  ADD CONSTRAINT `lojinha_caixa_movimentacoes_ibfk_1` FOREIGN KEY (`caixa_id`) REFERENCES `lojinha_caixa` (`id`),
  ADD CONSTRAINT `lojinha_caixa_movimentacoes_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `lojinha_estoque_movimentacoes`
--
ALTER TABLE `lojinha_estoque_movimentacoes`
  ADD CONSTRAINT `lojinha_estoque_movimentacoes_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `lojinha_produtos` (`id`),
  ADD CONSTRAINT `lojinha_estoque_movimentacoes_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `lojinha_estoque_movimentacoes_ibfk_3` FOREIGN KEY (`venda_id`) REFERENCES `lojinha_vendas` (`id`);

--
-- Limitadores para a tabela `lojinha_produtos`
--
ALTER TABLE `lojinha_produtos`
  ADD CONSTRAINT `lojinha_produtos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `lojinha_categorias` (`id`);

--
-- Limitadores para a tabela `lojinha_vendas`
--
ALTER TABLE `lojinha_vendas`
  ADD CONSTRAINT `lojinha_vendas_ibfk_1` FOREIGN KEY (`vendedor_id`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `lojinha_vendas_itens`
--
ALTER TABLE `lojinha_vendas_itens`
  ADD CONSTRAINT `lojinha_vendas_itens_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `lojinha_vendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lojinha_vendas_itens_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `lojinha_produtos` (`id`);

--
-- Limitadores para a tabela `lojinha_venda_itens`
--
ALTER TABLE `lojinha_venda_itens`
  ADD CONSTRAINT `lojinha_venda_itens_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `lojinha_vendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lojinha_venda_itens_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `lojinha_produtos` (`id`);

--
-- Limitadores para a tabela `obras_servicos_arquivos`
--
ALTER TABLE `obras_servicos_arquivos`
  ADD CONSTRAINT `obras_servicos_arquivos_ibfk_1` FOREIGN KEY (`servico_id`) REFERENCES `obras_servicos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
