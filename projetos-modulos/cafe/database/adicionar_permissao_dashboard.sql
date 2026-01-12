-- Inserir a permissão se não existir
INSERT IGNORE INTO cafe_permissoes (nome, pagina) 
VALUES ('visualizar_dashboard', 'dashboard_vendas.php');

-- Associar a permissão ao grupo Administrador
INSERT IGNORE INTO cafe_grupos_permissoes (grupo_id, permissao_id)
SELECT g.id, p.id
FROM cafe_grupos g, cafe_permissoes p
WHERE g.nome = 'Administrador'
AND p.nome = 'visualizar_dashboard';
