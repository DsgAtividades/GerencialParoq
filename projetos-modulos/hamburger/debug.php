<?php
// Debug simples para verificar o erro
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug - Projeto Hamburguer</h1>";

// Teste básico de conexão
try {
    $host = 'bdhamburger.mysql.dbaas.com.br';
    $dbname = 'dbhamburger';
    $username = 'dbhamburger';
    $password = 'Dsg#1806';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão com banco OK<br>";
    
    // Testar uma query simples
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Query de teste OK: " . $result['test'] . "<br>";
    
} catch (PDOException $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "<br>";
}

// Teste de carregamento de arquivos
echo "<h2>Teste de Arquivos:</h2>";

$files = [
    'config/config.php',
    'core/Database.php',
    'core/Router.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "✅ $file existe<br>";
        try {
            require_once $path;
            echo "✅ $file carregado com sucesso<br>";
        } catch (Exception $e) {
            echo "❌ Erro ao carregar $file: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ $file não encontrado<br>";
    }
}

echo "<h2>Teste do Router:</h2>";
try {
    $router = new Core\Router();
    echo "✅ Router criado com sucesso<br>";
} catch (Exception $e) {
    echo "❌ Erro ao criar Router: " . $e->getMessage() . "<br>";
}
?>
