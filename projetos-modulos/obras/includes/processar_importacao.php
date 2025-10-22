<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function logError($message) {
    error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, __DIR__ . '/../logs/import_error.log');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo_servicos'])) {
    try {
        if ($_FILES['arquivo_servicos']['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => 'O arquivo excede o tamanho máximo permitido pelo servidor',
                UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o tamanho máximo permitido pelo formulário',
                UPLOAD_ERR_PARTIAL => 'O upload do arquivo foi feito parcialmente',
                UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi selecionado para upload',
                UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada no servidor',
                UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar arquivo no servidor',
                UPLOAD_ERR_EXTENSION => 'Uma extensão PHP interrompeu o upload do arquivo'
            ];
            $error_message = isset($upload_errors[$_FILES['arquivo_servicos']['error']]) 
                          ? $upload_errors[$_FILES['arquivo_servicos']['error']]
                          : 'Erro desconhecido no upload do arquivo';
            throw new Exception($error_message);
        }

        $arquivo = $_FILES['arquivo_servicos']['tmp_name'];
        if (!file_exists($arquivo)) {
            throw new Exception('Arquivo não encontrado');
        }

        $spreadsheet = IOFactory::load($arquivo);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        // Iniciar transação
        $pdo->beginTransaction();

        // Preparar a declaração SQL
        $sql = "INSERT INTO obras_servicos (descricao, responsavel, total, status, observacoes) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        // Processar cada linha (começando da linha 2, pois a linha 1 é o cabeçalho)
        $sucessos = 0;
        $erros = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            // Validar campos obrigatórios
            $descricao = trim($worksheet->getCell('A' . $row)->getValue());
            $responsavel = trim($worksheet->getCell('B' . $row)->getValue());
            $total = $worksheet->getCell('D' . $row)->getValue();
            $status = trim($worksheet->getCell('E' . $row)->getValue());
            $observacoes = trim($worksheet->getCell('H' . $row)->getValue());

            // Verificar campos obrigatórios
            $campos_invalidos = [];
            if (empty($descricao)) {
                $campos_invalidos[] = 'Descrição do Serviço';
            }
            if (empty($responsavel)) {
                $campos_invalidos[] = 'Responsável';
            }
            if (empty($total)) {
                $campos_invalidos[] = 'Valor Total';
            }
            if (empty($status)) {
                $campos_invalidos[] = 'Status';
            }

            if (!empty($campos_invalidos)) {
                $erros[] = "Linha $row: Os seguintes campos obrigatórios estão vazios: " . implode(", ", $campos_invalidos);
                continue;
            }

            // Obter valor total e observações
            $total = $worksheet->getCell('C' . $row)->getValue();
            $observacoes = trim($worksheet->getCell('E' . $row)->getValue());


            // Tratar o valor total
            if (is_string($total)) {
                $total = str_replace(['R$', ' ', '.'], '', $total);
                $total = str_replace(',', '.', $total);
            }
            $total = floatval($total);

            $status = trim($worksheet->getCell('D' . $row)->getValue());
            if (!in_array($status, ['Em Andamento', 'Concluído', 'Pendente', 'Cancelado'])) {
                $erros[] = "Status inválido na linha $row: $status";
                continue;
            }

            try {
                $result = $stmt->execute([
                    $descricao,
                    $responsavel,
                    $total,
                    $status,
                    $observacoes
                ]);

                if ($result) {
                    $sucessos++;
                } else {
                    $erros[] = "Erro ao inserir linha $row no banco de dados";
                    logError("Erro ao inserir linha $row: " . print_r($stmt->errorInfo(), true));
                }
            } catch (Exception $e) {
                $erros[] = "Erro na linha $row: " . $e->getMessage();
                logError("Exceção na linha $row: " . $e->getMessage());
            }
        }

        if (empty($erros)) {
            $pdo->commit();
            $_SESSION['success'] = "<strong>Importação realizada com sucesso!</strong><br>"
                                . "- Total de serviços importados: <strong>$sucessos</strong><br>"
                                . "- Os serviços já estão disponíveis para consulta.";
        } else {
            $pdo->rollBack();
            $mensagem = "<strong>Encontramos alguns problemas na importação:</strong><br><br>";
            $mensagem .= "<ul class='list-unstyled'>";
            foreach ($erros as $erro) {
                $mensagem .= "<li><i class='fas fa-times-circle text-danger'></i> {$erro}</li>";
            }
            $mensagem .= "</ul><br>";
            $mensagem .= "<strong>Como resolver:</strong><br>";
            $mensagem .= "<ul>";
            $mensagem .= "<li>Verifique se todos os campos obrigatórios estão preenchidos:</li>";
            $mensagem .= "<ul>";
            $mensagem .= "<li>Descrição do Serviço</li>";
            $mensagem .= "<li>Responsável</li>";
            $mensagem .= "<li>Valor Total</li>";
            $mensagem .= "<li>Status (Em Andamento, Concluído, Pendente ou Cancelado)</li>";
            $mensagem .= "</ul>";

            $mensagem .= "<li>Verifique se os valores monetários estão formatados corretamente</li>";
            $mensagem .= "</ul>";
            $mensagem .= "Após corrigir os problemas, tente importar novamente.";
            
            $_SESSION['error'] = $mensagem;
            logError("Erros na importação: " . implode(", ", $erros));
        }

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_message = "Erro ao processar o arquivo: " . $e->getMessage();
        $_SESSION['error'] = $error_message;
        logError($error_message);
    }
}

header('Location: ../index.php?page=relatorios');
exit;
