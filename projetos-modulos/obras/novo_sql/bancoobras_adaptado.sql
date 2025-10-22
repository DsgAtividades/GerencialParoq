-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 12-Set-2025 às 19:24
-- Versão do servidor: 5.7.40
-- versão do PHP: 8.0.26
--
-- ADAPTADO: Todas as tabelas agora têm prefixo 'obras_' para compatibilidade com banco único

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Desabilitar verificação de chaves estrangeiras temporariamente
SET FOREIGN_KEY_CHECKS = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `gerencial_paroquia` (adaptado para usar prefixos)
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `obras_obras`
--

DROP TABLE IF EXISTS `obras_obras`;
CREATE TABLE IF NOT EXISTS `obras_obras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsavel_tecnico` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Em Andamento','Concluída','Pendente','Cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendente',
  `total` decimal(10,2) DEFAULT NULL,
  `valor_adiantado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `data_ordem_servico` date DEFAULT NULL,
  `data_conclusao` date DEFAULT NULL,
  `previsao_entrega` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `obras_servicos`
--

DROP TABLE IF EXISTS `obras_servicos`;
CREATE TABLE IF NOT EXISTS `obras_servicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `obras_servicos`
--

INSERT INTO `obras_servicos` (`id`, `descricao`, `responsavel`, `responsavel_autorizacao`, `adiantamento_1`, `data_adiant_1`, `adiantamento_2`, `data_adiant_2`, `adiantamento_3`, `data_adiant_3`, `valor_antecipado`, `total`, `falta_pagar`, `status`, `previsao_entrega`, `data_ordem_servico`, `data_previsao_entrega`, `data_entrega_final`, `observacoes`, `created_at`, `updated_at`) VALUES
(9, 'Forro Sala 1,  Sala 2, Corredor escada e Divisória do porão', 'Dinga', 'Pe Regis', '3000.00', '2024-10-01', '2000.00', '2024-10-11', '6900.00', '2024-10-30', '11900.00', '11900.00', NULL, 'Concluído', '2024-10-30', '2024-09-26', NULL, '2024-10-30', 'FINALIZADO E PAGO\r\npAGAMENTO FINAL REALIZADO EM 29/10/2025 NO VALOR DE R$ 6.900,00', '2025-04-19 14:30:31', '2025-04-21 02:51:14'),
(10, 'Arquib. lateral + Duto da cozinha + Estrutura metalica escada', 'Valmir', 'Pe Regis', NULL, NULL, NULL, NULL, '7500.00', '2024-11-27', '7500.00', '7500.00', NULL, 'Concluído', '2024-11-27', '2024-10-25', NULL, '2024-11-27', 'FINALIZADO E PAGO\r\nENTREGA REALIZADA EM 27/11/2024 E PAGO', '2025-04-19 14:30:31', '2025-04-21 02:52:23'),
(11, 'Construção Quadra', 'Valmir', 'Pe Regis', NULL, NULL, NULL, NULL, '32000.00', '2024-12-27', '32000.00', '32000.00', NULL, 'Concluído', '2024-12-27', '2024-12-03', NULL, '2024-12-27', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:50:10'),
(12, 'Pintura corredores, secretaria, bazar + rufo quadra', 'Adalberto', '', '2000.00', '2025-03-07', NULL, NULL, '2700.00', '2025-03-20', '4700.00', '4700.00', NULL, 'Concluído', '2025-03-06', '2025-03-06', NULL, '2025-03-20', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:43:22'),
(13, 'Porta e parede São Pedro Café - Salão', 'Dinga', 'Pe Regis', NULL, NULL, NULL, NULL, '2350.00', '2025-04-01', '2350.00', '2350.00', NULL, 'Concluído', '2025-04-01', '2025-03-25', NULL, '2025-04-01', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:49:02'),
(14, 'Construção São Pedro Café', 'Fabiano', 'Rener e Flavia', NULL, NULL, NULL, NULL, '2100.00', '2025-04-01', '2100.00', '2100.00', NULL, 'Concluído', '2025-03-29', '2025-03-25', NULL, '2025-04-01', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:54:29'),
(15, 'Parede e Porta de correr no Corredor cozinha + Duto fiação Café', 'Dinga', 'Rener e Flavia', NULL, NULL, NULL, NULL, '2500.00', '2025-04-02', '2500.00', '2500.00', NULL, 'Concluído', '2025-04-02', '2025-03-26', NULL, '2025-04-02', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 03:08:51'),
(16, 'Armarios (Catequese, Musicos)', 'Edson', 'Pe Regis', '3000.00', '2025-03-14', '2000.00', '2025-03-27', NULL, NULL, '5000.00', '5000.00', NULL, 'Concluído', '2025-04-01', '2025-04-14', NULL, '2025-04-01', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:42:19'),
(17, 'Instalação de Exaustor + Redução duto + Manutenção motor', 'Valmir', 'Pe Regis', NULL, NULL, NULL, NULL, '4800.00', '2025-04-22', '4800.00', '4800.00', NULL, 'Concluído', '2025-04-22', '2025-03-20', NULL, '2025-04-22', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:53:28'),
(18, 'Armarios (Coroinhas)', 'Edson', 'Pe Regis', '2000.00', '2025-04-10', NULL, NULL, '1000.00', '2025-03-27', '3000.00', '3000.00', NULL, 'Concluído', '2025-03-27', '2025-03-10', NULL, '2025-03-27', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 03:09:34'),
(19, 'Fechar dutos do ar condicionado + Teto bazar + placas das maq. ar condicionado', 'Dinga', 'Pe Regis', NULL, NULL, NULL, NULL, '1700.00', '2025-04-09', '1700.00', '1700.00', NULL, 'Concluído', '2025-04-01', '2025-04-01', NULL, '2025-04-09', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:47:03'),
(20, 'Instalação de 4 ar condicionado', 'Wagner', 'Pe Regis', '3400.00', NULL, NULL, NULL, '3400.00', '2025-04-10', '6800.00', '6800.00', NULL, 'Concluído', '2025-04-10', '2025-03-27', NULL, '2025-04-10', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:48:07'),
(21, 'Instalação de suporte da TV Tenda', 'Valmir', 'Pe Regis', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.01', NULL, 'Concluído', '2025-04-17', '2025-04-01', NULL, '2025-04-19', 'Concluido - Brinde do Valmir para a paróquia', '2025-04-19 14:30:32', '2025-04-21 01:04:55'),
(22, 'Construção (Obras Salão)', 'Valmir', 'Rener e Flavia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7500.00', NULL, 'Em Andamento', '2025-04-26', '2025-03-30', NULL, '2025-04-05', 'FINALIZADO A PAGAR \r\nPAGAMENTO A SER REALIZADO NO DIA 26/04/2025', '2025-04-19 14:30:32', '2025-04-21 13:17:54'),
(23, 'Elétrica do Salão e café', 'Gervacio', 'Pe Regis', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1850.00', NULL, 'Concluído', '2025-05-20', '2025-05-20', NULL, '2025-04-20', 'FINALIZADO A PAGAR', '2025-04-19 14:30:32', '2025-05-01 01:50:15'),
(24, 'Revestimento Corredor + Pintura corredor + Pintura Pastoral Social', 'Valmir', 'Rener e Flavia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '4000.00', NULL, 'Em Andamento', '2025-05-05', '2025-04-01', NULL, '2025-04-13', 'FINALIZADO A PAGAR', '2025-04-19 14:30:32', '2025-04-21 13:20:34'),
(25, 'Construção reforma quadra 1', 'Valmir', 'Pe Regis', '10000.00', '2025-04-01', NULL, NULL, NULL, NULL, '10000.00', '16200.00', NULL, 'Em Andamento', '2025-05-20', '2025-03-04', NULL, '2025-04-26', 'EM ANDAMENTO', '2025-04-19 14:30:32', '2025-04-21 13:38:41'),
(26, 'Instalação porta armario na parte baixa dos músicos', 'Edson', 'Pe Regis', NULL, NULL, NULL, NULL, '2000.00', '2025-04-16', '2000.00', '2000.00', NULL, 'Concluído', '2025-04-08', '2025-04-08', NULL, '2025-04-16', 'FINALIZADO E PAGO', '2025-04-19 14:30:32', '2025-04-21 02:46:14'),
(27, 'Troca das portas do Salão São Pedro café', 'TBD', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', NULL, 'Pendente', NULL, NULL, NULL, NULL, 'A INICIAR', '2025-04-19 14:30:32', '2025-05-01 03:25:49'),
(28, 'Instalação de depósito de cadeiras e mesas (container)', 'TBD', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '0.00', NULL, 'Pendente', NULL, NULL, NULL, NULL, 'A INICIAR', '2025-04-19 14:30:32', '2025-04-21 13:04:57'),
(29, 'Instalação de Telão na entrada do Salão', 'TBD', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '0.00', NULL, 'Pendente', NULL, NULL, NULL, NULL, 'A INICIAR', '2025-04-19 14:30:32', '2025-04-21 13:04:59'),
(31, 'Instalação de cameras de transmissoes para a PASCOM', 'Denys', 'Denys', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3000.00', 'Pendente', NULL, NULL, '2025-09-05', NULL, '', '2025-04-22 15:34:36', '2025-04-22 15:37:59');

-- --------------------------------------------------------

--
-- Estrutura da tabela `obras_servicos_arquivos`
--

DROP TABLE IF EXISTS `obras_servicos_arquivos`;
CREATE TABLE IF NOT EXISTS `obras_servicos_arquivos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `servico_id` int(11) DEFAULT NULL,
  `tipo` enum('comprovante_pagamento','nota_fiscal','ordem_servico') COLLATE latin1_general_ci DEFAULT NULL,
  `nome_arquivo` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `caminho_arquivo` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `data_upload` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `servico_id` (`servico_id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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

DROP TABLE IF EXISTS `obras_system_users`;
CREATE TABLE IF NOT EXISTS `obras_system_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_completo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_acesso` enum('Administrador','Operador') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `obras_system_users`
--

INSERT INTO `obras_system_users` (`id`, `username`, `password`, `nome_completo`, `tipo_acesso`, `ativo`, `created_at`) VALUES
(1, 'admin', '$2y$10$2a/3.Kg8yWLyV9sUyxc5bum5K45uSFepKSoEjumxpBQrE1fsNq9Py', 'Administrador', 'Administrador', 1, '2025-04-07 02:56:49');

-- --------------------------------------------------------

--
-- Estrutura da tabela `obras_users`
--

DROP TABLE IF EXISTS `obras_users`;
CREATE TABLE IF NOT EXISTS `obras_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `obras_servicos_arquivos`
--
ALTER TABLE `obras_servicos_arquivos`
  ADD CONSTRAINT `obras_servicos_arquivos_ibfk_1` FOREIGN KEY (`servico_id`) REFERENCES `obras_servicos` (`id`) ON DELETE CASCADE;

-- Reabilitar verificação de chaves estrangeiras
SET FOREIGN_KEY_CHECKS = 1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
