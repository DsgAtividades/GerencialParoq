<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('gerenciar_produtos');
header('Content-Type: text/html; charset=utf-8');

// // Adicionar coluna bloqueado se não existir
// try {
//     $db->query("ALTER TABLE cafe_produtos ADD COLUMN IF NOT EXISTS bloqueado TINYINT(1) DEFAULT 0");
// } catch (PDOException $e) {
//     // Ignora erro se a coluna já existir
// }

// Buscar categorias para o filtro
$stmt = $pdo->query("SELECT id, nome FROM cafe_categorias ORDER BY nome");
$categorias = $stmt->fetchAll();

// Filtros
$categoria_id = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 0;
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$apenas_bloqueados = isset($_GET['apenas_bloqueados']) ? true : false;
$apenas_sem_estoque = isset($_GET['apenas_sem_estoque']) ? true : false;

// Construir query
$query = "SELECT p.*, c.nome as categoria_nome 
          FROM cafe_produtos p 
          LEFT JOIN cafe_categorias c ON p.categoria_id = c.id 
          WHERE 1=1";
$params = [];

if ($categoria_id) {
    $query .= " AND p.categoria_id = :categoria_id";
    $params[':categoria_id'] = $categoria_id;
}

if ($busca) {
    $query .= " AND p.nome_produto LIKE :busca";
    $params[':busca'] = "%{$busca}%";
}

if ($apenas_bloqueados) {
    $query .= " AND p.bloqueado = 1";
}

if ($apenas_sem_estoque) {
    $query .= " AND p.estoque = 0";
}

$query .= " ORDER BY p.nome_produto";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$produtos = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Produtos</h1>
        <?php if(temPermissao('produtos_incluir')): ?>
        <a href="produtos_novo.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Novo Produto
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Produto atualizado com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="busca" class="form-control" value="<?= htmlspecialchars($busca) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Categoria</label>
                    <select name="categoria_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoria_id == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check me-3">
                        <input type="checkbox" name="apenas_bloqueados" class="form-check-input" id="apenas_bloqueados" <?= $apenas_bloqueados ? 'checked' : '' ?>>
                        <label class="form-check-label" for="apenas_bloqueados">Apenas bloqueados</label>
                    </div>
                    <div class="form-check me-3">
                        <input type="checkbox" name="apenas_sem_estoque" class="form-check-input" id="apenas_sem_estoque" <?= $apenas_sem_estoque ? 'checked' : '' ?>>
                        <label class="form-check-label" for="apenas_sem_estoque">Sem estoque</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th>Status</th>
                    <th width="150">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?= htmlspecialchars($produto['nome_produto']) ?></td>
                        <td><?= htmlspecialchars($produto['categoria_nome'] ?? 'Sem categoria') ?></td>
                        <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                        <td><?= $produto['estoque'] ?></td>
                        <td>
                            <?php if ($produto['bloqueado']): ?>
                                <span class="badge bg-danger">Bloqueado</span>
                            <?php else: ?>
                                <span class="badge bg-success">Ativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <?php if(temPermissao('produtos_estoque')): ?>
                                <a href="produtos_estoque.php?id=<?= $produto['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary" title="Ajustar Estoque">
                                    <i class="bi bi-box-seam"></i>
                                </a>
                                <?php endif ; ?>
                                <?php if(temPermissao('produtos_editar')): ?>
                                    <a href="produtos_editar.php?id=<?= $produto['id'] ?>" 
                                    class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if(temPermissao('produtos_bloquear')): ?>
                                    <?php if ($produto['bloqueado']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="alterarStatus(<?= $produto['id'] ?>, 0)" title="Desbloquear produto">
                                            <i class="bi bi-unlock"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="alterarStatus(<?= $produto['id'] ?>, 1)" title="Bloquear produto">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if(temPermissao('produtos_excluir')): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarExclusao(<?= $produto['id'] ?>)" title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmarExclusao(id) {
    if (confirm('Tem certeza que deseja excluir este produto?')) {
 
        fetch('api/excluir_produto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_produto: id
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Produto Excluído com sucesso!");
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao excluir Produto');
            });
        }
}

function alterarStatus(id, status) {
    if (confirm('Deseja realmente ' + (status ? 'bloquear' : 'desbloquear') + ' este produto?')) {
        

        fetch('produtos_alterar_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id,
                    status: status
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Produto Bloqueado com sucesso!");
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao bloquear Produto');
            });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
