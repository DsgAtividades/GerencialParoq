<?php
require_once 'config/database.php';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'adicionar':
                $stmt = $pdo->prepare("INSERT INTO estoque (nome_alimento, quantidade, unidade_medida, data_validade, categoria, local_armazenamento, observacoes) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['nome_alimento'],
                    $_POST['quantidade'],
                    $_POST['unidade_medida'],
                    $_POST['data_validade'],
                    $_POST['categoria'],
                    $_POST['local_armazenamento'],
                    $_POST['observacoes']
                ]);
                break;

            case 'atualizar':
                try {
                    // Inicia uma transação
                    $pdo->beginTransaction();

                    // Se a quantidade for zero, exclui o alimento
                    if (floatval($_POST['quantidade']) <= 0) {
                        // Exclui o histórico
                        $stmt = $pdo->prepare("DELETE FROM historico_estoque WHERE alimento_id = ?");
                        $stmt->execute([$_POST['id']]);

                        // Exclui o alimento
                        $stmt = $pdo->prepare("DELETE FROM estoque WHERE id = ?");
                        $stmt->execute([$_POST['id']]);

                        $_SESSION['mensagem'] = "Alimento excluído automaticamente pois a quantidade foi definida como zero.";
                    } else {
                        // Atualiza normalmente
                        $stmt = $pdo->prepare("UPDATE estoque SET 
                            nome_alimento = ?, 
                            quantidade = ?, 
                            unidade_medida = ?, 
                            data_validade = ?, 
                            categoria = ?, 
                            local_armazenamento = ?, 
                            observacoes = ? 
                            WHERE id = ?");
                        $stmt->execute([
                            $_POST['nome_alimento'],
                            $_POST['quantidade'],
                            $_POST['unidade_medida'],
                            $_POST['data_validade'],
                            $_POST['categoria'],
                            $_POST['local_armazenamento'],
                            $_POST['observacoes'],
                            $_POST['id']
                        ]);

                        $_SESSION['mensagem'] = "Alimento atualizado com sucesso.";
                    }

                    // Confirma as operações
                    $pdo->commit();

                } catch (Exception $e) {
                    // Em caso de erro, desfaz as operações
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $_SESSION['mensagem'] = "Erro ao atualizar alimento: " . $e->getMessage();
                }
                break;

            case 'movimentar':
                try {
                    // Inicia uma transação
                    $pdo->beginTransaction();

                    // Registra a movimentação
                    $stmt = $pdo->prepare("INSERT INTO historico_estoque (alimento_id, tipo_movimentacao, quantidade, motivo, usuario_nome) 
                                          VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['alimento_id'],
                        $_POST['tipo_movimentacao'],
                        $_POST['quantidade'],
                        $_POST['motivo'],
                        $_SESSION['nome_completo']
                    ]);

                    // Atualiza quantidade no estoque
                    $sinal = $_POST['tipo_movimentacao'] === 'entrada' ? '+' : '-';
                    $stmt = $pdo->prepare("UPDATE estoque SET quantidade = quantidade {$sinal} ? WHERE id = ?");
                    $stmt->execute([$_POST['quantidade'], $_POST['alimento_id']]);

                    // Verifica se a quantidade ficou zerada após a movimentação
                    $stmt = $pdo->prepare("SELECT quantidade FROM estoque WHERE id = ?");
                    $stmt->execute([$_POST['alimento_id']]);
                    $alimento = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Se a quantidade for zero ou negativa, exclui o alimento e seu histórico
                    if ($alimento && $alimento['quantidade'] <= 0) {
                        // Exclui o histórico
                        $stmt = $pdo->prepare("DELETE FROM historico_estoque WHERE alimento_id = ?");
                        $stmt->execute([$_POST['alimento_id']]);

                        // Exclui o alimento
                        $stmt = $pdo->prepare("DELETE FROM estoque WHERE id = ?");
                        $stmt->execute([$_POST['alimento_id']]);

                        $_SESSION['mensagem'] = "Alimento excluído automaticamente pois a quantidade foi zerada.";
                    }

                    // Confirma todas as operações
                    $pdo->commit();

                } catch (Exception $e) {
                    // Em caso de erro, desfaz todas as operações
                    $pdo->rollBack();
                    throw $e;
                }
                break;
        }
        
        // Redirecionar após a ação para evitar reenvio do formulário
        header('Location: index.php?page=estoque');
        exit;
    }
}

// Buscar todos os alimentos
$stmt = $pdo->query("SELECT * FROM estoque ORDER BY nome_alimento");
$alimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar histórico de movimentações
$stmt = $pdo->query("
    SELECT 
        h.*,
        e.nome_alimento,
        DATE_FORMAT(h.created_at, '%d/%m/%Y %H:%i') as data_formatada
    FROM 
        historico_estoque h
        JOIN estoque e ON h.alimento_id = e.id
    ORDER BY 
        h.created_at DESC
    LIMIT 100
");
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <?php 
                echo $_SESSION['mensagem'];
                unset($_SESSION['mensagem']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Abas de navegação -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#estoque">Estoque</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#historico">Histórico de Movimentações</a>
        </li>
    </ul>

    <!-- Conteúdo das abas -->
    <div class="tab-content">
        <!-- Aba de Estoque -->
        <div class="tab-pane fade show active" id="estoque">
            <div class="row mb-4">
                <div class="col">
                    <h2>Controle de Estoque</h2>
                </div>
                <div class="col text-end">
                    <button type="button" class="btn btn-primary" onclick="novoAlimento()" data-bs-toggle="modal" data-bs-target="#modalAlimento">
                        <i class="bi bi-plus-lg"></i> Novo Alimento
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Alimento</th>
                                    <th>Quantidade</th>
                                    <th>Unidade</th>
                                    <th>Validade</th>
                                    <th>Categoria</th>
                                    <th>Local</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alimentos as $alimento): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($alimento['nome_alimento']); ?></td>
                                    <td><?php echo htmlspecialchars($alimento['quantidade']); ?></td>
                                    <td><?php echo htmlspecialchars($alimento['unidade_medida']); ?></td>
                                    <td><?php echo $alimento['data_validade'] ? date('d/m/Y', strtotime($alimento['data_validade'])) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($alimento['categoria']); ?></td>
                                    <td><?php echo htmlspecialchars($alimento['local_armazenamento']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="movimentarEstoque(<?php echo $alimento['id']; ?>)">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="editarAlimento(<?php echo $alimento['id']; ?>, '<?php echo htmlspecialchars($alimento['nome_alimento']); ?>', '<?php echo htmlspecialchars($alimento['quantidade']); ?>', '<?php echo htmlspecialchars($alimento['unidade_medida']); ?>', '<?php echo $alimento['data_validade']; ?>', '<?php echo htmlspecialchars($alimento['categoria']); ?>', '<?php echo htmlspecialchars($alimento['local_armazenamento']); ?>', '<?php echo htmlspecialchars($alimento['observacoes']); ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-secondary" onclick="verObservacoes(<?php echo $alimento['id']; ?>, '<?php echo htmlspecialchars($alimento['nome_alimento']); ?>', '<?php echo htmlspecialchars($alimento['quantidade']); ?>', '<?php echo htmlspecialchars($alimento['unidade_medida']); ?>', '<?php echo $alimento['data_validade']; ?>', '<?php echo htmlspecialchars($alimento['categoria']); ?>', '<?php echo htmlspecialchars($alimento['local_armazenamento']); ?>', '<?php echo htmlspecialchars($alimento['observacoes']); ?>')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aba de Histórico -->
        <div class="tab-pane fade" id="historico">
            <div class="row mb-4">
                <div class="col">
                    <h2>Histórico de Movimentações</h2>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Alimento</th>
                                    <th>Tipo</th>
                                    <th>Quantidade</th>
                                    <th>Motivo</th>
                                    <th>Responsável</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historico as $movimento): ?>
                                <tr>
                                    <td><?php echo $movimento['data_formatada']; ?></td>
                                    <td><?php echo htmlspecialchars($movimento['nome_alimento']); ?></td>
                                    <td>
                                        <?php if ($movimento['tipo_movimentacao'] === 'entrada'): ?>
                                            <span class="badge bg-success">Entrada</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Saída</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($movimento['quantidade']); ?></td>
                                    <td><?php echo htmlspecialchars($movimento['motivo']); ?></td>
                                    <td><?php echo htmlspecialchars($movimento['usuario_nome']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo/Editar Alimento -->
<div class="modal fade" id="modalAlimento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alimento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="adicionar" id="form_action">
                    <input type="hidden" name="id" id="alimento_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nome do Alimento</label>
                        <input type="text" class="form-control" name="nome_alimento" id="nome_alimento" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Quantidade</label>
                                <input type="number" step="0.01" class="form-control" name="quantidade" id="quantidade" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Unidade de Medida</label>
                                <select class="form-select" name="unidade_medida" id="unidade_medida" required>
                                    <option value="kg">Quilograma (kg)</option>
                                    <option value="g">Grama (g)</option>
                                    <option value="L">Litro (L)</option>
                                    <option value="ml">Mililitro (ml)</option>
                                    <option value="un">Unidade</option>
                                    <option value="cx">Caixa</option>
                                    <option value="pct">Pacote</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Data de Validade</label>
                        <input type="date" class="form-control" name="data_validade" id="data_validade">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Categoria</label>
                        <input type="text" class="form-control" name="categoria" id="categoria" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Local de Armazenamento</label>
                        <input type="text" class="form-control" name="local_armazenamento" id="local_armazenamento" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" name="observacoes" id="observacoes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <!-- Botão de Excluir (só aparece na edição) -->
                        <button type="button" class="btn btn-danger" id="btnExcluir" style="display: none;" onclick="confirmarExclusao()">
                            <i class="bi bi-trash"></i> Excluir
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir o alimento <strong id="alimentoNome"></strong>? Esta ação não pode ser desfeita e todo o histórico relacionado será excluído.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDelete">Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Resposta -->
<div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">Mensagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="responseMessage">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Observações -->
<div class="modal fade" id="modalObservacoes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Alimento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Informações Básicas</h6>
                        <div class="card">
                            <div class="card-body">
                                <p><strong>Nome:</strong> <span id="nome_alimento_obs"></span></p>
                                <p><strong>Quantidade:</strong> <span id="quantidade_obs"></span> <span id="unidade_medida_obs"></span></p>
                                <p><strong>Data de Validade:</strong> <span id="data_validade_obs"></span></p>
                                <p><strong>Categoria:</strong> <span id="categoria_obs"></span></p>
                                <p><strong>Local de Armazenamento:</strong> <span id="local_armazenamento_obs"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Observações Gerais</h6>
                        <div class="card">
                            <div class="card-body">
                                <p id="observacoes_gerais" class="text-muted"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6>Histórico de Movimentações</h6>
                        <div id="historico_observacoes" class="mt-2">
                            <!-- O histórico será preenchido via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Movimentação -->
<div class="modal fade" id="modalMovimentacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Movimentação de Estoque</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="movimentar">
                    <input type="hidden" name="alimento_id" id="mov_alimento_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Tipo de Movimentação</label>
                        <select class="form-select" name="tipo_movimentacao" required>
                            <option value="entrada">Entrada</option>
                            <option value="saida">Saída</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantidade</label>
                        <input type="number" step="0.01" class="form-control" name="quantidade" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Motivo</label>
                        <textarea class="form-control" name="motivo" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Estilização das abas */
.nav-tabs .nav-link {
    color: #495057;
    font-weight: 500;
    padding: 12px 20px;
    border-radius: 0;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    background-color: #f8f9fa;
    border-color: transparent;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 3px solid #0d6efd;
}

/* Estilização da tabela */
.table {
    margin-bottom: 0;
}

.table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.875rem;
}

.table tbody td {
    vertical-align: middle;
    padding: 12px;
}

/* Estilização dos cards */
.card {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,0.05);
    border-radius: 8px;
}

.card-body {
    padding: 1.5rem;
}

/* Estilização dos botões */
.btn-primary {
    padding: 8px 16px;
    font-weight: 500;
}

.btn-sm {
    padding: 4px 8px;
    margin: 0 2px;
}

.btn-info {
    background-color: #0dcaf0;
    border-color: #0dcaf0;
    color: white;
}

.btn-info:hover {
    background-color: #31d2f2;
    border-color: #25cff2;
    color: white;
}

/* Estilização dos badges */
.badge {
    padding: 6px 10px;
    font-weight: 500;
}

/* Estilização do modal */
.modal-content {
    border: none;
    border-radius: 8px;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 8px 8px 0 0;
}

.modal-title {
    font-weight: 600;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    padding: 8px 12px;
    border-radius: 4px;
}

/* Responsividade */
@media (max-width: 768px) {
    .table-responsive {
        margin: 0 -1.5rem;
    }
    
    .btn-sm {
        padding: 6px 12px;
        margin-bottom: 4px;
    }
}
</style>

<script>
// Inicializa os modais do Bootstrap
let confirmModal;
let responseModal;
let modalAlimento;
let modalObservacoes;

// Inicializa os eventos após o carregamento da página
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa os modais
    confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
    modalAlimento = new bootstrap.Modal(document.getElementById('modalAlimento'));
    modalObservacoes = new bootstrap.Modal(document.getElementById('modalObservacoes'));

    // Vincula o evento de clique ao botão de confirmação de exclusão
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    if (btnConfirmDelete) {
        btnConfirmDelete.addEventListener('click', function() {
            const id = document.getElementById('alimento_id').value;
            
            fetch('api/delete_alimento.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id
                })
            })
            .then(response => response.json())
            .then(data => {
                confirmModal.hide();
                const responseMessage = document.getElementById('responseMessage');
                
                if (data.success) {
                    responseMessage.textContent = 'Alimento excluído com sucesso!';
                    responseModal.show();
                    
                    // Adiciona evento para recarregar após fechar o modal
                    document.getElementById('responseModal').addEventListener('hidden.bs.modal', function handler() {
                        window.location.reload();
                        this.removeEventListener('hidden.bs.modal', handler);
                    });
                } else {
                    responseMessage.textContent = 'Erro ao excluir alimento: ' + data.message;
                    responseModal.show();
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                confirmModal.hide();
                const responseMessage = document.getElementById('responseMessage');
                responseMessage.textContent = 'Erro ao excluir alimento. Por favor, tente novamente.';
                responseModal.show();
            });
        });
    }
});

function novoAlimento() {
    document.getElementById('form_action').value = 'adicionar';
    document.getElementById('alimento_id').value = '';
    document.getElementById('nome_alimento').value = '';
    document.getElementById('quantidade').value = '';
    document.getElementById('unidade_medida').value = 'kg';
    document.getElementById('data_validade').value = '';
    document.getElementById('categoria').value = '';
    document.getElementById('local_armazenamento').value = '';
    document.getElementById('observacoes').value = '';
    document.getElementById('btnExcluir').style.display = 'none'; // Esconde botão de excluir
}

function editarAlimento(id, nome, quantidade, unidade, validade, categoria, local, observacoes) {
    document.getElementById('form_action').value = 'atualizar';
    document.getElementById('alimento_id').value = id;
    document.getElementById('nome_alimento').value = nome;
    document.getElementById('quantidade').value = quantidade;
    document.getElementById('unidade_medida').value = unidade;
    document.getElementById('data_validade').value = validade;
    document.getElementById('categoria').value = categoria;
    document.getElementById('local_armazenamento').value = local;
    document.getElementById('observacoes').value = observacoes;
    document.getElementById('btnExcluir').style.display = 'block'; // Mostra botão de excluir
    
    modalAlimento.show();
}

function verObservacoes(id, nome, quantidade, unidade, validade, categoria, local, observacoes) {
    try {
        // Preenche as informações básicas
        document.getElementById('nome_alimento_obs').textContent = nome || '-';
        document.getElementById('quantidade_obs').textContent = quantidade || '0';
        document.getElementById('unidade_medida_obs').textContent = unidade || '-';
        
        // Formatação da data de validade
        let dataFormatada = '-';
        if (validade) {
            try {
                const data = new Date(validade);
                if (!isNaN(data.getTime())) {
                    dataFormatada = data.toLocaleDateString('pt-BR');
                }
            } catch (e) {
                console.error('Erro ao formatar data:', e);
            }
        }
        document.getElementById('data_validade_obs').textContent = dataFormatada;
        
        document.getElementById('categoria_obs').textContent = categoria || '-';
        document.getElementById('local_armazenamento_obs').textContent = local || '-';
        document.getElementById('observacoes_gerais').textContent = observacoes || 'Nenhuma observação registrada.';
        
        // Limpa o histórico anterior
        const historicoDiv = document.getElementById('historico_observacoes');
        historicoDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div></div>';
        
        // Buscar histórico de movimentações para este alimento
        fetch(`api/get_historico.php?id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao carregar histórico');
                }
                return response.json();
            })
            .then(data => {
                historicoDiv.innerHTML = '';
                
                if (!data.historico || data.historico.length === 0) {
                    historicoDiv.innerHTML = '<div class="alert alert-info">Nenhuma movimentação registrada.</div>';
                    return;
                }
                
                data.historico.forEach(movimento => {
                    const div = document.createElement('div');
                    div.className = 'card mb-2';
                    
                    // Determinar o tipo de movimentação e a cor do badge
                    const tipoMovimentacao = movimento.tipo_movimentacao === 'entrada' ? 'Entrada' : 'Saída';
                    const badgeClass = movimento.tipo_movimentacao === 'entrada' ? 'bg-success' : 'bg-danger';
                    const unidadeMedida = movimento.unidade_medida || unidade;
                    
                    div.innerHTML = `
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">${movimento.data_formatada}</small>
                                <span class="badge ${badgeClass}">${tipoMovimentacao}</span>
                            </div>
                            <p class="mb-1"><strong>Quantidade Movimentada:</strong> ${movimento.quantidade_formatada} ${unidadeMedida}</p>
                            <p class="mb-1"><strong>Quantidade Atual:</strong> ${movimento.quantidade_atual} ${unidadeMedida}</p>
                            <p class="mb-1"><strong>Motivo:</strong> ${movimento.motivo || '-'}</p>
                            <p class="mb-0"><strong>Responsável:</strong> ${movimento.usuario_nome || '-'}</p>
                        </div>
                    `;
                    historicoDiv.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Erro ao carregar histórico:', error);
                historicoDiv.innerHTML = '<div class="alert alert-info">Nenhuma movimentação registrada ainda.</div>';
            });
        
        // Mostra o modal
        if (typeof modalObservacoes === 'undefined') {
            modalObservacoes = new bootstrap.Modal(document.getElementById('modalObservacoes'));
        }
        modalObservacoes.show();
    } catch (error) {
        console.error('Erro ao exibir observações:', error);
        alert('Erro ao exibir as informações do alimento. Por favor, tente novamente.');
    }
}

function movimentarEstoque(id) {
    document.getElementById('mov_alimento_id').value = id;
    $('#modalMovimentacao').modal('show');
}

function confirmarExclusao() {
    const id = document.getElementById('alimento_id').value;
    const nome = document.getElementById('nome_alimento').value;
    
    document.getElementById('alimentoNome').textContent = nome;
    modalAlimento.hide(); // Esconde o modal de edição
    confirmModal.show();
}
</script> 