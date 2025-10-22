<?php
session_start();

// Evitar cache da página
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/get_arquivos.php';

// Verificar se a conexão com o banco de dados está funcionando
if (!isset($pdo)) {
    die('Erro: Conexão com o banco de dados não estabelecida');
}

// Inicializar variáveis
$success_msg = '';
$error_msg = '';

// Funções para conversão de datas
function convertDateToDB($date) {
    if (empty($date)) return null;
    
    // Remove espaços em branco
    $date = trim($date);
    
    // Verifica se a data já está no formato yyyy-mm-dd
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $date;
    }
    
    // Converte dd/mm/yyyy para yyyy-mm-dd
    $parts = explode('/', $date);
    if (count($parts) === 3) {
        $day = $parts[0];
        $month = $parts[1];
        $year = $parts[2];
        
        // Valida os componentes da data
        if (checkdate((int)$month, (int)$day, (int)$year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
    }
    
    return null;
}

// Verificar se foi fornecido um ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID inválido');
}

$id = (int)$_GET['id'];

// Forçar recarregamento dos dados do banco
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Incluir arquivos necessários
require_once __DIR__ . '/../includes/upload_handler.php';
require_once __DIR__ . '/../includes/get_obra.php';

// Buscar os dados da obra
try {
    $stmt = $pdo->prepare("SELECT * FROM obras_servicos WHERE id = ?");
    $stmt->execute([$id]);
    $obra = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$obra) {
        $_SESSION['error_msg'] = 'Serviço não encontrado.';
        header('Location: /gerencialParoquia/projetos-modulos/obras/pages/relatorios.php');
        exit();
    }

    // Converter datas para o formato brasileiro
    if (!empty($obra['data_ordem_servico'])) {
        $obra['data_ordem_servico'] = date('d/m/Y', strtotime($obra['data_ordem_servico']));
    }
    if (!empty($obra['previsao_entrega'])) {
        $obra['previsao_entrega'] = date('d/m/Y', strtotime($obra['previsao_entrega']));
    }
} catch (PDOException $e) {
    die('Erro ao buscar obra: ' . $e->getMessage());
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar campos obrigatórios
        if (empty($_POST['descricao']) || empty($_POST['responsavel']) || empty($_POST['status'])) {
            throw new Exception("Por favor, preencha todos os campos obrigatórios.");
        }

        // Iniciar transação
        $pdo->beginTransaction();
            // Calcular o valor antecipado total
            $valor_antecipado = (
                (float)($_POST['adiantamento_1'] ?? 0) + 
                (float)($_POST['adiantamento_2'] ?? 0) + 
                (float)($_POST['adiantamento_3'] ?? 0)
            );

            // Preparar os dados para atualização
            $data_ordem_servico = !empty($_POST['data_ordem_servico']) ? convertDateToDB($_POST['data_ordem_servico']) : null;
            $previsao_entrega = !empty($_POST['previsao_entrega']) ? convertDateToDB($_POST['previsao_entrega']) : null;
            $data_entrega_final = !empty($_POST['data_entrega_final']) ? convertDateToDB($_POST['data_entrega_final']) : null;
            $data_adiant_1 = !empty($_POST['data_adiant_1']) ? convertDateToDB($_POST['data_adiant_1']) : null;
            $data_adiant_2 = !empty($_POST['data_adiant_2']) ? convertDateToDB($_POST['data_adiant_2']) : null;
            $data_adiant_3 = !empty($_POST['data_adiant_3']) ? convertDateToDB($_POST['data_adiant_3']) : null;

            // Construir a query de atualização dinamicamente
            $sql = "UPDATE obras_servicos SET 
                descricao = :descricao,
                responsavel = :responsavel,
                responsavel_autorizacao = :responsavel_autorizacao,
                status = :status,
                total = :total,
                adiantamento_1 = :adiantamento_1,
                adiantamento_2 = :adiantamento_2,
                adiantamento_3 = :adiantamento_3,
                data_adiant_1 = :data_adiant_1,
                data_adiant_2 = :data_adiant_2,
                data_adiant_3 = :data_adiant_3,
                valor_antecipado = :valor_antecipado,
                data_entrega_final = :data_entrega_final,
                observacoes = :observacoes";

            // Só incluir data_ordem_servico e previsao_entrega se foram alteradas
            // Converter as datas do banco para o mesmo formato dd/mm/yyyy para comparação
            $data_ordem_servico_atual = $obra['data_ordem_servico'] ? date('d/m/Y', strtotime($obra['data_ordem_servico'])) : '';
            $previsao_entrega_atual = $obra['previsao_entrega'] ? date('d/m/Y', strtotime($obra['previsao_entrega'])) : '';
            
            // Remover espaços em branco para comparação
            $nova_data_ordem = trim($_POST['data_ordem_servico'] ?? '');
            $nova_previsao = trim($_POST['previsao_entrega'] ?? '');
            
            if ($nova_data_ordem !== $data_ordem_servico_atual) {
                $sql .= ", data_ordem_servico = :data_ordem_servico";
            }
            if ($nova_previsao !== $previsao_entrega_atual) {
                $sql .= ", previsao_entrega = :previsao_entrega";
            }

            $sql .= " WHERE id = :id";

            $stmt = $pdo->prepare($sql);

            // Preparar os parâmetros básicos
            $params = [
                ':descricao' => $_POST['descricao'],
                ':responsavel' => $_POST['responsavel'],
                ':responsavel_autorizacao' => $_POST['responsavel_autorizacao'] ?? null,
                ':status' => $_POST['status'],
                ':total' => $_POST['total'] ?: null,
                ':adiantamento_1' => $_POST['adiantamento_1'] ?: null,
                ':adiantamento_2' => $_POST['adiantamento_2'] ?: null,
                ':adiantamento_3' => $_POST['adiantamento_3'] ?: null,
                ':data_adiant_1' => $data_adiant_1,
                ':data_adiant_2' => $data_adiant_2,
                ':data_adiant_3' => $data_adiant_3,
                ':valor_antecipado' => $valor_antecipado ?: null,
                ':data_entrega_final' => $data_entrega_final,
                ':observacoes' => $_POST['observacoes'] ?? null,
                ':id' => $id
            ];

            // Adicionar parâmetros de data apenas se foram alterados
            if ($nova_data_ordem !== $data_ordem_servico_atual) {
                $params[':data_ordem_servico'] = $data_ordem_servico;
            }
            if ($nova_previsao !== $previsao_entrega_atual) {
                $params[':previsao_entrega'] = $previsao_entrega;
            }

            $stmt->execute($params);

            // Processar uploads de arquivos
            $tipos = ['comprovante_pagamento', 'nota_fiscal', 'ordem_servico'];
            
            foreach ($tipos as $tipo) {
                if (isset($_FILES[$tipo]) && is_array($_FILES[$tipo]['name'])) {
                    $total = count($_FILES[$tipo]['name']);
                    
                    for ($i = 0; $i < $total; $i++) {
                        if ($_FILES[$tipo]['error'][$i] === UPLOAD_ERR_OK) {
                            // Criar diretório se não existir
                            $uploadDir = __DIR__ . '/../uploads/' . $id;
                            if (!file_exists($uploadDir)) {
                                mkdir($uploadDir, 0777, true);
                            }
                            
                            // Informações do arquivo
                            $fileName = $_FILES[$tipo]['name'][$i];
                            $tmpName = $_FILES[$tipo]['tmp_name'][$i];
                            $fileSize = $_FILES[$tipo]['size'][$i];
                            $fileType = $_FILES[$tipo]['type'][$i];
                            
                            // Verificar tipo de arquivo
                            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                            if (!in_array($fileType, $allowedTypes)) {
                                throw new Exception("Tipo de arquivo não permitido: $fileName. Apenas PDF, JPEG e PNG são aceitos.");
                            }
                            
                            // Verificar tamanho (máximo 5MB)
                            if ($fileSize > 5 * 1024 * 1024) {
                                throw new Exception("Arquivo muito grande: $fileName. Tamanho máximo: 5MB");
                            }
                            
                            // Gerar nome único
                            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                            $uniqueName = $tipo . '_' . uniqid() . '_' . date('Ymd_His') . '.' . $extension;
                            $targetFile = $uploadDir . '/' . $uniqueName;
                            
                            // Mover arquivo
                            if (move_uploaded_file($tmpName, $targetFile)) {
                                // Salvar no banco
                                $stmt = $pdo->prepare("INSERT INTO obras_servicos_arquivos (servico_id, tipo, nome_arquivo, caminho_arquivo, data_upload) VALUES (?, ?, ?, ?, NOW())");
                                $caminhoRelativo = 'uploads/' . $id . '/' . $uniqueName;
                                $stmt->execute([$id, $tipo, $fileName, $caminhoRelativo]);
                            } else {
                                throw new Exception("Erro ao mover arquivo: $fileName");
                            }
                        }
                    }
                }
            }

            // Commit da transação
            $pdo->commit();
            
            $_SESSION['success_msg'] = "Serviço atualizado com sucesso!";
            header("Location: /gerencialParoquia/projetos-modulos/obras/pages/visualizar_obra.php?id=" . $id);
            exit();

    } catch (Exception $e) {
        // Rollback em caso de erro
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_msg = $e->getMessage();
        error_log('Erro na atualização do serviço: ' . $e->getMessage());
    }



}

// Buscar dados atualizados da obra após qualquer alteração
try {
    // Usar a função getObra para garantir dados atualizados
    $obra = getObra($pdo, $id);
    if (!$obra) {
        throw new Exception('Obra não encontrada');
    }

    if (!$obra) {
        $_SESSION['error_msg'] = 'Serviço não encontrado.';
        header('Location: /gerencialParoquia/projetos-modulos/obras/pages/relatorios.php');
        exit();
    }
} catch (PDOException $e) {
    die('Erro ao buscar obra: ' . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Obra - Sistema de Obras</title>
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
                        <h4 class="mb-0">Editar Obra</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success_msg'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error_msg'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?></div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                            <div class="row">
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle"></i> Campos marcados com * são obrigatórios.
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="descricao" class="form-label">Descrição da Obra *</label>
                                    <textarea class="form-control" id="descricao" name="descricao" rows="3" required><?php echo htmlspecialchars($obra['descricao'] ?? ''); ?></textarea>
                                    <div class="invalid-feedback">
                                        Por favor, forneça uma descrição para a obra.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="responsavel" class="form-label">Responsável pela Obra *</label>
                                    <input type="text" class="form-control" id="responsavel" name="responsavel" value="<?php echo htmlspecialchars($obra['responsavel'] ?? ''); ?>" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="responsavel_autorizacao" class="form-label">Responsável pela Autorização</label>
                                    <input type="text" class="form-control" id="responsavel_autorizacao" name="responsavel_autorizacao" value="<?php echo htmlspecialchars($obra['responsavel_autorizacao'] ?? ''); ?>">
                                    <div class="invalid-feedback">Por favor, informe o responsável técnico pela obra.</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="responsavel_autorizacao" class="form-label">Responsável pela Autorização</label>
                                    <input type="text" class="form-control" id="responsavel_autorizacao" name="responsavel_autorizacao" value="<?php echo htmlspecialchars($obra['responsavel_autorizacao'] ?? ''); ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status do Serviço *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Selecione o status</option>
                                        <option value="Em Andamento" <?php echo ($obra['status'] ?? '') === 'Em Andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                        <option value="Concluído" <?php echo ($obra['status'] ?? '') === 'Concluído' ? 'selected' : ''; ?>>Concluído</option>
                                        <option value="Pendente" <?php echo ($obra['status'] ?? '') === 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="Cancelado" <?php echo ($obra['status'] ?? '') === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                    <div class="invalid-feedback">Por favor, selecione o status do serviço.</div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="total" class="form-label">Valor Total (R$) *</label>
                                    <input type="number" step="0.01" class="form-control" id="total" name="total" value="<?php echo htmlspecialchars($obra['total'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">Por favor, informe o valor total do serviço.</div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="adiantamento_1" class="form-label text-muted">1º Adiantamento (R$) <small>(opcional)</small></label>
                                    <input type="number" step="0.01" class="form-control" id="adiantamento_1" name="adiantamento_1" value="<?php echo htmlspecialchars($obra['adiantamento_1'] ?? ''); ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="data_adiant_1" class="form-label text-muted">Data do 1º Adiantamento <small>(opcional)</small></label>
                                    <input type="text" class="form-control datepicker" id="data_adiant_1" name="data_adiant_1" value="<?php echo !empty($obra['data_adiant_1']) ? date('d/m/Y', strtotime($obra['data_adiant_1'])) : ''; ?>" placeholder="dd/mm/aaaa">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="adiantamento_2" class="form-label text-muted">2º Adiantamento (R$) <small>(opcional)</small></label>
                                    <input type="number" step="0.01" class="form-control" id="adiantamento_2" name="adiantamento_2" value="<?php echo htmlspecialchars($obra['adiantamento_2'] ?? ''); ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="data_adiant_2" class="form-label text-muted">Data do 2º Adiantamento <small>(opcional)</small></label>
                                    <input type="text" class="form-control datepicker" id="data_adiant_2" name="data_adiant_2" value="<?php echo !empty($obra['data_adiant_2']) ? date('d/m/Y', strtotime($obra['data_adiant_2'])) : ''; ?>" placeholder="dd/mm/aaaa">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="adiantamento_3" class="form-label text-muted">3º Adiantamento (R$) <small>(opcional)</small></label>
                                    <input type="number" step="0.01" class="form-control" id="adiantamento_3" name="adiantamento_3" value="<?php echo htmlspecialchars($obra['adiantamento_3'] ?? ''); ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="data_adiant_3" class="form-label text-muted">Data do 3º Adiantamento <small>(opcional)</small></label>
                                    <input type="text" class="form-control datepicker" id="data_adiant_3" name="data_adiant_3" value="<?php echo !empty($obra['data_adiant_3']) ? date('d/m/Y', strtotime($obra['data_adiant_3'])) : ''; ?>" placeholder="dd/mm/aaaa">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="valor_antecipado" class="form-label text-muted">Valor Antecipado Total (R$) <small>(calculado automaticamente)</small></label>
                                    <input type="number" step="0.01" class="form-control" id="valor_antecipado" name="valor_antecipado" value="<?php echo htmlspecialchars($obra['valor_antecipado'] ?? ''); ?>" readonly>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="total" class="form-label text-muted">Valor Total da Obra (R$) <small>(opcional)</small></label>
                                    <input type="number" step="0.01" class="form-control" id="total" name="total" value="<?php echo htmlspecialchars($obra['total'] ?? ''); ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="falta_pagar" class="form-label text-muted">Valor Faltante (R$) <small>(calculado automaticamente)</small></label>
                                    <input type="number" step="0.01" class="form-control" id="falta_pagar" name="falta_pagar" value="<?php echo htmlspecialchars($obra['falta_pagar'] ?? ''); ?>" readonly>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="status" class="form-label">Status da Obra *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Selecione...</option>
                                        <option value="Em Andamento" <?php echo ($obra['status'] ?? '') == 'Em Andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                        <option value="Concluído" <?php echo ($obra['status'] ?? '') == 'Concluído' ? 'selected' : ''; ?>>Concluído</option>
                                        <option value="Pendente" <?php echo ($obra['status'] ?? '') == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="Cancelado" <?php echo ($obra['status'] ?? '') == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                    <div class="invalid-feedback">Por favor, selecione o status da obra.</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="data_ordem_servico" class="form-label">Data da Ordem de Serviço</label>
                                    <input type="text" class="form-control datepicker" id="data_ordem_servico" name="data_ordem_servico" value="<?php echo htmlspecialchars($obra['data_ordem_servico'] ?? ''); ?>" placeholder="dd/mm/aaaa">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="previsao_entrega" class="form-label">Previsão de Entrega</label>
                                    <input type="text" class="form-control datepicker" id="previsao_entrega" name="previsao_entrega" value="<?php echo htmlspecialchars($obra['previsao_entrega'] ?? ''); ?>" placeholder="dd/mm/aaaa">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="data_entrega_final" class="form-label">Data de Entrega Final</label>
                                    <input type="text" class="form-control datepicker" id="data_entrega_final" name="data_entrega_final" value="<?php echo !empty($obra['data_entrega_final']) ? date('d/m/Y', strtotime($obra['data_entrega_final'])) : ''; ?>" placeholder="dd/mm/aaaa">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="observacoes" class="form-label">Observações</label>
                                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?php echo htmlspecialchars($obra['observacoes'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!-- Seção de Arquivos -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5 class="mb-3">Arquivos da Obra</h5>
                                </div>

                                <!-- Comprovante de Pagamento -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Comprovante de Pagamento</label>
                                    <input type="file" class="form-control" name="comprovante_pagamento[]" multiple accept=".pdf,.jpg,.jpeg,.png">
                                    <?php 
                                    $arquivos = getArquivosServico($pdo, $id, 'comprovante_pagamento');
                                    if (!empty($arquivos)): 
                                    ?>
                                        <div class="list-group mt-2">
                                        <?php foreach ($arquivos as $arquivo): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <a href="/gerencialParoquia/projetos-modulos/obras/<?php echo htmlspecialchars($arquivo['caminho_arquivo']); ?>" 
                                                   target="_blank" 
                                                   class="text-decoration-none">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                    <?php echo htmlspecialchars($arquivo['nome_arquivo']); ?>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="excluirArquivo(<?php echo $arquivo['id']; ?>, '<?php echo htmlspecialchars($arquivo['nome_arquivo']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Nota Fiscal -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nota Fiscal</label>
                                    <input type="file" class="form-control" name="nota_fiscal[]" multiple accept=".pdf,.jpg,.jpeg,.png">
                                    <?php 
                                    $arquivos = getArquivosServico($pdo, $id, 'nota_fiscal');
                                    if (!empty($arquivos)): 
                                    ?>
                                        <div class="list-group mt-2">
                                        <?php foreach ($arquivos as $arquivo): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <a href="/gerencialParoquia/projetos-modulos/obras/<?php echo htmlspecialchars($arquivo['caminho_arquivo']); ?>" 
                                                   target="_blank" 
                                                   class="text-decoration-none">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                    <?php echo htmlspecialchars($arquivo['nome_arquivo']); ?>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="excluirArquivo(<?php echo $arquivo['id']; ?>, '<?php echo htmlspecialchars($arquivo['nome_arquivo']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Ordem de Serviço -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Ordem de Serviço</label>
                                    <input type="file" class="form-control" name="ordem_servico[]" multiple accept=".pdf,.jpg,.jpeg,.png">
                                    <?php 
                                    $arquivos = getArquivosServico($pdo, $id, 'ordem_servico');
                                    if (!empty($arquivos)): 
                                    ?>
                                        <div class="list-group mt-2">
                                        <?php foreach ($arquivos as $arquivo): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <a href="/gerencialParoquia/projetos-modulos/obras/<?php echo htmlspecialchars($arquivo['caminho_arquivo']); ?>" 
                                                   target="_blank" 
                                                   class="text-decoration-none">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                    <?php echo htmlspecialchars($arquivo['nome_arquivo']); ?>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="excluirArquivo(<?php echo $arquivo['id']; ?>, '<?php echo htmlspecialchars($arquivo['nome_arquivo']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Salvar Alterações
                                    </button>
                                    <a href="/gerencialParoquia/projetos-modulos/obras/index.php?page=dashboard" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </a>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {

        // Função para calcular o valor total antecipado
        function calcularValorAntecipado() {
            const adiantamento1 = parseFloat(document.getElementById('adiantamento_1').value) || 0;
            const adiantamento2 = parseFloat(document.getElementById('adiantamento_2').value) || 0;
            const adiantamento3 = parseFloat(document.getElementById('adiantamento_3').value) || 0;
            
            const valorAntecipado = adiantamento1 + adiantamento2 + adiantamento3;
            document.getElementById('valor_antecipado').value = valorAntecipado.toFixed(2);
        }

        // Adicionar listeners para os campos de adiantamento
        document.getElementById('adiantamento_1').addEventListener('input', calcularValorAntecipado);
        document.getElementById('adiantamento_2').addEventListener('input', calcularValorAntecipado);
        document.getElementById('adiantamento_3').addEventListener('input', calcularValorAntecipado);

        // Configurar Flatpickr para os campos de data
        flatpickr("#data_ordem_servico", {
            dateFormat: "d/m/Y",
            locale: "pt"
        });

        flatpickr("#previsao_entrega", {
            dateFormat: "d/m/Y",
            locale: "pt"
        });

        // Validar formulário
        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <script>
    function excluirArquivo(id, nome) {
        if (confirm('Tem certeza que deseja excluir o arquivo "' + nome + '"?')) {
            fetch('/obras/includes/excluir_arquivo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'arquivo_id=' + id
            })
            .then(response => response.text())
            .then(data => {
                window.location.reload();
            })
            .catch(error => {
                console.error('Erro:', error);
            });
        }
    }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar datepicker
            flatpickr('.datepicker', {
                dateFormat: 'd/m/Y',
                locale: 'pt',
                allowInput: true
            });

            // Validação do Bootstrap
            var forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            // Cálculos automáticos
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

            // Eventos para cálculos
            ['total', 'adiantamento_1', 'adiantamento_2', 'adiantamento_3'].forEach(id => {
                document.getElementById(id).addEventListener('input', calcularValorFaltante);
            });
            calcularValorFaltante();
        });
    </script>
</body>
</html>
