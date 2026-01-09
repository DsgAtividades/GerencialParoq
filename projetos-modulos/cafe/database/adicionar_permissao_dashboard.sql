-- Inserir a permissão se não existir
INSERT IGNORE INTO permissoes (nome, pagina) 
VALUES ('visualizar_dashboard', 'dashboard_vendas.php');

-- Associar a permissão ao grupo Administrador
INSERT IGNORE INTO grupos_permissoes (grupo_id, permissao_id)
SELECT g.id, p.id
FROM grupos g, permissoes p
WHERE g.nome = 'Administrador'
AND p.nome = 'visualizar_dashboard';
