<?php
function handleFileUpload($file, $obra_id, $tipo) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'O arquivo excede o tamanho máximo permitido pelo PHP.',
            UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o tamanho máximo permitido pelo formulário.',
            UPLOAD_ERR_PARTIAL => 'O upload do arquivo foi feito parcialmente.',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
            UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária ausente.',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar arquivo em disco.',
            UPLOAD_ERR_EXTENSION => 'Uma extensão PHP interrompeu o upload do arquivo.'
        ];
        throw new Exception($errors[$file['error']] ?? 'Erro desconhecido no upload.');
    }

    // Validar tipo do arquivo usando finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
    
    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception('Tipo de arquivo não permitido. Apenas PDF, JPEG e PNG são aceitos.');
    }

    // Validar tamanho (máximo 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB em bytes
    if ($file['size'] > $max_size) {
        throw new Exception('Arquivo muito grande. Tamanho máximo permitido: 5MB');
    }

    // Criar diretório de uploads se não existir
    $base_upload_dir = __DIR__ . '/../uploads';
    if (!file_exists($base_upload_dir)) {
        if (!mkdir($base_upload_dir, 0777, true)) {
            throw new Exception('Erro ao criar diretório de uploads.');
        }
        // Adicionar .htaccess para proteção
        file_put_contents($base_upload_dir . '/.htaccess', "Options -Indexes\nRequire all granted");
        // Dar permissões de escrita
        chmod($base_upload_dir, 0777);
    }

    // Criar diretório específico para a obra
    $upload_dir = $base_upload_dir . '/' . $obra_id;
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            throw new Exception('Erro ao criar diretório para a obra.');
        }
        // Dar permissões de escrita
        chmod($upload_dir, 0777);
    }

    // Gerar nome único para o arquivo
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $tipo . '_' . uniqid() . '_' . date('Ymd_His') . '.' . $extension;
    $filepath = $upload_dir . '/' . $filename;

    // Remover arquivo antigo se existir
    if (isset($GLOBALS['pdo'])) {
        $column = $tipo === 'comprovante' ? 'comprovante_pagamento' : 'nota_fiscal';
        $stmt = $GLOBALS['pdo']->prepare("SELECT $column FROM obras_servicos WHERE id = ?");
        $stmt->execute([$obra_id]);
        $old_file = $stmt->fetchColumn();
        
        if ($old_file && file_exists(__DIR__ . '/../' . $old_file)) {
            unlink(__DIR__ . '/../' . $old_file);
        }
    }

    // Mover arquivo
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Erro ao salvar o arquivo. Verifique as permissões do diretório.');
    }

    // Retornar caminho relativo para salvar no banco
    $relative_path = 'uploads/' . $obra_id . '/' . $filename;
    error_log('Salvando arquivo em: ' . $relative_path);
    return $relative_path;
}
?>
