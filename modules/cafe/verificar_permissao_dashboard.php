<?php
require_once 'includes/conexao.php';
session_start();

echo "<h2>Verificação de Permissão visualizar_dashboard</h2>";

// Verificar se a permissão existe
$stmt = $pdo->query("SELECT id, nome FROM cafe_permissoes WHERE nome = 'visualizar_dashboard'");
$perm = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$perm) {
    echo "<p style='color: red;'>❌ Permissão 'visualizar_dashboard' NÃO encontrada no banco!</p>";
    echo "<p>Criando permissão...</p>";
    
    $stmt = $pdo->prepare("INSERT INTO cafe_permissoes (nome, pagina) VALUES (?, ?)");
    $stmt->execute(['visualizar_dashboard', 'dashboard_vendas.php']);
    $permId = $pdo->lastInsertId();
    echo "<p style='color: green;'>✅ Permissão criada com ID: $permId</p>";
} else {
    echo "<p style='color: green;'>✅ Permissão encontrada: ID {$perm['id']}, Nome: {$perm['nome']}</p>";
    $permId = $perm['id'];
}

// Verificar grupo Administrador
$stmt = $pdo->query("SELECT id, nome FROM cafe_grupos WHERE nome = 'Administrador' LIMIT 1");
$grupo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$grupo) {
    echo "<p style='color: red;'>❌ Grupo 'Administrador' NÃO encontrado!</p>";
    exit;
}

echo "<p style='color: green;'>✅ Grupo Administrador encontrado: ID {$grupo['id']}</p>";

// Verificar se o grupo tem a permissão
$stmt = $pdo->prepare("
    SELECT COUNT(*) as tem_permissao 
    FROM cafe_grupos_permissoes 
    WHERE grupo_id = ? AND permissao_id = ?
");
$stmt->execute([$grupo['id'], $permId]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result['tem_permissao'] == 0) {
    echo "<p style='color: orange;'>⚠️ Grupo Administrador NÃO tem a permissão 'visualizar_dashboard'</p>";
    echo "<p>Atribuindo permissão ao grupo Administrador...</p>";
    
    $stmt = $pdo->prepare("INSERT INTO cafe_grupos_permissoes (grupo_id, permissao_id) VALUES (?, ?)");
    $stmt->execute([$grupo['id'], $permId]);
    echo "<p style='color: green;'>✅ Permissão atribuída com sucesso!</p>";
} else {
    echo "<p style='color: green;'>✅ Grupo Administrador JÁ tem a permissão 'visualizar_dashboard'</p>";
}

// Verificar usuários do grupo Administrador
$stmt = $pdo->prepare("
    SELECT u.id, u.nome, u.email, u.ativo
    FROM cafe_usuarios u
    WHERE u.grupo_id = ?
");
$stmt->execute([$grupo['id']]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Usuários do grupo Administrador:</h3>";
echo "<ul>";
foreach ($usuarios as $user) {
    $status = $user['ativo'] == 1 ? '✅ Ativo' : '❌ Inativo';
    echo "<li>{$user['nome']} ({$user['email']}) - $status</li>";
}
echo "</ul>";

echo "<hr>";
echo "<p><a href='dashboard_vendas.php'>Tentar acessar dashboard_vendas.php</a></p>";
echo "<p><a href='index.php'>Voltar para index</a></p>";

