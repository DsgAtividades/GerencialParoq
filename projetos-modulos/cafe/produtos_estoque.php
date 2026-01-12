<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

//$message = '';
//$error = '';
verificarPermissao('produtos_estoque');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: produtos.php');
    exit;
}

// Buscar produto
$stmt = $pdo->prepare("SELECT * FROM cafe_produtos WHERE id = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header('Location: produtos.php');
    exit;
}

// Buscar histórico de movimentações
$stmt = $pdo->prepare("
    SELECT * FROM cafe_historico_estoque 
    WHERE id_produto = ? 
    ORDER BY data_operacao DESC 
    LIMIT 50
");
$stmt->execute([$id]);
$movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar o formulário de ajuste
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $quantidade = (int)$_POST['quantidade'];
        $motivo = trim($_POST['motivo']);
        
        if (empty($motivo)) {
            throw new Exception("O motivo do ajuste é obrigatório");
        }

        // Determinar o tipo de operação
        $tipo = $quantidade >= 0 ? 'entrada' : 'saida';
        $quantidade_abs = abs($quantidade);

        // Iniciar transação
        $pdo->beginTransaction();

        // Registrar no histórico
        $stmt = $pdo->prepare("
            INSERT INTO cafe_historico_estoque 
            (id_produto, tipo_operacao, quantidade, quantidade_anterior, motivo, data_operacao) 
            VALUES (?, ?, ?, ?, ?, now())
        ");
        
        $estoque_anterior = $produto['estoque'];
        $estoque_atual = $estoque_anterior + $quantidade;
        
        $stmt->execute([
            $id,
            $tipo,
            $estoque_atual,
            $estoque_anterior,
            $motivo
            
        ]);

        // Atualizar estoque do produto
        $stmt = $pdo->prepare("
            UPDATE cafe_produtos 
            SET estoque = estoque + ? 
            WHERE id = ?
        ");
        $stmt->execute([$quantidade, $id]);

        $pdo->commit();
        header("Location: produtos_estoque.php?id=$id&success=1");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Ajuste de Estoque - <?= htmlspecialchars($produto['nome_produto']) ?></h1>
        <a href="produtos.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Estoque atualizado com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Informações do Produto -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header py-2">
                    <h5 class="card-title h6 mb-0">Informações do Produto</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small">Nome</label>
                        <div class="fw-bold"><?= htmlspecialchars($produto['nome_produto']) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Estoque Atual</label>
                        <div class="fw-bold">
                            <span class="badge bg-<?= $produto['estoque'] > 0 ? 'success' : 'danger' ?>">
                                <?= $produto['estoque'] ?> unidades
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="form-label small">Preço</label>
                        <div class="fw-bold">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Ajuste -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header py-2">
                    <h5 class="card-title h6 mb-0">Ajustar Estoque</h5>
                </div>
                <div class="card-body">
                    <form method="post" id="formAjuste">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="quantidade" class="form-label">Quantidade</label>
                                <input type="number" class="form-control" id="quantidade" name="quantidade" required>
                                <div class="form-text">Use números positivos para entrada e negativos para saída.</div>
                            </div>
                            <div class="col-md-12">
                                <label for="motivo" class="form-label">Motivo do Ajuste</label>
                                <textarea class="form-control" id="motivo" name="motivo" rows="3" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg"></i> Confirmar Ajuste
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Histórico -->
            <div class="card">
                <div class="card-header py-2">
                    <h5 class="card-title h6 mb-0">Histórico de Movimentações</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Quantidade</th>
                                    <th>Estoque</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($movimentacoes)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-3">
                                            Nenhuma movimentação registrada.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($movimentacoes as $mov): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($mov['data_operacao'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $mov['tipo_operacao'] === 'entrada' ? 'success' : 'danger' ?>">
                                                    <?= ucfirst($mov['tipo_operacao']) ?>
                                                </span>
                                            </td>
                                            <td><?= $mov['quantidade'] ?></td>
                                            <td>
                                                <small class="text-muted">
                                                <?= $mov['quantidade'] ?> →
                                                </small>
                                                <?= $mov['quantidade_anterior'] ?>
                                            </td>
                                            <td><?= htmlspecialchars($mov['motivo']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
