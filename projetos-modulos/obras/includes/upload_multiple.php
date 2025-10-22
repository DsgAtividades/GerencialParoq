<?php
session_start();
require_once __DIR__ . '/../config/database.php';

function handleUpload($files, $tipo, $servico_id) {
    global $pdo;
    
    // Verificar se há arquivos enviados
    if (empty($files['name'][0])) {
        return ['success' => false, 'message' => 'Nenhum arquivo selecionado'];
    }
    
    $uploadDir = __DIR__ . '/../uploads/' . $servico_id . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $sucessos = 0;
    $erros = [];
    
    // Processar cada arquivo
    for ($i = 0; $i < count($files['name']); $i++) {
        $fileName = $files['name'][$i];
        $fileType = $files['type'][$i];
        $fileTmpName = $files['tmp_name'][$i];
        $fileError = $files['error'][$i];
        $fileSize = $files['size'][$i];
        
        // Verificar erro no upload
        if ($fileError !== UPLOAD_ERR_OK) {
            $erros[] = "Erro no upload do arquivo $fileName";
            continue;
        }
        
        // Verificar tipo de arquivo
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($fileType, $allowedTypes)) {
            $erros[] = "Tipo de arquivo não permitido para $fileName. Apenas PDF, JPEG e PNG são aceitos.";
            continue;
        }
        
        // Verificar tamanho (máximo 5MB)
        if ($fileSize > 5 * 1024 * 1024) {
            $erros[] = "Arquivo $fileName muito grande. Tamanho máximo: 5MB";
            continue;
        }
        
        // Gerar nome único para o arquivo
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $uniqueName = $tipo . '_' . uniqid() . '_' . date('Ymd_His') . '.' . $extension;
        $targetFile = $uploadDir . $uniqueName;
        
        // Mover arquivo
        if (move_uploaded_file($fileTmpName, $targetFile)) {
            try {
                // Inserir registro no banco
                $stmt = $pdo->prepare("INSERT INTO obras_servicos_arquivos (servico_id, tipo, nome_arquivo, caminho_arquivo, data_upload) VALUES (?, ?, ?, ?, NOW())");
                $caminhoRelativo = 'uploads/' . $servico_id . '/' . $uniqueName;
                
                if ($stmt->execute([$servico_id, $tipo, $fileName, $caminhoRelativo])) {
                    $sucessos++;
                } else {
                    $erros[] = "Erro ao salvar informações do arquivo $fileName no banco de dados";
                    unlink($targetFile); // Remove o arquivo se falhar ao salvar no banco
                }
            } catch (Exception $e) {
                $erros[] = "Erro ao processar arquivo $fileName: " . $e->getMessage();
                unlink($targetFile);
            }
        } else {
            $erros[] = "Falha ao mover arquivo $fileName para o destino";
        }
    }
    
    // Preparar resposta
    $response = [
        'success' => ($sucessos > 0),
        'message' => $sucessos . " arquivo(s) enviado(s) com sucesso" . 
                    (count($erros) > 0 ? ". Erros: " . implode("; ", $erros) : "")
    ];
    
    return $response;
}

// Processar requisição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['tipo']) || !isset($_POST['servico_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
        exit;
    }
    
    $tipo = $_POST['tipo'];
    $servico_id = (int)$_POST['servico_id'];
    
    // Verificar tipo válido
    $tiposValidos = ['comprovante_pagamento', 'nota_fiscal', 'ordem_servico'];
    if (!in_array($tipo, $tiposValidos)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tipo de arquivo inválido']);
        exit;
    }
    
    try {
        $response = handleUpload($_FILES['arquivos'], $tipo, $servico_id);
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // Resposta AJAX
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // Resposta normal
            $_SESSION[($response['success'] ? 'success_msg' : 'error_msg')] = $response['message'];
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Erro ao processar upload: ' . $e->getMessage()];
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($response);
        } else {
            $_SESSION['error_msg'] = $response['message'];
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }
    exit;
}
?>
