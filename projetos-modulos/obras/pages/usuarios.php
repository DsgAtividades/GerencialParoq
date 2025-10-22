<?php
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'todos';
$page = $_GET['p'] ?? 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build the query
$where = [];
$params = [];

if ($search) {
    $where[] = "(nome LIKE ? OR cpf LIKE ? OR telefone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter === 'Ativo') {
    $where[] = "situacao = 'Ativo'";
} elseif ($filter === 'Inativo') {
    $where[] = "situacao = 'Inativo'";
} elseif ($filter === 'Aguardando Documentação') {
    $where[] = "situacao = 'Aguardando Documentação'";
} elseif ($filter === 'Outros') {
    $where[] = "situacao NOT IN ('Ativo', 'Inativo', 'Aguardando Documentação')";
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM obras_users $where_clause";
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get users
$sql = "SELECT * FROM obras_users $where_clause ORDER BY nome LIMIT $per_page OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Usuários Cadastrados</h2>
        <a href="index.php?page=usuarios_novo" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Novo Usuário
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="get" class="row g-3 mb-4">
                <input type="hidden" name="page" value="usuarios">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Buscar por nome, CPF ou telefone">
                        <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="filter" class="form-select" onchange="this.form.submit()">
                        <option value="todos" <?php echo $filter === 'todos' ? 'selected' : ''; ?>>Todos</option>
                        <option value="Ativo" <?php echo $filter === 'Ativo' ? 'selected' : ''; ?>>Ativos</option>
                        <option value="Inativo" <?php echo $filter === 'Inativo' ? 'selected' : ''; ?>>Inativos</option>
                        <option value="Aguardando Documentação" <?php echo $filter === 'Aguardando Documentação' ? 'selected' : ''; ?>>Aguardando Documentação</option>
                        <option value="Outros" <?php echo $filter === 'Outros' ? 'selected' : ''; ?>>Outros</option>
                    </select>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Data Nasc.</th>
                            <th>Data Cad.</th>
                            <th>Telefone</th>
                            <th>Cidade</th>
                            <th>Situação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['cpf']); ?></td>
                            <td><?php echo $usuario['data_nascimento'] ? date('d/m/Y', strtotime($usuario['data_nascimento'])) : ''; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?></td>
                            <td><?php echo htmlspecialchars($usuario['telefone']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['cidade']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    switch($usuario['situacao']) {
                                        case 'Ativo':
                                            echo 'success';
                                            break;
                                        case 'Inativo':
                                            echo 'danger';
                                            break;
                                        case 'Aguardando Documentação':
                                            echo 'warning';
                                            break;
                                        default:
                                            echo 'secondary';
                                    }
                                ?>">
                                    <?php echo $usuario['situacao']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?page=usuarios_editar&id=<?php echo $usuario['id']; ?>" 
                                   class="btn btn-sm btn-info text-white" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($_SESSION['tipo_acesso'] === 'Administrador'): ?>
                                <button onclick="toggleStatus(<?php echo $usuario['id']; ?>, '<?php echo $usuario['situacao']; ?>')" 
                                        class="btn btn-sm btn-<?php 
                                            switch($usuario['situacao']) {
                                                case 'Ativo': echo 'warning'; break;
                                                case 'Inativo': echo 'success'; break;
                                                case 'Aguardando Documentação': echo 'info'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>" 
                                        title="<?php 
                                            switch($usuario['situacao']) {
                                                case 'Ativo': echo 'Inativar'; break;
                                                case 'Inativo': echo 'Ativar'; break;
                                                case 'Aguardando Documentação': echo 'Marcar como Ativo'; break;
                                                default: echo 'Alterar Status';
                                            }
                                        ?>">
                                    <i class="bi bi-toggle-<?php echo $usuario['situacao'] === 'Ativo' ? 'on' : 'off'; ?>"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <nav aria-label="Navegação de páginas">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=usuarios&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleStatus(id, currentStatus) {
    let newStatus;
    let actionText;
    
    switch(currentStatus) {
        case 'Ativo':
            newStatus = 'Inativo';
            actionText = 'inativar';
            break;
        case 'Inativo':
            newStatus = 'Ativo';
            actionText = 'ativar';
            break;
        case 'Aguardando Documentação':
            newStatus = 'Ativo';
            actionText = 'marcar como ativo';
            break;
        default:
            newStatus = 'Ativo';
            actionText = 'alterar o status';
    }
    
    Swal.fire({
        title: `Deseja ${actionText} este usuário?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('api/toggle_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    id: parseInt(id), 
                    status: newStatus 
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: data.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Erro ao atualizar status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Erro!',
                    text: error.message || 'Ocorreu um erro ao atualizar o status.',
                    icon: 'error'
                });
            });
        }
    });
}</script>
