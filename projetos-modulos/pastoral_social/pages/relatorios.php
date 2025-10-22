<?php
// Get filter values
$cidade = $_GET['cidade'] ?? '';
$bairro = $_GET['bairro'] ?? '';
$situacao = $_GET['situacao'] ?? '';
$paga_aluguel = $_GET['paga_aluguel'] ?? '';
$paroquia = $_GET['paroquia'] ?? '';

// Get unique values for filters
$stmt = $pdo->query("SELECT DISTINCT cidade FROM users WHERE cidade IS NOT NULL AND cidade != '' ORDER BY cidade");
$cidades = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->query("SELECT DISTINCT bairro FROM users WHERE bairro IS NOT NULL AND bairro != '' ORDER BY bairro");
$bairros = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->query("SELECT DISTINCT paroquia FROM users WHERE paroquia IS NOT NULL AND paroquia != '' ORDER BY paroquia");
$paroquias = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Build query based on filters
$where = [];
$params = [];

if ($cidade) {
    $where[] = "cidade = ?";
    $params[] = $cidade;
}

if ($bairro) {
    $where[] = "bairro = ?";
    $params[] = $bairro;
}

if ($situacao) {
    $where[] = "situacao = ?";
    $params[] = $situacao;
}

if ($paga_aluguel) {
    $where[] = "paga_aluguel = ?";
    $params[] = $paga_aluguel;
}

if ($paroquia) {
    $where[] = "paroquia = ?";
    $params[] = $paroquia;
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get users based on filters
$sql = "SELECT * FROM users $where_clause ORDER BY nome";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_usuarios = count($usuarios);
$total_ativos = 0;
$total_inativos = 0;
$total_aguardando = 0;
$total_outros = 0;
$total_pagam_aluguel = 0;
$total_moradores = 0;

foreach ($usuarios as $usuario) {
    switch ($usuario['situacao']) {
        case 'Ativo': $total_ativos++; break;
        case 'Inativo': $total_inativos++; break;
        case 'Aguardando Documentação': $total_aguardando++; break;
        case 'Outros': $total_outros++; break;
    }
    if ($usuario['paga_aluguel'] === 'Sim') {
        $total_pagam_aluguel++;
    }
    $total_moradores += (int)$usuario['qtd_moram_casa'];
}
?>

<div class="container-fluid">
    <h2 class="mb-4">Relatório Completo</h2>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <input type="hidden" name="page" value="relatorios">
                
                <div class="col-md-4">
                    <label for="cidade" class="form-label">Cidade</label>
                    <select name="cidade" id="cidade" class="form-select">
                        <option value="">Todas as cidades</option>
                        <?php foreach ($cidades as $c): ?>
                        <option value="<?php echo htmlspecialchars($c); ?>" 
                                <?php echo $c === $cidade ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="bairro" class="form-label">Bairro</label>
                    <select name="bairro" id="bairro" class="form-select">
                        <option value="">Todos os bairros</option>
                        <?php foreach ($bairros as $b): ?>
                        <option value="<?php echo htmlspecialchars($b); ?>" 
                                <?php echo $b === $bairro ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($b); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="paroquia" class="form-label">Paróquia</label>
                    <select name="paroquia" id="paroquia" class="form-select">
                        <option value="">Todas as paróquias</option>
                        <?php foreach ($paroquias as $p): ?>
                        <option value="<?php echo htmlspecialchars($p); ?>" 
                                <?php echo $p === $paroquia ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="situacao" class="form-label">Situação</label>
                    <select name="situacao" id="situacao" class="form-select">
                        <option value="">Todas</option>
                        <option value="Ativo" <?php echo $situacao === 'Ativo' ? 'selected' : ''; ?>>Ativo</option>
                        <option value="Inativo" <?php echo $situacao === 'Inativo' ? 'selected' : ''; ?>>Inativo</option>
                        <option value="Aguardando Documentação" <?php echo $situacao === 'Aguardando Documentação' ? 'selected' : ''; ?>>Aguardando Documentação</option>
                        <option value="Outros" <?php echo $situacao === 'Outros' ? 'selected' : ''; ?>>Outros</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="paga_aluguel" class="form-label">Paga Aluguel?</label>
                    <select name="paga_aluguel" id="paga_aluguel" class="form-select">
                        <option value="">Todos</option>
                        <option value="Sim" <?php echo $paga_aluguel === 'Sim' ? 'selected' : ''; ?>>Sim</option>
                        <option value="Não" <?php echo $paga_aluguel === 'Não' ? 'selected' : ''; ?>>Não</option>
                    </select>
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
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Estatísticas</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <p><strong>Total de Registros:</strong> <?php echo $total_usuarios; ?></p>
                            <p><strong>Total de Ativos:</strong> <?php echo $total_ativos; ?></p>
                            <p><strong>Total de Inativos:</strong> <?php echo $total_inativos; ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Aguardando Documentação:</strong> <?php echo $total_aguardando; ?></p>
                            <p><strong>Outros:</strong> <?php echo $total_outros; ?></p>
                            <p><strong>Pagam Aluguel:</strong> <?php echo $total_pagam_aluguel; ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Total de Moradores:</strong> <?php echo $total_moradores; ?></p>
                            <p><strong>Média de Moradores:</strong> <?php echo $total_usuarios > 0 ? number_format($total_moradores / $total_usuarios, 1) : 0; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="relatorioTable">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Data Nasc.</th>
                            <th>Data Cad.</th>
                            <th>Endereço</th>
                            <th>Bairro</th>
                            <th>Cidade</th>
                            <th>Estado</th>
                            <th>CEP</th>
                            <th>Telefone</th>
                            <th>Email</th>
                            <th>Visitado por</th>
                            <th>Qtd. Moradores</th>
                            <th>Paga Aluguel</th>
                            <th>Paróquia</th>
                            <th>Situação</th>
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['cpf']); ?></td>
                            <td><?php echo $usuario['data_nascimento'] ? date('d/m/Y', strtotime($usuario['data_nascimento'])) : ''; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?></td>
                            <td><?php echo htmlspecialchars($usuario['endereco']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['bairro']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['cidade']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['estado']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['cep']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['telefone']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['visitado_por']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['qtd_moram_casa']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['paga_aluguel']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['paroquia']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    switch($usuario['situacao']) {
                                        case 'Ativo': echo 'success'; break;
                                        case 'Inativo': echo 'danger'; break;
                                        case 'Aguardando Documentação': echo 'warning'; break;
                                        default: echo 'secondary';
                                    }
                                ?>">
                                    <?php echo $usuario['situacao']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($usuario['observacoes']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        Nenhum registro encontrado com os filtros selecionados.
    </div>
    <?php endif; ?>
</div>

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>

<script>
function exportToExcel() {
    const table = document.getElementById('relatorioTable');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Relatório Completo"});
    XLSX.writeFile(wb, 'relatorio_pastoral_social.xlsx');
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'pt', 'a4'); // Landscape mode
    
    // Add title
    doc.setFontSize(16);
    doc.text('Relatório Completo - Pastoral Social', 40, 40);
    
    // Add filters info
    doc.setFontSize(10);
    let filterText = 'Filtros: ';
    if (document.getElementById('cidade').value) 
        filterText += 'Cidade: ' + document.getElementById('cidade').value + ' | ';
    if (document.getElementById('bairro').value)
        filterText += 'Bairro: ' + document.getElementById('bairro').value + ' | ';
    if (document.getElementById('paroquia').value)
        filterText += 'Paróquia: ' + document.getElementById('paroquia').value + ' | ';
    if (document.getElementById('situacao').value)
        filterText += 'Situação: ' + document.getElementById('situacao').value + ' | ';
    if (document.getElementById('paga_aluguel').value)
        filterText += 'Paga Aluguel: ' + document.getElementById('paga_aluguel').value;
    doc.text(filterText, 40, 60);

    // Add table
    doc.autoTable({ 
        html: '#relatorioTable',
        startY: 70,
        styles: { fontSize: 7 },
        columnStyles: { 
            0: { cellWidth: 60 }, // Nome
            4: { cellWidth: 60 }, // Endereço
            16: { cellWidth: 60 }  // Observações
        },
        pageBreak: 'auto',
        rowPageBreak: 'avoid'
    });

    doc.save('relatorio_pastoral_social.pdf');
}</script>
