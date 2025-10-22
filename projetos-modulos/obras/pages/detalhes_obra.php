<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">ID da obra não especificado ou inválido.</div>';
    return;
}

$id = (int)$_GET['id'];

try {
    $sql = "SELECT o.*, 
            DATE_FORMAT(o.data_ordem_servico, '%d/%m/%Y') as data_ordem_formatada,
            DATE_FORMAT(o.data_conclusao, '%d/%m/%Y') as data_conclusao_formatada,
            DATE_FORMAT(o.previsao_entrega, '%d/%m/%Y') as previsao_formatada
            FROM obras_obras o 
            WHERE o.id = :id";
            
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $obra = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$obra) {
        echo '<div class="alert alert-danger">Obra não encontrada.</div>';
        return;
    }
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Erro ao buscar detalhes da obra: ' . htmlspecialchars($e->getMessage()) . '</div>';
    return;
}
?>

<div class="container-fluid">
    <div class="row mt-4">
        <div class="col">
            <h2>Detalhes da Obra</h2>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo htmlspecialchars($obra['descricao']); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Informações Gerais</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Responsável Técnico:</th>
                                    <td><?php echo htmlspecialchars($obra['responsavel_tecnico']); ?></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-<?php 
                                            $statusClass = '';
                                            switch($obra['status']) {
                                                case 'Em Andamento': $statusClass = 'primary'; break;
                                                case 'Concluído': $statusClass = 'success'; break;
                                                case 'Pendente': $statusClass = 'warning'; break;
                                                case 'Cancelado': $statusClass = 'danger'; break;
                                                default: $statusClass = 'secondary';
                                            }
                                            echo $statusClass;
                                        ?>">
                                            <?php echo htmlspecialchars($obra['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Informações Financeiras</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Valor Total:</th>
                                    <td>R$ <?php echo number_format($obra['total'] ?? 0, 2, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <th>Valor Adiantado:</th>
                                    <td>R$ <?php echo number_format($obra['valor_adiantado'], 2, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <th>Valor Restante:</th>
                                    <td>R$ <?php echo number_format(($obra['total'] ?? 0) - ($obra['valor_antecipado'] ?? 0), 2, ',', '.'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted">Datas</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Data da Ordem de Serviço:</th>
                                    <td><?php echo $obra['data_ordem_formatada'] ?: 'Não definida'; ?></td>
                                </tr>
                                <tr>
                                    <th>Previsão de Entrega:</th>
                                    <td><?php echo $obra['previsao_formatada'] ?: 'Não definida'; ?></td>
                                </tr>
                                <?php if ($obra['status'] === 'Concluída'): ?>
                                <tr>
                                    <th>Data de Conclusão:</th>
                                    <td><?php echo $obra['data_conclusao_formatada']; ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                    <a href="index.php?page=editar_obra&id=<?php echo $obra['id']; ?>" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                </div>
            </div>

            <!-- Formulário de Upload -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Upload de Arquivos</h5>
                </div>
                <div class="card-body">
                    <form action="../api/upload_files.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                        <input type="hidden" name="obra_id" value="<?php echo $id; ?>">
                        
                        <div class="mb-3">
                            <label for="comprovante" class="form-label">Comprovante de Pagamento</label>
                            <input type="file" class="form-control" id="comprovante" name="comprovante" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Formatos aceitos: PDF, JPEG, PNG (máx. 5MB)</small>
                        </div>

                        <div class="mb-3">
                            <label for="nota_fiscal" class="form-label">Nota Fiscal</label>
                            <input type="file" class="form-control" id="nota_fiscal" name="nota_fiscal" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Formatos aceitos: PDF, JPEG, PNG (máx. 5MB)</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Enviar Arquivos</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = 'Enviando...';

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.messages.join('\n'));
                location.reload();
            } else {
                alert(data.message || 'Erro ao enviar arquivos');
            }
        })
        .catch(error => {
            alert('Erro ao enviar arquivos: ' + error.message);
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Enviar Arquivos';
        });
    });
    </script>
</div>
