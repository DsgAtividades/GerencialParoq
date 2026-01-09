<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';


verificarPermissao('produtos_incluir');

$message = '';
$error = '';

// Buscar cartões disponíveis
$stmt = $pdo->query("SELECT id, codigo FROM cartoes WHERE usado = FALSE ORDER BY data_geracao DESC");
$cartoes_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cartoes_disponiveis)) {
    $error = "Não há cartões disponíveis. Por favor, gere novos cartões primeiro.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validar dados
        $nome = trim($_POST['nome']);
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']); // Remove tudo exceto números
        $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);
        $cartao_id = (int)$_POST['cartao'];
        
        if (strlen($cpf) != 11) {
            throw new Exception('CPF inválido');
        }

        if (empty($cartao_id)) {
            throw new Exception('É necessário selecionar um cartão');
        }
        
        // Formatar telefone
        $telefone = strlen($telefone) == 11 ? 
            '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7) :
            '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);

        // Iniciar transação
        $pdo->beginTransaction();
        
        try {
            // Inserir pessoa
            $stmt = $pdo->prepare("INSERT INTO pessoas (nome, cpf, telefone) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $cpf, $telefone]);
            $pessoa_id = $pdo->lastInsertId();
            
            // Criar saldo inicial
            $stmt = $pdo->prepare("INSERT INTO saldos_cartao (id_pessoa, saldo) VALUES (?, 0.00)");
            $stmt->execute([$pessoa_id]);

            // Marcar cartão como usado
            $stmt = $pdo->prepare("UPDATE cartoes SET usado = TRUE, id_pessoa = ? WHERE id = ?");
            $stmt->execute([$pessoa_id, $cartao_id]);

            // Confirmar transação
            $pdo->commit();
            
            // Redirecionar para a lista
            header('Location: pessoas.php?success=1');
            exit;
            
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000 && strpos($e->getMessage(), 'uk_pessoas_cpf') !== false) {
            $error = "Este CPF já está cadastrado para outro participante. Por favor, verifique o número e tente novamente.";
        } else {
            $error = "Ocorreu um erro ao salvar os dados. Por favor, tente novamente.";
            $error = $e->getMessage();
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Novo Cadastro</h1>
        <div>
            <a href="gerar_cartoes.php" class="btn btn-success me-2">
                <i class="bi bi-upc-scan"></i> Gerar Cartões
            </a>
            <a href="pessoas.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" id="formCadastro" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" required 
                               value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>">
                        <div class="invalid-feedback">Por favor, informe o nome completo.</div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="cpf" class="form-label">CPF</label>
                        <input type="text" class="form-control" id="cpf" name="cpf" required 
                               value="<?= isset($_POST['cpf']) ? htmlspecialchars($_POST['cpf']) : '' ?>">
                        <div class="invalid-feedback">Por favor, informe um CPF válido.</div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone" required 
                               value="<?= isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : '' ?>">
                        <div class="invalid-feedback">Por favor, informe um telefone válido.</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cartao" class="form-label">Cartão</label>
                        <select class="form-select" id="cartao" name="cartao" required>
                            <option value="">Selecione um cartão...</option>
                            <?php foreach ($cartoes_disponiveis as $cartao): ?>
                                <option value="<?= $cartao['id'] ?>" <?= (isset($_POST['cartao']) && $_POST['cartao'] == $cartao['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cartao['codigo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione um cartão.</div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Inicializar máscaras
document.addEventListener('DOMContentLoaded', function() {
    IMask(document.getElementById('cpf'), {
        mask: '000.000.000-00'
    });
    
    IMask(document.getElementById('telefone'), {
        mask: '(00) 00000-0000'
    });
});

// Validação do formulário
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php include 'includes/footer.php'; ?>
