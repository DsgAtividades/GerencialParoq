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
    WHERE tipo_operacao = 'credito' and motivo NOT REGEXP 'Estorno'
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
if($data_inicio != $data_fim){
    while ($dt <= $dt_fim) {
        $periodo[] = $dt->format('Y-m-d');
        $dt->modify('+1 day');
    }
}else{
    $periodo[] = $dt->format('Y-m-d');
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
    <div class="d-flex justify-content-end mb-2 gap-2">
        <button class="btn btn-outline-success" onclick="exportarExcel()">
            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
        </button>
        <button class="btn btn-outline-danger" onclick="exportarPDF()">
            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="tabelaFechamento">
            <thead class="table-light">
                <tr>
                    <th>Data</th>
                    <?php foreach ($motivos as $motivo): 
                            if(!str_starts_with($motivo, 'Estorno - Venda')): ?>
                                <th><?= htmlspecialchars($motivo) ?></th>
                            <?php endif; ?>
                    <?php endforeach; ?>
                    <th>Total do Dia</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Array para armazenar os totais por motivo
                $totais_motivos = array();
                foreach ($motivos as $motivo) {
                    if(!str_starts_with($motivo, 'Estorno - Venda')) {
                        $totais_motivos[$motivo] = 0;
                    }
                }
                $total_geral = 0;
                foreach ($periodo as $dia): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($dia)) ?></td>
                        <?php $total_dia = 0; ?>
                        <?php foreach ($motivos as $motivo): 
                            if(!str_starts_with($motivo, 'Estorno - Venda')): ?>
                            <?php $valor = isset($fechamento[$dia][$motivo]) ? $fechamento[$dia][$motivo] : 0; ?>
                            <td class="text-end">R$ <?= number_format($valor, 2, ',', '.') ?></td>
                            <?php 
                            $total_dia += $valor; 
                            $totais_motivos[$motivo] += $valor;
                            ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <td class="text-end fw-bold">R$ <?= number_format($total_dia, 2, ',', '.') ?></td>
                        <?php $total_geral += $total_dia; ?>
                    </tr>
                <?php endforeach; ?>
                <!-- Linha de totais das colunas -->
                <tr class="table-primary">
                    <td class="fw-bold">Total</td>
                    <?php foreach ($motivos as $motivo): 
                        if(!str_starts_with($motivo, 'Estorno - Venda')): ?>
                        <td class="text-end fw-bold">R$ <?= number_format($totais_motivos[$motivo], 2, ',', '.') ?></td>
                    <?php endif; endforeach; ?>
                    <td class="text-end fw-bold">R$ <?= number_format($total_geral, 2, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
<script>
function exportarExcel() {
    const table = document.getElementById('tabelaFechamento');
    const ws = XLSX.utils.table_to_sheet(table);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Fechamento');
    XLSX.writeFile(wb, 'fechamento_caixa.xlsx');
}

function exportarPDF() {
    const doc = new window.jspdf.jsPDF('l', 'pt', 'a4');
    // Informações do topo
    const dataInicio = document.getElementById('data_inicio').value;
    const dataFim = document.getElementById('data_fim').value;
    const usuario = "<?= isset($_SESSION['usuario_nome']) ? addslashes($_SESSION['usuario_nome']) : 'Usuário' ?>";
    const agora = new Date();
    const dataExport = agora.toLocaleDateString('pt-BR');
    const horaExport = agora.toLocaleTimeString('pt-BR');
    let y = 30;
    doc.setFontSize(16);
    doc.text('Fechamento de Caixa', 40, y);
    doc.setFontSize(10);
    y += 18;
    doc.text('Período pesquisado: ' + dataInicio + ' até ' + dataFim, 40, y);
    y += 14;
    doc.text('Usuário: ' + usuario, 40, y);
    y += 14;
    doc.text('Data da exportação: ' + dataExport + '   Hora: ' + horaExport, 40, y);
    y += 10;
    doc.autoTable({
        html: '#tabelaFechamento',
        startY: y + 10,
        styles: { fontSize: 8 },
        headStyles: { fillColor: [220, 53, 69] },
        theme: 'grid'
    });
    doc.save('fechamento_caixa.pdf');
}

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