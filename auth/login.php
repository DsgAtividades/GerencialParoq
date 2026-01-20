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
$module = $_POST['module'] ?? '';

// Validação básica
if (empty($input_username) || empty($input_password) || empty($module)) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

// Verificar se o módulo existe
$valid_modules = [
    'bazar', 'lojinha', 'cafe', 'pastoral-social', 'obras', 
    'contas-pagas', 'membros', 'catequese', 'atividades', 
    'secretaria', 'compras', 'eventos'
];

if (!in_array($module, $valid_modules)) {
    echo json_encode(['success' => false, 'message' => 'Módulo inválido']);
    exit;
}

try {
    // Buscar usuário no banco de dados
    $stmt = $pdo->prepare("
        SELECT id, username, password, module_access, is_active 
        FROM users 
        WHERE username = ? AND module_access = ? AND is_active = 1
    ");
    $stmt->execute([$input_username, $module]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado ou sem acesso a este módulo']);
        exit;
    }
    
    // Verificar senha
    if (password_verify($input_password, $user['password'])) {
        // Login bem-sucedido - criar sessão específica do módulo
        $_SESSION['module_user_id'] = $user['id'];
        $_SESSION['module_username'] = $user['username'];
        $_SESSION['module_access'] = $module;
        $_SESSION['module_logged_in'] = true;
        $_SESSION['module_login_time'] = time();
        
        // Registrar último acesso
        $update_stmt = $pdo->prepare("UPDATE users SET last_access = NOW() WHERE id = ?");
        $update_stmt->execute([$user['id']]);
        
        if ($module === 'pastoral-social') {
            $redirect = "projetos-modulos/pastoral_social/login.php";
        } elseif ($module === 'obras') {
            $redirect = "projetos-modulos/obras/index.php";
<<<<<<< HEAD
=======
        } elseif ($module === 'cafe') {
            $redirect = "projetos-modulos/cafe/index.php";
>>>>>>> main
        } else {
            $redirect = "modules/$module/index.php";
        }

        echo json_encode([
            'success' => true, 
            'message' => 'Login realizado com sucesso',
            'redirect' => $redirect
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Senha incorreta']);
    }
    
} catch(PDOException $e) {
    error_log("Erro de banco de dados: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
