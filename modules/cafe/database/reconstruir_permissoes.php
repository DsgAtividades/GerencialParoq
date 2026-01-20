<?php
/**
 * Script de Reconstrução Completa de Permissões
 * Módulo Café - Sistema de Gestão Paroquial
 * 
 * ATENÇÃO: Este script vai:
 * 1. Deletar TODAS as permissões existentes
 * 2. Deletar TODOS os vínculos grupo_permissoes
 * 3. Resetar o AUTO_INCREMENT das tabelas
 * 4. Criar as novas permissões padronizadas
 * 5. Atribuir TODAS as permissões ao grupo "Administrador"
 */

require_once '../includes/conexao.php';

// Função para log de ações
function log_acao($mensagem) {
    echo "<div style='padding: 8px; margin: 4px 0; background: #e3f2fd; border-left: 4px solid #2196f3;'>";
    echo "✓ " . htmlspecialchars($mensagem);
    echo "</div>";
}

function log_erro($mensagem) {
    echo "<div style='padding: 8px; margin: 4px 0; background: #ffebee; border-left: 4px solid #f44336;'>";
    echo "✗ " . htmlspecialchars($mensagem);
    echo "</div>";
}

function log_info($mensagem) {
    echo "<div style='padding: 8px; margin: 4px 0; background: #fff3e0; border-left: 4px solid #ff9800;'>";
    echo "ℹ " . htmlspecialchars($mensagem);
    echo "</div>";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Reconstruir Permissões - Módulo Café</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 1000px; margin: 0 auto; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #002930; border-bottom: 3px solid #ac4a00; padding-bottom: 10px; }
        h2 { color: #004d5a; margin-top: 30px; }
        .warning { background: #fff3cd; border: 2px solid #ffc107; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .success { background: #d4edda; border: 2px solid #28a745; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .btn { padding: 12px 30px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 5px; }
        .btn:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #002930; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
        .highlight { background: #ffff99 !important; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Reconstruir Sistema de Permissões</h1>
        
        <div class="warning">
            <strong>⚠️ ATENÇÃO!</strong><br>
            Este script vai DELETAR todas as permissões e vínculos existentes.<br>
            Apenas o grupo <strong>Administrador</strong> receberá automaticamente todas as novas permissões.<br>
            <strong>Outros grupos precisarão ter suas permissões reconfiguradas manualmente!</strong>
        </div>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    try {
        echo "<h2>📋 Iniciando Reconstrução...</h2>";
        
        // PASSO 1: Desabilitar verificação de chaves estrangeiras
        log_info("Desabilitando verificação de chaves estrangeiras...");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // PASSO 2: Deletar todos os vínculos grupo_permissoes
        log_info("Deletando todos os vínculos grupo_permissoes...");
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM cafe_grupos_permissoes");
        $total_vinculos = $stmt->fetch()['total'];
        $pdo->exec("DELETE FROM cafe_grupos_permissoes");
        log_acao("$total_vinculos vínculos deletados.");
        
        // PASSO 3: Deletar todas as permissões
        log_info("Deletando todas as permissões...");
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM cafe_permissoes");
        $total_permissoes = $stmt->fetch()['total'];
        $pdo->exec("DELETE FROM cafe_permissoes");
        log_acao("$total_permissoes permissões deletadas.");
        
        // PASSO 4: Resetar AUTO_INCREMENT
        log_info("Resetando AUTO_INCREMENT das tabelas...");
        $pdo->exec("ALTER TABLE cafe_permissoes AUTO_INCREMENT = 1");
        log_acao("AUTO_INCREMENT resetado.");
        
        // PASSO 5: Verificar se a coluna 'descricao' existe
        log_info("Verificando estrutura da tabela cafe_permissoes...");
        $stmt = $pdo->query("SHOW COLUMNS FROM cafe_permissoes LIKE 'descricao'");
        $coluna_descricao_existe = $stmt->rowCount() > 0;
        
        if (!$coluna_descricao_existe) {
            log_info("Adicionando coluna 'descricao' na tabela cafe_permissoes...");
            $pdo->exec("ALTER TABLE cafe_permissoes ADD COLUMN descricao VARCHAR(255) NULL AFTER nome");
            log_acao("Coluna 'descricao' adicionada com sucesso!");
        } else {
            log_acao("Coluna 'descricao' já existe.");
        }
        
        // PASSO 5.5: Iniciar transação APÓS os comandos DDL
        log_info("Iniciando transação para inserção de dados...");
        $pdo->beginTransaction();
        
        // PASSO 6: Criar novas permissões padronizadas
        log_info("Criando novas permissões...");
        
        $permissoes = [
            // Gestão de Usuários e Sistema
            ['gerenciar_usuarios', 'Gerenciar Usuários do Sistema', 'usuarios'],
            ['gerenciar_grupos', 'Gerenciar Grupos de Usuários', 'grupos'],
            ['gerenciar_permissoes', 'Gerenciar Permissões do Sistema', 'permissoes'],
            
            // Pessoas/Clientes
            ['gerenciar_pessoas', 'Gerenciar Pessoas/Clientes', 'pessoas'],
            
            // Produtos e Categorias
            ['gerenciar_produtos', 'Gerenciar Produtos', 'produtos'],
            ['gerenciar_categorias', 'Gerenciar Categorias de Produtos', 'categorias'],
            
            // Vendas
            ['gerenciar_vendas', 'Gerenciar Vendas (Relatórios)', 'vendas'],
            ['vendas_mobile', 'Realizar Vendas (Tela Mobile)', 'vendas_mobile'],
            
            // Transações e Saldos
            ['gerenciar_transacoes', 'Gerenciar Transações e Saldos', 'transacoes'],
            ['saldos_mobile', 'Adicionar Créditos (Tela Mobile)', 'saldos_mobile'],
            
            // Cartões
            ['gerenciar_cartoes', 'Gerenciar Cartões', 'cartoes'],
            ['gerar_cartoes', 'Gerar Novos Cartões QR', 'gerar_cartoes'],
            
            // Dashboard e Relatórios
            ['gerenciar_dashboard', 'Gerenciar Dashboard de Vendas', 'dashboard'],
            ['gerenciar_relatorios', 'Gerenciar Relatórios', 'relatorios'],
            
            // Permissões Especiais
            ['estornar_vendas', 'Estornar Vendas', 'estornar'],
            
            // APIs
            ['api_finalizar_venda', 'API: Finalizar Venda', 'api'],
            ['api_operacao_saldo', 'API: Operações de Saldo', 'api'],
            ['api_buscar_participante', 'API: Buscar Participante', 'api'],
            ['api_estornar_venda', 'API: Estornar Venda', 'api'],
        ];
        
        $stmt = $pdo->prepare("INSERT INTO cafe_permissoes (nome, descricao, pagina) VALUES (?, ?, ?)");
        
        foreach ($permissoes as $perm) {
            $stmt->execute($perm);
            log_acao("Permissão criada: {$perm[0]} - {$perm[1]}");
        }
        
        $total_criadas = count($permissoes);
        echo "<div class='success'><strong>✓ {$total_criadas} permissões criadas com sucesso!</strong></div>";
        
        // PASSO 7: Buscar grupo Administrador
        log_info("Buscando grupo Administrador...");
        $stmt = $pdo->query("SELECT id, nome FROM cafe_grupos WHERE nome = 'Administrador' LIMIT 1");
        $grupo_admin = $stmt->fetch();
        
        if ($grupo_admin) {
            log_acao("Grupo Administrador encontrado (ID: {$grupo_admin['id']})");
            
            // PASSO 8: Atribuir TODAS as permissões ao Administrador
            log_info("Atribuindo todas as permissões ao grupo Administrador...");
            $stmt = $pdo->prepare("
                INSERT INTO cafe_grupos_permissoes (grupo_id, permissao_id)
                SELECT ?, id FROM cafe_permissoes
            ");
            $stmt->execute([$grupo_admin['id']]);
            
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM cafe_grupos_permissoes WHERE grupo_id = {$grupo_admin['id']}");
            $total_atribuidas = $stmt->fetch()['total'];
            
            echo "<div class='success'><strong>✓ {$total_atribuidas} permissões atribuídas ao grupo Administrador!</strong></div>";
        } else {
            log_erro("Grupo Administrador não encontrado! As permissões foram criadas mas não foram atribuídas.");
        }
        
        // COMMIT da transação
        log_info("Finalizando transação (COMMIT)...");
        $pdo->commit();
        log_acao("Transação finalizada com sucesso!");
        
        // PASSO 10: Reabilitar verificação de chaves estrangeiras
        log_info("Reabilitando verificação de chaves estrangeiras...");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        log_acao("Chaves estrangeiras reabilitadas.");
        
        echo "<h2>✅ Reconstrução Concluída com Sucesso!</h2>";
        
        // Mostrar tabela de permissões criadas
        echo "<h2>📊 Permissões Criadas:</h2>";
        $stmt = $pdo->query("SELECT id, nome, descricao, pagina FROM cafe_permissoes ORDER BY id");
        $permissoes_criadas = $stmt->fetchAll();
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Descrição</th><th>Categoria</th></tr>";
        foreach ($permissoes_criadas as $p) {
            echo "<tr>";
            echo "<td>{$p['id']}</td>";
            echo "<td><strong>{$p['nome']}</strong></td>";
            echo "<td>{$p['descricao']}</td>";
            echo "<td>{$p['pagina']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div class='warning'>";
        echo "<strong>⚠️ PRÓXIMOS PASSOS:</strong><br><br>";
        echo "1. <strong>Faça LOGOUT</strong> de todos os usuários<br>";
        echo "2. <strong>Faça LOGIN novamente</strong> para carregar as novas permissões<br>";
        echo "3. Acesse <strong>Gerenciar Grupos</strong> para atribuir permissões aos outros grupos<br>";
        echo "4. <strong>Teste</strong> cada funcionalidade para garantir que está funcionando<br>";
        echo "</div>";
        
        echo "<p style='text-align: center; margin-top: 30px;'>";
        echo "<a href='../index.php' class='btn'>Voltar ao Dashboard</a> ";
        echo "<a href='../gerenciar_grupos.php' class='btn btn-secondary'>Gerenciar Grupos</a>";
        echo "</p>";
        
    } catch (PDOException $e) {
        // Verificar se há transação ativa antes de fazer rollback
        try {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
                log_info("Transação revertida (ROLLBACK).");
            }
        } catch (Exception $e2) {
            // Ignorar erro de rollback
        }
        
        log_erro("ERRO FATAL: " . $e->getMessage());
        echo "<div class='warning'>";
        echo "<strong>❌ A reconstrução falhou!</strong><br>";
        echo "Erro: " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "Linha: " . $e->getLine() . "<br>";
        echo "</div>";
        
        echo "<div class='warning'>";
        echo "<strong>⚠️ Atenção!</strong><br>";
        echo "As permissões foram parcialmente deletadas.<br>";
        echo "Execute novamente o script ou restaure o backup do banco de dados.<br>";
        echo "</div>";
        
        echo "<p><a href='reconstruir_permissoes.php' class='btn btn-secondary'>Tentar Novamente</a></p>";
    }
    
} else {
    // Mostrar tela de confirmação
    echo "<h2>📊 Status Atual do Sistema:</h2>";
    
    // Contar permissões atuais
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cafe_permissoes");
    $total_permissoes = $stmt->fetch()['total'];
    
    // Contar vínculos atuais
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cafe_grupos_permissoes");
    $total_vinculos = $stmt->fetch()['total'];
    
    // Contar grupos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cafe_grupos");
    $total_grupos = $stmt->fetch()['total'];
    
    echo "<table>";
    echo "<tr><th>Item</th><th>Quantidade</th></tr>";
    echo "<tr><td>Permissões Existentes</td><td><strong>{$total_permissoes}</strong></td></tr>";
    echo "<tr><td>Vínculos Grupo-Permissão</td><td><strong>{$total_vinculos}</strong></td></tr>";
    echo "<tr><td>Grupos Cadastrados</td><td><strong>{$total_grupos}</strong></td></tr>";
    echo "</table>";
    
    // Listar permissões atuais
    if ($total_permissoes > 0) {
        echo "<h2>📋 Permissões Atuais (Serão Deletadas):</h2>";
        $stmt = $pdo->query("SELECT id, nome, pagina FROM cafe_permissoes ORDER BY id");
        $permissoes_atuais = $stmt->fetchAll();
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Página</th></tr>";
        foreach ($permissoes_atuais as $p) {
            echo "<tr>";
            echo "<td>{$p['id']}</td>";
            echo "<td>{$p['nome']}</td>";
            echo "<td>{$p['pagina']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>🆕 Novas Permissões (Serão Criadas):</h2>";
    echo "<p>Total: <strong>19 permissões</strong></p>";
    echo "<table>";
    echo "<tr><th>Nome</th><th>Descrição</th><th>Categoria</th></tr>";
    echo "<tr><td>gerenciar_usuarios</td><td>Gerenciar Usuários do Sistema</td><td>usuarios</td></tr>";
    echo "<tr><td>gerenciar_grupos</td><td>Gerenciar Grupos de Usuários</td><td>grupos</td></tr>";
    echo "<tr><td>gerenciar_permissoes</td><td>Gerenciar Permissões do Sistema</td><td>permissoes</td></tr>";
    echo "<tr><td>gerenciar_pessoas</td><td>Gerenciar Pessoas/Clientes</td><td>pessoas</td></tr>";
    echo "<tr><td>gerenciar_produtos</td><td>Gerenciar Produtos</td><td>produtos</td></tr>";
    echo "<tr><td>gerenciar_categorias</td><td>Gerenciar Categorias de Produtos</td><td>categorias</td></tr>";
    echo "<tr><td>gerenciar_vendas</td><td>Gerenciar Vendas (Relatórios)</td><td>vendas</td></tr>";
    echo "<tr class='highlight'><td>vendas_mobile</td><td>Realizar Vendas (Tela Mobile)</td><td>vendas_mobile</td></tr>";
    echo "<tr><td>gerenciar_transacoes</td><td>Gerenciar Transações e Saldos</td><td>transacoes</td></tr>";
    echo "<tr class='highlight'><td>saldos_mobile</td><td>Adicionar Créditos (Tela Mobile)</td><td>saldos_mobile</td></tr>";
    echo "<tr><td>gerenciar_cartoes</td><td>Gerenciar Cartões</td><td>cartoes</td></tr>";
    echo "<tr><td>gerar_cartoes</td><td>Gerar Novos Cartões QR</td><td>gerar_cartoes</td></tr>";
    echo "<tr><td>gerenciar_dashboard</td><td>Gerenciar Dashboard de Vendas</td><td>dashboard</td></tr>";
    echo "<tr><td>gerenciar_relatorios</td><td>Gerenciar Relatórios</td><td>relatorios</td></tr>";
    echo "<tr><td>estornar_vendas</td><td>Estornar Vendas</td><td>estornar</td></tr>";
    echo "<tr><td>api_finalizar_venda</td><td>API: Finalizar Venda</td><td>api</td></tr>";
    echo "<tr><td>api_operacao_saldo</td><td>API: Operações de Saldo</td><td>api</td></tr>";
    echo "<tr><td>api_buscar_participante</td><td>API: Buscar Participante</td><td>api</td></tr>";
    echo "<tr><td>api_estornar_venda</td><td>API: Estornar Venda</td><td>api</td></tr>";
    echo "</table>";
    
    echo "<form method='post' onsubmit='return confirm(\"Tem certeza que deseja DELETAR todas as permissões e reconstruir o sistema? Esta ação não pode ser desfeita!\");'>";
    echo "<p style='text-align: center; margin-top: 30px;'>";
    echo "<button type='submit' name='confirmar' value='1' class='btn btn-danger'>🔄 CONFIRMAR E RECONSTRUIR PERMISSÕES</button><br><br>";
    echo "<a href='../index.php' class='btn btn-secondary'>Cancelar e Voltar</a>";
    echo "</p>";
    echo "</form>";
}
?>

    </div>
</body>
</html>

