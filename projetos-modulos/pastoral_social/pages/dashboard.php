<?php
// Get some basic statistics
$stmt = $pdo->query("SELECT 
    COUNT(*) as total_usuarios,
    SUM(CASE WHEN situacao = 'Ativo' THEN 1 ELSE 0 END) as usuarios_ativos,
    SUM(CASE WHEN situacao = 'Inativo' THEN 1 ELSE 0 END) as usuarios_inativos
FROM users");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get users by city
$stmt = $pdo->query("SELECT cidade, COUNT(*) as total FROM users WHERE situacao = 'Ativo' GROUP BY cidade ORDER BY total DESC LIMIT 5");
$cidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h2 class="mb-4">Dashboard</h2>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total de Usuários</h5>
                    <h2 class="card-text"><?php echo $stats['total_usuarios'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-4">
                <div class="card-body">
                    <h5 class="card-title">Usuários Ativos</h5>
                    <h2 class="card-text"><?php echo $stats['usuarios_ativos'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-4">
                <div class="card-body">
                    <h5 class="card-title">Usuários Inativos</h5>
                    <h2 class="card-text"><?php echo $stats['usuarios_inativos'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Usuários por Cidade</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($cidades)): ?>
                        <div class="alert alert-info mb-0">
                            Nenhum usuário cadastrado ainda.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Cidade</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cidades as $cidade): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cidade['cidade']); ?></td>
                                        <td><?php echo $cidade['total']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="index.php?page=usuarios_novo" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Novo Usuário
                        </a>
                        <a href="index.php?page=usuarios" class="btn btn-info text-white">
                            <i class="bi bi-search"></i> Buscar Usuários
                        </a>
                        <a href="index.php?page=relatorios" class="btn btn-success">
                            <i class="bi bi-file-earmark-text"></i> Gerar Relatório
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
