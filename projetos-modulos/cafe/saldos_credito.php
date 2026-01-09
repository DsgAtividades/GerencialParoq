<?php
require_once 'config/database.php';
session_start();

// Verificar se foi fornecido um ID
if (!isset($_GET['id'])) {
    $_SESSION['erro'] = "ID do participante não fornecido.";
    header('Location: index.php');
    exit;
}

$id_pessoa = (int)$_GET['id'];

$database = new Database();
$db = $database->getConnection();

// Buscar dados da pessoa e saldo
try {
    $stmt = $db->prepare("
        SELECT p.*, COALESCE(sc.saldo, 0) as saldo, sc.id_saldo
        FROM pessoas p 
        LEFT JOIN saldos_cartao sc ON p.id_pessoa = sc.id_pessoa 
        WHERE p.id_pessoa = ?
    ");
    $stmt->execute([$id_pessoa]);
    $pessoa = $stmt->fetch();
    
    if (!$pessoa) {
        $_SESSION['erro'] = "Participante não encontrado.";
        header('Location: pessoas.php');
        exit;
    }

    // Buscar histórico de operações
    $stmt = $db->prepare("
        SELECT *
        FROM historico_saldo
        WHERE id_pessoa = ?
        ORDER BY data_operacao DESC
        LIMIT 10
    ");
    $stmt->execute([$id_pessoa]);
    $historico = $stmt->fetchAll();

} catch(PDOException $e) {
    $_SESSION['erro'] = "Erro ao buscar dados: " . $e->getMessage();
    header('Location: pessoas.php');
    exit;
}

// Processar adição de crédito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        $valor = str_replace(['.', ','], ['', '.'], $_POST['valor']);
        $valor = (float)$valor;
        $motivo = trim($_POST['motivo']);

        if ($valor <= 0) {
            throw new Exception("O valor deve ser maior que zero.");
        }

        if (empty($motivo)) {
            throw new Exception("Informe o motivo da operação.");
        }

        // Se não tem registro de saldo, criar um
        if (!$pessoa['id_saldo']) {
            $stmt = $db->prepare("INSERT INTO saldos_cartao (id_pessoa, saldo) VALUES (?, 0)");
            $stmt->execute([$id_pessoa]);
            $pessoa['id_saldo'] = $db->lastInsertId();
            $pessoa['saldo'] = 0;
        }

        // Registrar no histórico
        $stmt = $db->prepare("
            INSERT INTO historico_saldo 
            (id_pessoa, tipo_operacao, valor, saldo_anterior, saldo_novo, motivo, data_operacao)
            VALUES (?, 'credito', ?, ?, ?, ?, NOW())
        ");
        $saldo_novo = $pessoa['saldo'] + $valor;
        $stmt->execute([$id_pessoa, $valor, $pessoa['saldo'], $saldo_novo, $motivo]);

        // Atualizar saldo
        $stmt = $db->prepare("UPDATE saldos_cartao SET saldo = saldo + ? WHERE id_saldo = ?");
        $stmt->execute([$valor, $pessoa['id_saldo']]);

        $db->commit();
        $_SESSION['sucesso'] = "Crédito adicionado com sucesso!";
        header("Location: saldos_credito.php?id=" . $id_pessoa);
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $erro = $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Adicionar Crédito</h1>
        <div>
            <a href="pessoas.php" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <a href="saldos_historico.php?id=<?= $id_pessoa ?>" class="btn btn-info text-white">
                <i class="bi bi-clock-history"></i> Ver Histórico Completo
            </a>
        </div>
    </div>

    <?php if (isset($erro)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($erro) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['sucesso'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($_SESSION['sucesso']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['sucesso']); endif; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Formulário de Crédito -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Dados do Participante</h5>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Nome:</strong> <?= htmlspecialchars($pessoa['nome']) ?></p>
                            <p><strong>CPF:</strong> <?= htmlspecialchars($pessoa['cpf']) ?></p>
                            <p><strong>Telefone:</strong> <?= htmlspecialchars($pessoa['telefone']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Saldo Atual</h6>
                                <h3 class="mb-0">R$ <?= number_format($pessoa['saldo'], 2, ',', '.') ?></h3>
                            </div>
                        </div>
                    </div>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="valor" class="form-label">Valor do Crédito</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" class="form-control" id="valor" name="valor" required>
                                </div>
                                <div class="invalid-feedback">
                                    Informe o valor do crédito.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="motivo" class="form-label">Motivo</label>
                                <select class="form-select" id="motivo" name="motivo" required>
                                    <option value="">Selecione...</option>
                                    <option value="credito_inicial">Crédito Inicial</option>
                                    <option value="recarga">Recarga</option>
                                    <option value="bonus">Bônus</option>
                                    <option value="estorno">Estorno de Venda</option>
                                    <option value="outro">Outro</option>
                                </select>
                                <div class="invalid-feedback">
                                    Selecione o motivo do crédito.
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-lg"></i> Adicionar Crédito
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Histórico Recente -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Últimas Operações</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Saldo Anterior</th>
                                    <th>Saldo Novo</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($historico)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhuma operação encontrada.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($historico as $operacao): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($operacao['data_operacao'])) ?></td>
                                    <td>
                                        <?php if ($operacao['tipo_operacao'] === 'credito'): ?>
                                            <span class="badge bg-success">Crédito</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Débito</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>R$ <?= number_format($operacao['valor'], 2, ',', '.') ?></td>
                                    <td>R$ <?= number_format($operacao['saldo_anterior'], 2, ',', '.') ?></td>
                                    <td>R$ <?= number_format($operacao['saldo_novo'], 2, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($operacao['motivo']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- QR Code do Participante -->
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">QR Code do Participante</h5>
                    <div id="qrcode" class="mb-3"></div>
                    <small class="text-muted"><?= htmlspecialchars($pessoa['qrcode']) ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Máscara para valor monetário
$('#valor').mask('#.##0,00', {
    reverse: true,
    placeholder: '0,00'
});

// Gerar QR Code
new QRCode(document.getElementById("qrcode"), {
    text: "<?= $pessoa['qrcode'] ?>",
    width: 128,
    height: 128
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

// Confirmar adição de crédito
document.querySelector('form').addEventListener('submit', function(e) {
    if (!confirm('Confirma a adição de crédito para este participante?')) {
        e.preventDefault();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
