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

/**
 * Verifica se o usuário tem uma permissão específica de produtos OU a permissão geral gerenciar_produtos
 * Isso permite que administradores com gerenciar_produtos tenham acesso a todas as ações
 */
function temPermissaoProduto($permissaoEspecifica) {
    require_once __DIR__ . '/verifica_permissao.php';
    
    // Se tem a permissão específica, retorna true
    if (temPermissao($permissaoEspecifica)) {
        return true;
    }
    
    // Se tem a permissão geral gerenciar_produtos, também retorna true
    if (temPermissao('gerenciar_produtos')) {
        return true;
    }
    
    return false;
}

/**
 * Verifica permissão de produto com redirect se não tiver acesso
 * Aceita permissão específica OU gerenciar_produtos
 */
function verificarPermissaoProduto($permissaoEspecifica) {
    require_once __DIR__ . '/verifica_permissao.php';
    
    // Verificar login primeiro
    if (!isset($_SESSION['usuario_id'])) {
        $_SESSION['alerta'] = [
            'tipo' => 'warning',
            'mensagem' => 'Por favor, faça login para continuar.'
        ];
        header("Location: login.php");
        exit;
    }
    
    // Se tem a permissão específica OU gerenciar_produtos, permite acesso
    if (temPermissao($permissaoEspecifica) || temPermissao('gerenciar_produtos')) {
        return;
    }
    
    // Se não tem nenhuma das permissões, redireciona
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensagem' => 'Você não tem permissão para acessar esta página.'
    ];
    header("Location: produtos.php");
    exit;
}