<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('gerenciar_dashboard');

// Filtros de data
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d');
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');

// Buscar motivos distintos
$stmt = $pdo->query("SELECT DISTINCT motivo FROM historico_saldo WHERE tipo_operacao = 'credito'");
$motivos = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Consulta agrupada por dia e motivo
$stmt = $pdo->prepare("
    SELECT DATE(data_operacao) as dia, motivo, SUM(valor) as total
    FROM historico_saldo
    WHERE tipo_operacao = 'credito'
      AND DATE(data_operacao) BETWEEN ? AND ?
    GROUP BY dia, motivo
    ORDER BY dia DESC, motivo
");
$stmt->execute([$data_inicio, $data_fim]);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar dados para exibição
$fechamento = [];
foreach ($dados as $row) {
    $fechamento[$row['dia']][$row['motivo']] = $row['total'];
}

// Buscar todos os dias do período
$periodo = [];
$dt = new DateTime($data_inicio);
$dt_fim = new DateTime($data_fim);
while ($dt <= $dt_fim) {
    $periodo[] = $dt->format('Y-m-d');
    $dt->modify('+1 day');
}

include 'includes/header.php';
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Fechamento de Caixa</h1>
        <a href="saldos.php" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-5">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>">
                </div>
                <div class="col-md-5">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Data</th>
                    <?php foreach ($motivos as $motivo): ?>
                        <th><?= htmlspecialchars($motivo) ?></th>
                    <?php endforeach; ?>
                    <th>Total do Dia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($periodo as $dia): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($dia)) ?></td>
                        <?php $total_dia = 0; ?>
                        <?php foreach ($motivos as $motivo): ?>
                            <?php $valor = isset($fechamento[$dia][$motivo]) ? $fechamento[$dia][$motivo] : 0; ?>
                            <td class="text-end">R$ <?= number_format($valor, 2, ',', '.') ?></td>
                            <?php $total_dia += $valor; ?>
                        <?php endforeach; ?>
                        <td class="text-end fw-bold">R$ <?= number_format($total_dia, 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const inicio = new Date(document.getElementById('data_inicio').value);
    const fim = new Date(document.getElementById('data_fim').value);
    if (fim < inicio) {
        e.preventDefault();
        alert('A data final não pode ser anterior à data inicial.');
    }
});
</script>
<?php include 'includes/footer.php'; ?> 