-- Tabelas do módulo Café Paroquial
-- Banco de dados: gerencialparoq

-- Tabela de produtos do café
CREATE TABLE IF NOT EXISTS `cafe_produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `descricao` text,
  `categoria` varchar(100) DEFAULT NULL,
  `preco_venda` decimal(10,2) NOT NULL DEFAULT '0.00',
  `estoque_atual` int(11) DEFAULT '0',
  `estoque_minimo` int(11) DEFAULT '0',
  `unidade_medida` enum('unidade','kg','litro','pacote') DEFAULT 'unidade',
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_produtos_ativo` (`ativo`),
  KEY `idx_produtos_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de vendas
CREATE TABLE IF NOT EXISTS `cafe_vendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_venda` varchar(20) NOT NULL,
  `data_venda` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vendedor_id` int(11) DEFAULT NULL,
  `cliente_nome` varchar(200) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `desconto` decimal(10,2) DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `forma_pagamento` enum('dinheiro','pix','cartao_debito','cartao_credito') NOT NULL DEFAULT 'dinheiro',
  `status` enum('pendente','finalizada','cancelada') DEFAULT 'finalizada',
  `observacoes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_venda` (`numero_venda`),
  KEY `idx_vendas_data` (`data_venda`),
  KEY `idx_vendas_vendedor` (`vendedor_id`),
  KEY `idx_vendas_status` (`status`),
  CONSTRAINT `cafe_vendas_ibfk_1` FOREIGN KEY (`vendedor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de itens das vendas
CREATE TABLE IF NOT EXISTS `cafe_vendas_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_venda` (`venda_id`),
  KEY `idx_produto` (`produto_id`),
  CONSTRAINT `cafe_vendas_itens_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `cafe_vendas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cafe_vendas_itens_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `cafe_produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de movimentações de estoque
CREATE TABLE IF NOT EXISTS `cafe_estoque_movimentacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) NOT NULL,
  `tipo` enum('entrada','saida','ajuste') NOT NULL,
  `quantidade` int(11) NOT NULL,
  `motivo` varchar(200) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `venda_id` int(11) DEFAULT NULL,
  `data_movimentacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_estoque_produto` (`produto_id`),
  KEY `idx_estoque_usuario` (`usuario_id`),
  KEY `idx_estoque_venda` (`venda_id`),
  CONSTRAINT `cafe_estoque_movimentacoes_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `cafe_produtos` (`id`),
  CONSTRAINT `cafe_estoque_movimentacoes_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`),
  CONSTRAINT `cafe_estoque_movimentacoes_ibfk_3` FOREIGN KEY (`venda_id`) REFERENCES `cafe_vendas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir alguns produtos iniciais de exemplo
INSERT INTO `cafe_produtos` (`codigo`, `nome`, `descricao`, `categoria`, `preco_venda`, `estoque_atual`, `estoque_minimo`, `unidade_medida`) VALUES
('CAFE001', 'Café Expresso', 'Café expresso tradicional', 'Bebidas', 3.50, 100, 20, 'unidade'),
('CAFE002', 'Café com Leite', 'Café com leite quente', 'Bebidas', 4.00, 100, 20, 'unidade'),
('CAFE003', 'Cappuccino', 'Cappuccino com chocolate', 'Bebidas', 5.50, 100, 20, 'unidade'),
('CAFE004', 'Pão de Açúcar', 'Pão doce tradicional', 'Alimentos', 2.50, 50, 10, 'unidade'),
('CAFE005', 'Bolo de Chocolate', 'Fatia de bolo de chocolate', 'Alimentos', 4.50, 30, 10, 'unidade'),
('CAFE006', 'Salgado Assado', 'Salgado assado variado', 'Alimentos', 3.00, 50, 15, 'unidade'),
('CAFE007', 'Água Mineral', 'Água mineral 500ml', 'Bebidas', 2.00, 100, 30, 'unidade'),
('CAFE008', 'Refrigerante', 'Refrigerante lata 350ml', 'Bebidas', 4.50, 80, 20, 'unidade');
