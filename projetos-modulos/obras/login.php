<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Obras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo">
                <h2>Sistema de Obras</h2>
                <p class="text-muted">Paróquia São Pedro</p>
                <p>Sistema de Gerenciamento de Obras e Serviços</p>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    switch ($_GET['error']) {
                        case '1':
                            echo 'Usuário ou senha inválidos';
                            break;
                        case '2':
                            echo 'Erro ao conectar ao banco de dados. Por favor, execute o setup.php primeiro.';
                            break;
                        default:
                            echo 'Erro desconhecido. Tente novamente.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <form action="auth.php" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuário</label>
                    <input type="text" class="form-control" id="username" name="username" required 
                           value="<?php echo isset($_GET['username']) ? htmlspecialchars($_GET['username']) : ''; ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        Se você está acessando pela primeira vez, execute o 
                        <a href="setup.php">setup.php</a> para configurar o sistema.
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
