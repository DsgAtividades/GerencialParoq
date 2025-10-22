<?php
// Arquivo de debug para testar conexão no servidor pspa.app.br
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Debug de Conexão - Servidor pspa.app.br</h2>";
echo "<hr>";

echo "<h3>📋 Informações do Servidor:</h3>";
echo "Servidor: " . $_SERVER['HTTP_HOST'] . "<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "<br>";
echo "Diretório atual: " . __DIR__ . "<br>";

echo "<hr>";

echo "<h3>🔧 Testando Configurações:</h3>";

// Teste 1: Verificar se o arquivo de configuração existe
$config_file = __DIR__ . '/config/database.php';
echo "Arquivo de config existe: " . (file_exists($config_file) ? "✅ SIM" : "❌ NÃO") . "<br>";

if (file_exists($config_file)) {
    echo "Caminho: $config_file<br>";
    echo "Tamanho: " . filesize($config_file) . " bytes<br>";
}

echo "<hr>";

// Teste 2: Tentar carregar a configuração
echo "<h3>📁 Carregando Configuração:</h3>";
try {
    require_once $config_file;
    echo "✅ Arquivo de configuração carregado com sucesso<br>";
    
    // Verificar se a classe Database existe
    if (class_exists('Database')) {
        echo "✅ Classe Database encontrada<br>";
    } else {
        echo "❌ Classe Database NÃO encontrada<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao carregar configuração: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Teste 3: Tentar conectar
echo "<h3>🔌 Testando Conexão:</h3>";
try {
    $database = new Database();
    echo "✅ Objeto Database criado<br>";
    
    $conn = $database->getConnection();
    if ($conn) {
        echo "✅ Conexão estabelecida com sucesso!<br>";
        
        // Teste simples
        $stmt = $conn->query("SELECT 1 as test");
        $result = $stmt->fetch();
        echo "✅ Query de teste executada: " . $result['test'] . "<br>";
        
    } else {
        echo "❌ Conexão retornou NULL<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
    echo "Tipo do erro: " . get_class($e) . "<br>";
}

echo "<hr>";

// Teste 4: Verificar extensões PHP
echo "<h3>🔧 Extensões PHP:</h3>";
echo "PDO disponível: " . (extension_loaded('pdo') ? "✅ SIM" : "❌ NÃO") . "<br>";
echo "PDO MySQL disponível: " . (extension_loaded('pdo_mysql') ? "✅ SIM" : "❌ NÃO") . "<br>";
echo "MySQLi disponível: " . (extension_loaded('mysqli') ? "✅ SIM" : "❌ NÃO") . "<br>";

echo "<hr>";

// Teste 5: Verificar se as tabelas existem
echo "<h3>📊 Verificando Tabelas:</h3>";
try {
    if (isset($conn) && $conn) {
        $stmt = $conn->query("SHOW TABLES LIKE 'lojinha_%'");
        $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Tabelas encontradas: " . count($tabelas) . "<br>";
        foreach ($tabelas as $tabela) {
            echo "✅ $tabela<br>";
        }
        
        if (count($tabelas) == 0) {
            echo "❌ Nenhuma tabela da lojinha encontrada!<br>";
            echo "💡 Você precisa importar os scripts SQL primeiro.<br>";
        }
    } else {
        echo "❌ Não foi possível verificar tabelas (sem conexão)<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar tabelas: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Teste 6: Informações de debug adicionais
echo "<h3>🐛 Debug Adicional:</h3>";
echo "Erro atual: " . error_get_last()['message'] . "<br>";
echo "Log de erros: " . ini_get('log_errors') . "<br>";
echo "Arquivo de log: " . ini_get('error_log') . "<br>";

echo "<hr>";
echo "<p><a href='index.php'>← Voltar para o módulo</a></p>";
?>
