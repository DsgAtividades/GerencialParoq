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

$id = $_GET['id'] ?? 0;

// Verificar se o usuário existe
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header("Location: usuarios_lista.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $grupo_id = $_POST['grupo_id'] ?? null;
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    $erros = [];
    
    if (empty($nome)) $erros[] = "Nome é obrigatório";
    if (empty($email)) $erros[] = "Email é obrigatório";
    if (!empty($senha) && strlen($senha) < 6) $erros[] = "Senha deve ter no mínimo 6 caracteres";
    if (empty($grupo_id)) $erros[] = "Grupo é obrigatório";

    if (empty($erros)) {
        try {
            // Verificar se o email já existe para outro usuário
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                exibirAlerta("Este email já está cadastrado para outro usuário", "danger");
            } else {
                // Atualizar usuário
                if (!empty($senha)) {
                    $sql = "UPDATE usuarios SET nome = ?, email = ?, senha = ?, grupo_id = ?, ativo = ? WHERE id = ?";
                    $params = [$nome, $email, password_hash($senha, PASSWORD_DEFAULT), $grupo_id, $ativo, $id];
                } else {
                    $sql = "UPDATE usuarios SET nome = ?, email = ?, grupo_id = ?, ativo = ? WHERE id = ?";
                    $params = [$nome, $email, $grupo_id, $ativo, $id];
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                exibirAlerta("Usuário atualizado com sucesso!");
                header("Location: usuarios_lista.php");
                exit;
            }
        } catch (PDOException $e) {
            exibirAlerta("Erro ao atualizar usuário: " . $e->getMessage(), "danger");
        }
    } else {
        exibirAlerta(implode("<br>", $erros), "danger");
    }
}

// Buscar grupos para o select
$stmt = $pdo->query("SELECT id, nome FROM grupos $where ORDER BY nome");
$grupos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Editar Usuário</h4>
                        <a href="usuarios_lista.php" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                    </div>
                    <div class="card-body">
                        <?php mostrarAlerta(); ?>
                        
                        <form method="post" class="needs-validation" novalidate>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nome" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="nome" name="nome" required
                                           value="<?= escapar($usuario['nome']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required
                                           value="<?= escapar($usuario['email']) ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="senha" class="form-label">Nova Senha</label>
                                    <input type="password" class="form-control" id="senha" name="senha"
                                           minlength="6" placeholder="Deixe em branco para manter a senha atual">
                                </div>
                                <div class="col-md-6">
                                    <label for="grupo_id" class="form-label">Grupo</label>
                                    <select class="form-select" id="grupo_id" name="grupo_id" required>
                                        <option value="">Selecione um grupo</option>
                                        <?php foreach ($grupos as $grupo): ?>
                                            <option value="<?= $grupo['id'] ?>" <?= $usuario['grupo_id'] == $grupo['id'] ? 'selected' : '' ?>>
                                                <?= escapar($grupo['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"
                                           <?= $usuario['ativo'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="ativo">
                                        Usuário Ativo
                                    </label>
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
