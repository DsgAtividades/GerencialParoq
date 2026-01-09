<?php
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';
session_start();

//Se já estiver logado, redireciona para index.php
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (!empty($email) && !empty($senha)) {
        try {
            $stmt = $pdo->prepare("
                SELECT u.*, g.nome as grupo_nome 
                FROM usuarios u
                LEFT JOIN grupos g ON u.grupo_id = g.id
                WHERE u.email = ? AND u.ativo = 1
            ");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_grupo'] = $usuario['grupo_nome'];
                $_SESSION['projeto'] = 'paroquianspraga';

                // Buscar permissões do usuário
                $stmt = $pdo->prepare("
                    SELECT p.nome
                    FROM permissoes p
                    JOIN grupos_permissoes gp ON p.id = gp.permissao_id
                    WHERE gp.grupo_id = ?
                ");
                $stmt->execute([$usuario['grupo_id']]);
                $_SESSION['usuario_permissoes'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
                header("Location: index.php");
                exit;
            } else {
                exibirAlerta("Email ou senha incorretos", "danger");
            }
        } catch (PDOException $e) {
            exibirAlerta("Erro ao realizar login: " . $e->getMessage(), "danger");
        }
    } else {
        exibirAlerta("Por favor, preencha todos os campos", "danger");
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Festa Junina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            background-color: white;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header i {
            font-size: 3rem;
            color: #0d6efd;
        }
        .login-header h1 {
            font-size: 1.5rem;
            margin-top: 1rem;
            color: #333;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .btn-login {
            width: 100%;
            padding: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-house-heart-fill"></i>
                <h1>Festa Junina</h1>
            </div>

            <?php mostrarAlerta(); ?>

            <form method="post" class="needs-validation" novalidate >
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="nome@exemplo.com" required
                           value="<?= isset($_POST['email']) ? escapar($_POST['email']) : '' ?>">
                    <label for="email">Email</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="senha" name="senha" 
                           placeholder="Senha" required>
                    <label for="senha">Senha</label>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>
