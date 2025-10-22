<?php
// Função helper para obter conexão com banco de dados
function getConnection() {
    require_once __DIR__ . '/database.php';
    $database = new Database();
    return $database->getConnection();
}
?>

