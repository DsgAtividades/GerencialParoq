<?php
/**
 * Endpoint: Upload de Foto do Membro
 * Método: POST
 * URL: /api/membros/upload-foto
 */

// Desabilitar exibição de erros para evitar HTML antes do JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Capturar qualquer output antes do JSON
ob_start();

try {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../utils/Response.php';
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao carregar dependências: ' . $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Limpar qualquer output capturado
ob_end_clean();

// Log inicial
error_log("=== INICIO UPLOAD FOTO ===");
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
error_log("_FILES: " . print_r($_FILES, true));
error_log("_POST: " . print_r($_POST, true));

try {
    // Verificar se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("Erro: Método não permitido - " . $_SERVER['REQUEST_METHOD']);
        Response::error('Método não permitido', 405);
    }
    
    // Verificar se arquivo foi enviado
    if (!isset($_FILES['foto'])) {
        error_log("Erro: Campo 'foto' não encontrado em _FILES");
        Response::error('Campo de arquivo não encontrado na requisição', 400);
    }
    
    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo excede o tamanho máximo permitido pelo PHP',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o tamanho máximo permitido pelo formulário',
            UPLOAD_ERR_PARTIAL => 'Arquivo foi enviado parcialmente',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta uma pasta temporária',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever arquivo no disco',
            UPLOAD_ERR_EXTENSION => 'Uma extensão PHP interrompeu o upload'
        ];
        $errorMsg = $errorMessages[$_FILES['foto']['error']] ?? 'Erro desconhecido no upload';
        Response::error('Erro no upload: ' . $errorMsg, 400);
    }
    
    $file = $_FILES['foto'];
    
    // Validar tipo de arquivo (apenas imagens)
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        Response::error('Tipo de arquivo não permitido. Apenas imagens (JPEG, PNG, GIF) são aceitas.', 400);
    }
    
    // Validar tamanho (máximo 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        Response::error('Arquivo muito grande. Tamanho máximo: 5MB', 400);
    }
    
    // Criar diretório de uploads se não existir
    $uploadDir = __DIR__ . '/../../uploads/fotos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Gerar nome único para o arquivo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nomeArquivo = uniqid('foto_', true) . '.' . $extension;
    $caminhoCompleto = $uploadDir . $nomeArquivo;
    
    // Mover arquivo para diretório de uploads
    error_log("Tentando mover arquivo de " . $file['tmp_name'] . " para " . $caminhoCompleto);
    if (!move_uploaded_file($file['tmp_name'], $caminhoCompleto)) {
        error_log("Erro ao mover arquivo. tmp_name existe: " . (file_exists($file['tmp_name']) ? 'sim' : 'não'));
        error_log("Diretório existe: " . (is_dir($uploadDir) ? 'sim' : 'não'));
        error_log("Diretório é gravável: " . (is_writable($uploadDir) ? 'sim' : 'não'));
        Response::error('Erro ao salvar arquivo', 500);
    }
    error_log("Arquivo movido com sucesso para: " . $caminhoCompleto);
    
    // Construir URL relativa baseada no DOCUMENT_ROOT
    // O upload está em: projetos-modulos/membros/uploads/fotos/
    
    // Caminho absoluto do arquivo de upload
    $uploadPath = $uploadDir . $nomeArquivo;
    
    // Obter DOCUMENT_ROOT (normalmente C:\xampp\htdocs)
    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $documentRoot = str_replace('\\', '/', $documentRoot);
    $uploadPath = str_replace('\\', '/', $uploadPath);
    
    // Calcular caminho relativo ao DOCUMENT_ROOT
    if (strpos($uploadPath, $documentRoot) === 0) {
        // O arquivo está dentro do DOCUMENT_ROOT
        $urlArquivo = str_replace($documentRoot, '', $uploadPath);
        // Garantir que começa com /
        if (strpos($urlArquivo, '/') !== 0) {
            $urlArquivo = '/' . $urlArquivo;
        }
    } else {
        // Fallback: usar caminho fixo baseado na estrutura conhecida
        $urlArquivo = '/PROJETOS/GerencialParoq/projetos-modulos/membros/uploads/fotos/' . $nomeArquivo;
    }
    
    error_log("URL do arquivo construída: " . $urlArquivo);
    error_log("DOCUMENT_ROOT: " . $documentRoot);
    error_log("Upload path: " . $uploadPath);
    
    // Se membro_id foi fornecido, salvar na tabela membros_anexos
    $membroId = isset($_POST['membro_id']) && !empty($_POST['membro_id']) ? trim($_POST['membro_id']) : null;
    
    $anexoId = null;
    
    if ($membroId) {
        // Se tem membro_id, salvar na tabela membros_anexos
        try {
            $db = new MembrosDatabase();
            
            // Gerar UUID para o anexo
            $anexoId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            
            // Salvar na tabela membros_anexos
            $stmt = $db->prepare("
                INSERT INTO membros_anexos 
                (id, entidade_tipo, entidade_id, nome_arquivo, tipo_arquivo, tamanho_bytes, url_arquivo, descricao) 
                VALUES (?, 'membro', ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $anexoId,
                $membroId,
                $file['name'],
                $mimeType,
                $file['size'],
                $urlArquivo,
                'Foto do membro'
            ]);
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            $errorCode = $e->getCode();
            
            error_log("Erro PDO ao salvar anexo: " . $errorMsg);
            error_log("SQL State: " . $errorCode);
            
            // Se for erro de foreign key, retornar erro específico
            if ($errorCode == '23000' || strpos($errorMsg, 'foreign key') !== false || strpos($errorMsg, 'Cannot add or update') !== false) {
                // Remover arquivo se o membro não existe
                if (file_exists($caminhoCompleto)) {
                    @unlink($caminhoCompleto);
                }
                Response::error('Membro não encontrado. Não foi possível associar a foto.', 404);
            }
            
            // Se for outro erro, logar e continuar (arquivo já foi salvo)
            error_log("Continuando sem salvar anexo no banco devido a erro: " . $errorMsg);
            $anexoId = null;
        }
    }
    
    // Retornar resposta (com ou sem anexo_id)
    $responseData = [
        'url' => $urlArquivo,
        'nome_arquivo' => $nomeArquivo
    ];
    
    if ($anexoId) {
        $responseData['anexo_id'] = $anexoId;
    }
    
    error_log("=== FIM UPLOAD FOTO - SUCESSO ===");
    error_log("Response data: " . print_r($responseData, true));
    
    // Garantir que não há output buffer ativo antes de chamar Response
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    Response::success($responseData);
    
} catch (PDOException $e) {
    error_log("=== ERRO PDO UPLOAD FOTO ===");
    error_log("Mensagem: " . $e->getMessage());
    error_log("Código: " . $e->getCode());
    error_log("Arquivo: " . $e->getFile() . ":" . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Tentar garantir que a resposta seja JSON
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Erro no banco de dados: ' . $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    error_log("=== ERRO GERAL UPLOAD FOTO ===");
    error_log("Mensagem: " . $e->getMessage());
    error_log("Código: " . $e->getCode());
    error_log("Arquivo: " . $e->getFile() . ":" . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Tentar garantir que a resposta seja JSON
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>

