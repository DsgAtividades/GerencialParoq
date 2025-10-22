<?php
require_once 'config/database.php';

$pdo = getConnection();
if ($pdo) {
    echo "✅ Conexão com banco de dados realizada com sucesso!";
} else {
    echo "❌ Erro na conexão com banco de dados!";
}
?>