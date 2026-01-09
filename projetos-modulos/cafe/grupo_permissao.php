<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('gerenciar_permissoes');

$grupo_id = $_GET['id'] ?? 0;

// Verificar se o grupo existe
$stmt = $pdo->prepare("SELECT nome FROM cafe_grupos WHERE id = ?");
$stmt->execute([$grupo_id]);
$grupo = $stmt->fetch();

if (!$grupo) {
    header("Location: gerenciar_grupos.php");
    exit;
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar transação
        $pdo->beginTransaction();

        // Remover todas as permissões atuais do grupo
        $stmt = $pdo->prepare("DELETE FROM cafe_grupos_permissoes WHERE grupo_id = ?");
        $stmt->execute([$grupo_id]);

        // Inserir novas permissões
        if (isset($_POST['permissoes']) && is_array($_POST['permissoes'])) {
            $stmt = $pdo->prepare("INSERT INTO cafe_grupos_permissoes (grupo_id, permissao_id) VALUES (?, ?)");
            foreach ($_POST['permissoes'] as $permissao_id) {
                $stmt->execute([$grupo_id, $permissao_id]);
            }
        }

        $pdo->commit();
        exibirAlerta("Permissões atualizadas com sucesso!");
    } catch (PDOException $e) {
        $pdo->rollBack();
        exibirAlerta("Erro ao atualizar permissões: " . $e->getMessage(), "danger");
    }
}

// Buscar todas as permissões
$stmt = $pdo->query("SELECT id, nome, pagina FROM cafe_permissoes ORDER BY nome");
$permissoes = $stmt->fetchAll();

// Buscar permissões do grupo
$stmt = $pdo->prepare("SELECT permissao_id FROM cafe_grupos_permissoes WHERE grupo_id = ?");
$stmt->execute([$grupo_id]);
$permissoes_grupo = $stmt->fetchAll(PDO::FETCH_COLUMN);

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permissões do Grupo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Permissões do Grupo: <?= escapar($grupo['nome']) ?></h4>
                        <a href="gerenciar_grupos.php" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                    </div>
                    <div class="card-body">
                        <?php mostrarAlerta(); ?>

                        <form method="post">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;"></th>
                                            <th>Permissão</th>
                                            <th>Página</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($permissoes as $permissao): ?>
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permissoes[]" 
                                                           value="<?= $permissao['id'] ?>"
                                                           <?= in_array($permissao['id'], $permissoes_grupo) ? 'checked' : '' ?>>
                                                </div>
                                            </td>
                                            <td><?= escapar($permissao['nome']) ?></td>
                                            <td><?= escapar($permissao['pagina']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Salvar Permissões
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
