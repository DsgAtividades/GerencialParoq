<?php
require_once '../includes/conexao.php';

try {
    // Limpar todas as permissões existentes
    $pdo->exec("DELETE FROM grupos_permissoes");
    $pdo->exec("DELETE FROM permissoes");
    
    // Inserir permissões
    $permissoes = [
        // Usuários
        ['gerenciar_usuarios', 'Gerenciar Usuários'],
        
        // Grupos e Permissões
        ['gerenciar_grupos', 'Gerenciar Grupos'],
        ['gerenciar_permissoes', 'Gerenciar Permissões'],
        
        // Pessoas
        ['gerenciar_pessoas', 'Gerenciar Pessoas'],
        
        // Produtos
        ['gerenciar_produtos', 'Gerenciar Produtos'],
        
        // Categorias
        ['gerenciar_categorias', 'Gerenciar Categorias'],
        
        // Vendas
        ['gerenciar_vendas', 'Gerenciar Vendas'],
        
        // Relatórios
        ['visualizar_relatorios', 'Visualizar Relatórios'],
        
        // Cartões
        ['gerenciar_cartoes', 'Gerenciar Cartões']
    ];

    // Inserir as permissões
    $stmt = $pdo->prepare("INSERT INTO permissoes (nome, pagina) VALUES (?, ?)");
    foreach ($permissoes as $perm) {
        $stmt->execute($perm);
    }

    // Obter o ID do grupo Administrador
    $stmt = $pdo->query("SELECT id FROM grupos WHERE nome = 'Administrador' LIMIT 1");
    $grupo = $stmt->fetch();
    $grupoAdminId = $grupo['id'] ?? null;

    if ($grupoAdminId) {
        // Dar todas as permissões para o grupo Administrador
        $stmt = $pdo->prepare("
            INSERT INTO grupos_permissoes (grupo_id, permissao_id)
            SELECT ?, id FROM permissoes
        ");
        $stmt->execute([$grupoAdminId]);
    }

    echo "Permissões criadas e atribuídas ao grupo Administrador com sucesso!";

} catch (PDOException $e) {
    echo "Erro ao criar permissões: " . $e->getMessage();
}
