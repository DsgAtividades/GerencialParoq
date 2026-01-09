<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('gerenciar_usuarios');
$grupo = verificaGrupoPermissao();
$where = "";
if($grupo != "Administrador"){
    $where = "where nome not like('Administrador') ";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';
    $grupo_id = $_POST['grupo_id'] ?? null;
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    $erros = [];
    
    if (empty($nome)) $erros[] = "Nome é obrigatório";
    if (empty($email)) $erros[] = "Email é obrigatório";
    if (empty($senha)) $erros[] = "Senha é obrigatória";
    if (strlen($senha) < 6) $erros[] = "Senha deve ter no mínimo 6 caracteres";
    if ($senha !== $confirma_senha) $erros[] = "As senhas não conferem";
    if (empty($grupo_id)) $erros[] = "Grupo é obrigatório";

    if (empty($erros)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM cafe_usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                exibirAlerta("Este email já está cadastrado", "danger");
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO cafe_usuarios (nome, email, senha, grupo_id, ativo)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $nome,
                    $email,
                    password_hash($senha, PASSWORD_DEFAULT),
                    $grupo_id,
                    $ativo
                ]);

                exibirAlerta("Usuário cadastrado com sucesso!");
                header("Location: usuarios_lista.php");
                exit;
            }
        } catch (PDOException $e) {
            exibirAlerta("Erro ao cadastrar usuário: " . $e->getMessage(), "danger");
        }
    } else {
        exibirAlerta(implode("<br>", $erros), "danger");
    }
}

// Buscar grupos para o select
$stmt = $pdo->query("SELECT id, nome FROM cafe_grupos $where ORDER BY nome");
$grupos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Novo Usuário</h4>
                    </div>
                    <div class="card-body">
                        <?php mostrarAlerta(); ?>
                        
                        <form method="post" class="needs-validation" novalidate>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nome" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="nome" name="nome" required
                                           value="<?= isset($_POST['nome']) ? escapar($_POST['nome']) : '' ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required
                                           value="<?= isset($_POST['email']) ? escapar($_POST['email']) : '' ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="senha" class="form-label">Senha</label>
                                    <input type="password" class="form-control" id="senha" name="senha" required
                                           minlength="6">
                                </div>
                                <div class="col-md-6">
                                    <label for="confirma_senha" class="form-label">Confirmar Senha</label>
                                    <input type="password" class="form-control" id="confirma_senha" name="confirma_senha"
                                           required minlength="6">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="grupo_id" class="form-label">Grupo</label>
                                    <select class="form-select" id="grupo_id" name="grupo_id" required>
                                        <option value="">Selecione um grupo</option>
                                        <?php foreach ($grupos as $grupo): ?>
                                            <option value="<?= $grupo['id'] ?>" <?= (isset($_POST['grupo_id']) && $_POST['grupo_id'] == $grupo['id']) ? 'selected' : '' ?>>
                                                <?= escapar($grupo['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"
                                               <?= (!isset($_POST['ativo']) || $_POST['ativo']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="ativo">
                                            Usuário Ativo
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="usuarios_lista.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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
