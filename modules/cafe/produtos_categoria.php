<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarLogin();
if (!temPermissao('gerenciar_dashboard')) {
    http_response_code(403);
    echo 'Acesso negado.';
    exit;
}

$categoria_id = $_GET['categoria_id'] ?? '';
$data_inicial = $_GET['data_inicial'] ?? '';
$data_final = $_GET['data_final'] ?? '';

if (!$categoria_id) {
    echo '<div class="alert alert-warning">Categoria não informada.</div>';
    exit;
}

// Monta filtro de data
$where = ['pd.categoria_id = :categoria_id', '(ve.estornada IS NULL OR ve.estornada = 0)'];
$params = [':categoria_id' => $categoria_id];
if ($data_inicial) {
  $where[] = 'date(ve.data_venda) >= :data_inicial';
  $params[':data_inicial'] = $data_inicial;
}
if ($data_final) {
  $where[] = 'date(ve.data_venda) <= :data_final';
  $params[':data_final'] = $data_final;
}
$where_sql = 'WHERE ' . implode(' AND ', $where);

$sql = "
    SELECT pd.nome_produto, pd.preco, pd.estoque, 
           SUM(iv.quantidade) as quantidade_vendida,
           SUM(iv.quantidade * iv.valor_unitario) as valor_vendido
    FROM cafe_itens_venda iv
    INNER JOIN cafe_vendas ve ON ve.id_venda = iv.id_venda
    INNER JOIN cafe_produtos pd ON pd.id = iv.id_produto
    $where_sql
    GROUP BY pd.id, pd.nome_produto, pd.preco, pd.estoque
    ORDER BY valor_vendido DESC, pd.nome_produto
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll();

if (!$produtos) {
    echo '<div class="alert alert-info">Nenhum produto vendido nesta categoria no período.</div>';
    exit;
}
$total_categoria = 0;

// Nova consulta para produtos vendidos (desconsiderando estornados) - AJUSTADA
$sqlVendidos = "
    SELECT 
        pd.nome_produto, 
        pd.preco, 
        SUM(iv.quantidade) as quantidade_vendida, 
        SUM(iv.quantidade * iv.valor_unitario) as total_vendido
    FROM cafe_itens_venda iv
    INNER JOIN cafe_vendas ve ON ve.id_venda = iv.id_venda
    INNER JOIN cafe_produtos pd ON pd.id = iv.id_produto
    WHERE (ve.estornada IS NULL OR ve.estornada = 0)
    GROUP BY pd.id, pd.nome_produto, pd.preco
    ORDER BY quantidade_vendida DESC, pd.nome_produto
";
$stmtVendidos = $pdo->prepare($sqlVendidos);
$stmtVendidos->execute();
$produtosVendidos = $stmtVendidos->fetchAll();
?>
<div class="table-responsive">
<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>Produto</th>
            <th>Preço Unitário</th>
            <th>Quantidade Vendida</th>
            <th>Total Vendido</th>
            <th>Estoque Atual</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($produtos as $produto): $total_categoria += $produto['valor_vendido']; ?>
        <tr>
            <td><?= htmlspecialchars($produto['nome_produto']) ?></td>
            <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
            <td><?= (int)$produto['quantidade_vendida'] ?></td>
            <td>R$ <?= number_format($produto['valor_vendido'], 2, ',', '.') ?></td>
            <td><?= (int)$produto['estoque'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr class="table-light">
            <th colspan="3">Total da Categoria</th>
            <th>R$ <?= number_format($total_categoria, 2, ',', '.') ?></th>
        </tr>
    </tfoot>
</table>
</div>

<!-- Modal de Produtos Vendidos -->
<div class="modal fade" id="modalProdutosVendidos" tabindex="-1" aria-labelledby="modalProdutosVendidosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalProdutosVendidosLabel">Produtos Vendidos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Produto</th>
                <th>Quantidade Vendida</th>
                <th>Preço Unitário</th>
                <th>Total Vendido</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($produtosVendidos)): ?>
                <tr><td colspan="4" class="text-center text-muted">Nenhum produto vendido.</td></tr>
              <?php else: ?>
                <?php foreach ($produtosVendidos as $produto): ?>
                  <tr>
                    <td><?= htmlspecialchars($produto['nome_produto']) ?></td>
                    <td><?= (int)$produto['quantidade_vendida'] ?></td>
                    <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format($produto['total_vendido'], 2, ',', '.') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
function exportarTabela(tableId, filename) {
    const table = document.getElementById(tableId);
    const ws = XLSX.utils.table_to_sheet(table);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
    XLSX.writeFile(wb, filename + '.xlsx');
}
</script> 