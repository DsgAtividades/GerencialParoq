CREATE TABLE IF NOT EXISTS `obras_obras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) NOT NULL,
  `responsavel_tecnico` varchar(100) NOT NULL,
  `status` enum('Em Andamento','Concluída','Pendente','Cancelada') NOT NULL DEFAULT 'Pendente',
  `valor_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_adiantado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `data_ordem_servico` date DEFAULT NULL,
  `data_conclusao` date DEFAULT NULL,
  `previsao_entrega` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
