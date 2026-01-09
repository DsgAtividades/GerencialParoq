<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';


verificarPermissao('pessoas_editar');

// Verificar se foi fornecido um ID
if (!isset($_GET['id'])) {
    $_SESSION['erro'] = "ID do participante não fornecido.";
    header('Location: pessoas.php');
    exit;
}

$id_pessoa = (int)$_GET['id'];



// Buscar cartões disponíveis
$stmt = $pdo->query("SELECT codigo FROM cartoes WHERE usado = FALSE ORDER BY data_geracao DESC");
$cartoes_disponiveis = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Buscar dados da pessoa
try {
    $stmt = $pdo->prepare("
        SELECT p.*, COALESCE(sc.saldo, 0.00) as saldo, c.codigo as qrcode 
        FROM pessoas p 
        LEFT JOIN saldos_cartao sc ON p.id_pessoa = sc.id_pessoa 
        left JOIN cartoes c on c.id_pessoa = p.id_pessoa
        WHERE p.id_pessoa = ?
    ");
    $stmt->execute([$id_pessoa]);
    $pessoa = $stmt->fetch();
    
    if (!$pessoa) {
        $_SESSION['erro'] = "Participante não encontrado.";
        header('Location: pessoas.php');
        exit;
    }
} catch(PDOException $e) {
    $_SESSION['erro'] = "Erro ao buscar dados: " . $e->getMessage();
    header('Location: pessoas.php');
    exit;
}

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar dados
        $nome = trim($_POST['nome'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $novo_cartao = trim($_POST['cartao'] ?? '');
        
        if (empty($nome) || empty($cpf)) {
            throw new Exception("Nome e CPF são obrigatórios.");
        }
        
        // Verificar se o CPF já existe (excluindo o registro atual)
        $stmt = $pdo->prepare("SELECT id_pessoa FROM pessoas WHERE cpf = ? AND id_pessoa != ?");
        $stmt->execute([$cpf, $id_pessoa]);
        if ($stmt->fetch()) {
            throw new Exception("Este CPF já está cadastrado para outra pessoa.");
        }

        // Iniciar transação
        $pdo->beginTransaction();
        
        // Se está trocando o cartão
        if (!empty($novo_cartao) && $novo_cartao !== $pessoa['qrcode']) {
            // Marcar cartão antigo como não usado
            $stmt = $pdo->prepare("UPDATE cartoes SET usado = FALSE, id_pessoa = NULL WHERE codigo = ?");
            $stmt->execute([$pessoa['qrcode']]);
            
            // Marcar novo cartão como usado
            $stmt = $pdo->prepare("UPDATE cartoes SET usado = TRUE, id_pessoa = ? WHERE codigo = ?");
            $stmt->execute([$id_pessoa, $novo_cartao]);
            
            // Atualizar pessoa com novo cartão
            $stmt = $pdo->prepare("
                UPDATE pessoas 
                SET nome = ?, cpf = ?, telefone = ?
                WHERE id_pessoa = ?
            ");
            $stmt->execute([$nome, $cpf, $telefone, $id_pessoa]);
            
            $novo_saldo = number_format($pessoa['saldo'] + (float)-2.00, 2);
            $novo_saldo = str_replace(',', '',$novo_saldo);
            $stmt = $pdo->prepare("
                UPDATE saldos_cartao SET saldo = ? where id_pessoa = ?  
            ");
            $stmt->execute([$novo_saldo, $id_pessoa]);

            $stmt = $pdo->prepare("
                INSERT INTO historico_saldo 
                (id_pessoa, valor, tipo_operacao, saldo_anterior, saldo_novo, motivo, data_operacao)
                VALUES (?, -2.00, 'custo cartao', ?, ?, 'Custo Inicial Cartao', NOW())
                ");
                $stmt->execute([$id_pessoa, $pessoa['saldo'], $novo_saldo]);


        } else {
            // Atualizar apenas dados básicos
            $stmt = $pdo->prepare("
                UPDATE pessoas 
                SET nome = ?, cpf = ?, telefone = ?
                WHERE id_pessoa = ?
            ");
            $stmt->execute([$nome, $cpf, $telefone, $id_pessoa]);
        }
        
        // Confirmar transação
        $pdo->commit();
        
        $_SESSION['sucesso'] = "Dados do participante atualizados com sucesso!";
        header('Location: pessoas.php');
        exit;
        
    } catch (PDOException $e) {
        // Reverter transação em caso de erro
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Tratar erro de CPF duplicado
        if ($e->getCode() == 23000 && strpos($e->getMessage(), 'uk_pessoas_cpf') !== false) {
            $erro = "Este CPF já está cadastrado para outro participante. Por favor, verifique o número e tente novamente.";
        } else {
            $erro = "Ocorreu um erro ao salvar os dados. Por favor, tente novamente. " . $pessoa['saldo']. " - ". $e->getMessage();
        }
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erro = $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Editar Participante</h1>
        <div>
            <!-- <a href="gerar_cartoes.php" class="btn btn-success me-2">
                <i class="bi bi-upc-scan"></i> Gerar Cartões
            </a> -->
            <a href="pessoas.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php if (isset($erro)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($erro) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" 
                               value="<?= htmlspecialchars($pessoa['nome']) ?>" required>
                        <div class="invalid-feedback">
                            Por favor, informe o nome.
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="cpf" class="form-label">CPF</label>
                        <input type="text" class="form-control" id="cpf" name="cpf" maxlength="11"
                               value="<?= htmlspecialchars($pessoa['cpf']) ?>" required>
                        <div class="invalid-feedback">
                            Por favor, informe um CPF válido.
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone"
                               value="<?= htmlspecialchars($pessoa['telefone']) ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Cartão de Acesso</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="hidden" id="cartao" name="cartao" value="<?= htmlspecialchars($pessoa['qrcode']) ?>">
                            <button type="button" class="btn btn-outline-secondary" id="btnLerQRCode">
                                <i class="bi bi-qr-code-scan"></i> Ler QR Code do Cartão
                            </button>
                            <span id="cartaoStatus" class="ms-2">
                                <?php if($pessoa['qrcode']): ?>
                                    <span class="badge bg-primary">Atual: <?= htmlspecialchars($pessoa['qrcode']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nenhum cartão vinculado</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="form-text">
                            Use o leitor para trocar o cartão. O cartão atual será desvinculado.
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Saldo Atual</h6>
                                <h4 class="mb-0">R$ <?= number_format($pessoa['saldo'], 2, ',', '.') ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal QR Code -->
<div class="modal fade" id="qrcodeModal" tabindex="-1" aria-labelledby="qrcodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrcodeModalLabel">Ler QR Code do Cartão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-center">
                <div id="reader" style="width: 100%; max-width: 350px; margin: 0 auto;"></div>
                <div id="qrStatusMsg" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
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

// Máscara para CPF
document.getElementById('cpf').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    e.target.value = value;
});

// Máscara para telefone
document.getElementById('telefone').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    e.target.value = value;
});

// QR Code - Troca de Cartão
let scanner = null;
let scanning = false;

const btnLerQRCode = document.getElementById('btnLerQRCode');
const modalElement = document.getElementById('qrcodeModal');
const cartaoInput = document.getElementById('cartao');
const cartaoStatus = document.getElementById('cartaoStatus');
const qrStatusMsg = document.getElementById('qrStatusMsg');

btnLerQRCode.addEventListener('click', function() {
    if (scanner) scanner.clear();
    qrStatusMsg.textContent = '';
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    scanner = new Html5QrcodeScanner(
        "reader",
        { fps: 10, qrbox: { width: 250, height: 250 } },
        false
    );
    scanner.render(onScanSuccess, onScanFailure);
    scanning = true;
    // Limpa scanner ao fechar modal
    modalElement.addEventListener('hidden.bs.modal', () => {
        if (scanner) scanner.clear();
    }, { once: true });
});

function onScanSuccess(decodedText) {
    if (!scanning) return;
    scanning = false;
    scanner.clear();
    const modal = bootstrap.Modal.getInstance(modalElement);
    modal.hide();
    qrStatusMsg.textContent = '';
    // Buscar cartão na base
    fetch('api/buscar_participante.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ codigo: decodedText })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.participante && data.participante.cartao_codigo === decodedText) {
            // Verifica se o cartão está disponível (não está em uso)
            if (data.participante.id == <?= $id_pessoa ?>) {
                cartaoInput.value = decodedText;
                cartaoStatus.innerHTML = `<span class='badge bg-primary'>Atual: ${decodedText}</span>`;
                showQrStatus('Cartão já está vinculado a este participante.', 'info');
            } else {
                showQrStatus('Este cartão já está vinculado a outro participante!', 'danger');
            }
        } else if (!data.success) {
            // Cartão não encontrado, pode estar disponível
            cartaoInput.value = decodedText;
            cartaoStatus.innerHTML = `<span class='badge bg-success'>Novo: ${decodedText}</span>`;
            showQrStatus('Cartão disponível para troca.', 'success');
        } else {
            showQrStatus('Erro ao validar cartão.', 'danger');
        }
    })
    .catch(() => {
        showQrStatus('Erro ao buscar cartão.', 'danger');
    });
}
function onScanFailure(error) {
    // Ignorar erros de leitura
}
function showQrStatus(msg, type) {
    qrStatusMsg.innerHTML = `<div class='alert alert-${type}'>${msg}</div>`;
    setTimeout(() => { qrStatusMsg.innerHTML = ''; }, 4000);
}
</script>

<?php include 'includes/footer.php'; ?>
