-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 09/01/2026 às 21:19
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `homolog`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_cartoes`
--

CREATE TABLE `cafe_cartoes` (
  `id` int(11) NOT NULL,
  `codigo` varchar(255) NOT NULL,
  `data_geracao` timestamp NULL DEFAULT current_timestamp(),
  `usado` tinyint(1) DEFAULT 0,
  `id_pessoa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_categorias`
--

CREATE TABLE `cafe_categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `icone` varchar(50) NOT NULL,
  `ordem` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cafe_categorias`
--

INSERT INTO `cafe_categorias` (`id`, `nome`, `icone`, `ordem`, `created_at`) VALUES
(48, 'Caixas', '', 0, '2025-07-04 17:20:18'),
(49, 'Portaria', '', 0, '2025-07-04 17:21:12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_grupos`
--

CREATE TABLE `cafe_grupos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cafe_grupos`
--

INSERT INTO `cafe_grupos` (`id`, `nome`, `created_at`) VALUES
(1, 'Administrador', '2025-04-08 01:11:40'),
(17, 'Caixas', '2025-04-27 03:12:26'),
(18, 'Portaria', '2025-04-29 00:20:05'),
(20, 'Admin_Paroquia', '2025-06-02 13:56:21'),
(36, 'Barraca de Batata Frita e Pastel', '2025-07-04 01:52:38'),
(46, 'Gerente', '2025-07-04 17:21:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_grupos_permissoes`
--

CREATE TABLE `cafe_grupos_permissoes` (
  `grupo_id` int(11) NOT NULL,
  `permissao_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cafe_grupos_permissoes`
--

INSERT INTO `cafe_grupos_permissoes` (`grupo_id`, `permissao_id`, `created_at`) VALUES
(1, 1, '2025-06-05 12:20:20'),
(1, 2, '2025-06-05 12:20:20'),
(1, 3, '2025-06-05 12:20:20'),
(1, 4, '2025-06-05 12:20:20'),
(1, 5, '2025-06-05 12:20:20'),
(1, 6, '2025-06-05 12:20:20'),
(1, 7, '2025-06-05 12:20:20'),
(1, 8, '2025-06-05 12:20:20'),
(1, 9, '2025-06-05 12:20:20'),
(1, 10, '2025-06-05 12:20:20'),
(1, 11, '2025-06-05 12:20:20'),
(1, 12, '2025-06-05 12:20:20'),
(1, 13, '2025-06-05 12:20:20'),
(1, 14, '2025-06-05 12:20:20'),
(1, 15, '2025-06-05 12:20:20'),
(1, 18, '2025-06-05 12:20:20'),
(1, 20, '2025-06-05 12:20:20'),
(1, 22, '2025-06-05 12:20:20'),
(1, 23, '2025-06-05 12:20:20'),
(1, 24, '2025-06-05 12:20:20'),
(1, 25, '2025-06-05 12:20:20'),
(1, 26, '2025-06-05 12:20:20'),
(1, 27, '2025-06-05 12:20:20'),
(1, 28, '2025-06-05 12:20:20'),
(1, 29, '2025-06-05 12:20:20'),
(1, 30, '2025-06-05 12:20:20'),
(1, 31, '2025-06-05 12:20:20'),
(1, 32, '2025-06-05 12:20:20'),
(1, 33, '2025-06-05 12:20:20'),
(1, 34, '2025-06-05 12:20:20'),
(1, 35, '2025-06-05 12:20:20'),
(1, 36, '2025-06-05 12:20:20'),
(1, 37, '2025-06-05 12:20:20'),
(1, 38, '2025-06-05 12:20:20'),
(1, 39, '2025-06-05 12:20:20'),
(1, 40, '2025-06-05 12:20:20'),
(1, 41, '2025-06-05 12:20:20'),
(1, 42, '2025-06-05 12:20:20'),
(1, 43, '2025-06-05 12:20:20'),
(1, 44, '2025-06-05 12:20:20'),
(1, 45, '2025-06-05 12:20:20'),
(1, 46, '2025-06-05 12:20:20'),
(1, 47, '2025-06-05 12:20:20'),
(1, 48, '2025-06-05 12:20:20'),
(2, 10, '2025-04-26 17:59:01'),
(2, 32, '2025-04-26 17:59:01'),
(3, 6, '2025-04-09 23:31:16'),
(3, 13, '2025-04-09 23:31:16'),
(3, 14, '2025-04-09 23:31:16'),
(3, 15, '2025-04-09 23:31:16'),
(3, 18, '2025-04-09 23:31:16'),
(3, 20, '2025-04-09 23:31:16'),
(4, 32, '2025-05-27 22:56:38'),
(4, 36, '2025-05-27 22:56:38'),
(4, 37, '2025-05-27 22:56:38'),
(4, 38, '2025-05-27 22:56:38'),
(4, 39, '2025-05-27 22:56:38'),
(5, 32, '2025-04-23 11:45:44'),
(5, 36, '2025-04-23 11:45:44'),
(5, 37, '2025-04-23 11:45:44'),
(5, 39, '2025-04-23 11:45:44'),
(6, 32, '2025-04-23 11:44:42'),
(6, 36, '2025-04-23 11:44:42'),
(6, 37, '2025-04-23 11:44:42'),
(6, 39, '2025-04-23 11:44:42'),
(7, 32, '2025-04-23 11:45:05'),
(7, 36, '2025-04-23 11:45:05'),
(7, 37, '2025-04-23 11:45:05'),
(7, 39, '2025-04-23 11:45:05'),
(8, 32, '2025-04-23 11:45:20'),
(8, 36, '2025-04-23 11:45:20'),
(8, 37, '2025-04-23 11:45:20'),
(8, 39, '2025-04-23 11:45:20'),
(9, 7, '2025-05-28 00:23:34'),
(9, 32, '2025-05-28 00:23:34'),
(9, 36, '2025-05-28 00:23:34'),
(9, 37, '2025-05-28 00:23:34'),
(9, 38, '2025-05-28 00:23:34'),
(9, 39, '2025-05-28 00:23:34'),
(10, 32, '2025-04-23 11:45:58'),
(10, 36, '2025-04-23 11:45:58'),
(10, 37, '2025-04-23 11:45:58'),
(10, 39, '2025-04-23 11:45:58'),
(11, 32, '2025-04-23 11:46:17'),
(11, 36, '2025-04-23 11:46:17'),
(11, 37, '2025-04-23 11:46:17'),
(11, 39, '2025-04-23 11:46:17'),
(13, 32, '2025-04-23 11:46:40'),
(13, 36, '2025-04-23 11:46:40'),
(13, 37, '2025-04-23 11:46:40'),
(13, 39, '2025-04-23 11:46:40'),
(14, 32, '2025-05-27 22:55:33'),
(14, 36, '2025-05-27 22:55:33'),
(14, 37, '2025-05-27 22:55:33'),
(14, 39, '2025-05-27 22:55:33'),
(15, 32, '2025-05-20 04:04:31'),
(15, 36, '2025-05-20 04:04:31'),
(15, 37, '2025-05-20 04:04:31'),
(15, 39, '2025-05-20 04:04:31'),
(15, 46, '2025-05-20 04:04:31'),
(16, 32, '2025-04-27 03:00:36'),
(16, 36, '2025-04-27 03:00:36'),
(16, 37, '2025-04-27 03:00:36'),
(16, 39, '2025-04-27 03:00:36'),
(17, 5, '2025-06-05 21:06:16'),
(17, 31, '2025-06-05 21:06:16'),
(17, 45, '2025-06-05 21:06:16'),
(18, 10, '2025-05-05 23:33:38'),
(19, 3, '2025-05-27 03:07:16'),
(19, 4, '2025-05-27 03:07:16'),
(19, 5, '2025-05-27 03:07:16'),
(19, 6, '2025-05-27 03:07:16'),
(19, 7, '2025-05-27 03:07:16'),
(19, 8, '2025-05-27 03:07:16'),
(19, 10, '2025-05-27 03:07:16'),
(19, 12, '2025-05-27 03:07:16'),
(19, 13, '2025-05-27 03:07:16'),
(19, 14, '2025-05-27 03:07:16'),
(19, 15, '2025-05-27 03:07:16'),
(19, 18, '2025-05-27 03:07:16'),
(19, 20, '2025-05-27 03:07:16'),
(19, 24, '2025-05-27 03:07:16'),
(19, 25, '2025-05-27 03:07:16'),
(19, 26, '2025-05-27 03:07:16'),
(19, 27, '2025-05-27 03:07:16'),
(19, 28, '2025-05-27 03:07:16'),
(19, 32, '2025-05-27 03:07:16'),
(19, 35, '2025-05-27 03:07:16'),
(19, 36, '2025-05-27 03:07:16'),
(19, 37, '2025-05-27 03:07:16'),
(19, 38, '2025-05-27 03:07:16'),
(19, 39, '2025-05-27 03:07:16'),
(19, 43, '2025-05-27 03:07:16'),
(19, 45, '2025-05-27 03:07:16'),
(19, 46, '2025-05-27 03:07:16'),
(20, 3, '2025-06-05 12:20:42'),
(20, 4, '2025-06-05 12:20:42'),
(20, 5, '2025-06-05 12:20:42'),
(20, 6, '2025-06-05 12:20:42'),
(20, 7, '2025-06-05 12:20:42'),
(20, 8, '2025-06-05 12:20:42'),
(20, 9, '2025-06-05 12:20:42'),
(20, 10, '2025-06-05 12:20:42'),
(20, 11, '2025-06-05 12:20:42'),
(20, 12, '2025-06-05 12:20:42'),
(20, 13, '2025-06-05 12:20:42'),
(20, 14, '2025-06-05 12:20:42'),
(20, 15, '2025-06-05 12:20:42'),
(20, 18, '2025-06-05 12:20:42'),
(20, 20, '2025-06-05 12:20:42'),
(20, 22, '2025-06-05 12:20:42'),
(20, 23, '2025-06-05 12:20:42'),
(20, 24, '2025-06-05 12:20:42'),
(20, 25, '2025-06-05 12:20:42'),
(20, 26, '2025-06-05 12:20:42'),
(20, 27, '2025-06-05 12:20:42'),
(20, 28, '2025-06-05 12:20:42'),
(20, 29, '2025-06-05 12:20:42'),
(20, 30, '2025-06-05 12:20:42'),
(20, 31, '2025-06-05 12:20:42'),
(20, 32, '2025-06-05 12:20:42'),
(20, 33, '2025-06-05 12:20:42'),
(20, 34, '2025-06-05 12:20:42'),
(20, 35, '2025-06-05 12:20:42'),
(20, 36, '2025-06-05 12:20:42'),
(20, 37, '2025-06-05 12:20:42'),
(20, 38, '2025-06-05 12:20:42'),
(20, 39, '2025-06-05 12:20:42'),
(20, 40, '2025-06-05 12:20:42'),
(20, 42, '2025-06-05 12:20:42'),
(20, 43, '2025-06-05 12:20:42'),
(20, 44, '2025-06-05 12:20:42'),
(20, 45, '2025-06-05 12:20:42'),
(20, 46, '2025-06-05 12:20:42'),
(20, 47, '2025-06-05 12:20:42'),
(20, 48, '2025-06-05 12:20:42'),
(21, 5, '2025-06-27 21:50:36'),
(21, 10, '2025-06-27 21:50:36'),
(21, 31, '2025-06-27 21:50:36'),
(21, 45, '2025-06-27 21:50:36'),
(22, 32, '2025-06-27 22:12:52'),
(22, 36, '2025-06-27 22:12:52'),
(22, 37, '2025-06-27 22:12:52'),
(22, 39, '2025-06-27 22:12:52'),
(23, 32, '2025-06-27 22:13:29'),
(23, 36, '2025-06-27 22:13:29'),
(23, 37, '2025-06-27 22:13:29'),
(23, 39, '2025-06-27 22:13:29'),
(36, 32, '2025-07-05 21:44:46'),
(36, 36, '2025-07-05 21:44:46'),
(36, 37, '2025-07-05 21:44:46'),
(36, 39, '2025-07-05 21:44:46'),
(37, 32, '2025-07-04 01:56:21'),
(37, 36, '2025-07-04 01:56:21'),
(37, 37, '2025-07-04 01:56:21'),
(37, 39, '2025-07-04 01:56:21'),
(38, 32, '2025-07-04 01:56:07'),
(38, 36, '2025-07-04 01:56:07'),
(38, 37, '2025-07-04 01:56:07'),
(38, 39, '2025-07-04 01:56:07'),
(39, 32, '2025-07-04 01:55:28'),
(39, 36, '2025-07-04 01:55:28'),
(39, 37, '2025-07-04 01:55:28'),
(39, 39, '2025-07-04 01:55:28'),
(40, 32, '2025-07-04 01:56:39'),
(40, 36, '2025-07-04 01:56:39'),
(40, 37, '2025-07-04 01:56:39'),
(40, 39, '2025-07-04 01:56:39'),
(42, 32, '2025-07-04 01:55:54'),
(42, 36, '2025-07-04 01:55:54'),
(42, 37, '2025-07-04 01:55:54'),
(42, 39, '2025-07-04 01:55:54'),
(43, 32, '2025-07-04 15:40:39'),
(43, 36, '2025-07-04 15:40:39'),
(43, 37, '2025-07-04 15:40:39'),
(43, 39, '2025-07-04 15:40:39'),
(44, 32, '2025-07-04 01:56:55'),
(44, 36, '2025-07-04 01:56:55'),
(44, 37, '2025-07-04 01:56:55'),
(44, 39, '2025-07-04 01:56:55'),
(45, 32, '2025-07-04 16:47:24'),
(45, 36, '2025-07-04 16:47:24'),
(45, 37, '2025-07-04 16:47:24'),
(45, 39, '2025-07-04 16:47:24'),
(46, 3, '2025-07-04 19:55:12'),
(46, 5, '2025-07-04 19:55:12'),
(46, 6, '2025-07-04 19:55:12'),
(46, 7, '2025-07-04 19:55:12'),
(46, 9, '2025-07-04 19:55:12'),
(46, 10, '2025-07-04 19:55:12'),
(46, 12, '2025-07-04 19:55:12'),
(46, 13, '2025-07-04 19:55:12'),
(46, 14, '2025-07-04 19:55:12'),
(46, 15, '2025-07-04 19:55:12'),
(46, 18, '2025-07-04 19:55:12'),
(46, 20, '2025-07-04 19:55:12'),
(46, 22, '2025-07-04 19:55:12'),
(46, 24, '2025-07-04 19:55:12'),
(46, 25, '2025-07-04 19:55:12'),
(46, 26, '2025-07-04 19:55:12'),
(46, 27, '2025-07-04 19:55:12'),
(46, 28, '2025-07-04 19:55:12'),
(46, 30, '2025-07-04 19:55:12'),
(46, 31, '2025-07-04 19:55:12'),
(46, 32, '2025-07-04 19:55:12'),
(46, 35, '2025-07-04 19:55:12'),
(46, 36, '2025-07-04 19:55:12'),
(46, 37, '2025-07-04 19:55:12'),
(46, 38, '2025-07-04 19:55:12'),
(46, 39, '2025-07-04 19:55:12'),
(46, 43, '2025-07-04 19:55:12'),
(46, 45, '2025-07-04 19:55:12'),
(46, 46, '2025-07-04 19:55:12'),
(47, 3, '2025-07-04 17:32:10'),
(47, 5, '2025-07-04 17:32:10'),
(47, 6, '2025-07-04 17:32:10'),
(47, 7, '2025-07-04 17:32:10'),
(47, 13, '2025-07-04 17:32:10'),
(47, 14, '2025-07-04 17:32:10'),
(47, 15, '2025-07-04 17:32:10'),
(47, 25, '2025-07-04 17:32:10'),
(47, 30, '2025-07-04 17:32:10'),
(47, 31, '2025-07-04 17:32:10'),
(47, 32, '2025-07-04 17:32:10'),
(47, 34, '2025-07-04 17:32:10'),
(47, 36, '2025-07-04 17:32:10'),
(47, 37, '2025-07-04 17:32:10'),
(47, 39, '2025-07-04 17:32:10'),
(47, 43, '2025-07-04 17:32:10'),
(47, 45, '2025-07-04 17:32:10'),
(47, 46, '2025-07-04 17:32:10'),
(48, 32, '2025-07-05 21:09:20'),
(48, 36, '2025-07-05 21:09:20'),
(48, 37, '2025-07-05 21:09:20'),
(48, 39, '2025-07-05 21:09:20'),
(49, 32, '2025-07-05 21:09:39'),
(49, 36, '2025-07-05 21:09:39'),
(49, 37, '2025-07-05 21:09:39'),
(49, 39, '2025-07-05 21:09:39');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_historico_estoque`
--

CREATE TABLE `cafe_historico_estoque` (
  `id_historico` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `tipo_operacao` enum('entrada','saida') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `quantidade` int(11) NOT NULL,
  `quantidade_anterior` int(11) NOT NULL,
  `motivo` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `data_operacao` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_historico_saldo`
--

CREATE TABLE `cafe_historico_saldo` (
  `id_historico` int(11) NOT NULL,
  `id_pessoa` int(11) NOT NULL,
  `tipo_operacao` enum('credito','debito','custo cartao','dinheiro','bonus') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `saldo_anterior` decimal(10,2) NOT NULL,
  `saldo_novo` decimal(10,2) NOT NULL,
  `motivo` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `data_operacao` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_historico_transacoes_sistema`
--

CREATE TABLE `cafe_historico_transacoes_sistema` (
  `id_transacao` int(11) NOT NULL,
  `nome_usuario` varchar(255) DEFAULT NULL,
  `grupo_usuario` varchar(255) DEFAULT NULL,
  `tipo` varchar(255) DEFAULT NULL,
  `tipo_transacao` varchar(255) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `id_pessoa` int(11) DEFAULT NULL,
  `cartao` varchar(255) DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_itens_venda`
--

CREATE TABLE `cafe_itens_venda` (
  `id_item` int(10) NOT NULL,
  `id_venda` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `valor_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_permissoes`
--

CREATE TABLE `cafe_permissoes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `pagina` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cafe_permissoes`
--

INSERT INTO `cafe_permissoes` (`id`, `nome`, `pagina`, `created_at`) VALUES
(1, 'gerenciar_grupos', 'grupos.php', '2025-04-08 01:11:40'),
(2, 'gerenciar_permissoes', 'permissoes.php', '2025-04-08 01:11:40'),
(3, 'gerenciar_usuarios', 'usuarios.php', '2025-04-08 01:11:40'),
(4, 'gerenciar_pessoas', 'pessoas.php', '2025-04-08 01:11:40'),
(5, 'gerenciar_transacoes', 'transacoes_lista.php', '2025-04-08 01:11:40'),
(6, 'gerenciar_produtos', 'produtos_lista.php', '2025-04-08 01:11:40'),
(7, 'gerenciar_vendas', 'vendas.php', '2025-04-08 01:11:40'),
(8, 'visualizar_dashboard', 'dashboard_vendas.php', '2025-04-08 01:11:40'),
(9, 'visualizar_relatorios', 'relatorio_vendas.php', '2025-04-08 01:11:40'),
(10, 'gerenciar_cartoes', 'cartoes.php', '2025-04-08 01:11:40'),
(11, 'saldos_historico.php', 'saldos_historico.php', '2025-04-08 01:22:23'),
(12, 'gerenciar_categorias', 'categorias.php', '2025-04-08 04:46:48'),
(13, 'produtos_incluir', 'produtos_novo.php', '2025-04-09 11:07:11'),
(14, 'produtos_editar', 'produtos_editar.php', '2025-04-09 12:04:39'),
(15, 'produtos_estoque', 'produtos_estoque.php', '2025-04-09 12:04:39'),
(18, 'produtos_bloquear', 'produtos_bloquear.php', '2025-04-09 12:06:12'),
(20, 'produtos_excluir', 'produtos_excluir.php', '2025-04-09 12:06:28'),
(22, 'gerenciar_saldos', 'saldos.php', '2025-04-10 00:25:40'),
(23, 'gerenciar_saldos_historicos', 'saldos_historicos.php', '2025-04-10 01:23:33'),
(24, 'gerenciar_relatorios', 'relatorios.php', '2025-04-10 01:25:12'),
(25, 'vendas_incluir', 'vendas_novo.php', '2025-04-10 01:29:51'),
(26, 'pessoas_incluir', 'pessoas_novo.php', '2025-04-10 03:52:25'),
(27, 'pessoas_editar', 'pessoas_editar.php', '2025-04-10 03:52:34'),
(28, 'pessoas_excluir', 'pessoas_excluir.php', '2025-04-10 03:53:04'),
(29, 'pessoas_saldos', 'pessoas_saldos.php', '2025-04-10 03:53:41'),
(30, 'saldos_incluir', 'saldo_incluir.php', '2025-04-17 15:28:01'),
(31, 'saldos_mobile', 'saldos_mobile.php', '2025-04-19 13:56:16'),
(32, 'buscar_participante', 'buscar_participante.php', '2025-04-19 13:56:34'),
(33, 'operacao_saldo', 'operacao_saldo.php', '2025-04-19 13:56:58'),
(34, 'operacao_saldo', 'operacao_saldo.php', '2025-04-19 14:40:13'),
(35, 'vendas_detalhes', 'vendas_detalhes.php', '2025-04-20 15:09:41'),
(36, 'finalizar_venda', 'finalizar_venda.php', '2025-04-20 15:10:08'),
(37, 'vendas_mobile', 'vendas_mobile.php', '2025-04-23 00:58:33'),
(38, 'gerencia_vendas_mobile', 'vendas_mobile.php', '2025-04-23 01:32:51'),
(39, 'gerenciar_vendas_mobile', 'vendas_mobile.php', '2025-04-23 01:33:54'),
(40, 'saldos_historico_qr.php', 'saldos_historico_qr.php', '2025-05-03 01:06:47'),
(41, 'gerenciar_geracao_cartoes', 'gerenciar_cartoes.php', '2025-05-05 23:36:18'),
(42, 'gerenciar_dashboard', 'gerenciar_dashboard.php', '2025-05-06 00:03:39'),
(43, 'estornar_vendas', 'estornar_vendas.php', '2025-05-12 01:07:13'),
(44, 'importarmd5.php', 'importarmd5.php', '2025-05-13 01:06:37'),
(45, 'consulta_saldo', 'consulta_saldo.php', '2025-05-20 01:19:44'),
(46, 'cadastrar_produto', 'produto.php', '2025-05-20 04:03:57'),
(47, 'fechamento_caixa.php', 'fechamento_caixa.php', '2025-06-05 03:59:11'),
(48, 'relatorio_categoria', 'relatorio_categorias.php', '2025-06-05 12:19:34');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_pessoas`
--

CREATE TABLE `cafe_pessoas` (
  `id_pessoa` int(10) NOT NULL,
  `nome` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_produtos`
--

CREATE TABLE `cafe_produtos` (
  `id` int(11) NOT NULL,
  `nome_produto` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `descricao` text CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `estoque` int(11) NOT NULL DEFAULT 0,
  `categoria_id` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bloqueado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_saldos_cartao`
--

CREATE TABLE `cafe_saldos_cartao` (
  `id_saldo` int(11) NOT NULL,
  `id_pessoa` int(11) NOT NULL,
  `saldo` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_usuarios`
--

CREATE TABLE `cafe_usuarios` (
  `id` int(10) NOT NULL,
  `nome` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `grupo_id` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cafe_usuarios`
--

INSERT INTO `cafe_usuarios` (`id`, `nome`, `email`, `senha`, `grupo_id`, `ativo`, `created_at`) VALUES
(1, 'Administrador', 'admin@festa.com', '$2y$10$5e9d8Gu9vPlprnkp1I.BR.qwMWU4bEnL1o.EtHtHZfGQODXbOw5Jm', 1, 1, '2025-04-08 01:12:14'),
(12, 'admin_paroquia', 'admin_paroquia@festa.com', '$2y$10$/I.vCrXI/gzbBCF1cSjFFuGclCeSuQiX3JSzkA72qgtTynz/.U4Ji', 20, 1, '2025-06-02 13:55:44'),
(18, 'Denys', 'denys@festa.com', '$2y$10$A.1.s/hYNL7oNoMVWjWTz.bCHNVmV8D788sbZ4g99.Mym0YyhKG4C', 36, 1, '2025-06-05 21:05:17'),
(27, 'Ana Beatriz ConceiÃ§Ã£o Dias', 'anabeatrizconceicaoa13@gmail.com', '$2y$10$aMrA5C3qX5UsNm9E9FHcmOPWRYqdSk0B63Kv1eqi2m.R7GQaxc2ui', 18, 1, '2025-07-04 19:59:56'),
(28, 'Ã‰rika de Jesus ConceiÃ§Ã£o', 'erikadejesuscnc08@gmail.com', '$2y$10$sVT37IEH4tdsjm/kpAQUx.09i8Lc5y2j.p1F4xv0weub/oXbt1IBS', 46, 1, '2025-07-04 20:02:26'),
(30, 'James Willi', 'james.jwsb@hotmail.com', '$2y$10$IHYZhPTw1GZ0c7BEnuAWbO5moIGraWIjd36It.W3.HHHRQq7mHsYK', 20, 1, '2025-07-04 20:03:31'),
(31, 'Stephany Viana', 'stefhanyviana@gmail.com', '$2y$10$hTZdZ1JfD8VKaVH7DY1NR.rX5sREmH9I.CuOVm1oUU2kz8IjjnDsK', 43, 1, '2025-07-04 20:04:06'),
(32, 'Alex Pereira', 'alexpereiralx1@gmail.com', '$2y$10$NqLhorJNWYm6MGuiaxKRr.xygwqjrYQP.ScRNsGM1UGurX2Mpjg0m', 18, 1, '2025-07-04 21:23:02'),
(33, 'Aline Costa Pereira', 'costa.aline57@gmail.com', '$2y$10$q6pdqNd8.7QUW1qNjC5/3O6BzXSEQ/HVHKunxBtHcOLavyH8KzQe2', 17, 1, '2025-07-04 21:25:25'),
(34, 'Eldivar Coelho', 'eldivar@gmail.com', '$2y$10$2TwT3Rlmd87RfKyKfuUt5eTEuxX7ENgh5vgPWaTkVXWY.fpL6vGUS', 46, 1, '2025-07-04 21:25:52'),
(35, 'Leticia de Jesus ConceiÃ§Ã£o', 'ld440241@gmail.com', '$2y$10$Iurz2KWEEkXYpsqKH31ZMey2e84krrRUjC97GF3Afr0pTBqyNqiAi', 40, 1, '2025-07-05 20:35:42'),
(36, 'Arthur Amorim de Sousa', 'artamoorim@gmail.com', '$2y$10$UDX4tidAfm6fTRsi2.vrQ.l93enKpKn37d2AVltFnzPzQwC2PEmxS', 42, 1, '2025-07-05 20:36:25'),
(37, 'Pedro Henrique Brito Freitas', 'pedrohenriquebritofreitaspedro@gmail.com', '$2y$10$zlU3W53lYHeWeyBvsOP2CuXDfftVYcg1VV0Ll/WVV8ENhZY5Pc2pq', 38, 1, '2025-07-05 20:37:01'),
(38, 'Paulo Henrique Lima de Sousa', 'ph.paulolima10@gmail.com', '$2y$10$HNO7OZ1.I6TIFNJnIKsitO1htChATIrcujqB8s0.YfiWPYpmHB8yG', 37, 1, '2025-07-05 20:37:35'),
(39, 'Rodrigo Santos da Silva', 'digobifinho2@gmail.com', '$2y$10$MDgXYKFdpyMId87S.EK.POeHC9FnBuejYp/VsgiG35Is3YMJRyQK2', 37, 1, '2025-07-05 20:38:05'),
(40, 'Henzo William Matos Prata', 'henzowilliamp@gmail.com', '$2y$10$0eEH0xBhYew4P96Mofe3DOQg89qtDP4GfqUIK4NSm.f7pviJnf4fu', 36, 1, '2025-07-05 20:38:41'),
(41, 'Ingrid Rodrigues Silva ', 'ingrid2020ff@gmail.com', '$2y$10$aIDHT003roUp61L4B2ekMO0ncLnrd4f2xKGVR3OITjQkaGDZ7Ntci', 36, 1, '2025-07-05 20:39:14'),
(42, 'Gabrielle Araujo Carvalho', 'gabrielleacarvalho14@gmail.com', '$2y$10$91u5IaymxLUZeMIZV9thlOHjLM5zieA.nLTnfs.KcWIFF54R1SEga', 43, 1, '2025-07-05 20:39:48'),
(43, 'Ana JÃºlia Santos da Silva', 'anaajuus2@gmail.com', '$2y$10$.jwWil5sg.CcgnxmnSMtP.Yy65fdiGNRthqtNmnH.jZV94ddZlS5e', 45, 1, '2025-07-05 20:40:23'),
(44, 'Victoria Rayane Santos da Silva ', 'vivitoriagamora@gmail.com', '$2y$10$nK8HFvAYLa0FHci2VwUNi..XhUYGQmFxTjEsveH9w9.tOAi1xwMM2', 43, 1, '2025-07-05 20:41:20'),
(45, 'Miguel da Silva', 'Miguelalexandredasilva15@gmai.com', '$2y$10$7kH9gozwgpMbfeye2jEQluvpvsaiXJCaqzLpNkAoDPWC.ifOAxRpO', 42, 1, '2025-07-05 20:43:12'),
(46, 'Stefhany Aparecida Viana Teodoro', 'stefhanyviana1@gmail.com', '$2y$10$O06VxYjoAtZ02oSkEoUi8OyDHBw3lxvXKWTPzKkHyHudu0bEFpRWS', 49, 1, '2025-07-05 20:47:04'),
(47, 'Julia Ferraz dos Santos', 'juhferrazs234@gmail.com', '$2y$10$1CqtfshClsxXkSvCA9EiiOHCiDfCAFiUsZK1HebAeYmhopx6l8Drm', 39, 1, '2025-07-05 22:13:26'),
(48, 'VitÃ³ria FranÃ§a ', 'vitoriaconceicao@gmail.com', '$2y$10$.s74tyWgDo0YxYlO2p5bjOAQXikRTNERQEfzzrPFHo6phxlIzRPrW', 17, 1, '2025-07-06 02:15:25'),
(49, 'Matheus Silva ', 'matheuscostasilva120899@gmail.com', '$2y$10$GQ4kWC4mr9fAtEl8xujRnOVk1Spdjm5H.4TIPR6DVMk9R8.8N4v1y', 17, 1, '2025-07-13 01:45:53'),
(50, 'Rute Carvalho ', 'rutineidearaujocarvalho@gmail.com', '$2y$10$aa9EY70p/H3RYCZm5BNVge8CSboQ1MlznFdyCdEgoiHIIX1dVCA1W', 43, 1, '2025-07-13 23:47:30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cafe_vendas`
--

CREATE TABLE `cafe_vendas` (
  `id_venda` int(10) NOT NULL,
  `id_pessoa` int(11) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `estornada` tinyint(1) DEFAULT NULL,
  `data_venda` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cafe_cartoes`
--
ALTER TABLE `cafe_cartoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_cartoes_codigo` (`codigo`),
  ADD KEY `fk_cartoes_pessoa` (`id_pessoa`);

--
-- Índices de tabela `cafe_categorias`
--
ALTER TABLE `cafe_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `cafe_grupos`
--
ALTER TABLE `cafe_grupos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `cafe_grupos_permissoes`
--
ALTER TABLE `cafe_grupos_permissoes`
  ADD PRIMARY KEY (`grupo_id`,`permissao_id`),
  ADD KEY `permissao_id` (`permissao_id`);

--
-- Índices de tabela `cafe_historico_estoque`
--
ALTER TABLE `cafe_historico_estoque`
  ADD PRIMARY KEY (`id_historico`),
  ADD KEY `fk_historico_produto` (`id_produto`);

--
-- Índices de tabela `cafe_historico_saldo`
--
ALTER TABLE `cafe_historico_saldo`
  ADD PRIMARY KEY (`id_historico`),
  ADD KEY `idx_historico_pessoa` (`id_pessoa`);

--
-- Índices de tabela `cafe_historico_transacoes_sistema`
--
ALTER TABLE `cafe_historico_transacoes_sistema`
  ADD PRIMARY KEY (`id_transacao`);

--
-- Índices de tabela `cafe_itens_venda`
--
ALTER TABLE `cafe_itens_venda`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `idx_itens_venda` (`id_venda`),
  ADD KEY `idx_itens_produto` (`id_produto`);

--
-- Índices de tabela `cafe_permissoes`
--
ALTER TABLE `cafe_permissoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Índices de tabela `cafe_pessoas`
--
ALTER TABLE `cafe_pessoas`
  ADD PRIMARY KEY (`id_pessoa`),
  ADD UNIQUE KEY `uk_pessoas_cpf` (`cpf`);

--
-- Índices de tabela `cafe_produtos`
--
ALTER TABLE `cafe_produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices de tabela `cafe_saldos_cartao`
--
ALTER TABLE `cafe_saldos_cartao`
  ADD PRIMARY KEY (`id_saldo`),
  ADD KEY `fk_saldo_pessoa` (`id_pessoa`);

--
-- Índices de tabela `cafe_usuarios`
--
ALTER TABLE `cafe_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `grupo_id` (`grupo_id`);

--
-- Índices de tabela `cafe_vendas`
--
ALTER TABLE `cafe_vendas`
  ADD PRIMARY KEY (`id_venda`),
  ADD KEY `fk_vendas_pessoa` (`id_pessoa`),
  ADD KEY `idx_vendas_data` (`data_venda`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cafe_cartoes`
--
ALTER TABLE `cafe_cartoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cafe_categorias`
--
ALTER TABLE `cafe_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de tabela `cafe_grupos`
--
ALTER TABLE `cafe_grupos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de tabela `cafe_historico_estoque`
--
ALTER TABLE `cafe_historico_estoque`
  MODIFY `id_historico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `cafe_historico_saldo`
--
ALTER TABLE `cafe_historico_saldo`
  MODIFY `id_historico` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cafe_historico_transacoes_sistema`
--
ALTER TABLE `cafe_historico_transacoes_sistema`
  MODIFY `id_transacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cafe_itens_venda`
--
ALTER TABLE `cafe_itens_venda`
  MODIFY `id_item` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cafe_permissoes`
--
ALTER TABLE `cafe_permissoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de tabela `cafe_pessoas`
--
ALTER TABLE `cafe_pessoas`
  MODIFY `id_pessoa` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cafe_produtos`
--
ALTER TABLE `cafe_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cafe_saldos_cartao`
--
ALTER TABLE `cafe_saldos_cartao`
  MODIFY `id_saldo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cafe_usuarios`
--
ALTER TABLE `cafe_usuarios`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de tabela `cafe_vendas`
--
ALTER TABLE `cafe_vendas`
  MODIFY `id_venda` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
