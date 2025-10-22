<?php
try {
   
    $host = 'gerencialparoq.mysql.dbaas.com.br';
    $dbname = 'gerencialparoq';
    $username = 'gerencialparoq';
    $password = 'Dsg#1806';
    /*
    $host = '177.153.63.28';
    $dbname = 'bancoobras';
    $username = 'bancoobras';
    $password = 'Dsg#1806';
*/
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
