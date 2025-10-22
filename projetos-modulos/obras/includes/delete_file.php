<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurar log de erros
ini_set('log_errors', 1);
ini_set('error_log', 'C:/wamp/www/obras/logs/delete_file.log');

function logError($message) {
    $logMessage = "[" . date('Y-m-d H:i:s') . "] delete_file.php: " . $message;
    error_log($logMessage);
    
    // Tentar criar o diretório de logs se não existir
    $logDir = 'C:/wamp/www/obras/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logFile = $logDir . '/delete_file.log';
    file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);
}

// Função para verificar se um arquivo pode ser excluído
function canDeleteFile($arquivo) {
    if (!file_exists($arquivo)) {
        logError("Arquivo não existe: $arquivo");
        return false;
    }
    
    if (!is_writable($arquivo)) {
        logError("Arquivo não tem permissão de escrita: $arquivo");
        return false;
    }
    
    return true;
}

// Função para excluir arquivo com verificações
function deleteFile($arquivo) {
    if (!canDeleteFile($arquivo)) {
        return false;
    }
    
    try {
        if (unlink($arquivo)) {
            logError("Arquivo excluído com sucesso: $arquivo");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['tipo']) || !isset($_POST['id']) || !isset($_POST['arquivo_id'])) {
        $response = [
            'success' => false,
            'message' => 'Parâmetros inválidos'
        ];
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode($response);
            exit;
        }
        
        $_SESSION['error_msg'] = $response['message'];
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    $tipo = $_POST['tipo'];
    $servicoId = (int)$_POST['id'];
    $arquivoId = (int)$_POST['arquivo_id'];

    try {
        // Iniciar transação
        $pdo->beginTransaction();
        logError("Iniciando transação para excluir arquivo");

        // Buscar informações do arquivo
        $stmt = $pdo->prepare("SELECT * FROM obras_servicos_arquivos WHERE id = ? AND servico_id = ? AND tipo = ?");
        $stmt->execute([$arquivoId, $servicoId, $tipo]);
        $arquivo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($arquivo) {
            $caminhoArquivo = __DIR__ . '/../' . $arquivo['caminho_arquivo'];

            // Verificar se o arquivo existe
            if (file_exists($caminhoArquivo)) {
                // Tentar excluir o arquivo
                if (unlink($caminhoArquivo)) {
                    logError("Arquivo excluído com sucesso: $caminhoArquivo");

                    // Excluir registro do banco de dados
                    $stmt = $pdo->prepare("DELETE FROM obras_servicos_arquivos WHERE id = ?");
                    $stmt->execute([$arquivoId]);
                    
                    // Confirmar transação
                    $pdo->commit();
                    logError("Transação finalizada com sucesso");
                    
                    $response = [
                        'success' => true,
                        'message' => 'Arquivo excluído com sucesso'
                    ];
                } else {
                    throw new Exception("Não foi possível excluir o arquivo");
                }
            } else {
                // Se o arquivo não existe fisicamente, apenas remover do banco
                $stmt = $pdo->prepare("DELETE FROM obras_servicos_arquivos WHERE id = ?");
                $stmt->execute([$arquivoId]);
                $pdo->commit();
                
                $response = [
                    'success' => true,
                    'message' => 'Registro removido com sucesso'
                ];
            }
        } else {
            throw new Exception("Arquivo não encontrado");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        logError("Transação revertida devido a erro");
        
        $response = [
            'success' => false,
            'message' => 'Erro ao excluir arquivo: ' . $e->getMessage()
        ];
        
        logError("Erro: " . $e->getMessage());
    }
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        if (!$response['success']) {
            http_response_code(500);
        }
        echo json_encode($response);
        exit;
    }
    
    $_SESSION[$response['success'] ? 'success_msg' : 'error_msg'] = $response['message'];
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Se chegou aqui, redirecionar de volta
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>
