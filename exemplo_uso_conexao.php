<?php
/**
 * Exemplo de como usar a conexão centralizada com banco de dados
 */

// Inclui a configuração centralizada
require_once 'config/database_connection.php';

// Exemplo 1: Usando a função de conveniência getConnection()
try {
    $pdo = getConnection();
    echo "Conexão estabelecida com sucesso!<br>";
} catch (Exception $e) {
    echo "Erro na conexão: " . $e->getMessage();
}

// Exemplo 2: Usando a classe DatabaseConnection diretamente
try {
    $db = getDatabase();
    
    // Buscar todos os registros
    $usuarios = $db->fetchAll("SELECT * FROM usuarios LIMIT 5");
    echo "Usuários encontrados: " . count($usuarios) . "<br>";
    
    // Buscar um único registro
    $usuario = $db->fetchOne("SELECT * FROM usuarios WHERE id = ?", [1]);
    if ($usuario) {
        echo "Usuário encontrado: " . $usuario['nome'] . "<br>";
    }
    
    // Executar uma query de inserção
    // $novoId = $db->execute("INSERT INTO usuarios (nome, email) VALUES (?, ?)", ['João', 'joao@email.com']);
    // echo "Novo usuário inserido com ID: " . $novoId . "<br>";
    
    // Usando transações
    $db->beginTransaction();
    try {
        // Múltiplas operações aqui
        // $db->execute("UPDATE usuarios SET nome = ? WHERE id = ?", ['João Silva', 1]);
        // $db->execute("INSERT INTO logs (acao) VALUES (?)", ['Usuário atualizado']);
        
        $db->commit();
        echo "Transação executada com sucesso!<br>";
    } catch (Exception $e) {
        $db->rollback();
        echo "Erro na transação: " . $e->getMessage();
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

// Exemplo 3: Testando a conexão
if (testConnection()) {
    echo "Teste de conexão: OK<br>";
} else {
    echo "Teste de conexão: FALHOU<br>";
}
?>
