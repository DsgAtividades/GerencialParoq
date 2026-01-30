<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('gerenciar_grupos');
$grupo = verificaGrupoPermissao();
$where = "";
if($grupo != "Administrador"){
    $where = "where g.nome not like('Administrador') ";
}

// Processar formulário de novo grupo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        if ($_POST['acao'] === 'criar') {
            $nome = $_POST['nome'] ?? '';
            if (!empty($nome)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO cafe_grupos (nome) VALUES (?)");
                    $stmt->execute([$nome]);
                    exibirAlerta("Grupo criado com sucesso!");
                } catch (PDOException $e) {
                    exibirAlerta("Erro ao criar grupo: " . $e->getMessage(), "danger");
                }
            }
        } elseif ($_POST['acao'] === 'excluir') {
            $id = $_POST['id'] ?? '';
            if (!empty($id)) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM cafe_grupos WHERE id = ?");
                    $stmt->execute([$id]);
                    exibirAlerta("Grupo excluído com sucesso!");
                } catch (PDOException $e) {
                    exibirAlerta("Erro ao excluir grupo. Verifique se não há usuários vinculados.", "danger");
                }
            }
        }
    }
}

// Buscar grupos com contadores
$stmt = $pdo->query("
    SELECT g.*,
           (SELECT COUNT(*) FROM cafe_usuarios u WHERE u.grupo_id = g.id) as total_usuarios,
           (SELECT COUNT(*) FROM cafe_grupos_permissoes gp WHERE gp.grupo_id = g.id) as total_permissoes
    FROM cafe_grupos g
    $where
    ORDER BY g.nome
");
$grupos = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Gerenciar Grupos</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Início</a></li>
                        <li class="breadcrumb-item active">Gerenciar Grupos</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="index.php" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novoGrupo">
                    <i class="bi bi-plus-lg"></i> Novo Grupo
                </button>
            </div>
        </div>

        <?php mostrarAlerta(); ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Usuários</th>
                        <th>Permissões</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grupos as $grupo): ?>
                    <tr>
                        <td><?= escapar($grupo['nome']) ?></td>
                        <td><?= $grupo['total_usuarios'] ?></td>
                        <td><?= $grupo['total_permissoes'] ?></td>
                        <td>
                            <a href="grupo_permissao.php?id=<?= $grupo['id'] ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-key"></i>
                            </a>
                            <?php if ($grupo['total_usuarios'] == 0): ?>
                            <button class="btn btn-sm btn-danger" onclick="confirmarExclusao(<?= $grupo['id'] ?>)">
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

    <!-- Modal Novo Grupo -->
    <div class="modal fade" id="novoGrupo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <input type="hidden" name="acao" value="criar">
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Grupo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome do Grupo</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
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
                    Tem certeza que deseja excluir este grupo?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="acao" value="excluir">
                        <input type="hidden" name="id" id="grupoId">
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmarExclusao(id) {
        document.getElementById('grupoId').value = id;
        new bootstrap.Modal(document.getElementById('confirmarExclusao')).show();
    }
    </script>

<?php include 'includes/footer.php'; ?>
