<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('gerenciar_saldos');

// Buscar todos os saldos com informações das pessoas
$query = "SELECT 
            p.*, 
            sc.saldo,
            sc.id_saldo as saldo_id,
            COALESCE(MAX(hs.data_operacao)) as ultima_atualizacao
          FROM cafe_pessoas p
          LEFT JOIN cafe_saldos_cartao sc ON p.id_pessoa = sc.id_pessoa
          LEFT JOIN cafe_historico_saldo hs ON p.id_pessoa = hs.id_pessoa
          GROUP BY p.id_pessoa
          ORDER BY p.nome";
$stmt = $pdo->query($query);
$saldos = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Gestão de Saldos</h1>
        <div>
            <button type="button" class="btn btn-success me-2" onclick="abrirLeitorQR()">
                <i class="bi bi-qr-code-scan"></i> Ler QR Code
            </button>
            <a href="saldos_historico.php" class="btn btn-info text-white">
                <i class="bi bi-clock-history"></i> Histórico
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-<?= $_SESSION['tipo_mensagem'] ?? 'info' ?> alert-dismissible fade show">
            <?= $_SESSION['mensagem'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tabelaSaldos">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th class="text-end">Saldo Atual</th>
                            <th>Última Atualização</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($saldos)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    Nenhum registro encontrado
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($saldos as $saldo): ?>
                                <tr>
                                    <td><?= htmlspecialchars($saldo['nome']) ?></td>
                                    <td><?= htmlspecialchars($saldo['cpf']) ?></td>
                                    <td class="text-end">R$ <?= number_format($saldo['saldo'] ?? 0, 2, ',', '.') ?></td>
                                    <td>
                                        <?php if ($saldo['ultima_atualizacao']): ?>
                                            <?= date('d/m/Y H:i', strtotime($saldo['ultima_atualizacao'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sem movimentação</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    onclick="abrirModalSaldo(<?= $saldo['id_pessoa'] ?>, '<?= htmlspecialchars($saldo['nome']) ?>', <?= $saldo['saldo'] ?? 0 ?>)">
                                                <i class="bi bi-cash-coin"></i> Crédito
                                            </button>
                                            <a href="saldos_historico.php?pessoa_id=<?= $saldo['id_pessoa'] ?>" 
                                               class="btn btn-sm btn-info text-white">
                                                <i class="bi bi-clock-history"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Ajuste de Saldo -->
<div class="modal fade" id="saldoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Crédito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formSaldo" action="saldos_adicionar.php" method="POST">
                    <input type="hidden" id="pessoa_id" name="pessoa_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Pessoa</label>
                        <input type="text" class="form-control" id="nome_pessoa" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Saldo Atual</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control" id="saldo_atual" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor do Crédito</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control" id="valor" name="valor" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="2" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formSaldo" class="btn btn-primary">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Formatar campo de valor como moeda
document.getElementById('valor').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = (parseInt(value) / 100).toFixed(2);
    e.target.value = value;
});

// Função para abrir modal de saldo
function abrirModalSaldo(pessoaId, nome, saldoAtual) {
    document.getElementById('pessoa_id').value = pessoaId;
    document.getElementById('nome_pessoa').value = nome;
    document.getElementById('saldo_atual').value = saldoAtual.toFixed(2);
    document.getElementById('valor').value = '';
    document.getElementById('motivo').value = '';
    
    new bootstrap.Modal(document.getElementById('saldoModal')).show();
}

// Função para abrir leitor de QR Code
function abrirLeitorQR() {
    // Implementar leitor de QR Code
    alert('Funcionalidade em desenvolvimento');
}

// DataTable
$(document).ready(function() {
    $('#tabelaSaldos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json',
        },
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: 4 }
        ]
    });
});
</script>

<?php include 'includes/footer.php'; ?>
