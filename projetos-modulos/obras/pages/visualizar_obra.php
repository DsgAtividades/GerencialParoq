<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/get_arquivos.php';

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: relatorios.php');
    exit;
}

$id = $_GET['id'];

// Buscar os dados da obra
$stmt = $pdo->prepare("SELECT * FROM obras_servicos WHERE id = ?");
$stmt->execute([$id]);
$obra = $stmt->fetch(PDO::FETCH_ASSOC);

// Se a obra não for encontrada, redirecionar
if (!$obra) {
    header('Location: relatorios.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Serviço - <?php echo htmlspecialchars($obra['descricao']); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include_once '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/gerencialParoquia/projetos-modulos/obras/">Início</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($obra['descricao']); ?></li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0">Detalhes do Serviço</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Informações Gerais</h5>
                        <dl class="row">
                            <dt class="col-sm-4">Descrição:</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($obra['descricao']); ?></dd>

                            <dt class="col-sm-4">Responsável:</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($obra['responsavel']); ?></dd>

                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-<?php 
                                    $statusColor = 'secondary';
                                    switch($obra['status']) {
                                        case 'Em Andamento':
                                            $statusColor = 'primary';
                                            break;
                                        case 'Concluído':
                                            $statusColor = 'success';
                                            break;
                                        case 'Pendente':
                                            $statusColor = 'warning';
                                            break;
                                        case 'Cancelado':
                                            $statusColor = 'danger';
                                            break;
                                    }
                                    echo $statusColor;
                                ?>">
                                    <?php echo htmlspecialchars($obra['status']); ?>
                                </span>
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Informações Financeiras</h5>
                        <dl class="row">
                            <dt class="col-sm-4">Valor Total:</dt>
                            <dd class="col-sm-8">R$ <?php echo number_format($obra['total'] ?? 0, 2, ',', '.'); ?></dd>

                            <dt class="col-sm-4">Valor Antecipado:</dt>
                            <dd class="col-sm-8">R$ <?php echo number_format($obra['valor_antecipado'] ?? 0, 2, ',', '.'); ?></dd>

                            <dt class="col-sm-4">Valor Faltante:</dt>
                            <dd class="col-sm-8">R$ <?php echo number_format(($obra['total'] ?? 0) - ($obra['valor_antecipado'] ?? 0), 2, ',', '.'); ?></dd>
                        </dl>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Datas</h5>
                        <dl class="row">
                            <dt class="col-sm-4">Data da Ordem de Serviço:</dt>
                            <dd class="col-sm-8"><?php echo $obra['data_ordem_servico'] ? date('d/m/Y', strtotime($obra['data_ordem_servico'])) : '-'; ?></dd>

                            <dt class="col-sm-4">Previsão de Entrega:</dt>
                            <dd class="col-sm-8"><?php echo $obra['previsao_entrega'] ? date('d/m/Y', strtotime($obra['previsao_entrega'])) : '-'; ?></dd>
                        </dl>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Documentos</h5>
                    </div>
                    
                    <!-- Comprovantes de Pagamento -->
                    <div class="col-md-4 mt-3">
                        <h6><i class="bi bi-receipt"></i> Comprovantes de Pagamento</h6>
                        <?php
                        $arquivos = getArquivosServico($pdo, $id, 'comprovante_pagamento');
                        if (!empty($arquivos)): ?>
                            <div class="list-group">
                                <?php foreach ($arquivos as $arquivo): ?>
                                    <a href="/gerencialParoquia/projetos-modulos/obras/<?php echo htmlspecialchars($arquivo['caminho_arquivo']); ?>" 
                                       target="_blank" 
                                       class="list-group-item list-group-item-action">
                                        <i class="bi bi-file-earmark-text"></i>
                                        <?php echo htmlspecialchars($arquivo['nome_arquivo']); ?>
                                        <small class="text-muted d-block">
                                            <?php echo date('d/m/Y H:i', strtotime($arquivo['data_upload'])); ?>
                                        </small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><small>Nenhum comprovante anexado</small></p>
                        <?php endif; ?>
                    </div>

                    <!-- Notas Fiscais -->
                    <div class="col-md-4 mt-3">
                        <h6><i class="bi bi-file-text"></i> Notas Fiscais</h6>
                        <?php
                        $arquivos = getArquivosServico($pdo, $id, 'nota_fiscal');
                        if (!empty($arquivos)): ?>
                            <div class="list-group">
                                <?php foreach ($arquivos as $arquivo): ?>
                                    <a href="/gerencialParoquia/projetos-modulos/obras/<?php echo htmlspecialchars($arquivo['caminho_arquivo']); ?>" 
                                       target="_blank" 
                                       class="list-group-item list-group-item-action">
                                        <i class="bi bi-file-earmark-text"></i>
                                        <?php echo htmlspecialchars($arquivo['nome_arquivo']); ?>
                                        <small class="text-muted d-block">
                                            <?php echo date('d/m/Y H:i', strtotime($arquivo['data_upload'])); ?>
                                        </small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><small>Nenhuma nota fiscal anexada</small></p>
                        <?php endif; ?>
                    </div>

                    <!-- Ordens de Serviço -->
                    <div class="col-md-4 mt-3">
                        <h6><i class="bi bi-clipboard-check"></i> Ordens de Serviço</h6>
                        <?php
                        $arquivos = getArquivosServico($pdo, $id, 'ordem_servico');
                        if (!empty($arquivos)): ?>
                            <div class="list-group">
                                <?php foreach ($arquivos as $arquivo): ?>
                                    <a href="/gerencialParoquia/projetos-modulos/obras/<?php echo htmlspecialchars($arquivo['caminho_arquivo']); ?>" 
                                       target="_blank" 
                                       class="list-group-item list-group-item-action">
                                        <i class="bi bi-file-earmark-text"></i>
                                        <?php echo htmlspecialchars($arquivo['nome_arquivo']); ?>
                                        <small class="text-muted d-block">
                                            <?php echo date('d/m/Y H:i', strtotime($arquivo['data_upload'])); ?>
                                        </small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><small>Nenhuma ordem de serviço anexada</small></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="/gerencialParoquia/projetos-modulos/obras/" class="btn btn-secondary">Voltar</a>
                <a href="/gerencialParoquia/projetos-modulos/obras/pages/editar_obra.php?id=<?php echo $obra['id']; ?>" class="btn btn-warning">Editar</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
