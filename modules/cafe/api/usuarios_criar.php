<?php
/**
 * API para criar novo usuário
 * 
 * Endpoint: POST api/usuarios_criar.php
 * Permissão requerida: gerenciar_usuarios
 */

require_once '../includes/conexao.php';
require_once '../includes/verifica_permissao.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar permissão
verificarPermissao('gerenciar_usuarios');

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido. Use POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Obter dados do corpo da requisição
    $input = file_get_contents('php://input');
    $dados = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Dados JSON inválidos');
    }

    // Validar campos obrigatórios
    $nome = trim($dados['nome'] ?? '');
    $email = trim($dados['email'] ?? '');
    $senha = $dados['senha'] ?? '';
    $grupo_id = $dados['grupo_id'] ?? null;
    $ativo = isset($dados['ativo']) ? (int)$dados['ativo'] : 1;

    $erros = [];

    if (empty($nome)) {
        $erros[] = "Nome é obrigatório";
    }

    if (empty($email)) {
        $erros[] = "Email é obrigatório";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email inválido";
    }

    if (empty($senha)) {
        $erros[] = "Senha é obrigatória";
    } elseif (strlen($senha) < 6) {
        $erros[] = "Senha deve ter no mínimo 6 caracteres";
    }

    if (empty($grupo_id)) {
        $erros[] = "Grupo é obrigatório";
    } else {
        // Verificar se o grupo existe
        $stmt = $pdo->prepare("SELECT id FROM cafe_grupos WHERE id = ?");
        $stmt->execute([$grupo_id]);
        if (!$stmt->fetch()) {
            $erros[] = "Grupo selecionado não existe";
        }
    }

    if (!empty($erros)) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => implode('; ', $erros)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Verificar se o email já está cadastrado
    $stmt = $pdo->prepare("SELECT id FROM cafe_usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Este email já está cadastrado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Verificar grupo do usuário logado (não-admin não pode criar admin)
    $grupoLogado = verificaGrupoPermissao();
    if ($grupoLogado !== 'Administrador') {
        $stmt = $pdo->prepare("SELECT nome FROM cafe_grupos WHERE id = ?");
        $stmt->execute([$grupo_id]);
        $grupoSelecionado = $stmt->fetch();
        
        if ($grupoSelecionado && $grupoSelecionado['nome'] === 'Administrador') {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para criar usuários administradores'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Inserir novo usuário
    $stmt = $pdo->prepare("
        INSERT INTO cafe_usuarios (nome, email, senha, grupo_id, ativo)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    
    $stmt->execute([
        $nome,
        $email,
        $senhaHash,
        $grupo_id,
        $ativo
    ]);

    $usuarioId = $pdo->lastInsertId();

    // Registrar no histórico
    $stmt = $pdo->prepare("
        INSERT INTO cafe_historico_transacoes_sistema 
        (nome_usuario, grupo_usuario, tipo, tipo_transacao, id_pessoa)
        VALUES (?, ?, 'Usuario', 'Criação de usuário', ?)
    ");
    
    $stmt->execute([
        $_SESSION['usuario_nome'] ?? 'Sistema',
        $_SESSION['usuario_grupo'] ?? 'Sistema',
        $usuarioId
    ]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Usuário criado com sucesso',
        'usuario_id' => $usuarioId
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Erro ao criar usuário: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao criar usuário: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}



