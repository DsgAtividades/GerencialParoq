<?php
function exibirAlerta($mensagem, $tipo = 'success') {
    $_SESSION['mensagem'] = [
        'texto' => $mensagem,
        'tipo' => $tipo
    ];
}

function mostrarAlerta() {
    if (isset($_SESSION['mensagem']) && isset($_SESSION['mensagem']["texto"])) {
        $tipo = $_SESSION['mensagem']['tipo'];
        $texto = $_SESSION['mensagem']['texto'];
        echo "<div class='alert alert-{$tipo} alert-dismissible fade show' role='alert'>
                {$texto}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
        unset($_SESSION['mensagem']);
    }
}

function escapar($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Normaliza o ícone para aceitar tanto HTML completo quanto apenas o nome
 * Aceita formatos:
 * - <i class="bi bi-cup-straw"></i> -> retorna "cup-straw"
 * - bi bi-cup-straw -> retorna "cup-straw"
 * - bi-cup-straw -> retorna "cup-straw"
 * - cup-straw -> retorna "cup-straw"
 */
function normalizarIcone($icone) {
    if (empty($icone)) {
        return '';
    }
    
    $icone = trim($icone);
    
    // Se contém HTML completo <i class="bi bi-..."></i>
    if (preg_match('/<i\s+class=["\']bi\s+bi-([^"\']+)["\']/i', $icone, $matches)) {
        return $matches[1];
    }
    
    // Se contém apenas as classes "bi bi-..."
    if (preg_match('/bi\s+bi-([^\s]+)/i', $icone, $matches)) {
        return $matches[1];
    }
    
    // Se contém "bi-..." no início
    if (preg_match('/^bi-([^\s]+)/i', $icone, $matches)) {
        return $matches[1];
    }
    
    // Se já está no formato correto (apenas o nome), retorna como está
    return $icone;
}