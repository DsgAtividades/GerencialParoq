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
