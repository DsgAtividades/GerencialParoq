<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verificar timeout da sessão (1 hora)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 3600)) {
    session_unset();
    session_destroy();
    echo json_encode(['success' => false, 'message' => 'Sessão expirada']);
    exit;
}

// Retornar informações do usuário
echo json_encode([
    'success' => true,
    'user' => [
        'username' => $_SESSION['username'] ?? 'Usuário',
        'module_access' => $_SESSION['module_access'] ?? 'Sistema',
        'user_id' => $_SESSION['user_id'] ?? 0
    ]
]);
?>
