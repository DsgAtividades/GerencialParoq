<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

// Verifica se o usuário está logado e tem permissão
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_acesso'] != 'Administrador') {
    header('Location: ../login.php');
    exit();
}

// Verifica se foi fornecido um ID
if (!isset($_GET['id'])) {
    header('Location: index.php?page=equipe');
    exit();
}

$id = $_GET['id'];

// Busca os dados do membro
$stmt = $pdo->prepare("SELECT * FROM equipe_pastoral WHERE id = ?");
$stmt->execute([$id]);
$membro = $stmt->fetch();

if (!$membro) {
    header('Location: index.php?page=equipe');
    exit();
}

// Processa o formulário de edição
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

    $stmt = $pdo->prepare("UPDATE equipe_pastoral SET 
        nome = ?, cpf = ?, data_nascimento = ?, endereco = ?, 
        bairro = ?, cidade = ?, estado = ?, cep = ?, 
        telefone = ?, email = ?, data_ingresso = ?, 
        funcao = ?, situacao = ?, observacoes = ? 
        WHERE id = ?");
    
    if ($stmt->execute([
        $nome, $cpf, $data_nascimento, $endereco, 
        $bairro, $cidade, $estado, $cep, 
        $telefone, $email, $data_ingresso, 
        $funcao, $situacao, $observacoes, $id
    ])) {
        $_SESSION['mensagem'] = "Membro da equipe atualizado com sucesso!";
        header('Location: index.php?page=equipe');
        exit();
    } else {
        $erro = "Erro ao atualizar membro da equipe.";
    }

    // Recarrega os dados do membro após a atualização
    $stmt = $pdo->prepare("SELECT * FROM equipe_pastoral WHERE id = ?");
    $stmt->execute([$id]);
    $membro = $stmt->fetch();
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Editar Membro da Equipe</h2>
        <a href="index.php?page=equipe" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if (isset($mensagem)): ?>
        <div class="alert alert-success"><?php echo $mensagem; ?></div>
    <?php endif; ?>
    
    <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?php echo $erro; ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-4">
        <div class="row g-4">
            <!-- Dados Pessoais -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Dados Pessoais</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($membro['nome']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">CPF</label>
                            <input type="text" name="cpf" class="form-control" value="<?php echo htmlspecialchars($membro['cpf']); ?>" placeholder="000.000.000-00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" class="form-control" value="<?php echo $membro['data_nascimento']; ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contato -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Contato</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" class="form-control" value="<?php echo htmlspecialchars($membro['telefone']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($membro['email']); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Endereço</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Endereço</label>
                                <input type="text" name="endereco" class="form-control" value="<?php echo htmlspecialchars($membro['endereco']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Bairro</label>
                                <input type="text" name="bairro" class="form-control" value="<?php echo htmlspecialchars($membro['bairro']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">CEP</label>
                                <input type="text" name="cep" class="form-control" value="<?php echo htmlspecialchars($membro['cep']); ?>" placeholder="00000-000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cidade</label>
                                <input type="text" name="cidade" class="form-control" value="<?php echo htmlspecialchars($membro['cidade']); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="">Selecione...</option>
                                    <?php
                                    $estados = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
                                    foreach ($estados as $uf) {
                                        $selected = $membro['estado'] == $uf ? 'selected' : '';
                                        echo "<option value=\"$uf\" $selected>$uf</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informações Adicionais</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Data de Ingresso</label>
                                <input type="date" name="data_ingresso" class="form-control" value="<?php echo $membro['data_ingresso']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Função</label>
                                <input type="text" name="funcao" class="form-control" value="<?php echo htmlspecialchars($membro['funcao']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Situação</label>
                                <select name="situacao" class="form-select">
                                    <option value="Ativo" <?php echo $membro['situacao'] == 'Ativo' ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="Inativo" <?php echo $membro['situacao'] == 'Inativo' ? 'selected' : ''; ?>>Inativo</option>
                                    <option value="Afastado" <?php echo $membro['situacao'] == 'Afastado' ? 'selected' : ''; ?>>Afastado</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observações</label>
                                <textarea name="observacoes" class="form-control" rows="3"><?php echo htmlspecialchars($membro['observacoes']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Salvar Alterações
                </button>
                <a href="index.php?page=equipe" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
            </div>
        </div>
    </form>
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
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 