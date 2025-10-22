-- Script para criar a tabela relatorios_atividades
-- Execute este script no banco de dados gerencial_paroquia

CREATE TABLE IF NOT EXISTS relatorios_atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo_atividade VARCHAR(255) NOT NULL,
    setor VARCHAR(100) NOT NULL,
    responsavel VARCHAR(100) NOT NULL,
    data_inicio DATE NOT NULL,
    data_previsao DATE NOT NULL,
    data_termino DATE NULL,
    status ENUM('a_fazer', 'em_andamento', 'pausado', 'concluido', 'cancelado') DEFAULT 'a_fazer',
    observacao TEXT,
    user_id INT NULL, -- Pode ser NULL para relatórios globais
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir alguns dados de exemplo (opcional)
INSERT INTO relatorios_atividades 
(titulo_atividade, setor, responsavel, data_inicio, data_previsao, status, observacao) 
VALUES 
('Preparação para Primeira Comunhão', 'Catequese', 'Maria Silva', '2024-01-15', '2024-06-15', 'em_andamento', 'Turma de 20 crianças'),
('Campanha de Arrecadação de Alimentos', 'Pastoral Social', 'João Santos', '2024-02-01', '2024-02-28', 'concluido', 'Arrecadados 500kg de alimentos'),
('Retiro de Jovens', 'Pastoral da Juventude', 'Ana Costa', '2024-03-10', '2024-03-12', 'a_fazer', 'Retiro de fim de semana');

