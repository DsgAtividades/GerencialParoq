<?php
// Inclui o arquivo de configuração do banco de dados
require_once __DIR__ . '/../config/database.php';

// Buscar membros da equipe pastoral
$stmt = $pdo->query("SELECT * FROM equipe_pastoral ORDER BY nome");
$membros = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Equipe Pastoral</h2>
        <a href="index.php?page=equipe_pastoral_novo" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Novo Membro
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            $message = '';
            switch ($_GET['success']) {
                case '1':
                    $message = 'Membro cadastrado com sucesso!';
                    break;
                case '2':
                    $message = 'Membro atualizado com sucesso!';
                    break;
                case '3':
                    $message = 'Membro excluído com sucesso!';
                    break;
            }
            echo $message;
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Função</th>
                            <th>Telefone</th>
                            <th>Email</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($membros as $membro): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($membro['nome']); ?></td>
                                <td><?php echo htmlspecialchars($membro['funcao']); ?></td>
                                <td><?php echo htmlspecialchars($membro['telefone']); ?></td>
                                <td><?php echo htmlspecialchars($membro['email']); ?></td>
                                <td>
                                    <a href="index.php?page=equipe_pastoral_editar&id=<?php echo $membro['id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirmarExclusao(<?php echo $membro['id']; ?>, '<?php echo addslashes($membro['nome']); ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir o membro <strong id="membroNome"></strong> da equipe pastoral? Esta ação não pode ser desfeita.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDelete">Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Resposta -->
<div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">Mensagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="responseMessage">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
// Inicializa os modais do Bootstrap
const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));

let membroIdToDelete = null;

function confirmarExclusao(id, nome) {
    membroIdToDelete = id;
    document.getElementById('membroNome').textContent = nome;
    confirmModal.show();
}

document.getElementById('btnConfirmDelete').addEventListener('click', function() {
    if (membroIdToDelete === null) return;
    
    fetch('api/delete_membro.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: membroIdToDelete
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na requisição');
        }
        return response.json();
    })
    .then(data => {
        confirmModal.hide();
        const responseMessage = document.getElementById('responseMessage');
        
        if (data.success) {
            responseMessage.textContent = 'Membro excluído com sucesso!';
            responseModal.show();
            
            // Adiciona evento para recarregar após fechar o modal
            document.getElementById('responseModal').addEventListener('hidden.bs.modal', function handler() {
                window.location.href = 'index.php?page=equipe_pastoral&success=3';
                this.removeEventListener('hidden.bs.modal', handler);
            });
        } else {
            responseMessage.textContent = 'Erro ao excluir membro: ' + data.message;
            responseModal.show();
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        confirmModal.hide();
        const responseMessage = document.getElementById('responseMessage');
        responseMessage.textContent = 'Erro ao excluir membro. Por favor, tente novamente.';
        responseModal.show();
    });
});
</script> 