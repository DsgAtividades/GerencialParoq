-- Script para atualizar a view vw_cafe_caixas_resumo
-- Adiciona suporte para PIX e Cortesia

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

