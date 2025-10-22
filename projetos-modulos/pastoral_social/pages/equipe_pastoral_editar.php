<?php
// Inclui o arquivo de configuração do banco de dados
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id'])) {
    header('Location: index.php?page=equipe_pastoral');
    exit;
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE equipe_pastoral SET 
            nome = ?,
            funcao = ?,
            telefone = ?,
            email = ?,
            observacoes = ?
            WHERE id = ?");
        
        $stmt->execute([
            $_POST['nome'],
            $_POST['funcao'],
            $_POST['telefone'],
            $_POST['email'],
            $_POST['observacoes'],
            $id
        ]);
        
        header('Location: index.php?page=equipe_pastoral&success=2');
        exit;
    } catch (PDOException $e) {
        $error = 'Erro ao atualizar membro: ' . $e->getMessage();
    }
}

// Buscar dados do membro
$stmt = $pdo->prepare("SELECT * FROM equipe_pastoral WHERE id = ?");
$stmt->execute([$id]);
$membro = $stmt->fetch();

if (!$membro) {
    header('Location: index.php?page=equipe_pastoral');
    exit;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Editar Membro da Equipe Pastoral</h2>
        <a href="index.php?page=equipe_pastoral" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label for="nome" class="form-label">Nome *</label>
                    <input type="text" class="form-control" id="nome" name="nome" 
                           value="<?php echo htmlspecialchars($membro['nome']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="funcao" class="form-label">Função *</label>
                    <input type="text" class="form-control" id="funcao" name="funcao" 
                           value="<?php echo htmlspecialchars($membro['funcao']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" 
                           value="<?php echo htmlspecialchars($membro['telefone']); ?>"
                           oninput="formatTelefone(this)" maxlength="15">
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($membro['email']); ?>">
                </div>

                <div class="col-12">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?php 
                        echo htmlspecialchars($membro['observacoes']); 
                    ?></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar
                    </button>
                    <button type="button" class="btn btn-danger" onclick="confirmarExclusao(<?php echo $id; ?>)">
                        <i class="bi bi-trash"></i> Excluir
                    </button>
                </div>
            </form>
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
                Tem certeza que deseja excluir este membro da equipe pastoral? Esta ação não pode ser desfeita.
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
function formatTelefone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 11) value = value.substr(0, 11);
    
    if (value.length > 0) {
        value = '(' + value;
        if (value.length > 3) {
            value = value.substr(0, 3) + ') ' + value.substr(3);
        }
        if (value.length > 10) {
            value = value.substr(0, 10) + '-' + value.substr(10);
        }
    }
    
    input.value = value;
}

let membroIdToDelete = null;
const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));

function confirmarExclusao(id) {
    membroIdToDelete = id;
    confirmModal.show();
}

document.getElementById('btnConfirmDelete').addEventListener('click', function() {
    if (membroIdToDelete === null) return;
    
    fetch('../api/delete_membro.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: membroIdToDelete
        })
    })
    .then(response => response.json())
    .then(data => {
        confirmModal.hide();
        const responseMessage = document.getElementById('responseMessage');
        
        if (data.success) {
            responseMessage.textContent = 'Membro excluído com sucesso!';
            responseModal.show();
            
            // Adiciona evento para redirecionar após fechar o modal
            document.getElementById('responseModal').addEventListener('hidden.bs.modal', function handler() {
                window.location.href = '../index.php?page=equipe_pastoral';
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