<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

// Verifica se o usuário está logado e tem permissão
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_acesso'] != 'Administrador') {
    header('Location: ../login.php');
    exit();
}

// Processa o formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $data_nascimento = $_POST['data_nascimento'];
    $endereco = $_POST['endereco'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $cep = $_POST['cep'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $data_ingresso = $_POST['data_ingresso'];
    $funcao = $_POST['funcao'];
    $situacao = $_POST['situacao'];
    $observacoes = $_POST['observacoes'];

    $stmt = $pdo->prepare("INSERT INTO equipe_pastoral (nome, cpf, data_nascimento, endereco, bairro, cidade, estado, cep, telefone, email, data_ingresso, funcao, situacao, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$nome, $cpf, $data_nascimento, $endereco, $bairro, $cidade, $estado, $cep, $telefone, $email, $data_ingresso, $funcao, $situacao, $observacoes])) {
        $mensagem = "Membro da equipe cadastrado com sucesso!";
    } else {
        $erro = "Erro ao cadastrar membro da equipe.";
    }
}

// Busca todos os membros da equipe
$stmt = $pdo->query("SELECT * FROM equipe_pastoral ORDER BY nome");
$membros = $stmt->fetchAll();

// Verifica se existe mensagem na sessão
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']); // Remove a mensagem da sessão após exibir
}

// Mostrar mensagens de sucesso/erro
if (isset($_SESSION['mensagem'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '" . ($_SESSION['mensagem_tipo'] === 'success' ? 'Sucesso!' : 'Erro!') . "',
                text: '" . addslashes($_SESSION['mensagem']) . "',
                icon: '" . $_SESSION['mensagem_tipo'] . "',
                confirmButtonText: 'OK',
                timer: 3000,
                timerProgressBar: true
            });
        });
    </script>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['mensagem_tipo']);
}

if (isset($_SESSION['erro'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Erro!',
                text: '" . addslashes($_SESSION['erro']) . "',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    </script>";
    unset($_SESSION['erro']);
}
?>

<div class="container">
    <h2 class="mb-4">Cadastro da Equipe Pastoral</h2>
    
    <?php if (isset($mensagem)): ?>
        <div class="alert alert-success"><?php echo $mensagem; ?></div>
    <?php endif; ?>
    
    <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?php echo $erro; ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-4">
        <div class="row g-4">
            <!-- Dados Pessoais -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Dados Pessoais</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Nome Completo</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CPF</label>
                                <input type="text" name="cpf" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Data de Nascimento</label>
                                <input type="date" name="data_nascimento" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="telefone" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Endereço</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">CEP</label>
                                <input type="text" name="cep" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Endereço</label>
                                <input type="text" name="endereco" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bairro</label>
                                <input type="text" name="bairro" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cidade</label>
                                <input type="text" name="cidade" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select" required>
                                    <option value="">Selecione...</option>
                                    <option value="AC">AC</option>
                                    <option value="AL">AL</option>
                                    <option value="AP">AP</option>
                                    <option value="AM">AM</option>
                                    <option value="BA">BA</option>
                                    <option value="CE">CE</option>
                                    <option value="DF">DF</option>
                                    <option value="ES">ES</option>
                                    <option value="GO">GO</option>
                                    <option value="MA">MA</option>
                                    <option value="MT">MT</option>
                                    <option value="MS">MS</option>
                                    <option value="MG">MG</option>
                                    <option value="PA">PA</option>
                                    <option value="PB">PB</option>
                                    <option value="PR">PR</option>
                                    <option value="PE">PE</option>
                                    <option value="PI">PI</option>
                                    <option value="RJ">RJ</option>
                                    <option value="RN">RN</option>
                                    <option value="RS">RS</option>
                                    <option value="RO">RO</option>
                                    <option value="RR">RR</option>
                                    <option value="SC">SC</option>
                                    <option value="SP">SP</option>
                                    <option value="SE">SE</option>
                                    <option value="TO">TO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informações Adicionais</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Data de Ingresso</label>
                                <input type="date" name="data_ingresso" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Função</label>
                                <input type="text" name="funcao" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Situação</label>
                                <select name="situacao" class="form-select">
                                    <option value="Ativo">Ativo</option>
                                    <option value="Inativo">Inativo</option>
                                    <option value="Afastado">Afastado</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observações</label>
                                <textarea name="observacoes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Cadastrar Membro
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Limpar
                </button>
            </div>
        </div>
    </form>

    <h3>Membros Cadastrados</h3>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>Email</th>
                    <th>Função</th>
                    <th>Situação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($membros as $membro): ?>
                <tr>
                    <td><?php echo htmlspecialchars($membro['nome']); ?></td>
                    <td><?php echo htmlspecialchars($membro['telefone']); ?></td>
                    <td><?php echo htmlspecialchars($membro['email']); ?></td>
                    <td><?php echo htmlspecialchars($membro['funcao']); ?></td>
                    <td><?php echo htmlspecialchars($membro['situacao']); ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#visualizarModal<?php echo $membro['id']; ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                        <a href="index.php?page=editar_membro&id=<?php echo $membro['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="#" onclick="confirmarExclusao(<?php echo $membro['id']; ?>, '<?php echo addslashes($membro['nome']); ?>')" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i>
                        </a>

                        <!-- Modal de Visualização -->
                        <div class="modal fade" id="visualizarModal<?php echo $membro['id']; ?>" tabindex="-1" aria-labelledby="visualizarModalLabel<?php echo $membro['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="visualizarModalLabel<?php echo $membro['id']; ?>">Dados do Membro</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="mb-3">Dados Pessoais</h6>
                                                <p><strong>Nome:</strong> <?php echo htmlspecialchars($membro['nome']); ?></p>
                                                <p><strong>CPF:</strong> <?php echo htmlspecialchars($membro['cpf']); ?></p>
                                                <p><strong>Data de Nascimento:</strong> <?php echo date('d/m/Y', strtotime($membro['data_nascimento'])); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="mb-3">Contato</h6>
                                                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($membro['telefone']); ?></p>
                                                <p><strong>Email:</strong> <?php echo htmlspecialchars($membro['email']); ?></p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-12">
                                                <h6 class="mb-3">Endereço</h6>
                                                <p><strong>Endereço:</strong> <?php echo htmlspecialchars($membro['endereco']); ?></p>
                                                <p><strong>Bairro:</strong> <?php echo htmlspecialchars($membro['bairro']); ?></p>
                                                <p><strong>Cidade:</strong> <?php echo htmlspecialchars($membro['cidade']); ?></p>
                                                <p><strong>Estado:</strong> <?php echo htmlspecialchars($membro['estado']); ?></p>
                                                <p><strong>CEP:</strong> <?php echo htmlspecialchars($membro['cep']); ?></p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-12">
                                                <h6 class="mb-3">Informações Adicionais</h6>
                                                <p><strong>Data de Ingresso:</strong> <?php echo date('d/m/Y', strtotime($membro['data_ingresso'])); ?></p>
                                                <p><strong>Função:</strong> <?php echo htmlspecialchars($membro['funcao']); ?></p>
                                                <p><strong>Situação:</strong> <?php echo htmlspecialchars($membro['situacao']); ?></p>
                                                <p><strong>Observações:</strong> <?php echo nl2br(htmlspecialchars($membro['observacoes'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Função para formatar CPF
function formatCPF(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    input.value = value;
}

// Função para formatar CEP
function formatCEP(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 8) value = value.slice(0, 8);
    value = value.replace(/(\d{5})(\d)/, '$1-$2');
    input.value = value;
}

// Função para formatar telefone
function formatTelefone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    if (value.length > 10) {
        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else {
        value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    }
    input.value = value;
}

// Adicionar os eventos de formatação
document.querySelectorAll('input[name="cpf"]').forEach(input => {
    input.addEventListener('input', function() {
        formatCPF(this);
    });
});

document.querySelectorAll('input[name="cep"]').forEach(input => {
    input.addEventListener('input', function() {
        formatCEP(this);
    });
});

document.querySelectorAll('input[name="telefone"]').forEach(input => {
    input.addEventListener('input', function() {
        formatTelefone(this);
    });
});

function confirmarExclusao(id, nome) {
    Swal.fire({
        title: 'Tem certeza?',
        text: `Deseja realmente excluir o membro ${nome}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?page=excluir_membro&id=${id}`;
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 