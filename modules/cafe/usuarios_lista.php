<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

// Verificar se o usuário tem permissão para acessar esta página
verificarPermissao('gerenciar_usuarios');
$grupo = verificaGrupoPermissao();
$where = "";
if($grupo != "Administrador"){
    $where = "where g.nome not like('Administrador') ";
}

$stmt = $pdo->query("
    SELECT u.*, g.nome as grupo_nome,
    (SELECT COUNT(*) FROM cafe_grupos_permissoes gp WHERE gp.grupo_id = u.grupo_id) as total_permissoes
    FROM cafe_usuarios u
    LEFT JOIN cafe_grupos g ON u.grupo_id = g.id
    $where
    ORDER BY u.nome
");
$usuarios = $stmt->fetchAll();

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Lista de Usuários</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Início</a></li>
                        <li class="breadcrumb-item active">Lista de Usuários</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="index.php" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="usuarios_novo.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Novo Usuário
                </a>
            </div>
        </div>

        <?php mostrarAlerta(); ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Grupo</th>
                        <th>Permissões</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= escapar($usuario['nome']) ?></td>
                        <td><?= escapar($usuario['email']) ?></td>
                        <td>
                            <span class="badge bg-info">
                                <?= escapar($usuario['grupo_nome'] ?? 'Sem grupo') ?>
                            </span>
                        </td>
                        <td><?= $usuario['total_permissoes'] ?></td>
                        <td>
                            <?php if ($usuario['ativo']): ?>
                                <span class="badge bg-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="usuarios_editar.php?id=<?= $usuario['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($_SESSION['usuario_id'] != $usuario['id']): ?>
                            <button class="btn btn-sm btn-danger" onclick="confirmarExclusao(<?= $usuario['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmarExclusao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir este usuário?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="usuarios_excluir.php" method="post" class="d-inline">
                        <input type="hidden" name="id" id="usuarioId">
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmarExclusao(id) {
        document.getElementById('usuarioId').value = id;
        new bootstrap.Modal(document.getElementById('confirmarExclusao')).show();
    }
    </script>
</body>
</html>
