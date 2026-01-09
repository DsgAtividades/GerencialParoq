<?php
require_once '../includes/conexao.php';

try {
    // Verificar se já existe o grupo Administrador
    $stmt = $pdo->query("SELECT id FROM cafe_grupos WHERE nome = 'Administrador' LIMIT 1");
    $grupo = $stmt->fetch();
    
    if (!$grupo) {
        // Criar grupo Administrador se não existir
        $pdo->exec("INSERT INTO cafe_grupos (nome) VALUES ('Administrador')");
        $grupo_id = $pdo->lastInsertId();
    } else {
        $grupo_id = $grupo['id'];
    }

    // Criar usuário administrador
    $stmt = $pdo->prepare("
        INSERT INTO cafe_usuarios (nome, email, senha, grupo_id, ativo)
        VALUES (?, ?, ?, ?, 1)
    ");

    $stmt->execute([
        'Administrador',
        'admin@festa.com',
        password_hash('admin123', PASSWORD_DEFAULT),
        $grupo_id
    ]);

    echo "Usuário administrador criado com sucesso!<br>";
    echo "Email: admin@festa.com<br>";
    echo "Senha: admin123";

} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Erro de duplicidade
        echo "O usuário administrador já existe!";
    } else {
        echo "Erro ao criar usuário administrador: " . $e->getMessage();
    }
}
