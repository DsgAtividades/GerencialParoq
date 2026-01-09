<?php
try {
    $pdo = new PDO("mysql:host=dbhomolog.mysql.dbaas.com.br;dbname=dbhomolog", "dbhomolog", "Dsg#1806");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
