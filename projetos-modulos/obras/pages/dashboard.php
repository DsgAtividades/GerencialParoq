<?php
try {
    // Buscar média de valor por serviço
    $sql = "SELECT 
        ROUND(AVG(total), 2) as media_valor,
        ROUND(AVG(DATEDIFF(COALESCE(previsao_entrega, CURDATE()), data_ordem_servico)), 0) as media_dias_conclusao
        FROM obras_servicos
        WHERE data_ordem_servico IS NOT NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $media_estatisticas = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar estatísticas gerais
    $sql = "SELECT 
        COUNT(CASE WHEN status = 'Em Andamento' THEN 1 END) as em_andamento,
        COUNT(CASE WHEN status = 'Concluído' THEN 1 END) as concluido,
        COUNT(CASE WHEN status = 'Pendente' THEN 1 END) as pendente,
        COUNT(CASE WHEN status = 'Cancelado' THEN 1 END) as cancelado,
        SUM(total) as valor_total,
        SUM(valor_antecipado) as valor_antecipado,
        SUM(CASE WHEN status = 'Em Andamento' THEN total ELSE 0 END) as valor_andamento,
        SUM(CASE WHEN status = 'Concluído' THEN total ELSE 0 END) as valor_concluido,
        SUM(CASE WHEN status = 'Pendente' THEN total ELSE 0 END) as valor_pendente,
        SUM(CASE WHEN status = 'Cancelado' THEN total ELSE 0 END) as valor_cancelado
        FROM obras_servicos";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $estatisticas = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar serviços em andamento
    $sql = "SELECT id, descricao, responsavel, total, valor_antecipado,
           DATE_FORMAT(data_ordem_servico, '%d/%m/%Y') as data_ordem_servico, 
           DATE_FORMAT(previsao_entrega, '%d/%m/%Y') as previsao,
           (total - valor_antecipado) as valor_faltante
           FROM obras_servicos 
           WHERE status = 'Em Andamento'
           ORDER BY data_ordem_servico DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $servicos_andamento = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar serviços concluídos nos últimos 30 dias
    $sql = "SELECT id, descricao, responsavel, total, valor_antecipado,
           DATE_FORMAT(data_ordem_servico, '%d/%m/%Y') as data_ordem_servico, 
           DATE_FORMAT(previsao_entrega, '%d/%m/%Y') as previsao,
           (total - valor_antecipado) as valor_faltante
           FROM obras_servicos
           WHERE status = 'Concluído'
           AND data_ordem_servico >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
           ORDER BY data_ordem_servico DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $servicos_concluidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar serviços pendentes
    $sql = "SELECT id, descricao, responsavel, total, valor_antecipado,
           DATE_FORMAT(data_ordem_servico, '%d/%m/%Y') as data_ordem_servico, 
           DATE_FORMAT(previsao_entrega, '%d/%m/%Y') as previsao,
           (total - valor_antecipado) as valor_faltante
           FROM obras_servicos
           WHERE status = 'Pendente'
           ORDER BY data_ordem_servico DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $servicos_pendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar serviços por responsável
    $sql = "SELECT 
        responsavel,
        COUNT(*) as total_servicos,
        ROUND(SUM(total), 2) as valor_total_responsavel,
        ROUND(SUM(valor_antecipado), 2) as valor_antecipado_responsavel
        FROM obras_servicos
        WHERE responsavel IS NOT NULL
        GROUP BY responsavel
        ORDER BY total_servicos DESC
        LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $top_responsaveis = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // Buscar serviços cancelados
    $sql = "SELECT id, descricao, responsavel, total, valor_antecipado,
           DATE_FORMAT(data_ordem_servico, '%d/%m/%Y') as data_ordem_servico, 
           DATE_FORMAT(previsao_entrega, '%d/%m/%Y') as previsao,
           (total - valor_antecipado) as valor_faltante
           FROM obras_servicos
           WHERE status = 'Cancelado'
           ORDER BY data_ordem_servico DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $servicos_cancelados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar todos os serviços com pagamentos faltantes (maior que R$ 0,01)
    $sql = "SELECT 
           s.id, 
           s.descricao, 
           s.responsavel, 
           s.total, 
           COALESCE(s.valor_antecipado, 0) as valor_antecipado,
           DATE_FORMAT(s.data_ordem_servico, '%d/%m/%Y') as data_ordem_servico, 
           DATE_FORMAT(s.previsao_entrega, '%d/%m/%Y') as previsao,
           s.status,
           CAST(s.total AS DECIMAL(10,2)) - CAST(COALESCE(s.valor_antecipado, 0) AS DECIMAL(10,2)) as valor_faltante
           FROM obras_servicos s
           WHERE CAST(s.total AS DECIMAL(10,2)) - CAST(COALESCE(s.valor_antecipado, 0) AS DECIMAL(10,2)) > 0.01
           ORDER BY valor_faltante DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pagamentos_faltantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Erro ao acessar o banco de dados: ' . htmlspecialchars($e->getMessage()) . '</div>';
    return;
}
?>

<div class="dashboard-container">
    <!-- Cards de Status -->
    <div class="dashboard-grid row">
        <div class="col-md-3 mb-4">
            <div class="card status-card bg-primary text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Em Andamento</h5>
                    <h2 class="display-4"><?php echo $estatisticas['em_andamento']; ?></h2>
                    <p class="mt-2 money-value">Valor Total: R$ <?php echo number_format($estatisticas['valor_andamento'], 2, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card status-card bg-success text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Concluídas</h5>
                    <h2 class="display-4"><?php echo $estatisticas['concluido']; ?></h2>
                    <p class="mt-2 money-value">Valor Total: R$ <?php echo number_format($estatisticas['valor_concluido'], 2, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card status-card bg-warning text-dark h-100">
                <div class="card-body">
                    <h5 class="card-title">Pendentes</h5>
                    <h2 class="display-4"><?php echo $estatisticas['pendente']; ?></h2>
                    <p class="mt-2 money-value">Valor Total: R$ <?php echo number_format($estatisticas['valor_pendente'], 2, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card status-card bg-danger text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Canceladas</h5>
                    <h2 class="display-4"><?php echo $estatisticas['cancelado']; ?></h2>
                    <p class="mt-2 money-value">Valor Total: R$ <?php echo number_format($estatisticas['valor_cancelado'], 2, ',', '.'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cards de Valores -->
    <div class="dashboard-grid row">
        <div class="col-md-4">
            <div class="card summary-card h-100">
                <div class="card-body">
                    <div class="card-label">Valor Total Geral</div>
                    <div class="card-value money-value">R$ <?php echo number_format($estatisticas['valor_total'], 2, ',', '.'); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card summary-card h-100">
                <div class="card-body">
                    <div class="card-label">Valor Antecipado Total</div>
                    <div class="card-value money-value">R$ <?php echo number_format($estatisticas['valor_antecipado'], 2, ',', '.'); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card summary-card h-100">
                <div class="card-body">
                    <div class="card-label">Valor Faltante Total</div>
                    <div class="card-value money-value">R$ <?php echo number_format($estatisticas['valor_total'] - $estatisticas['valor_antecipado'], 2, ',', '.'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas Adicionais -->
    <div class="row mb-4">
        <!-- Média de Valores -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Médias e Prazos</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Valor Médio por Serviço:</span>
                        <strong>R$ <?php echo number_format($media_estatisticas['media_valor'], 2, ',', '.'); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Tempo Médio de Conclusão:</span>
                        <strong><?php echo $media_estatisticas['media_dias_conclusao']; ?> dias</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 5 Responsáveis -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Top 5 Responsáveis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Responsável</th>
                                    <th class="text-center">Serviços</th>
                                    <th class="text-end">Valor Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_responsaveis as $resp): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($resp['responsavel']); ?></td>
                                    <td class="text-center"><?php echo $resp['total_servicos']; ?></td>
                                    <td class="text-end">R$ <?php echo number_format($resp['valor_total_responsavel'], 2, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Serviços em Andamento -->
    <div class="card mb-4 dashboard-section">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Serviços em Andamento</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped dashboard-table">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Responsável</th>
                            <th>Valor Total</th>
                            <th>Valor Antecipado</th>
                            <th>Valor Faltante</th>
                            <th>Data Ordem</th>
                            <th>Previsão</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicos_andamento as $obra): ?>
                        <tr>
                            <td title="<?php echo htmlspecialchars($obra['descricao']); ?>"><?php echo htmlspecialchars($obra['descricao']); ?></td>
                            <td title="<?php echo htmlspecialchars($obra['responsavel']); ?>"><?php echo htmlspecialchars($obra['responsavel']); ?></td>
                            <td class="valor">R$ <?php echo number_format($obra['total'], 2, ',', '.'); ?></td>
                            <td class="valor">R$ <?php echo number_format($obra['valor_antecipado'], 2, ',', '.'); ?></td>
                            <td class="valor">R$ <?php echo number_format($obra['valor_faltante'], 2, ',', '.'); ?></td>
                            <td class="data"><?php echo $obra['data_ordem_servico'] ?: ''; ?></td>
                            <td class="data"><?php echo $obra['previsao'] ?: ''; ?></td>
                            <td class="acoes">
                                <a href="/gerencialParoquia/projetos-modulos/obras/pages/visualizar_obra.php?id=<?php echo $obra['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Obras Concluídas -->
    <div class="card mb-4 dashboard-section">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Serviços Concluídos (Últimos 30 dias)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped dashboard-table">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Responsável</th>
                            <th>Valor Total</th>
                            <th>Valor Antecipado</th>
                            <th>Valor Faltante</th>
                            <th>Data Ordem Serviço</th>
                            <th>Conclusão</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicos_concluidos as $obra): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($obra['descricao']); ?></td>
                            <td><?php echo htmlspecialchars($obra['responsavel']); ?></td>
                            <td>R$ <?php echo number_format($obra['total'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($obra['valor_antecipado'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($obra['valor_faltante'], 2, ',', '.'); ?></td>
                            <td><?php echo $obra['data_ordem_servico'] ?: ''; ?></td>
                            <td><?php echo $obra['previsao'] ?: ''; ?></td>
                            <td class="acoes">
                                <a href="/gerencialParoquia/projetos-modulos/obras/pages/visualizar_obra.php?id=<?php echo $obra['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Obras Pendentes -->
    <div class="card mb-4 dashboard-section">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Serviços Pendentes</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped dashboard-table">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Responsável</th>
                            <th>Valor Total</th>
                            <th>Valor Antecipado</th>
                            <th>Valor Faltante</th>
                            <th>Data Ordem Serviço</th>
                            <th>Previsão</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicos_pendentes as $obra): ?>
                        <tr>
                            <td title="<?php echo htmlspecialchars($obra['descricao']); ?>"><?php echo htmlspecialchars($obra['descricao']); ?></td>
                            <td title="<?php echo htmlspecialchars($obra['responsavel']); ?>"><?php echo htmlspecialchars($obra['responsavel']); ?></td>
                            <td class="valor">R$ <?php echo number_format($obra['total'], 2, ',', '.'); ?></td>
                            <td class="valor">R$ <?php echo number_format($obra['valor_antecipado'], 2, ',', '.'); ?></td>
                            <td class="valor">R$ <?php echo number_format($obra['valor_faltante'], 2, ',', '.'); ?></td>
                            <td class="data"><?php echo $obra['data_ordem_servico'] ?: ''; ?></td>
                            <td class="data"><?php echo $obra['previsao'] ?: ''; ?></td>
                            <td class="acoes">
                                <a href="/gerencialParoquia/projetos-modulos/obras/pages/visualizar_obra.php?id=<?php echo $obra['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Obras Canceladas -->
    <div class="card mb-4 dashboard-section">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Serviços Cancelados</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped dashboard-table">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Responsável</th>
                            <th>Valor Total</th>
                            <th>Valor Antecipado</th>
                            <th>Valor Faltante</th>
                            <th>Data Ordem Serviço</th>
                            <th>Previsão</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicos_cancelados as $obra): ?>
                        <tr>
                            <td title="<?php echo htmlspecialchars($obra['descricao']); ?>"><?php echo htmlspecialchars($obra['descricao']); ?></td>
                            <td title="<?php echo htmlspecialchars($obra['responsavel']); ?>"><?php echo htmlspecialchars($obra['responsavel']); ?></td>
                            <td class="valor">R$ <?php echo number_format($obra['total'], 2, ',', '.'); ?></td>
                            <td class="valor">R$ <?php echo number_format($obra['valor_antecipado'], 2, ',', '.'); ?></td>
                            <td class="valor">R$ <?php echo number_format($obra['valor_faltante'], 2, ',', '.'); ?></td>
                            <td class="data"><?php echo $obra['data_ordem_servico'] ?: ''; ?></td>
                            <td class="data"><?php echo $obra['previsao'] ?: ''; ?></td>
                            <td class="acoes">
                                <a href="/gerencialParoquia/projetos-modulos/obras/pages/visualizar_obra.php?id=<?php echo $obra['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagamentos Faltantes -->
    <div class="card mb-4 dashboard-section">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pagamentos Faltantes</h5>
            <span class="badge bg-light text-danger">Total: <?php echo count($pagamentos_faltantes); ?></span>
        </div>
        <div class="card-body">
            <?php if (!empty($pagamentos_faltantes)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped dashboard-table">
                        <thead>
                            <tr>
                                <th>Descrição</th>
                                <th>Responsável</th>
                                <th>Status</th>
                                <th>Valor Total</th>
                                <th>Valor Antecipado</th>
                                <th>Valor Faltante</th>
                                <th>Data Ordem Serviço</th>
                                <th>Previsão</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagamentos_faltantes as $servico): ?>
                            <tr>
                                <td title="<?php echo htmlspecialchars($servico['descricao']); ?>"><?php echo htmlspecialchars($servico['descricao']); ?></td>
                                <td title="<?php echo htmlspecialchars($servico['responsavel']); ?>"><?php echo htmlspecialchars($servico['responsavel']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        $statusClass = '';
                                        switch($servico['status']) {
                                            case 'Em Andamento': $statusClass = 'primary'; break;
                                            case 'Concluído': $statusClass = 'success'; break;
                                            case 'Pendente': $statusClass = 'warning'; break;
                                            case 'Cancelado': $statusClass = 'danger'; break;
                                            default: $statusClass = 'secondary';
                                        }
                                        echo $statusClass;
                                    ?>">
                                        <?php echo htmlspecialchars($servico['status']); ?>
                                    </span>
                                </td>
                                <td class="valor">R$ <?php echo number_format($servico['total'], 2, ',', '.'); ?></td>
                                <td class="valor">R$ <?php echo number_format($servico['valor_antecipado'], 2, ',', '.'); ?></td>
                                <td class="valor text-danger fw-bold">R$ <?php echo number_format($servico['valor_faltante'], 2, ',', '.'); ?></td>
                                <td class="data"><?php echo $servico['data_ordem_servico'] ?: ''; ?></td>
                                <td class="data"><?php echo $servico['previsao'] ?: ''; ?></td>
                                <td class="acoes">
                                    <a href="/gerencialParoquia/projetos-modulos/obras/pages/visualizar_obra.php?id=<?php echo $servico['id']; ?>" class="btn btn-sm btn-info" title="Visualizar detalhes">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="/gerencialParoquia/projetos-modulos/obras/pages/editar_obra.php?id=<?php echo $servico['id']; ?>" class="btn btn-sm btn-warning" title="Editar serviço">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0">
                    Não há pagamentos faltantes no momento.
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
