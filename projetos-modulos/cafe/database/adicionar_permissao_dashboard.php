<?php
require_once '../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Inserir a permissão se não existir
    $sql = "INSERT IGNORE INTO cafe_permissoes (nome, pagina) VALUES (:nome, :pagina)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nome' => 'visualizar_dashboard',
        ':pagina' => 'dashboard_vendas.php'
    ]);

    // Associar a permissão ao grupo Administrador
    $sql = "INSERT IGNORE INTO cafe_grupos_permissoes (grupo_id, permissao_id)
            SELECT g.id, p.id
            FROM cafe_grupos g, cafe_permissoes p
            WHERE g.nome = 'Administrador'
            AND p.nome = 'visualizar_dashboard'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    echo "Permissão 'visualizar_dashboard' adicionada com sucesso ao grupo Administrador!";

} catch (PDOException $e) {
    echo "Erro ao adicionar permissão: " . $e->getMessage();
}
