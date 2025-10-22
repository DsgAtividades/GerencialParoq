<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Get filter values
$status = $_GET['status'] ?? '';
$responsavel = $_GET['responsavel'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Get unique values for filters
$stmt = $pdo->query("SELECT DISTINCT responsavel FROM obras_servicos WHERE responsavel IS NOT NULL AND responsavel != '' ORDER BY responsavel");
$responsaveis = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Build query based on filters
$where = [];
$params = [];

if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}

if ($responsavel) {
    $where[] = "responsavel = ?";
    $params[] = $responsavel;
}

if ($data_inicio) {
    $where[] = "data_ordem_servico >= ?";
    $params[] = $data_inicio;
}

if ($data_fim) {
    $where[] = "data_ordem_servico <= ?";
    $params[] = $data_fim;
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get services based on filters
$sql = "SELECT * FROM obras_servicos $where_clause ORDER BY data_ordem_servico DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_usuarios = count($usuarios);
// Inicializar contadores
$total_em_andamento = 0;
$total_concluido = 0;
$total_pendente = 0;
$total_cancelado = 0;
$valor_total_obras = 0;
$valor_total_antecipado = 0;

// Calcular estatísticas
foreach ($usuarios as $obra) {
    switch ($obra['status']) {
        case 'Em Andamento': $total_em_andamento++; break;
        case 'Concluído': $total_concluido++; break;
        case 'Pendente': $total_pendente++; break;
        case 'Cancelado': $total_cancelado++; break;
    }
    $valor_total_obras += floatval($obra['total'] ?? 0);
    $valor_total_antecipado += floatval($obra['valor_antecipado'] ?? 0);
}
?>

<div class="container-fluid">
    <!-- Mensagens de Feedback -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Atenção!</strong>
            <br>
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <h2 class="mb-4">Relatório Completo</h2>

    <!-- Seção de Importação -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Importação de Serviços</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <a href="includes/gerar_modelo_excel.php" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Baixar Modelo Excel
                    </a>
                </div>
                <div class="col-md-6">
                    <form action="includes/processar_importacao.php" method="post" enctype="multipart/form-data" class="d-flex gap-2">
                        <input type="file" name="arquivo_servicos" class="form-control" accept=".xlsx,.xls" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Importar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <input type="hidden" name="page" value="relatorios">
                
                <div class="col-md-4">
                    <label for="cidade" class="form-label">Cidade</label>
                    <select name="cidade" id="cidade" class="form-select">
                        <option value="">Todas as cidades</option>
                        <option value="">Todos</option>
                        <option value="Em Andamento" <?php echo $status === 'Em Andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                        <option value="Concluído" <?php echo $status === 'Concluído' ? 'selected' : ''; ?>>Concluído</option>
                        <option value="Pendente" <?php echo $status === 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                        <option value="Cancelado" <?php echo $status === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="responsavel" class="form-label">Responsável</label>
                    <select class="form-select" id="responsavel" name="responsavel">
                        <option value="">Todos</option>
                        <?php foreach ($responsaveis as $r): ?>
                            <option value="<?php echo htmlspecialchars($r); ?>" <?php echo $responsavel === $r ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($r); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>">
                </div>

                <div class="col-md-3">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo htmlspecialchars($data_fim); ?>">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <?php if (!empty($usuarios)): ?>
                    <button type="button" onclick="exportToExcel()" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Exportar para Excel
                    </button>
                    <button type="button" onclick="exportToPDF()" class="btn btn-danger">
                        <i class="bi bi-file-earmark-pdf"></i> Exportar para PDF
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($usuarios)): ?>

    <!-- Seção de Estatísticas -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h3 class="card-title mb-0">Estatísticas Gerais</h3>
        </div>
        <div class="card-body">
            <!-- Estatísticas de Status -->
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="border-bottom pb-2 mb-3">Status dos Serviços</h4>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="status-circle bg-primary"></div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Em Andamento</h6>
                            <h4 class="mb-0"><?php echo $total_em_andamento; ?></h4>
                            <small class="text-muted"><?php echo $total_usuarios ? round(($total_em_andamento / $total_usuarios) * 100) : 0; ?>% do total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="status-circle bg-success"></div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Concluídos</h6>
                            <h4 class="mb-0"><?php echo $total_concluido; ?></h4>
                            <small class="text-muted"><?php echo $total_usuarios ? round(($total_concluido / $total_usuarios) * 100) : 0; ?>% do total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="status-circle bg-warning"></div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Pendentes</h6>
                            <h4 class="mb-0"><?php echo $total_pendente; ?></h4>
                            <small class="text-muted"><?php echo $total_usuarios ? round(($total_pendente / $total_usuarios) * 100) : 0; ?>% do total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="status-circle bg-danger"></div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Cancelados</h6>
                            <h4 class="mb-0"><?php echo $total_cancelado; ?></h4>
                            <small class="text-muted"><?php echo $total_usuarios ? round(($total_cancelado / $total_usuarios) * 100) : 0; ?>% do total</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estatísticas Financeiras -->
            <div class="row">
                <div class="col-12">
                    <h4 class="border-bottom pb-2 mb-3">Resumo Financeiro</h4>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="financial-stat">
                        <h6 class="text-muted mb-2">Valor Total dos Serviços</h6>
                        <h3 class="mb-0">R$ <?php echo number_format($valor_total_obras, 2, ',', '.'); ?></h3>
                        <small class="text-muted">Total de <?php echo $total_usuarios; ?> serviços</small>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="financial-stat">
                        <h6 class="text-muted mb-2">Valor Antecipado</h6>
                        <h3 class="mb-0">R$ <?php echo number_format($valor_total_antecipado, 2, ',', '.'); ?></h3>
                        <small class="text-muted"><?php echo $valor_total_obras ? round(($valor_total_antecipado / $valor_total_obras) * 100) : 0; ?>% do valor total</small>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="financial-stat">
                        <h6 class="text-muted mb-2">Valor Faltante</h6>
                        <h3 class="mb-0">R$ <?php echo number_format($valor_total_obras - $valor_total_antecipado, 2, ',', '.'); ?></h3>
                        <small class="text-muted"><?php echo $valor_total_obras ? round((($valor_total_obras - $valor_total_antecipado) / $valor_total_obras) * 100) : 0; ?>% do valor total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Serviços -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Total de Serviços</h5>
                    <p class="card-text display-6"><?php echo $total_usuarios; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Valor Total</h5>
                    <p class="card-text display-6">R$ <?php echo number_format($valor_total_obras, 2, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Valor Antecipado</h5>
                    <p class="card-text display-6">R$ <?php echo number_format($valor_total_antecipado, 2, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <h5 class="card-title">Valor Faltante</h5>
                    <p class="card-text display-6">R$ <?php echo number_format($valor_total_obras - $valor_total_antecipado, 2, ',', '.'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary h-100">
                <div class="card-body text-primary">
                    <h5 class="card-title">Em Andamento</h5>
                    <p class="card-text display-6"><?php echo $total_em_andamento; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success h-100">
                <div class="card-body text-success">
                    <h5 class="card-title">Concluídos</h5>
                    <p class="card-text display-6"><?php echo $total_concluido; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning h-100">
                <div class="card-body text-warning">
                    <h5 class="card-title">Pendentes</h5>
                    <p class="card-text display-6"><?php echo $total_pendente; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger h-100">
                <div class="card-body text-danger">
                    <h5 class="card-title">Cancelados</h5>
                    <p class="card-text display-6"><?php echo $total_cancelado; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <h3 class="card-title">Lista de Serviços</h3>
            </div>

            <script>
            function confirmarExclusao(id) {
                if (confirm('Tem certeza que deseja excluir este serviço?')) {
                    window.location.href = '/obras/pages/excluir_servico.php?id=' + id;
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                console.log('Inicializando eventos dos botões...');
                
                document.getElementById('btnExcel').addEventListener('click', function() {
                    console.log('Botão Excel clicado');
                    exportToExcel();
                });

                document.getElementById('btnPDF').addEventListener('click', function() {
                    console.log('Botão PDF clicado');
                    exportToPDF();
                });
            });
            </script>
            <div class="table-responsive">
                <table class="table table-striped table-hover dashboard-table table-bordered" id="relatorioTable">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Responsável</th>
                            <th>Status</th>
                            <th>Valor Total</th>
                            <th>Valor Antecipado</th>
                            <th>Valor Faltante</th>
                            <th>Data Ordem</th>
                            <th>Previsão Entrega</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['descricao']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['responsavel']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        $statusClass = '';
                                        switch($usuario['status']) {
                                            case 'Em Andamento': $statusClass = 'primary'; break;
                                            case 'Concluído': $statusClass = 'success'; break;
                                            case 'Pendente': $statusClass = 'warning'; break;
                                            case 'Cancelado': $statusClass = 'danger'; break;
                                            default: $statusClass = 'secondary';
                                        }
                                        echo $statusClass;
                                    ?>">
                                        <?php echo htmlspecialchars($usuario['status']); ?>
                                    </span>
                                </td>
                                <td class="valor">R$ <?php echo number_format($usuario['total'] ?? 0, 2, ',', '.'); ?></td>
                                <td class="valor">R$ <?php echo number_format($usuario['valor_antecipado'] ?? 0, 2, ',', '.'); ?></td>
                                <td class="valor">R$ <?php echo number_format(($usuario['total'] ?? 0) - ($usuario['valor_antecipado'] ?? 0), 2, ',', '.'); ?></td>
                                <td class="data"><?php echo $usuario['data_ordem_servico'] ? date('d/m/Y', strtotime($usuario['data_ordem_servico'])) : '-'; ?></td>
                                <td class="data"><?php echo $usuario['previsao_entrega'] ? date('d/m/Y', strtotime($usuario['previsao_entrega'])) : '-'; ?></td>
                                <td class="acoes text-center">
                                    <a href="/gerencialParoquia/projetos-modulos/obras/pages/visualizar_obra.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-primary" title="Visualizar detalhes">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="/gerencialParoquia/projetos-modulos/obras/pages/editar_obra.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-warning" title="Editar serviço">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button onclick="confirmarExclusao(<?php echo $usuario['id']; ?>)" class="btn btn-sm btn-danger" title="Excluir serviço">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-end mt-4 gap-2">
                <button type="button" id="btnExcel" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Exportar para Excel
                </button>
                <button type="button" id="btnPDF" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i> Exportar para PDF
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if (empty($usuarios)): ?>
    <div class="alert alert-info">
        Nenhum registro encontrado com os filtros selecionados.
    </div>
    <?php endif; ?>
</div>

<!-- Estilos personalizados -->
<style>
    /* Estilos para estatísticas */
    .status-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .financial-stat {
        padding: 1.5rem;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        height: 100%;
    }

    .financial-stat h3 {
        color: #2c3e50;
        font-weight: 600;
    }

    .card-header.bg-dark {
        background-color: #343a40 !important;
    }

    .border-bottom {
        border-bottom: 2px solid #dee2e6 !important;
    }

    /* Animações para os cards de estatísticas */
    .d-flex {
        transition: transform 0.2s ease;
    }

    .d-flex:hover {
        transform: translateY(-5px);
    }

    .financial-stat {
        transition: all 0.3s ease;
    }

    .financial-stat:hover {
        background-color: #e9ecef;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .dashboard-table {
        font-size: 0.9rem;
    }
    .dashboard-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .dashboard-table td {
        vertical-align: middle;
    }
    .valor {
        font-family: 'Roboto Mono', monospace;
        text-align: right;
    }
    .data {
        text-align: center;
    }
    .badge {
        font-size: 0.85rem;
        padding: 0.5em 0.8em;
    }
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .display-6 {
        font-size: 1.8rem;
        font-weight: 500;
    }
    .acoes {
        white-space: nowrap;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Excel Export -->
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

<!-- PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.2.11/jspdf.plugin.autotable.min.js"></script>

<script>
function confirmarExclusao(id) {
    if (confirm('Tem certeza que deseja excluir este serviço?')) {
        window.location.href = '/obras/pages/excluir_servico.php?id=' + id;
    }
}

function exportToExcel() {
    try {
        console.log('Iniciando exportação para Excel...');
        const table = document.getElementById('relatorioTable');
        if (!table) {
            console.error('Tabela não encontrada');
            alert('Erro: Tabela não encontrada');
            return;
        }
        const wb = XLSX.utils.table_to_book(table, {sheet: "Relatório de Obras"});
        XLSX.writeFile(wb, 'relatorio_obras.xlsx');
        console.log('Excel exportado com sucesso!');
    } catch (e) {
        console.error('Erro ao exportar Excel:', e);
        alert('Erro ao exportar para Excel. Verifique o console para mais detalhes.');
    }
}

function exportToPDF() {
    try {
        console.log('Iniciando exportação para PDF...');
        const doc = new jsPDF('l', 'pt', 'a4'); // Modo paisagem
    
    // Adicionar título
    doc.setFontSize(16);
    doc.text('Relatório de Obras', 40, 40);
    
    // Preparar dados da tabela
    const table = document.getElementById('relatorioTable');
    const rows = [];
    const headers = [];
    
    // Obter cabeçalhos
    table.querySelectorAll('thead th').forEach(th => {
        headers.push(th.textContent.trim());
    });
    
    // Obter dados
    table.querySelectorAll('tbody tr').forEach(tr => {
        const rowData = [];
        tr.querySelectorAll('td').forEach(td => {
            rowData.push(td.textContent.trim());
        });
        if (rowData.length > 0) rows.push(rowData);
    });
    
    // Remover coluna de ações antes de exportar
    const dataForExport = rows.map(row => row.slice(0, -1));
    
    // Remover cabeçalho de ações
    const headersForExport = headers.slice(0, -1);
    
    // Configurar estilos da tabela
    const styles = {
        headStyles: {
            fillColor: [51, 51, 51],
            textColor: 255,
            fontSize: 10
        },
        alternateRowStyles: {
            fillColor: [245, 245, 245]
        },
        columnStyles: {
            0: { cellWidth: 150 }, // Descrição
            1: { cellWidth: 80 },  // Responsável
            2: { cellWidth: 60 },  // Status
            3: { cellWidth: 70 },  // Valor Total
            4: { cellWidth: 70 },  // Valor Antecipado
            5: { cellWidth: 70 },  // Valor Faltante
            6: { cellWidth: 70 },  // Data Ordem
            7: { cellWidth: 70 }   // Previsão Entrega
        },
        margin: { top: 50 },
        styles: {
            fontSize: 8,
            cellPadding: 2
        }
    };

    // Adicionar tabela com dados filtrados
    doc.autoTable({
        head: [headersForExport],
        body: dataForExport,
        ...styles
    });

    console.log('Salvando PDF...');
    doc.save('relatorio_obras.pdf');
    console.log('PDF exportado com sucesso!');
    } catch (e) {
        console.error('Erro ao exportar PDF:', e);
        alert('Erro ao exportar para PDF. Verifique o console para mais detalhes.');
    }
}
</script>
