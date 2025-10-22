<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Verificar se a conexão com o banco de dados está funcionando
if (!isset($pdo)) {
    die('Erro: Conexão com o banco de dados não estabelecida');
}

// Inicializar variáveis
$success_msg = '';
$error_msg = '';

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = $_POST['descricao'] ?? '';
    $responsavel = $_POST['responsavel'] ?? '';
    $responsavel_autorizacao = $_POST['responsavel_autorizacao'] ?? '';
    
    // Tratamento de valores monetários
    $adiantamento_1 = !empty($_POST['adiantamento_1']) ? str_replace(',', '.', $_POST['adiantamento_1']) : null;
    $adiantamento_2 = !empty($_POST['adiantamento_2']) ? str_replace(',', '.', $_POST['adiantamento_2']) : null;
    $adiantamento_3 = !empty($_POST['adiantamento_3']) ? str_replace(',', '.', $_POST['adiantamento_3']) : null;
    $valor_antecipado = !empty($_POST['valor_antecipado']) ? str_replace(',', '.', $_POST['valor_antecipado']) : null;
    $total = !empty($_POST['total']) ? str_replace(',', '.', $_POST['total']) : null;
    $falta_pagar = !empty($_POST['falta_pagar']) ? str_replace(',', '.', $_POST['falta_pagar']) : null;
    
    // Função para converter data de dd/mm/yyyy para yyyy-mm-dd
    function convertDateToDB($date) {
        if (empty($date)) return null;
        $date = str_replace('/', '-', $date);
        return date('Y-m-d', strtotime(str_replace('-', '/', $date)));
    }

    // Tratamento de datas
    $data_adiant_1 = convertDateToDB($_POST['data_adiant_1'] ?? '');
    $data_adiant_2 = convertDateToDB($_POST['data_adiant_2'] ?? '');
    $data_adiant_3 = convertDateToDB($_POST['data_adiant_3'] ?? '');
    $previsao_entrega = convertDateToDB($_POST['previsao_entrega'] ?? '');
    $data_ordem_servico = convertDateToDB($_POST['data_ordem_servico'] ?? '');
    $data_previsao_entrega = convertDateToDB($_POST['data_previsao_entrega'] ?? '');
    $data_entrega_final = convertDateToDB($_POST['data_entrega_final'] ?? '');
    
    $status = $_POST['status'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';

    // Debug - Mostrar valores recebidos
    error_log('Dados recebidos: ' . print_r($_POST, true));

    // Validar campos obrigatórios
    if (empty($descricao) || empty($responsavel) || empty($status)) {
        $error_msg = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        try {
            // Preparar a query com tratamento para NULL
            $stmt = $pdo->prepare("INSERT INTO obras_servicos (
                descricao, responsavel, responsavel_autorizacao, 
                adiantamento_1, data_adiant_1, adiantamento_2, data_adiant_2, 
                adiantamento_3, data_adiant_3, valor_antecipado, total, falta_pagar, 
                status, previsao_entrega, data_ordem_servico, data_previsao_entrega, 
                data_entrega_final, observacoes
            ) VALUES (
                ?, ?, ?, 
                NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), 
                NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), 
                ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), 
                NULLIF(?, ''), ?
            )");
            if ($stmt->execute([
                $descricao, $responsavel, $responsavel_autorizacao, 
                $adiantamento_1, $data_adiant_1, $adiantamento_2, 
                $data_adiant_2, $adiantamento_3, $data_adiant_3, 
                $valor_antecipado, $total, $falta_pagar, $status, 
                $previsao_entrega, $data_ordem_servico, $data_previsao_entrega, 
                $data_entrega_final, $observacoes
            ])) {
                $success_msg = "Serviço de obra cadastrado com sucesso!";
                // Limpar os campos após o sucesso
                // Limpar todos os campos
                $descricao = $responsavel = $responsavel_autorizacao = '';
                $adiantamento_1 = $adiantamento_2 = $adiantamento_3 = '';
                $data_adiant_1 = $data_adiant_2 = $data_adiant_3 = '';
                $valor_antecipado = $total = $falta_pagar = '';
                $status = '';
                $data_ordem_servico = $data_previsao_entrega = $data_entrega_final = '';
                $observacoes = '';
            } else {
                $error_msg = "Erro ao cadastrar o serviço de obra.";
                error_log('Erro no execute: ' . print_r($stmt->errorInfo(), true));
            }
        } catch (PDOException $e) {
            $error_msg = "Erro ao cadastrar: " . $e->getMessage();
            error_log('PDOException: ' . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Obra - Sistema de Obras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Cadastro de Serviço de Obra</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success_msg): ?>
                            <div class="alert alert-success"><?php echo $success_msg; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error_msg): ?>
                            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle"></i> Campos marcados com * são obrigatórios.
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="descricao" class="form-label">Descrição da Obra *</label>
                                    <textarea class="form-control" id="descricao" name="descricao" rows="3" required><?php echo htmlspecialchars($descricao ?? ''); ?></textarea>
                                    <div class="invalid-feedback">
                                        Por favor, forneça uma descrição para a obra.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="responsavel" class="form-label">Responsável pela Obra *</label>
                                    <input type="text" class="form-control" id="responsavel" name="responsavel" value="<?php echo htmlspecialchars($responsavel ?? ''); ?>" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="responsavel_autorizacao" class="form-label">Responsável pela Autorização</label>
                                    <input type="text" class="form-control" id="responsavel_autorizacao" name="responsavel_autorizacao" value="<?php echo htmlspecialchars($responsavel_autorizacao ?? ''); ?>">
                                    <div class="invalid-feedback">Por favor, informe o responsável pela obra.</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="responsavel_autorizacao" class="form-label">Responsável pela Autorização</label>
                                    <input type="text" class="form-control" id="responsavel_autorizacao" name="responsavel_autorizacao" value="<?php echo htmlspecialchars($responsavel_autorizacao ?? ''); ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="adiantamento_1" class="form-label text-muted">1º Adiantamento (R$) <small>(opcional)</small></label>
                                    <input type="number" step="0.01" class="form-control" id="adiantamento_1" name="adiantamento_1" value="<?php echo htmlspecialchars($adiantamento_1 ?? ''); ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="data_adiant_1" class="form-label text-muted">Data do 1º Adiantamento <small>(opcional)</small></label>
                                    <input type="text" class="form-control datepicker" id="data_adiant_1" name="data_adiant_1" value="<?php echo !empty($data_adiant_1) ? date('d/m/Y', strtotime($data_adiant_1)) : ''; ?>" placeholder="dd/mm/aaaa">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="adiantamento_2" class="form-label text-muted">2º Adiantamento (R$) <small>(opcional)</small></label>
                                    <input type="number" step="0.01" class="form-control" id="adiantamento_2" name="adiantamento_2" value="<?php echo htmlspecialchars($adiantamento_2 ?? ''); ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="data_adiant_2" class="form-label text-muted">Data do 2º Adiantamento <small>(opcional)</small></label>
                                    <input type="text" class="form-control datepicker" id="data_adiant_2" name="data_adiant_2" value="<?php echo !empty($data_adiant_2) ? date('d/m/Y', strtotime($data_adiant_2)) : ''; ?>" placeholder="dd/mm/aaaa">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="adiantamento_3" class="form-label text-muted">3º Adiantamento (R$) <small>(opcional)</small></label>
                                    <input type="number" step="0.01" class="form-control" id="adiantamento_3" name="adiantamento_3" value="<?php echo htmlspecialchars($adiantamento_3 ?? ''); ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="data_adiant_3" class="form-label text-muted">Data do 3º Adiantamento <small>(opcional)</small></label>
                                    <input type="text" class="form-control datepicker" id="data_adiant_3" name="data_adiant_3" value="<?php echo !empty($data_adiant_3) ? date('d/m/Y', strtotime($data_adiant_3)) : ''; ?>" placeholder="dd/mm/aaaa">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="valor_antecipado" class="form-label text-muted">Valor Antecipado Total (R$) <small>(calculado automaticamente)</small></label>
                                    <input type="number" step="0.01" class="form-control" id="valor_antecipado" name="valor_antecipado" value="<?php echo htmlspecialchars($valor_antecipado ?? ''); ?>" readonly>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="total" class="form-label text-muted">Valor Total da Obra (R$) <small>(opcional)</small></label>
                                    <input type="number" step="0.01" class="form-control" id="total" name="total" value="<?php echo htmlspecialchars($total ?? ''); ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="falta_pagar" class="form-label text-muted">Valor Faltante (R$) <small>(calculado automaticamente)</small></label>
                                    <input type="number" step="0.01" class="form-control" id="falta_pagar" name="falta_pagar" value="<?php echo htmlspecialchars($falta_pagar ?? ''); ?>" readonly>
                                </div>

                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    function calcularValorFaltante() {
                                        const total = parseFloat(document.getElementById('total').value) || 0;
                                        const adiant1 = parseFloat(document.getElementById('adiantamento_1').value) || 0;
                                        const adiant2 = parseFloat(document.getElementById('adiantamento_2').value) || 0;
                                        const adiant3 = parseFloat(document.getElementById('adiantamento_3').value) || 0;
                                        
                                        const totalAdiantamentos = adiant1 + adiant2 + adiant3;
                                        document.getElementById('valor_antecipado').value = totalAdiantamentos.toFixed(2);
                                        
                                        const faltaPagar = total - totalAdiantamentos;
                                        document.getElementById('falta_pagar').value = faltaPagar.toFixed(2);
                                    }

                                    // Adicionar eventos para recalcular quando os valores mudarem
                                    document.getElementById('total').addEventListener('input', calcularValorFaltante);
                                    document.getElementById('adiantamento_1').addEventListener('input', calcularValorFaltante);
                                    document.getElementById('adiantamento_2').addEventListener('input', calcularValorFaltante);
                                    document.getElementById('adiantamento_3').addEventListener('input', calcularValorFaltante);
                                });
                                </script>

                                <div class="col-md-3 mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Selecione...</option>
                                        <option value="Em Andamento" <?php echo ($status ?? '') === 'Em Andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                        <option value="Pendente" <?php echo ($status ?? '') === 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="Concluído" <?php echo ($status ?? '') === 'Concluído' ? 'selected' : ''; ?>>Concluído</option>
                                        <option value="Cancelado" <?php echo ($status ?? '') === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                    <div class="invalid-feedback">Por favor, selecione o status da obra.</div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="data_ordem_servico" class="form-label">Data da Ordem de Serviço</label>
                                    <input type="text" class="form-control datepicker" id="data_ordem_servico" name="data_ordem_servico" value="<?php echo !empty($data_ordem_servico) ? date('d/m/Y', strtotime($data_ordem_servico)) : ''; ?>" placeholder="dd/mm/aaaa">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="data_previsao_entrega" class="form-label">Previsão de Entrega</label>
                                    <input type="text" class="form-control datepicker" id="data_previsao_entrega" name="data_previsao_entrega" value="<?php echo !empty($data_previsao_entrega) ? date('d/m/Y', strtotime($data_previsao_entrega)) : ''; ?>" placeholder="dd/mm/aaaa">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="data_entrega_final" class="form-label">Data de Entrega Final</label>
                                    <input type="text" class="form-control datepicker" id="data_entrega_final" name="data_entrega_final" value="<?php echo !empty($data_entrega_final) ? date('d/m/Y', strtotime($data_entrega_final)) : ''; ?>" placeholder="dd/mm/aaaa">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="observacoes" class="form-label">Observações</label>
                                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?php echo htmlspecialchars($observacoes ?? ''); ?></textarea>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Salvar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <script>
    // Inicializar datepicker em todos os campos de data
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr('.datepicker', {
            dateFormat: 'd/m/Y',
            locale: 'pt',
            allowInput: true
        });
    });

    // Ativar validação do Bootstrap
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>
