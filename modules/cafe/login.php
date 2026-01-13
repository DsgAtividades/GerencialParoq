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
                FROM cafe_usuarios u
                LEFT JOIN cafe_grupos g ON u.grupo_id = g.id
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
                    FROM cafe_permissoes p
                    JOIN cafe_grupos_permissoes gp ON p.id = gp.permissao_id
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
    <title>Login - PSPA Café</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Estilos para página de login - baseado no padrão do sistema */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #002930 0%, #004d5a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .botao-voltar {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(248, 240, 175, 0.2);
            color: #f8f0af;
            border: 1px solid rgba(248, 240, 175, 0.3);
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .botao-voltar:hover {
            background: rgba(248, 240, 175, 0.3);
            transform: translateY(-1px);
            color: #f8f0af;
        }

        .container-login {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .cabecalho-modulo {
            margin-bottom: 30px;
        }

        .icone-modulo {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2.5rem;
            color: white;
            background: linear-gradient(135deg, #002930 0%, #004d5a 100%);
        }

        .cabecalho-modulo h1 {
            color: #002930;
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .cabecalho-modulo p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 0.95rem;
        }

        .mensagem-erro, .mensagem-sucesso {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .mensagem-erro {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .mensagem-sucesso {
            background: #efe;
            color: #363;
            border: 1px solid #cfc;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        form {
            margin-top: 20px;
        }

        .grupo-formulario {
            margin-bottom: 20px;
            text-align: left;
        }

        .grupo-formulario label {
            display: block;
            margin-bottom: 8px;
            color: #002930;
            font-weight: 600;
            font-size: 14px;
        }

        .grupo-formulario label i {
            margin-right: 5px;
            color: #ac4a00;
        }

        .grupo-formulario input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .grupo-formulario input:focus {
            outline: none;
            border-color: #ac4a00;
            box-shadow: 0 0 0 3px rgba(172, 74, 0, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #002930 0%, #004d5a 100%);
            color: #f8f0af;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 41, 48, 0.3);
            background: linear-gradient(135deg, #004d5a 0%, #006e7f 100%);
        }

        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        @media (max-width: 480px) {
            .container-login {
                padding: 30px 20px;
            }

            .cabecalho-modulo h1 {
                font-size: 1.5rem;
            }

            .icone-modulo {
                width: 70px;
                height: 70px;
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <a href="../../dashboard.html" class="botao-voltar">
        <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
    </a>

    <div class="container-login">
        <div class="cabecalho-modulo">
            <div class="icone-modulo">
                <i class="fas fa-coffee"></i>
            </div>
            <h1>PSPA Café</h1>
            <p>Sistema de Gestão de Vendas e Estoque</p>
        </div>

        <?php 
        if (isset($_SESSION['mensagem'])): 
            $tipo = $_SESSION['mensagem']['tipo'];
            $texto = $_SESSION['mensagem']['texto'];
        ?>
            <div class="alert alert-<?= $tipo ?>">
                <?= htmlspecialchars($texto) ?>
            </div>
        <?php 
            unset($_SESSION['mensagem']);
        endif; 
        ?>

        <form method="post">
            <div class="grupo-formulario">
                <label for="email">
                    <i class="fas fa-envelope"></i> E-mail
                </label>
                <input type="email" id="email" name="email" 
                       placeholder="Digite seu e-mail" 
                       required
                       autocomplete="email"
                       value="<?= isset($_POST['email']) ? escapar($_POST['email']) : '' ?>">
            </div>

            <div class="grupo-formulario">
                <label for="senha">
                    <i class="fas fa-lock"></i> Senha
                </label>
                <input type="password" id="senha" name="senha" 
                       placeholder="Digite sua senha" 
                       required
                       autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Entrar no Sistema
            </button>
        </form>
    </div>
</body>
</html>
