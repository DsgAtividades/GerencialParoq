<?php
/**
 * Script de debug para verificar permissões do usuário
 * Acesse via navegador quando estiver logado
 */

require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';

session_start();

echo "<h2>Debug de Permissões</h2>";
echo "<pre>";

echo "=== INFORMAÇÕES DA SESSÃO ===\n";
echo "Usuario ID: " . ($_SESSION['usuario_id'] ?? 'NÃO DEFINIDO') . "\n";
echo "Usuario Nome: " . ($_SESSION['usuario_nome'] ?? 'NÃO DEFINIDO') . "\n";
echo "Projeto: " . ($_SESSION['projeto'] ?? 'NÃO DEFINIDO') . "\n";
echo "\n";

if (!isset($_SESSION['usuario_id'])) {
    echo "ERRO: Usuário não está logado!\n";
    exit;
}

// Verificar grupo
$grupo = verificaGrupoPermissao();
echo "Grupo do Usuário: " . ($grupo ?: 'NÃO ENCONTRADO') . "\n";
echo "\n";

// Verificar permissão específica
echo "=== VERIFICAÇÃO DE PERMISSÃO 'gerenciar_usuarios' ===\n";
$temPermissao = temPermissao('gerenciar_usuarios');
echo "temPermissao('gerenciar_usuarios'): " . ($temPermissao ? 'TRUE' : 'FALSE') . "\n";
echo "\n";

// Query direta no banco
echo "=== QUERY DIRETA NO BANCO ===\n";
try {
    $stmt = $pdo->prepare("
        SELECT 
            u.id as usuario_id,
            u.nome as usuario_nome,
            u.email,
            u.ativo as usuario_ativo,
            g.id as grupo_id,
            g.nome as grupo_nome,
            p.id as permissao_id,
            p.nome as permissao_nome
        FROM cafe_usuarios u
        LEFT JOIN cafe_grupos_permissoes gp ON u.grupo_id = gp.grupo_id
        LEFT JOIN cafe_permissoes p ON gp.permissao_id = p.id
        LEFT JOIN cafe_grupos g ON u.grupo_id = g.id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total de permissões encontradas: " . count($permissoes) . "\n";
    echo "\nPermissões do usuário:\n";
    foreach ($permissoes as $perm) {
        $destaque = ($perm['permissao_nome'] === 'gerenciar_usuarios') ? ' ⭐' : '';
        echo "  - {$perm['permissao_nome']}{$destaque}\n";
    }
    
    // Verificar especificamente gerenciar_usuarios
    $temGerenciarUsuarios = false;
    foreach ($permissoes as $perm) {
        if ($perm['permissao_nome'] === 'gerenciar_usuarios' && $perm['usuario_ativo'] == 1) {
            $temGerenciarUsuarios = true;
            break;
        }
    }
    
    echo "\n=== RESULTADO FINAL ===\n";
    echo "Usuário TEM permissão 'gerenciar_usuarios': " . ($temGerenciarUsuarios ? 'SIM ✅' : 'NÃO ❌') . "\n";
    
} catch (PDOException $e) {
    echo "ERRO na query: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<hr>";
echo "<a href='usuarios_lista.php'>Voltar para Lista de Usuários</a>";



