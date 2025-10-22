<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarLogin();
if (!temPermissao('gerenciar_dashboard')) {
    header('Location: index.php');
    exit;
}

// Filtros
$data_inicial = $_POST['data_inicial'] ?? '';
$data_final = $_POST['data_final'] ?? '';
$categoria_id = $_POST['categoria_id'] ?? '';

// Monta WHERE dinâmico
$where = [];
$params = [];
if ($data_inicial) {
    $where[] = 'date(ve.data_venda) >= :data_inicial';
    $params[':data_inicial'] = $data_inicial;
}
if ($data_final) {
    $where[] = 'date(ve.data_venda) <= :data_final';
    $params[':data_final'] = $data_final;
}
if ($categoria_id) {
    $where[] = 'ca.id = :categoria_id';
    $params[':categoria_id'] = $categoria_id;
}
// Adiciona filtro para não contabilizar vendas estornadas
$where[] = '(ve.estornada IS NULL OR ve.estornada = 0)';
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Busca categorias para o filtro
$categorias = $pdo->query('SELECT id, nome FROM categorias ORDER BY nome')->fetchAll();

// Consulta principal
$sql = "
    SELECT ca.id as categoria_id, ca.nome, SUM(iv.quantidade * iv.valor_unitario) as total_vendas
    FROM itens_venda iv
    INNER JOIN vendas ve ON ve.id_venda = iv.id_venda
    INNER JOIN produtos pd ON pd.id = iv.id_produto
    INNER JOIN categorias ca ON ca.id = pd.categoria_id
    $where_sql
    GROUP BY ca.id, ca.nome
    ORDER BY total_vendas DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll();

include 'includes/header.php';
?>
<div class="container py-4">
    <h1>Relatório de Vendas por Categoria</h1>
    <form class="row g-3 mb-4" method="post">
        <div class="col-md-3">
            <label for="data_inicial" class="form-label">Data Inicial</label>
            <input type="date" class="form-control" id="data_inicial" name="data_inicial" value="<?= htmlspecialchars($data_inicial) ?>">
        </div>
        <div class="col-md-3">
            <label for="data_final" class="form-label">Data Final</label>
            <input type="date" class="form-control" id="data_final" name="data_final" value="<?= htmlspecialchars($data_final) ?>">
        </div>
        <div class="col-md-3">
            <label for="categoria_id" class="form-label">Categoria</label>
            <select class="form-select" id="categoria_id" name="categoria_id">
                <option value="">Todas</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoria_id == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Totais por Categoria</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Total de Vendas (R$)</th>
                            <th>Produtos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultados): ?>
                            <?php foreach ($resultados as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nome']) ?></td>
                                    <td>R$ <?= number_format($row['total_vendas'], 2, ',', '.') ?></td>
                                    <td>
                                        <button class="btn btn-outline-info btn-sm ver-produtos-btn" data-categoria-id="<?= htmlspecialchars($row['categoria_id'] ?? '') ?>" data-categoria-nome="<?= htmlspecialchars($row['nome']) ?>">
                                            <i class="bi bi-box"></i> Ver Produtos
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center">Nenhum resultado encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal Produtos -->
<div class="modal fade" id="produtosModal" tabindex="-1" aria-labelledby="produtosModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="produtosModalLabel">Produtos da Categoria</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="produtosModalBody">
        Carregando...
      </div>
    </div>
  </div>
</div>
<script>
$(function() {
    $('.ver-produtos-btn').on('click', function() {
        var categoriaId = $(this).data('categoria-id');
        var categoriaNome = $(this).data('categoria-nome');
        var dataInicial = $('#data_inicial').val();
        var dataFinal = $('#data_final').val();
        $('#produtosModalLabel').text('Produtos da Categoria: ' + categoriaNome);
        $('#produtosModalBody').html('Carregando...');
        $('#produtosModal').modal('show');
        $.get('produtos_categoria.php', {
            categoria_id: categoriaId,
            data_inicial: dataInicial,
            data_final: dataFinal
        }, function(data) {
            $('#produtosModalBody').html(data);
        });
    });
});
</script>
<?php include 'includes/footer.php'; ?> 