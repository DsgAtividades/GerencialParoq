-- Tabela para eventos específicos de pastorais
-- Nome: membros_eventos_pastorais

CREATE TABLE IF NOT EXISTS membros_eventos_pastorais (
    id VARCHAR(36) NOT NULL PRIMARY KEY,
    pastoral_id VARCHAR(36) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    tipo VARCHAR(50) DEFAULT NULL COMMENT 'Tipo do evento em texto livre (ex: reunião, tarde de recreação, etc)',
    data_evento DATE NOT NULL,
    horario TIME DEFAULT NULL,
    local VARCHAR(255) DEFAULT NULL,
    responsavel_id VARCHAR(36) DEFAULT NULL,
    descricao TEXT DEFAULT NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Chaves estrangeiras
    CONSTRAINT fk_evento_pastoral FOREIGN KEY (pastoral_id) REFERENCES membros_pastorais(id) ON DELETE CASCADE,
    CONSTRAINT fk_evento_responsavel FOREIGN KEY (responsavel_id) REFERENCES membros_membros(id) ON DELETE SET NULL,
    
    -- Índices
    INDEX idx_evento_pastoral (pastoral_id),
    INDEX idx_evento_data (data_evento),
    INDEX idx_evento_ativo (ativo),
    INDEX idx_evento_responsavel (responsavel_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

