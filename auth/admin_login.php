<?php
session_start();
header('Content-Type: application/json');

// Incluir configuração centralizada do banco de dados
require_once '../config/database_connection.php';

try {
    $pdo = getConnection();
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados']);
    exit;
}

// Verificar se os dados foram enviados
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$input_username = trim($_POST['username'] ?? '');
$input_password = $_POST['password'] ?? '';

// Validação básica
if (empty($input_username) || empty($input_password)) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

try {
    // Buscar usuário no banco de dados
    $stmt = $pdo->prepare("
        SELECT id, username, password, module_access, is_active, created_at, last_access
        FROM users 
        WHERE username = ? AND is_active = 1
    ");
    $stmt->execute([$input_username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado ou inativo']);
        exit;
    }
    
    // Verificar senha
    if (password_verify($input_password, $user['password'])) {
        // Login bem-sucedido
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['module_access'] = $user['module_access'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Registrar último acesso
        $update_stmt = $pdo->prepare("UPDATE users SET last_access = NOW() WHERE id = ?");
        $update_stmt->execute([$user['id']]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login realizado com sucesso',
            'user' => [
                'username' => $user['username'],
                'module_access' => $user['module_access']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Senha incorreta']);
    }
    
} catch(PDOException $e) {
    error_log("Erro de banco de dados: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
