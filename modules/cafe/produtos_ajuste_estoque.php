<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Se for uma requisição AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $db->beginTransaction();
            
            $id = (int)($_POST['id'] ?? 0);
            $quantidade = (int)($_POST['quantidade'] ?? 0);
            $tipo = trim($_POST['tipo'] ?? '');
            $motivo = trim($_POST['motivo'] ?? '');
            
            // Validações
            if ($id <= 0) {
                throw new Exception('Produto inválido');
            }
            
            if ($quantidade <= 0) {
                throw new Exception('A quantidade deve ser maior que zero');
            }
            
            if (!in_array($tipo, ['entrada', 'saida'])) {
                throw new Exception('Tipo de operação inválido');
            }
            
            if (empty($motivo)) {
                throw new Exception('Informe o motivo do ajuste');
            }
            
            // Buscar produto
            $stmt = $db->prepare("SELECT id, nome, estoque FROM cafe_produtos WHERE id = ?");
            $stmt->execute([$id]);
            $produto = $stmt->fetch();
            
            if (!$produto) {
                throw new Exception('Produto não encontrado');
            }
            
            // Calcular novo estoque
            $estoque_anterior = $produto['estoque'];
            $novo_estoque = $tipo === 'entrada' ? 
                $estoque_anterior + $quantidade : 
                $estoque_anterior - $quantidade;
            
            if ($novo_estoque < 0) {
                throw new Exception('Estoque insuficiente para esta saída');
            }
            
            // Atualizar estoque
            $stmt = $db->prepare("UPDATE cafe_produtos SET estoque = ? WHERE id = ?");
            $stmt->execute([$novo_estoque, $id]);
            
            // Registrar histórico
            $stmt = $db->prepare("
                INSERT INTO cafe_historico_estoque 
                (produto_id, tipo, quantidade, estoque_anterior, estoque_atual, motivo, data_operacao) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $id,
                $tipo,
                $quantidade,
                $estoque_anterior,
                $novo_estoque,
                $motivo
            ]);
            
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Estoque atualizado com sucesso',
                'data' => [
                    'produto' => $produto['nome'],
                    'estoque_anterior' => $estoque_anterior,
                    'estoque_atual' => $novo_estoque,
                    'tipo' => $tipo,
                    'quantidade' => $quantidade
                ]
            ]);
            
        } catch (Exception $e) {
            $db->rollBack();
            
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Método não permitido'
        ]);
    }
    exit;
}

// Se não for AJAX, buscar dados do produto
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $db->prepare("
        SELECT p.*, c.nome as categoria_nome, c.icone as categoria_icone 
        FROM cafe_produtos p
        LEFT JOIN cafe_categorias c ON p.categoria_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $produto = $stmt->fetch();
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Ajuste de Estoque</h1>
        <a href="produtos.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form id="formAjuste" class="needs-validation" novalidate>
                        <?php if (isset($produto)): ?>
                            <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Produto</label>
                                <div class="form-control-plaintext">
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($produto['categoria_icone']): ?>
                                            <i class="bi bi-<?= htmlspecialchars($produto['categoria_icone']) ?>"></i>
                                        <?php endif; ?>
                                        <div>
                                            <?= htmlspecialchars($produto['nome']) ?>
                                            <small class="text-muted d-block">
                                                <?= htmlspecialchars($produto['categoria_nome']) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label for="id" class="form-label">Produto</label>
                                <select class="form-select" id="id" name="id" required>
                                    <option value="">Selecione...</option>
                                    <?php
                                    $stmt = $db->query("
                                        SELECT p.id, p.nome, p.estoque, c.nome as categoria_nome, c.icone as categoria_icone
                                        FROM cafe_produtos p
                                        LEFT JOIN cafe_categorias c ON p.categoria_id = c.id
                                        ORDER BY c.ordem, c.nome, p.nome
                                    ");
                                    $produtos = $stmt->fetchAll();
                                    
                                    foreach ($produtos as $p):
                                    ?>
                                        <option value="<?= $p['id'] ?>">
                                            <?= htmlspecialchars($p['nome']) ?> 
                                            (<?= htmlspecialchars($p['categoria_nome']) ?>) - 
                                            Estoque: <?= $p['estoque'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor, selecione um produto.
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo de Ajuste</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="tipo" id="tipo_entrada" value="entrada" required>
                                    <label class="btn btn-outline-success" for="tipo_entrada">
                                        <i class="bi bi-plus-lg"></i> Entrada
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="tipo" id="tipo_saida" value="saida" required>
                                    <label class="btn btn-outline-danger" for="tipo_saida">
                                        <i class="bi bi-dash-lg"></i> Saída
                                    </label>
                                </div>
                                <div class="invalid-feedback">
                                    Selecione o tipo de ajuste
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="quantidade" class="form-label">Quantidade</label>
                                <input type="number" class="form-control" id="quantidade" name="quantidade" required min="1">
                                <div class="invalid-feedback">
                                    Informe uma quantidade válida
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo</label>
                            <textarea class="form-control" id="motivo" name="motivo" required rows="2"></textarea>
                            <div class="invalid-feedback">
                                Informe o motivo do ajuste
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Confirmar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Últimos Ajustes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <tbody>
                                <?php
                                $stmt = $db->query("
                                    SELECT h.*, p.nome as produto_nome
                                    FROM cafe_historico_estoque h
                                    JOIN cafe_produtos p ON h.produto_id = p.id
                                    ORDER BY h.data_registro DESC
                                    LIMIT 5
                                ");
                                $historico = $stmt->fetchAll();
                                
                                if (empty($historico)): ?>
                                    <tr>
                                        <td class="text-center text-muted py-3">
                                            Nenhum ajuste registrado
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($historico as $registro): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle p-1 <?= $registro['tipo'] === 'entrada' ? 'bg-success' : 'bg-danger' ?> bg-opacity-10">
                                                        <i class="bi bi-<?= $registro['tipo'] === 'entrada' ? 'plus' : 'dash' ?> text-<?= $registro['tipo'] === 'entrada' ? 'success' : 'danger' ?>"></i>
                                                    </div>
                                                    <div>
                                                        <small class="d-block text-muted">
                                                            <?= date('d/m/Y H:i', strtotime($registro['data_registro'])) ?>
                                                        </small>
                                                        <div class="small">
                                                            <?= htmlspecialchars($registro['produto_nome']) ?>
                                                            <span class="text-<?= $registro['tipo'] === 'entrada' ? 'success' : 'danger' ?>">
                                                                <?= $registro['tipo'] === 'entrada' ? '+' : '-' ?><?= $registro['quantidade'] ?>
                                                            </span>
                                                        </div>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars($registro['motivo']) ?>
                                                        </small>
                                                    </div>
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
    </div>
</div>

<script>
document.getElementById('formAjuste').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!this.checkValidity()) {
        e.stopPropagation();
        this.classList.add('was-validated');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('produtos_ajuste_estoque.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'produtos_estoque.php?success=1';
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        alert('Erro ao processar requisição');
        console.error(error);
    });
});
</script>

<?php include 'includes/footer.php'; ?>
