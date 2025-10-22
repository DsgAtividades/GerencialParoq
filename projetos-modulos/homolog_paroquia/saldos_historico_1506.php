<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

header('Content-Type: text/html; charset=utf-8');
verificarPermissao('visualizar_relatorios');
// Filtros
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
$pessoa_id = isset($_GET['pessoa_id']) ? (int)$_GET['pessoa_id'] : null;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;

// Construir query
$query = "
    SELECT h.*, p.nome, p.cpf
    FROM historico_saldo h
    JOIN pessoas p ON h.id_pessoa = p.id_pessoa
    WHERE 1=1
";

if ($pessoa_id) {
    $query .= " AND h.id_pessoa = ".$pessoa_id;
}

if ($tipo) {
    $query .= " AND h.tipo_operacao = '".trim($tipo)."'";
}

if($data_inicio && $data_fim){
    $query .= " AND DATE(h.data_operacao) BETWEEN '$data_inicio' AND '$data_fim'";
}
$query .= " ORDER BY h.data_operacao DESC";
// Buscar histórico
$stmt = $pdo->query($query);
//$stmt->execute($params);
$historico = $stmt->fetchAll();

// Buscar lista de pessoas para o filtro
$stmt = $pdo->query("SELECT id_pessoa, nome FROM pessoas ORDER BY nome");
$pessoas = $stmt->fetchAll();

include 'includes/header.php';
?>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Histórico de Saldos</h1>
        <a href="saldos.php" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> Voltar para Saldos
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                           value="<?php echo $data_inicio; ?>">
                </div>
                <div class="col-md-3">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" 
                           value="<?php echo $data_fim; ?>">
                </div>
                <div class="col-md-3">
                    <label for="pessoa_id" class="form-label">Participante</label>
                    <select class="form-select" id="pessoa_id" name="pessoa_id">
                        <option value="">Todos</option>
                        <?php foreach ($pessoas as $p): ?>
                            <option value="<?php echo $p['id_pessoa']; ?>" 
                                    <?php echo $pessoa_id == $p['id_pessoa'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Todos</option>
                        <option value="credito" <?php echo $tipo === 'credito' ? 'selected' : ''; ?>>Crédito</option>
                        <option value="debito" <?php echo $tipo === 'debito' ? 'selected' : ''; ?>>Débito</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de histórico -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Participante</th>
                    <th>CPF</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($historico)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Nenhum registro encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($historico as $h): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($h['data_operacao'])); ?></td>
                            <td><?php echo htmlspecialchars($h['nome']); ?></td>
                            <td><?php echo htmlspecialchars($h['cpf']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $h['tipo_operacao'] === 'credito' ? 'success' : 'danger'; ?>">
                                    <?php echo ucwords($h['tipo_operacao']); ?>
                                </span>
                            </td>
                            <td class="<?=$h['tipo_operacao'] === 'credito' ? 'text-success' : 'text-danger'; ?>">
                                R$ <?php echo number_format(abs($h['valor']), 2, ',', '.'); ?>
                            </td>
                            <td><?php echo htmlspecialchars($h['motivo']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
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
