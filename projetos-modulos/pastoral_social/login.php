<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pastoral Social</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>


<body>
    <div class="container">
        <div class="login-container">
            <div class="logo">
                <h2>Pastoral Social</h2>
                <p class="text-muted">Sistema de Cadastro de Usuários</p>
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

            <form class="formulario" action="auth.php" method="post">
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
            
            <div class="mt-3">
                <a href="../../dashboard.php" class="btn btn-outline-secondary w-100">Voltar aos Módulos</a>
            </div>
            
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
