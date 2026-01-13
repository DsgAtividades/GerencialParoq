<?php
// Mapeamento de páginas e suas permissões necessárias
$PERMISSOES_PAGINAS = [
    // Usuários
    'usuarios_lista.php' => 'gerenciar_usuarios',
    'usuarios_novo.php' => 'gerenciar_usuarios',
    'usuarios_editar.php' => 'gerenciar_usuarios',
    'usuarios_excluir.php' => 'gerenciar_usuarios',
    
    // Grupos e Permissões
    'gerenciar_grupos.php' => 'gerenciar_grupos',
    'grupo_permissao.php' => 'gerenciar_grupos',
    'gerenciar_permissoes.php' => 'gerenciar_permissoes',
    
    // Pessoas
    'pessoas.php' => 'gerenciar_pessoas',
    'pessoas_novo.php' => 'gerenciar_pessoas',
    'pessoas_editar.php' => 'gerenciar_pessoas',
    'pessoas_mobile.php' => 'gerenciar_pessoas',
    'pessoas_novo_mobile.php' => 'gerenciar_pessoas',
    'pessoas_editar_mobile.php' => 'gerenciar_pessoas',
    
    // Categorias
    'categorias.php' => 'gerenciar_categorias',
    'categorias_novo.php' => 'gerenciar_categorias',
    'categorias_editar.php' => 'gerenciar_categorias',
    'categorias_mobile.php' => 'gerenciar_categorias',
    'categorias_novo_mobile.php' => 'gerenciar_categorias',
    'categorias_editar_mobile.php' => 'gerenciar_categorias',
    
    // Produtos
    'produtos.php' => 'gerenciar_produtos',
    'produtos_novo.php' => 'gerenciar_produtos',
    'produtos_editar.php' => 'gerenciar_produtos',
    'produtos_ajuste_estoque.php' => 'gerenciar_produtos',
    'produtos_mobile.php' => 'gerenciar_produtos',
    'produtos_novo_mobile.php' => 'gerenciar_produtos',
    'produtos_editar_mobile.php' => 'gerenciar_produtos',
    'produtos_estoque.php' => 'gerenciar_produtos',
    'produtos_ajuste_estoque_mobile.php' => 'gerenciar_produtos',
    
    // Vendas e Transações
    'vendas.php' => 'gerenciar_vendas',
    'vendas_novo.php' => 'gerenciar_vendas',
    'vendas_detalhes.php' => 'gerenciar_vendas',
    'vendas_mobile.php' => 'gerenciar_vendas',
    'vendas_novo_mobile.php' => 'gerenciar_vendas',
    'vendas_detalhes_mobile.php' => 'gerenciar_vendas',
    
    // Relatórios
    'relatorios.php' => 'visualizar_relatorios',
    'relatorio_vendas.php' => 'visualizar_relatorios',
    'relatorio_produtos.php' => 'visualizar_relatorios',
    'relatorio_pessoas.php' => 'visualizar_relatorios',
    
    // Cartões
    'cartoes.php' => 'gerenciar_cartoes',
    'cartoes_novo.php' => 'gerenciar_cartoes',
    'cartoes_editar.php' => 'gerenciar_cartoes',
    'cartoes_mobile.php' => 'gerenciar_cartoes',
    'cartoes_novo_mobile.php' => 'gerenciar_cartoes',
    'cartoes_editar_mobile.php' => 'gerenciar_cartoes'
];
