<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('gerenciar_permissoes');

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        if ($_POST['acao'] === 'criar') {
            $nome = $_POST['nome'] ?? '';
            $pagina = $_POST['pagina'] ?? '';
            
            if (!empty($nome) && !empty($pagina)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO cafe_permissoes (nome, pagina) VALUES (?, ?)");
                    $stmt->execute([$nome, $pagina]);
                    exibirAlerta("Permissão criada com sucesso!");
                } catch (PDOException $e) {
                    exibirAlerta("Erro ao criar permissão: " . $e->getMessage(), "danger");
                }
            }
        } elseif ($_POST['acao'] === 'excluir') {
            $id = $_POST['id'] ?? '';
            if (!empty($id)) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM cafe_permissoes WHERE id = ?");
                    $stmt->execute([$id]);
                    exibirAlerta("Permissão excluída com sucesso!");
                } catch (PDOException $e) {
                    exibirAlerta("Erro ao excluir permissão. Verifique se não há grupos usando esta permissão.", "danger");
                }
            }
        } elseif ($_POST['acao'] === 'editar') {
            $id = $_POST['id'] ?? '';
            $nome = $_POST['nome'] ?? '';
            $pagina = $_POST['pagina'] ?? '';
            
            if (!empty($id) && !empty($nome) && !empty($pagina)) {
                try {
                    $stmt = $pdo->prepare("UPDATE cafe_permissoes SET nome = ?, pagina = ? WHERE id = ?");
                    $stmt->execute([$nome, $pagina, $id]);
                    exibirAlerta("Permissão atualizada com sucesso!");
                } catch (PDOException $e) {
                    exibirAlerta("Erro ao atualizar permissão: " . $e->getMessage(), "danger");
                }
            }
        }
    }
}

// Buscar todas as permissões com contagem de grupos
$stmt = $pdo->query("
    SELECT p.*,
           (SELECT COUNT(*) FROM cafe_grupos_permissoes gp WHERE gp.permissao_id = p.id) as total_grupos
    FROM cafe_permissoes p
    ORDER BY p.nome
");
$permissoes = $stmt->fetchAll();

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Permissões</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Gerenciar Permissões</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Início</a></li>
                        <li class="breadcrumb-item active">Gerenciar Permissões</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="index.php" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novaPermissao">
                    <i class="bi bi-plus-lg"></i> Nova Permissão
                </button>
            </div>
        </div>

        <?php mostrarAlerta(); ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Página</th>
                        <th>Grupos</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissoes as $permissao): ?>
                    <tr>
                        <td><?= escapar($permissao['nome']) ?></td>
                        <td><?= escapar($permissao['pagina']) ?></td>
                        <td><?= $permissao['total_grupos'] ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editarPermissao(<?= htmlspecialchars(json_encode($permissao)) ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php if ($permissao['total_grupos'] == 0): ?>
                            <button class="btn btn-sm btn-danger" onclick="confirmarExclusao(<?= $permissao['id'] ?>)">
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

    <!-- Modal Nova Permissão -->
    <div class="modal fade" id="novaPermissao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <input type="hidden" name="acao" value="criar">
                    <div class="modal-header">
                        <h5 class="modal-title">Nova Permissão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome da Permissão</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="pagina" class="form-label">Página</label>
                            <input type="text" class="form-control" id="pagina" name="pagina" required>
                            <div class="form-text">Exemplo: usuarios_lista.php</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Permissão -->
    <div class="modal fade" id="editarPermissao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <input type="hidden" name="acao" value="editar">
                    <input type="hidden" name="id" id="editar_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Permissão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editar_nome" class="form-label">Nome da Permissão</label>
                            <input type="text" class="form-control" id="editar_nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="editar_pagina" class="form-label">Página</label>
                            <input type="text" class="form-control" id="editar_pagina" name="pagina" required>
                            <div class="form-text">Exemplo: usuarios_lista.php</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
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
                    Tem certeza que deseja excluir esta permissão?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="acao" value="excluir">
                        <input type="hidden" name="id" id="permissaoId">
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmarExclusao(id) {
        document.getElementById('permissaoId').value = id;
        new bootstrap.Modal(document.getElementById('confirmarExclusao')).show();
    }

    function editarPermissao(permissao) {
        document.getElementById('editar_id').value = permissao.id;
        document.getElementById('editar_nome').value = permissao.nome;
        document.getElementById('editar_pagina').value = permissao.pagina;
        new bootstrap.Modal(document.getElementById('editarPermissao')).show();
    }
    </script>
</body>
</html>
