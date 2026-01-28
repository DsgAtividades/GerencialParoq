-- =====================================================
-- Sistema de Registro de Sobras de Produtos
-- =====================================================
-- Este script cria a estrutura para registrar produtos
-- que sobraram ao final do expediente (não vendidos)
-- =====================================================

-- Tabela de sobras por caixa
CREATE TABLE IF NOT EXISTS cafe_caixas_sobras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    caixa_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    data_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_registro_id INT NOT NULL,
    observacao VARCHAR(500) NULL,
    
    -- Constraints
    CONSTRAINT fk_sobras_caixa FOREIGN KEY (caixa_id) 
        REFERENCES cafe_caixas(id) ON DELETE CASCADE,
    CONSTRAINT fk_sobras_produto FOREIGN KEY (produto_id) 
        REFERENCES cafe_produtos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_sobras_usuario FOREIGN KEY (usuario_registro_id) 
        REFERENCES cafe_usuarios(id) ON DELETE RESTRICT,
    CONSTRAINT chk_sobras_quantidade_positiva CHECK (quantidade > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para performance
CREATE INDEX idx_sobras_caixa ON cafe_caixas_sobras(caixa_id);
CREATE INDEX idx_sobras_produto ON cafe_caixas_sobras(produto_id);
CREATE INDEX idx_sobras_data ON cafe_caixas_sobras(data_registro);

-- View para facilitar consultas de sobras com informações completas
CREATE OR REPLACE VIEW vw_cafe_caixas_sobras AS
SELECT 
    s.id,
    s.caixa_id,
    s.produto_id,
    p.nome_produto as produto_nome,
    p.preco as produto_valor_unitario,
    s.quantidade,
    (s.quantidade * p.preco) as valor_total_perdido,
    s.data_registro,
    s.usuario_registro_id,
    u.nome as usuario_nome,
    s.observacao
FROM cafe_caixas_sobras s
JOIN cafe_produtos p ON s.produto_id = p.id
JOIN cafe_usuarios u ON s.usuario_registro_id = u.id;

-- Adicionar coluna para controlar se já foram registradas sobras
ALTER TABLE cafe_caixas 
ADD COLUMN sobras_registradas TINYINT(1) NOT NULL DEFAULT 0 
COMMENT 'Indica se já foram registradas sobras para este caixa';

