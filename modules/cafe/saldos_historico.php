<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

header('Content-Type: text/html; charset=utf-8');
verificarPermissao('visualizar_relatorios');
// Filtros
$data_inicio = isset($_POST['data_inicio']) ? $_POST['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_POST['data_fim']) ? $_POST['data_fim'] : date('Y-m-d');
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : null;
$atendente = isset($_POST['atendente']) ? trim($_POST['atendente']) : '';

// Buscar lista de atendentes únicos
$stmt_atendentes = $pdo->query("
    SELECT DISTINCT COALESCE(Atendente, 'N/A') as atendente 
    FROM cafe_vendas 
    ORDER BY atendente ASC
");
$atendentes_list = $stmt_atendentes->fetchAll(PDO::FETCH_COLUMN);
$atendentes = $atendentes_list ? array_unique($atendentes_list) : [];

// Construir query com prepared statements para segurança
// Consultar vendas em vez de histórico de saldo
$query = "
    SELECT 
        v.id_venda,
        v.valor_total as valor,
        COALESCE(v.Tipo_venda, 'N/A') as tipo_venda,
        v.data_venda as data_operacao,
        COALESCE(v.Atendente, 'N/A') as atendente,
        p.nome,
        p.cpf,
        CONCAT('Venda #', v.id_venda, ' (', UPPER(COALESCE(v.Tipo_venda, 'N/A')), ')') as motivo
    FROM cafe_vendas v
    JOIN cafe_pessoas p ON v.id_pessoa = p.id_pessoa
    WHERE 1=1
";

$params = [];

if ($atendente && $atendente !== '') {
    $query .= " AND COALESCE(v.Atendente, 'N/A') = :atendente";
    $params[':atendente'] = $atendente;
}

if ($tipo) {
    // Filtrar por tipo de pagamento: dinheiro, credito ou debito
    $tipo_limpo = strtolower(trim($tipo));
    if (in_array($tipo_limpo, ['dinheiro', 'credito', 'debito'])) {
        $query .= " AND v.Tipo_venda = :tipo";
        $params[':tipo'] = $tipo_limpo;
    }
}

if($data_inicio && $data_fim){
    $query .= " AND DATE(v.data_venda) BETWEEN :data_inicio AND :data_fim";
    $params[':data_inicio'] = $data_inicio;
    $params[':data_fim'] = $data_fim;
}

$query .= " ORDER BY v.data_venda DESC LIMIT 100";

// Buscar histórico usando prepared statement
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$historico = $stmt->fetchAll();

include 'includes/header.php';
?>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Histórico Vendas</h1>
        <a href="saldos.php" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> Voltar para Saldos
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="post" class="row g-3">
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
                <div class="col-md-2">
                    <label for="atendente" class="form-label">Atendente</label>
                    <select class="form-select" id="atendente" name="atendente">
                        <option value="">Todos</option>
                        <?php foreach ($atendentes as $atend): ?>
                            <option value="<?= htmlspecialchars($atend) ?>" <?php echo $atendente === $atend ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($atend) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="tipo" class="form-label">Tipo Pagamento</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Todos</option>
                        <option value="dinheiro" <?php echo $tipo === 'dinheiro' ? 'selected' : ''; ?>>Dinheiro</option>
                        <option value="credito" <?php echo $tipo === 'credito' ? 'selected' : ''; ?>>Crédito</option>
                        <option value="debito" <?php echo $tipo === 'debito' ? 'selected' : ''; ?>>Débito</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
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
                    <th>Tipo Pagamento</th>
                    <th>Atendente</th>
                    <th>Valor</th>
                    <th>Venda</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($historico)): ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhum registro encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($historico as $h): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($h['data_operacao'])); ?></td>
                            <td>
                                <?php 
                                $tipo_venda = strtolower($h['tipo_venda'] ?? '');
                                $badge_class = 'secondary';
                                if ($tipo_venda === 'dinheiro') $badge_class = 'warning';
                                elseif ($tipo_venda === 'credito') $badge_class = 'success';
                                elseif ($tipo_venda === 'debito') $badge_class = 'info';
                                ?>
                                <span class="badge bg-<?php echo $badge_class; ?>">
                                    <?php echo ucfirst($h['tipo_venda'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($h['atendente'] ?? 'N/A'); ?></td>
                            <td class="text-primary fw-bold">
                                R$ <?php echo number_format($h['valor'], 2, ',', '.'); ?>
                            </td>
                            <td>
                                <a href="vendas_detalhes.php?id=<?php echo $h['id_venda']; ?>" 
                                   class="text-decoration-none" 
                                   title="Ver detalhes da venda">
                                    <?php echo htmlspecialchars($h['motivo']); ?>
                                </a>
                            </td>
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
