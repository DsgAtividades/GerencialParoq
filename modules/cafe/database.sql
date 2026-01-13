-- Criação do banco de dados para o sistema de festa
-- Criado em: 2025-03-20

-- Configuração do charset
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

-- Tabela de cartões de acesso
CREATE TABLE cafe_cartoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(255) NOT NULL UNIQUE,
  data_geracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  usado BOOLEAN DEFAULT FALSE,
  id_pessoa INT NULL,
  CONSTRAINT uk_cartoes_codigo UNIQUE (codigo),
  CONSTRAINT fk_cartoes_pessoa FOREIGN KEY (id_pessoa) REFERENCES cafe_pessoas(id_pessoa) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Pessoas (armazena informações dos participantes)
CREATE TABLE cafe_pessoas (
  id_pessoa INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  cpf VARCHAR(14) NOT NULL,
  telefone VARCHAR(15),
  qrcode VARCHAR(255) NOT NULL UNIQUE,
  CONSTRAINT uk_pessoas_cpf UNIQUE (cpf),
  CONSTRAINT fk_pessoas_cartao FOREIGN KEY (qrcode) REFERENCES cafe_cartoes(codigo) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Produtos (catálogo de itens disponíveis para venda)
CREATE TABLE cafe_produtos (
  id_produto INT AUTO_INCREMENT PRIMARY KEY,
  nome_produto VARCHAR(255) NOT NULL,
  preco DECIMAL(10, 2) NOT NULL,
  quantidade_estoque INT NOT NULL CHECK (quantidade_estoque >= 0),
  bloqueado TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Vendas (registro de transações)
CREATE TABLE cafe_vendas (
  id_venda INT AUTO_INCREMENT PRIMARY KEY,
  id_pessoa INT NOT NULL,
  valor_total DECIMAL(10, 2) DEFAULT 0.00,
  data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_vendas_pessoa FOREIGN KEY (id_pessoa) REFERENCES cafe_pessoas(id_pessoa) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Itens da Venda (produtos em cada venda)
CREATE TABLE cafe_itens_venda (
  id_item INT AUTO_INCREMENT PRIMARY KEY,
  id_venda INT NOT NULL,
  id_produto INT NOT NULL,
  quantidade INT NOT NULL CHECK (quantidade > 0),
  valor_unitario DECIMAL(10, 2) NOT NULL,
  valor_total DECIMAL(10, 2) GENERATED ALWAYS AS (quantidade * valor_unitario) STORED,
  CONSTRAINT fk_item_venda FOREIGN KEY (id_venda) REFERENCES cafe_vendas(id_venda) ON DELETE RESTRICT,
  CONSTRAINT fk_item_produto FOREIGN KEY (id_produto) REFERENCES cafe_produtos(id_produto) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trigger para atualizar o valor total da venda
DELIMITER //
CREATE TRIGGER after_item_venda_insert AFTER INSERT ON cafe_itens_venda
FOR EACH ROW
BEGIN
    UPDATE cafe_vendas 
    SET valor_total = (
        SELECT SUM(valor_total) 
        FROM cafe_itens_venda 
        WHERE id_venda = NEW.id_venda
    )
    WHERE id_venda = NEW.id_venda;
END//

CREATE TRIGGER after_item_venda_update AFTER UPDATE ON cafe_itens_venda
FOR EACH ROW
BEGIN
    UPDATE cafe_vendas 
    SET valor_total = (
        SELECT SUM(valor_total) 
        FROM cafe_itens_venda 
        WHERE id_venda = NEW.id_venda
    )
    WHERE id_venda = NEW.id_venda;
END//

CREATE TRIGGER after_item_venda_delete AFTER DELETE ON cafe_itens_venda
FOR EACH ROW
BEGIN
    UPDATE cafe_vendas 
    SET valor_total = COALESCE((
        SELECT SUM(valor_total) 
        FROM cafe_itens_venda 
        WHERE id_venda = OLD.id_venda
    ), 0.00)
    WHERE id_venda = OLD.id_venda;
END//
DELIMITER ;

-- Tabela de Saldos de Cartão (controle de créditos dos participantes)
CREATE TABLE cafe_saldos_cartao (
  id_saldo INT AUTO_INCREMENT PRIMARY KEY,
  id_pessoa INT NOT NULL,
  saldo DECIMAL(10, 2) NOT NULL DEFAULT 0.00 CHECK (saldo >= 0),
  CONSTRAINT fk_saldo_pessoa FOREIGN KEY (id_pessoa) REFERENCES cafe_pessoas(id_pessoa) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para histórico de operações de saldo
CREATE TABLE IF NOT EXISTS `cafe_historico_saldo` (
  `id_historico` int(11) NOT NULL AUTO_INCREMENT,
  `id_pessoa` int(11) NOT NULL,
  `tipo_operacao` enum('credito','debito') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `saldo_anterior` decimal(10,2) NOT NULL,
  `saldo_novo` decimal(10,2) NOT NULL,
  `motivo` varchar(50) NOT NULL,
  `data_operacao` datetime NOT NULL,
  PRIMARY KEY (`id_historico`),
  KEY `fk_historico_pessoa` (`id_pessoa`),
  CONSTRAINT `fk_historico_pessoa` FOREIGN KEY (`id_pessoa`) REFERENCES `cafe_pessoas` (`id_pessoa`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para histórico de movimentações de estoque
CREATE TABLE IF NOT EXISTS `cafe_historico_estoque` (
  `id_historico` int(11) NOT NULL AUTO_INCREMENT,
  `id_produto` int(11) NOT NULL,
  `tipo_operacao` enum('entrada','saida') NOT NULL,
  `quantidade` int(11) NOT NULL,
  `quantidade_anterior` int(11) NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `data_operacao` datetime NOT NULL,
  PRIMARY KEY (`id_historico`),
  KEY `fk_historico_produto` (`id_produto`),
  CONSTRAINT `fk_historico_produto` FOREIGN KEY (`id_produto`) REFERENCES `cafe_produtos` (`id_produto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices adicionais para melhor performance
CREATE INDEX idx_pessoas_cpf ON cafe_pessoas(cpf);
CREATE INDEX idx_pessoas_qrcode ON cafe_pessoas(qrcode);
CREATE INDEX idx_vendas_data ON cafe_vendas(data_venda);
CREATE INDEX idx_cartoes_codigo ON cafe_cartoes(codigo);
CREATE INDEX idx_cartoes_usado ON cafe_cartoes(usado);
