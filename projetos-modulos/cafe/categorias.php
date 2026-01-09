<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

// Verificar se o usuário tem permissão para gerenciar categorias
verificarPermissao('gerenciar_categorias');

// Processar exclusão
if (isset($_POST['excluir']) && isset($_POST['id'])) {
    try {
        // Verificar se existem produtos usando esta categoria
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cafe_produtos WHERE categoria_id = ?");
        $stmt->execute([$_POST['id']]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            exibirAlerta("Não é possível excluir esta categoria pois existem produtos vinculados a ela.", "danger");
        } else {
            $stmt = $pdo->prepare("DELETE FROM cafe_categorias WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            exibirAlerta("Categoria excluída com sucesso!");
        }
    } catch(PDOException $e) {
        exibirAlerta("Erro ao excluir categoria: " . $e->getMessage(), "danger");
    }
}

// Processar ativação/desativação
if (isset($_POST['toggle_status']) && isset($_POST['id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE cafe_categorias SET ativo = NOT ativo WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        exibirAlerta("Status da categoria atualizado com sucesso!");
    } catch(PDOException $e) {
        exibirAlerta("Erro ao atualizar status: " . $e->getMessage(), "danger");
    }
}

// Buscar todas as categorias
$query = "SELECT c.id, c.nome, c.icone, c.ordem, 
                 (SELECT COUNT(*) FROM cafe_produtos WHERE categoria_id = c.id) as total_produtos 
          FROM cafe_categorias c 
          ORDER BY c.ordem, c.nome";
$stmt = $pdo->query($query);
$categorias = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <h1 class="h2 mb-0">Categorias</h1>
        <div>
            <a href="produtos.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-box-seam"></i> Produtos
            </a>
            <a href="categorias_novo.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nova Categoria
            </a>
        </div>
    </div>

    <?php if (isset($erro)): ?>
        <div class="alert alert-danger" role="alert">
            <?= $erro ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0 p-sm-3">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th class="text-center">Ordem</th>
                            <th class="text-center">Produtos</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $categoria): ?>
                        <tr>
                            <td>
                                <?php if ($categoria['icone']): ?>
                                    <i class="bi bi-<?= htmlspecialchars($categoria['icone']) ?> me-2"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($categoria['nome']) ?>
                            </td>
                            <td class="text-center">
                                <?= $categoria['ordem'] ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">
                                    <?= $categoria['total_produtos'] ?> produto(s)
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="categorias_editar.php?id=<?= $categoria['id'] ?>" 
                                       class="btn btn-sm btn-warning text-white"
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <?php if ($categoria['total_produtos'] == 0): ?>
                                    <form method="post" class="d-inline" 
                                          onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?');">
                                        <input type="hidden" name="excluir" value="1">
                                        <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
                                        <button type="submit" 
                                                class="btn btn-sm btn-danger"
                                                title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
